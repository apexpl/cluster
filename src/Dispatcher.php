<?php
declare(strict_types = 1);

namespace Apex\Cluster;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Interfaces\{MessageRequestInterface, MessageResponseInterface, BrokerInterface};


/**
 * Handles dispatching of messages.
 */
class Dispatcher
{


    /**
     * Constructor
     */
    public function __construct(string $instance_name = 'web1')
    {

        // Instantiate Cluster, if needed
        if (!Di::has(Cluster::class)) {
            $this->cluster = new Cluster($instance_name);
        } else { 
            $this->cluster = Di::get(Cluster::class);
        }

        // Get broker, and open connection
        $this->broker = Di::get(BrokerInterface::class);
        $this->broker->openChannel();

    }

    /**
     * Dispatch a message
     */
    public function dispatch(MessageRequestInterface $msg, string $msg_type = 'rpc', callable $fe_handler_callback = null):?MessageResponseInterface
    {

        // Set instance name on message, add log
        $msg->setInstanceName($this->cluster->instance_name);
        $msg->setType($msg_type);
        $this->cluster->addLog("Dispatching message on " . $msg->getType() . " to routing key: " . $msg->getRoutingKey());

        // Send message
        $response = $this->broker->publish($msg);

        // Execute fe handler callback, if needed
        if ($msg->getType() == 'rpc' && $fe_handler_callback !== null) { 
            $fe_handler = $response->getFeHandler();
            call_user_func($fe_handler_callback, $fe_handler);
        } elseif ($msg->getType() == 'rpc' && Di::has('fe_handler_callback')) { 
            $fe_handler = $response->getFeHandler();
            Di::call('fe_handler_callback', ['handler' => $fe_handler]);
        }

        // Return
    return $response;

    }

    /**
     * Destructor
     */
    public function __destruct()
    {

        // Close connection
        if (isset($this->broker) && $this->broker instanceof BrokerInterface) { 
            $this->broker->closeChannel();
        }

    }

}



