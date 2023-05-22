<?php
declare(strict_types = 1);

namespace Apex\Cluster;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Router\InstanceMap;
use Apex\Cluster\Message\MessageResponse;
use Apex\Cluster\Interfaces\{BrokerInterface, ReceiverInterface, MessageRequestInterface, FeHandlerInterface};
use Symfony\Component\String\UnicodeString;

/**
 * Handles the receiving of all messages from the listeners, processes the 
 * message and returns MessageResponse object as back to message broker.
 */
class Receiver implements ReceiverInterface
{

    /**
     * Construct
     */
    public function __construct(
        private InstanceMap $map, 
        private $prepare_msg_handler = null 
    ) {

    }

    /**
     * Receive message
     */
    public function receive($payload)
    {

        // Grab some things from the container
        $cluster = Di::get(Cluster::class);
        $broker = Di::get(BrokerInterface::class);

        // Extract body
        $msg = $broker->extractMessage($payload);
        list($package, $class, $method) = explode('.', $msg->getRoutingKey());

        // Invoke message preparation handler, if needed
        if ($this->prepare_msg_handler !== null) { 
            call_user_func($this->prepare_msg_handler, $msg);
        } elseif (Di::has('cluster.prepare_msg_handler')) { 
            Di::call('cluster.prepare_msg_handler', ['msg' => $msg]);
        }

        // Check for custom routing
        if (Di::has('cluster.custom_router')) { 
            $php_classes = Di::call('cluster.custom_router', ['msg' => $msg]);
        } else { 
            $php_classes = $this->map->queryRoutes($msg);
        }

        // Initialize response message, add log
        $response = new MessageResponse($msg);
        $fe_handler = Di::make(FeHandlerInterface::class);
        $cluster->addLog("Received message of " . $msg->getType() . " to routing key: " . $msg->getRoutingKey());

        // GO through php classes
        foreach ($php_classes as $alias => $class_name) { 

            // Check for auto-routing
            if (preg_match("/~(.+)~/", $class_name)) { 
                $class_name = $this->getAutoRoutingClass($msg, $class_name);
            }

            // Execute method
            if (!$data = $this->executeMethod($class_name, $method, $msg, $fe_handler)) { 
                continue;
            }
            $response->addResponse($alias, $data);

            // Add log
            $cluster->addLog("Executed method " . $class_name . "::"  . $method . " for routing key " . $msg->getRoutingKey());
            $response->addCalled($alias, $class_name);
        }

        // Send acknowledgement, if needed
        if ($msg->getType() != 'rpc') { 
            $broker->ack($payload);
        }

        // Reply, if RPC call
        if ($msg->getType() == 'rpc') {
            $response->setFeHandler($fe_handler);
            return $broker->reply($response, $payload);
        }

    }

    /**
     * Execute php method of listener
     */
    protected function executeMethod(string $class_name, string $method, MessageRequestInterface $msg, ?FeHandlerInterface $fe_handler = null):mixed
    {

        // Check if class exists
        if (class_exists($class_name)) { 
            $consumer = Di::make($class_name);
        } else {
            return false;
        }

        // Execute method, and return
        if (!method_exists($consumer, $method)) { 
            return null;
        } else { 
            return $consumer->$method($msg, $fe_handler);
        }
    }

    /**
     * Get PHP class for auto-routing 
     */
    protected function getAutoRoutingClass(MessageRequestInterface $msg, string $php_class):string
    {

        // Initialize
        list($package, $module, $method) = explode('.', $msg->getRoutingKey());

        // Initialize words
        $words = [
            'package' => $package, 
            'module' => $module, 
            'method' => $method, 
            'msg_type' => $msg->getType()
        ];

        // Create merge vars
        $vars = [];
        foreach ($words as $key => $value) { 
            $word = new UnicodeString($value);
            $vars['~' . $key . '~'] = $value;
            $vars['~' . $key . '.lower~'] = strtolower($value);
            $vars['~' . $key . '.camel~'] = $word->camel();
            $vars['~' . $key . '.title~'] = $word->camel()->title();
        }

        // Return
        return strtr($php_class, $vars);
    }

}


