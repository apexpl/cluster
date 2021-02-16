<?php
declare(strict_types = 1);

namespace Apex\Cluster\Router\Loaders;

use Apex\Cluster\Router\Router;
use Apex\Container\Di;
use redis;

/**
 * Handles loading / saving router configuration within redis.
 */
class RedisLoader extends Router
{

    /**
     * Save to redis
     */
    public function save(redis $redis, array $routes):void
    {

        // Grab items from container
        $container_file = Di::get('container_file');

        // Save container
        $redis->set('cluster:container:items', serialize(file_get_contents($container_file)));
        $redis->set('cluster:container:sha1', sha1_file($container_file));
        $redis->set('cluster:container:mtime', time());

        // Save routes
        $redis->set('cluster:routes', serialize($routes));
    }

}





