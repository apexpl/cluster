<?php
declare(strict_types = 1);

namespace Apex\Cluster\Router;

use Apex\Container\Di;
use Apex\Cluster\Router\{Mapper, Validator};
use Apex\Cluster\Router\Loaders\{RedisLoader, YamlLoader};
use redis;

/**
 * Handles the routing for the cluster package.
 */
class Router
{

    // Properties
    public array $routes = [];
    public array $instances = [];


    /**
     * Load routes
     */
    public function loadRoutes(string $yaml_file = ''):void
    {

        // Load from redis
        $redis = Di::get(redis::class);
        if ($redis instanceof redis && $routes_serialized = $redis->get('cluster:routes')) { 
            $this->routes = unserialize($routes_serialized);
            return;
        }

        // Load Yaml file 
        $loader = new YamlLoader();
        $loader->loadFile($yaml_file);
    }

    /**
     * Save to redis
     */
    public function saveRedis(redis $redis, bool $restart_listeners = false):void
    {
        $client = new RedisLoader();
        $client->save($redis, $this->routes);
    }

    /**
     * Add route
     */
    public function addRoute(
        string $routing_key, 
        string $php_class,
        string $alias = 'default',  
        string $msg_type = 'rpc', 
        string $queue_name = '', 
        array $params = [], 
        array $instances = [], 
        bool $is_config = false
    ):string { 

        // Validate routing key, php class and msg type
        Validator::validateRoutingKey($routing_key);
        Validator::validatePhpClassName($php_class);
        Validator::validateMsgType($msg_type);

        // Format routing key
        $routing_key = $this->formatRoutingKey($routing_key);

        // Check instances
        if (count($instances) == 0) { 
            $instances[] = 'all';
        }
        $routing_id = crc32($msg_type . ':' . $routing_key . ':' . implode(',', $instances) . ':' . serialize($params));

        // Set route vars
        $vars = [ 
            'type' => $msg_type,  
            'routing_key' => $routing_key, 
            'alias' => $alias, 
            'php_class' => $php_class, 
            'instances' => $instances,
            'params' => $params, 
            'routing_id' => $routing_id, 
            'queue_name' => $queue_name, 
            'is_config' => $is_config
        ];

        // Add to routes
        $id = uniqid();
        $this->routes[$id] = $vars;

        // Return
        return $id;
    }

    /**
     * Delete route
     */
    public function deleteRoutes(string $routing_key = '', string $php_class = '', string $msg_type = '', string $instance = ''):int
    {

        // Go through routes
    $count = 0;
        foreach ($this->routes as $id => $vars) { 

            // Skip, if needed
            if (($routing_key != '' && $routing_key != $vars['routing_key']) || 
                ($php_class != '' && $php_class != $vars['php_class']) || 
                ($msg_type != '' && $msg_type != $vars['type']) || 
                ($instance != '' && !in_array($instance, $vars['instances']))
            ) { continue; }

            // Delete route
            unset($this->routes[$id]);
            $count++;
        }

        // Return count
        return $count;
    }

    /**
     * Delete route by id
     */
    public function deleteRouteId(string $id):bool
    {
        $ok = isset($this->routes[$id]) ? true : false;
        unset($this->routes[$id]);
        return $ok;
    }

    /**
     * Purge routes
     */
    public function purgeRoutes():void
    {
        $this->routes = [];
    }

    /**
     * Create instance routes map
     */
    public function GetRoutesMap(string $instance_name):?InstanceMap
    {

        // Create instance map
        $mapper = new Mapper();
        $map = $mapper->createInstanceMap($instance_name, $this->routes);

        // Return
        return $map;
    }

    /**
     * Add instance
     */
    public function addInstance(string $name, array $props):void
    {
        $this->instances[$name] = $props;
    }

    /**
     * Format routing key
     */
    private function formatRoutingKey(string $routing_key):string
    {

        // Check for all
        if ($routing_key == 'all') { 
            return '*.*.*';
        }

        // Format, ensure three segments
        $parts = explode('.', $routing_key);
        if (count($parts) < 3) { 
            do { 
                $parts[] = '*';
            } while (count($parts) < 3);
    }

        // Return
        return implode('.', $parts);
    }

}

