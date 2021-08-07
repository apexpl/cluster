<?php
declare(strict_types = 1);

namespace Apex\Cluster\Brokers;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Interfaces\{BrokerInterface, MessageRequestInterface, MessageResponseInterface};
use Apex\Cluster\Exceptions\ClusterTimeoutException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\{AMQPConnectionClosedException, AMQPTimeoutException};

/**
 * Handles all message broker functionality for 
 * RabbitMQ via the php-amqplib package.
 */
class RabbitMQ implements BrokerInterface
{

    // Properties
    private ?AMQPStreamConnection $conn = null;
        private ?AMQPChannel $channel = null;

    // Properties for RPC calls
    private mixed $response = null;
    private string $correlation_id;
    private string $callback_queue = '';


    /**
     * Constructor
     */
    public function __construct(
        private string $host = 'localhost', 
        private int $port = 5672, 
        private string $username = 'guest', 
        private string $password = 'guest'
    ) {

        // Set defaults, if needed
        if ($host == '') { $this->host = 'localhost'; }
        if ($port == 0) { $this->port = 5672; }
        if ($username == '') { $this->username = 'guest'; }
        if ($this->password == '') { $this->password = 'guest'; }

    }

    /**
     * Open connection to RabbitMQ
    */
    public function openChannel():object
    {

        // Check if already connected
        if ($this->channel !== null) { 
            return $this->channel;
        }

        // Try to connect
        try {
            $this->conn = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password);
        } catch (AMQPConnectionClosedException $e) { 
            throw new Exception('Unable to connect to RabbitMQ');
        }

        // Return
        $this->channel = $this->conn->channel();
        return $this->channel;
    }


    /**
     * Close connection
     */
    public function closeChannel():void
    {
        $this->channel->close();
        $this->conn->close();
    }


    /**
     * Declare an exchange.
     */
    public function declareExchange(string $name, string $type = 'direct', bool $durable = false, bool $auto_delete = true):void
    {
        $this->channel->exchange_declare($name, $type, false, $durable, $auto_delete);
    }

    /**
     * Delete exchange
     */
    public function deleteExchange(string $name):void
    {
        $this->channel->exchange_delete($name);
    }

    /**
     * Declare queue
     */ 
    public function declareQueue(string $name = '', bool $durable = false, bool $exclusive = false, bool $auto_delete = true):string
    {
        list($queue, $err) = $this->channel->queue_declare($name, false, $durable, $exclusive, $auto_delete);
        return $queue; 
    }

    /**
     * Bind queue to an exchange.
     */
    public function bindQueue(string $queue, string $exchange, string $routing_key = ''):void
    {
        $this->channel->queue_bind($queue, $exchange, $routing_key);
    }


    /**
     * Unbind queue from an exchange.
     */
    public function unbindQueue(string $queue, string $exchange, string $routing_key = ''):void
    {
        $this->channel->queue_unbind($queue, $exchange, $routing_key);
    }

    /**
     * Delete queue
     */
    public function deleteQueue(string $name):void
    {
        $this->channel->queue_delete($name);
    }

    /**
     * Purge queue
     */
    public function purgeQueue(string $name):void
    {
        $this->channel->queue_purge($name);
    }


    /**
     * Consume
     */
    public function consume(string $queue, bool $no_ack = false, bool $exclusive = false, callable $callback = null, int $max_msg = 1):void
    {

        // One message at a time, if needed
        if ($no_ack === false) {
            $this->channel->basic_qos(null, $max_msg, null);
        }

        // Consume
        $this->channel->basic_consume($queue, '', false, $no_ack, $exclusive, false, $callback);
    }

    /**
     * Wait
     */
    public function wait(bool $is_rpc_dispatcher = false, int $timeout = 5):void
    {

    // If RPC dispatcher
        if ($is_rpc_dispatcher === true) { 
            $this->channel->wait(false, false, $timeout);
            return;
    }

        // Wait for connections
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }

    }

    /**
     * Publish message
     */
    public function publish(MessageRequestInterface $msg):?MessageResponseInterface
    {

        // Get msg_type
        $msg_type = $msg->getType();
        $routing_key = $msg->getRoutingKey();
    $ex_name = 'cluster.ex.' . $msg_type;

        // Declare callback queue, if RPC
        if ($msg_type == 'rpc') { 
            $this->callback_queue = $this->declareQueue('', false, true, true);
            $this->consume($this->callback_queue, false, false, [$this, 'onResponse']);
        }
        $this->correlation_id = uniqid();

        // Define message properties
        $msg_properties = [
            'delivery_mode' => $msg_type == 'ack_only' ? 2 : 1, 
            'content_type' => 'text/plain', 
            'timestamp' => time(),
            'type' => $msg_type, 
            'app_id' => 'Apex/Cluster',  
            'cluster_id' => $msg->getInstanceName(), 
            'correlation_id' => $this->correlation_id
        ];

        // Add reply-to if RPC call
        if ($msg_type == 'rpc') { 
            $msg_properties['reply_to'] = $this->callback_queue;
        }

        // Get message
        $payload = new AMQPMessage(
            serialize($msg), 
            $msg_properties
        );

        // Publish message
        $this->channel->basic_publish($payload, $ex_name, $routing_key);
        if ($msg_type != 'rpc') { 
            return null;
        }

        // Wait for response, if RPC call
        try { 
            $secs = Di::get('cluster.timeout_seconds') ?? 5;
            $this->wait(true, (int) $secs);
        } catch (AMQPTimeoutException $e) { 

            // Close connection
            $this->closeChannel();

            // Add log
            $cluster = Di::get(Cluster::class);
            $cluster->addLog("RPC timeout with routing key: " . $msg->getRoutingKey(), 'warning');

            if (Di::has('cluster.timeout_handler')) {
                Di::call('cluster.timeout_handler', ['msg' => $msg]);
            } else { 
                throw new ClusterTimeoutException("The RPC call has timed out, and no RPC server is currently reachable.  please try again later.");
            }
        }

        // Return
        return $this->response;

    }

    /**
     * onResponse
     */
    public function onResponse($response):void
    {

        // Check correlation id
        if ($response->get('correlation_id') != $this->correlation_id) {
            return;
        }

        // Get response
        $this->response = unserialize($response->body);
    }

    /**
     * Extract MessageRequestInterface object from body of message received by listener / consumer.
     */
    public function extractMessage($msg):MessageRequestInterface
    {
        return unserialize($msg->body);
    }

    /**
     * Ack
     */
    public function ack($msg):void 
    {
        $msg->ack();
    }

    /**
     * Nack
     */
    public function nack($msg):void
    {
        $msg->nack();
    }

    /**
     * Send reply for PRC call.
     */
    public function reply($data, $msg):void
    {

        // Define new message
        $payload = new AMQPMessage(
            serialize($data), 
            array('correlation_id' => $msg->get('correlation_id'))
        );

        // Send reply
        $msg->delivery_info['channel']->basic_publish($payload, '', $msg->get('reply_to'));
    }

    /**
     * Fetch next message in queue.
     */
    public function fetch (string $queue, bool $no_ack = false):?MessageRequestInterface
    {

        // Get message
        if (!$res = $this->channel->basic_get($queue, $no_ack)) { 
            return null;
        }
        $this->ack($res);

        // Extract, and return
        return $this->extractMessage($res);
    }

}

