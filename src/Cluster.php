<?php
declare(strict_types = 1);

namespace Apex\Cluster;

use Apex\Container\Di;
use Apex\Cluster\Router\Router;
use Apex\Cluster\Interfaces\{BrokerInterface, ReceiverInterface, FeHandlerInterface};
use Apex\Cluster\Exceptions\ClusterFileNotWriteableException;
use Psr\Log\LoggerInterface;
use redis;

/**
 * Central class for the Cluster package to help handle and 
 * facilitate horizontal scaling across a cluster of servers.
 */
class Cluster extends Router
{

    // Properties
    private ?BrokerInterface $broker = null;
    private ?LoggerInterface $logger = null;
    private bool $screen_logging = false;

    // Exchange defaults
    public array $exchange_defaults = [
        'rpc' => ['type' => 'topic', 'durable' => false, 'no_ack' => true, 'auto_delete' => true],
        'queue' => ['type' => 'topic', 'durable' => true, 'no_ack' => false, 'auto_delete' => false], 
        'broadcast' => ['type' => 'fanout', 'durable' => true, 'no_ack' => true, 'auto_delete' => false]
    ];

    /**
     * Constructor
     */
    public function __construct(
        public string $instance_name,
        public ?redis $redis = null,  
        private string $router_file = '', 
        private ?string $container_file = '' 
    ) {

        // Setup container
        if ($this->container_file !== null) { 
            $this->setupContainer();
        } else {
            Di::set(__CLASS__, $this);
            $this->loadRoutes($this->router_file);
        }

    }

    /**
     * Setup container
     */
    private function setupContainer()
    {

        // Get container file
        if ($this->container_file !== null && $this->container_file == '') { 
            $this->container_file = __DIR__ . '/../config/container.php';
        }

        // Check for redis and new container file
        if ($this->redis instanceof redis && $sha1_hash = $this->redis->get('cluster:container:sha1')) {

            // Check SHA1 hash of local file
            if ($sha1_hash != sha1_file($this->container_file)) {

                // Ensure container file is writeable
                if (!is_writeable($this->container_file)) { 
                    throw new ClusterFileNotWriteableException("New container file detected from redis, but container file is not writeable at: $this->container_file");
                }

                // Save new container file
                $contents = $this->redis->get('cluster:container:items');
                file_put_contents($this->container_file, unserialize($contents));
            }
        }

        // Build container
        Di::buildContainer($this->container_file);

        // Set base container items
        Di::set(__CLASS__, $this);
        Di::set('cluster.container_file', $this->container_file);

        // Add redis to container
        $redis_val = $this->redis instanceof redis ? $this->redis : 'no_connect';
        Di::set(redis::class, $redis_val); 

        // Mark necessary items as services
        Di::markItemAsService(BrokerInterface::class);
        Di::markItemAsService(FeHandlerInterface::class);
        Di::markItemAsService(LoggerInterface::class);
        Di::markItemAsService(ReceiverInterface::class);

        // Instantiate logger
        $this->logger = Di::get(LoggerInterface::class);

        // Load routes
        $this->loadRoutes($this->router_file);
    }

    /**
     * set screen logging
     */
    public function setScreenLogging(bool $logging):void
    {
        $this->screen_logging = $logging;
    }

    /**
     * Set message broker
     */
    public function setBroker(BrokerInterface $broker)
    {
        Di::set(BrokerInterface::class, $broker);
    }

    /**
     * Add log
     */
    public function addLog(string $message, string $level = 'info'):void
    {

        // Screen logging
        if ($this->screen_logging === true) { 
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
            fwrite(STDOUT, $line);
        }

        // Add log
        if ($this->logger !== null) { 
            $this->logger->$level($message);
        }

    }

}



