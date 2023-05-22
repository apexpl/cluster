<?php
declare(strict_types = 1);

namespace Apex\Cluster\Router;

use Apex\Cluster\Router\ParamChecker;
use Apex\Cluster\Interfaces\MessageRequestInterface;
use Apex\Cluster\Exceptions\{ClusterExchangeTypeOverlayException, ClusterDuplicateRoutingAliasException};


/**
 * Handles the routing map for a single server instance.
 */
class InstanceMap
{

    // Properties
    private array $queues = [];
    private array $routes = [];
    private array $alias_map = [];


    /**
     * Constructor
     */
    public function __construct(
        private string $instance_name
    ) {

    }

    /**
     * Declare queue
     */
    public function declareQueue(string $queue, string $exchange = '', array $routes = []):void
    {
        $this->queues[$queue] = [
            'exchange' => $exchange, 
            'routes' => $routes
        ];

    }

    /**
     * Add route
     */
    public function addRoute(string $queue, string|int $id, array $route_vars):void
    {

        // Declare queue, if needed
        if (!isset($this->queues[$queue])) { 
            $this->declareQueue($queue, $route_vars['type']);
        }

        // Add routing key to queue
        $key = $route_vars['routing_key'];
        $this->queues[$queue]['routes'][] = $key;

        // Add route
        if (!isset($this->routes[$key])) { 
            $this->routes[$key] = [];
        }
        $this->routes[$key][] = $route_vars;

        // Define exchange type in queue
        if ($this->queues[$queue]['exchange'] != '' && $this->queues[$queue]['exchange'] != $route_vars['type']) { 
            throw new ClusterExchangeTypeOverlayException("Unable to define queue '$queue' as exchange type '$route_vars[type]' as it is already defind as exchange type '" . $this->queues[$queue]['exchange'] . "'.");
        }
        $this->queues[$queue]['exchange'] = $route_vars['type'];

        // Add to alias map if msg_type is queue
        if ($route_vars['type'] == 'queue') { 
            $this->alias_map[$route_vars['queue_name']] = $queue;
        }
    }

    /**
     * Get all routes
     */
    public function getAllRoutes():array
    {
        return $this->routes;
    }

    /**
     * Get all exchanges
     */
    public function getAllExchanges():array
    {

        // Go through queues
        $exchanges = [];
        foreach ($this->queues as $id => $vars) { 
            $type = $vars['exchange'];

            if (!isset($exchanges[$type])) { 
                $exchanges[$type] = [];
            }
            $exchanges[$type][$id] = $vars['routes'];
        }

        // Return
        return $exchanges;

    }

    /**
     * Query routes, return php classes to execute
     */
    public function queryRoutes(MessageRequestInterface $msg):array
    {

        // Initialize
        $php_classes = [];
        $parts = explode('.', $msg->getRoutingKey());
        $msg_type = $msg->getType();

        // GO through all routes
        foreach ($this->routes as $key => $routes) {

            // Set variables
            $x = 0;
            $ok = true;
            $chk = explode('.', $key);

            // Check routing key
            foreach ($chk as $c) { 
                if ($c == '*' || $c == $parts[$x++]) { 
                    continue; 
                }
                $ok = false;
            }
            if ($ok === false) { continue; }

            // Go through routes, check params
            foreach ($routes as $id => $vars) { 

                // Check msg type
            if ($msg_type != $vars['type']) { 
                continue;
            }

                // Check params
                if (count($vars['params']) > 0 && !ParamChecker::check($vars['params'], $msg)) { 
                    continue;
                }
                $alias = $vars['alias'];

                // Add php class
                if (isset($php_classes[$alias]) && $key == '*.*.*') { 
                    continue;
                } elseif (isset($php_classes[$alias])) { 
                    throw new ClusterDuplicateRoutingAliasException("A duplicate routing alias of $alias already exists for the routing key $routing_key");
                }
                $php_classes[$alias] = $vars['php_class'];
            }
        }

        // Return
        return $php_classes;

    }

    /**
     * Check alias
     */
    public function checkAlias(string $queue_name):?string
    {
        return isset($this->alias_map[$queue_name]) ? $this->alias_map[$queue_name] : null;
    }
}



