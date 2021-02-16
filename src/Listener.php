<?php
declare(strict_types = 1);

namespace Apex\Cluster;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Router\{Validator, InstanceMap};
use Apex\Cluster\Interfaces\{MessageRequestInterface, BrokerInterface, ReceiverInterface};
use Apex\Cluster\Exceptions\{ClusterInvalidArgumentException, ClusterZeroRoutesException};


/**
 * Listener class.
 */
class Listener
{

    // Properties
    private array $exchanges;

    /**
     * Constructor
     */
    public function __construct(string $instance_name = 'app1')
    {

        // Instantiate Cluster, if needed
        if (!Di::has(Cluster::class)) { 
            $cluster = new Cluster($instance_name);
        }
    }

    /**
     * Listen
     */
    public function listen(bool $screen_logging = true, int $max_msg = 1, callable $prepare_msg_handler = null):void
    {

        // Declare exchange and queues
        if ($prepare_msg_handler === null) { 
            $broker = $this->declare($screen_logging, $max_msg);
        } else { 
            $broker = $this->declare($screen_logging, $max_msg, $prepare_msg_handler);
        }
        $cluster = Di::get(Cluster::class);

        // Start listening
        $cluster->addLog("Listening on " . $cluster->instance_name . " for messages to " . implode(", ", array_keys($this->exchanges))) . " ...";
        $broker->wait();

        // Close connection
        $broker->closeChannel();
        $cluster->addLog("Shutting down...");
    }

    /**
     * Declare exchange and queues
     */
    public function declare(bool $screen_logging = true, int $max_msg = 1, callable $prepare_msg_handler = null):BrokerInterface
    {

        // Get cluster
        $cluster = Di::get(Cluster::class);
        $cluster->setScreenLogging($screen_logging);
        $in_name = $cluster->instance_name;

        // Check for 'instances' configuration
        if (isset($cluster->instances[$in_name]) && isset($cluster->instances[$in_name]['max_msg'])) { 
            $max_msg = (int) $cluster->instances[$in_name]['max_msg'];
        }

        // Get routes table
        $map = $cluster->getRoutesMap($cluster->instance_name);
        if (count($map->getAllRoutes()) == 0) { 
            throw new ClusterZeroRoutesException("There are no routes configured to listen to msg_type $msg_type");
        }

        // Get receiver
        $receiver = Di::make(ReceiverInterface::class, ['map' => $map, 'prepare_msg_handler' => $prepare_msg_handler]);

        // Open connection
        $broker = Di::get(BrokerInterface::class);
        $broker->openChannel();

        // Declare exchanges
        $this->exchanges = $map->getAllExchanges();
        foreach ($this->exchanges as $msg_type => $queues) { 

            // Set variables
            $ex_name = 'cluster.ex.' . $msg_type;
            $defs = $cluster->exchange_defaults[$msg_type];

            // Declare exchange
            $broker->declareExchange($ex_name, $defs['type'], $defs['durable'], $defs['auto_delete']);
            $cluster->addLog("Declared exchange $msg_type");

            // Declare queues and bindings
            foreach ($queues as $queue_id => $routes) { 
                $name = 'cluster.' . $queue_id;
                $broker->declareQueue($name, $defs['durable'], false, $defs['auto_delete']); 
                $cluster->addLog("Declared queue $queue_id for exchange $msg_type");

                // Bind queue to routing keys
                foreach ($routes as $routing_key) { 
                    $broker->bindQueue($name, $ex_name, $routing_key);
                    $cluster->addLog("Binding queue $queue_id to exchange $msg_type with routing key $routing_key");
                }   

                // Consume queue
                $broker->consume($name, $defs['no_ack'], false, [$receiver, 'receive'], $max_msg);
            }
        }
        Di::set(BrokerInterface::class, $broker);

        // Return
        return $broker;
    }

}


