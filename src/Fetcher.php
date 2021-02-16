<?php
declare(strict_types = 1);

namespace Apex\Cluster;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Interfaces\{MessageRequestInterface, BrokerInterface};
use Apex\Cluster\Exceptions\{ClusterZeroRoutesException, ClusterOutOfBoundsException};


/**
 * Fetcher class
 */
class Fetcher
{
    /**
     * Constructor
     */
    public function __construct(string $instance_name = 'app1')
    {

        // Instantiate Cluster, if needed
        if (!Di::has(Cluster::class)) { 
            $cluster = new Cluster($instance_name);
        } else { 
            $cluster = Di::get(Cluster::class);
        }
        $this->cluster = $cluster;

        // Get routes table
        $this->map = $cluster->getRoutesMap($cluster->instance_name);
        if (count($this->map->getAllRoutes()) == 0) { 
            throw new ClusterZeroRoutesException("There are no routes configured to listen to msg_type queue");
        }

        // Declare exchange and queues
        $listener = new Listener();
        $this->broker = $listener->declare(false);

    }

    /**
     * Fetch messages from queue.
     */
    public function fetch(string $queue_name):?MessageRequestInterface
    {

        // Get queue alias
        if (!$queue_id = $this->map->checkAlias($queue_name)) { 
            throw new ClusterOutOfBoundsException("Queue does not exist within router configuration, $queue_name");
        }
        $queue_name = 'cluster.' . $queue_id;

        // Ensure queue is declared
        $defs = $this->cluster->exchange_defaults['queue'];
        $this->broker->declareQueue($queue_name, $defs['durable'], false, $defs['auto_delete']); 

        // Fetch from queue
        $res = $this->broker->fetch($queue_name);

        // Return
        return $res;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        //$this->broker->closeChannel();
    }

}


