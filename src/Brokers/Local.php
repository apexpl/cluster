<?php
declare(strict_types = 1);

namespace Apex\Cluster\Brokers;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Interfaces\{BrokerInterface, MessageRequestInterface, MessageResponseInterface, ReceiverInterface};
use Apex\Cluster\Exceptions\ClusterZeroRoutesException;


/**
 * Local message broker that routes all messages locally to correct class / method 
 * without going through RabbitMQ.  Allows applications to be developed on 
 * one server instance, but with logic and functionality of horizontal scaling 
 * built-in and ready for use.
 */
class Local implements BrokerInterface
{

    /**
     * Constructor
     */
    public function __construct(string $host = '', int $port = 0, string $user = '', string $password = '') 
    {

        // Get cluster
        $cluster = Di::get(Cluster::class);

        // Get routes table
        $map = $cluster->getRoutesMap($cluster->instance_name);
        if (count($map->getAllRoutes()) == 0) { 
            //throw new ClusterZeroRoutesException("There are no routes configured to listen to msg_type $msg_type");
        }
        $this->receiver = Di::make(ReceiverInterface::class, ['map' => $map]);

    }

    /**
     * Publish message
     */
    public function publish(MessageRequestInterface $msg):?MessageResponseInterface
    {
        return $this->receiver->receive($msg);
    }

    /**
     * Extract MessageRequestInterface object from body of message received by listener / consumer.
     */
    public function extractMessage($msg):MessageRequestInterface { 
        return $msg; 
    }

    /**
     * Reply to RPC call.
     */
    public function reply($data, $msg) { 
        return $data; 
    }

    /**
     * All methods below this line are nulled, not required for this 
     * class and only present to satisfy the requirements of the BrokerInterface.
     */
    public function openChannel() { }
    public function closeChannel():void { }
    public function declareExchange(string $name, string $type, bool $durable, bool $auto_delete):void { }
    public function deleteExchange(string $name):void { }
    public function declareQueue(string $name = '', bool $durable = false, bool $exclusive = false, bool $auto_delete = true):string { return $name; }
    public function bindQueue(string $queue, string $exchange, string $routing_key):void { }
    public function unbindQueue(string $queue, string $exchange, string $routing_key = ''):void { }
    public function deleteQueue(string $name):void { }
    public function purgeQueue(string $name):void { }
    public function consume(string $queue = '', bool $no_ack = false, bool $exclusive = false, callable $callback = null, int $max_msg = 1):void { }
    public function wait(bool $is_rpc_dispatcher = false, int $timeout = 0):void { }
    public function ack($msg):void { }
    public function nack($msg):void { }
    public function fetch (string $queue, bool $no_ack = false):?MessageRequestInterface { return null; }


}

