<?php
declare(strict_types = 1);

namespace Apex\Cluster\Router;

use Apex\Cluster\Router\InstanceMap;
use Apex\Cluster\Exceptions\ClusterZeroRoutesException;


/**
 * Maps array of routes into appropriate queue and instance groupings.
 */
class Mapper
{

    // Properties
    private array $routes_map = [];
    private array $instance_map = [];
    private array $queues = [];

    /**
     * Create instance map
     */
    public function createInstanceMap(string $instance_name, array $routes):InstanceMap
    {

        // Map routes
        $this->mapRoutes($routes);

        // Instantiate new map
        $map = new InstanceMap($instance_name);

        // Get instance queues
        $in_queues = $this->instance_map[$instance_name] ?? $this->instance_map['all'];
        if (count($in_queues) == 0) { 
            throw new ClusterZeroRoutesException("No queues defined for the instance '$instance_name', and no routes to install 'all' exist.");
        }

        // Go through queues
        foreach ($in_queues as $queue_id) { 
            $map->declareQueue($queue_id);

            // Add routes as needed
            foreach ($this->queues[$queue_id] as $id) { 
                $map->addRoute($queue_id, $id, $routes[$id]);
            }
        }

        // Return
        return $map;
    }

    /**
     * Map routes 
     */
    public function mapRoutes(array $routes):void
    {

        // Get routing IDs
        $routing_ids = $this->groupByRoutingId($routes);

        // Group into instances
        $instance_groups = $this->groupIdsByInstance($routing_ids);

        // Generate queues
        $this->queues = $this->generateQueues($instance_groups);

    }

    /**
     * Group routes by routing ID
     */
    private function groupByRoutingId(array $routes):array
    {

        // Initialize
        list($routing_ids, $excludes, $all, $instance_ids) = array([], [], [], []);

        // GO through routes
        foreach ($routes as $id => $vars) { 

            // Get routing id
            $routing_id = $vars['routing_id'];
            if (!isset($routing_ids[$routing_id])) { 
                $routing_ids[$routing_id] = [];
            }

            // Add to routes map
            if (!isset($this->routes_map[$routing_id])) { 
                $this->routes_map[$routing_id] = [];
            }
            $this->routes_map[$routing_id][] = $id;

            // Go through instances
            $added=0;
            foreach ($vars['instances'] as $instance) { 

                // Check for exclude
                if (preg_match("/^~(.+)/", $instance, $match)) { 
                    $excludes[] = $match[1] . ':' . $routing_id;
                    $instance_ids[$match[1]] = 1;
                    continue;
                } elseif ($instance == 'all') { 
                    $all[] = $vars['type']  . '.' . $routing_id;
                } else { 
                    $instance_ids[$instance] = 1;
                }

                // Add to instances
                $routing_ids[$routing_id][] = $vars['type'] . '.' . $instance;
                $added++; 
            }

            // Add to all, if no instances defined
            if ($added == 0) { 
                $routing_ids[$routing_id][] = $vars['type'] . '.all';
                $all[] = $vars['type'] . '.' . $routing_id;
            }
        }

        // GO through all
        foreach ($all as $def) { 
            list($type, $id) = explode('.', $def, 2);
 

            foreach (array_keys($instance_ids) as $instance) { 
            if (in_array($instance . ':' . $id, $excludes)) { continue; }
            if (in_array($type . '.' . $instance, $routing_ids[$id])) { continue; }
                $routing_ids[$id][] = $type . '.' . $instance;
            }
        }

        // Return
        return $routing_ids;
    }

    /**
     * Group routing IDs by instance
     */
    private function groupIdsByInstance(array $routing_ids):array
    {

        // Map routes based on instance groupings
        $groupings = [];
        foreach ($routing_ids as $id => $instances) { 

            // Get key
            sort($instances);
            $key = implode(' ', array_unique($instances));

            // Ensure grouping key exists
            if (!isset($groupings[$key])) { 
                $groupings[$key] = [];
            }
            $groupings[$key][] = $id;
        }

        // Return
        return $groupings;
    }

    /**
     * Generate queues from instance groupings
     */
    private function generateQueues(array $instance_groups):array
    {

        // Generate queues
        $queues = [];
        foreach ($instance_groups as $in_key => $routing_ids) { 

            // Add queue
            $queue_id = dechex(array_sum($routing_ids));
            $queues[$queue_id] = [];

            // Add routing ids to queue
            foreach ($routing_ids as $id) {
                array_push($queues[$queue_id], ...$this->routes_map[$id]);
            }

            // Map to instances
            $instances = explode(' ', $in_key);
            foreach ($instances as $instance) { 
                if (str_starts_with($instance, '~')) { continue; }
                $instance = preg_replace("/^(.+?)\./", "", $instance);

                if (!isset($this->instance_map[$instance])) { 
                    $this->instance_map[$instance] = [];
                }
                $this->instance_map[$instance][] = $queue_id;
            }
        }

        // Return
        return $queues;
    }

}



