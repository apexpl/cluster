<?php
declare(strict_types = 1);

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use PHPUnit\Framework\TestCase;


/**
 * Basic router tests
 */
class router_test extends TestCase
{

    /**
     * Add routes
     */
    public function test_add_routes()
    {

        $c = new Cluster('app1');

        // Add route
        $profile_id = $c->addRoute('users.profile', Apex\Cluster\Dispatcher::class);
        $upload_id = $c->addRoute('gallery.images', Apex\Cluster\Cluster::class, 'default', 'queue', 'images', [], ['app3', 'app4']);
        $broadcast_id = $c->addRoute('cluster', Apex\Cluster\Sys\Sys::class, 'cluster', 'broadcast');
        $param_id = $c->addRoute('core.template.parse', Apex\Cluster\Listener::class, 'default', 'rpc', '', ['method' => '== POST', 'uri' => '=> login$'], ['app2']);
        $bitcoin_id = $c->addRoute('bitcoin.tx', Apex\Cluster\Router\Router::class);

        // Validate routes
        $this->assertCount(5, $c->routes);
        $this->validateRoute($c->routes[$profile_id], 'users.profile.*', Apex\Cluster\Dispatcher::class, 'rpc', 'all', 1);
        $this->validateRoute($c->routes[$upload_id], 'gallery.images.*', Apex\Cluster\Cluster::class, 'queue', 'app3', 2, 'images');
        $this->validateRoute($c->routes[$broadcast_id], 'cluster.*.*', Apex\Cluster\Sys\Sys::class, 'broadcast', 'all', 1);
        $this->validateRoute($c->routes[$param_id], 'core.template.parse', Apex\Cluster\Listener::class, 'rpc', 'app2', 1);
        $this->assertCount(2, $c->routes[$param_id]['params']);

        // Delete route by id
        $this->assertArrayHasKey($bitcoin_id, $c->routes);
        $c->deleteRouteId($bitcoin_id);
        $this->assertArrayNotHasKey($bitcoin_id, $c->routes);
        $this->assertCount(4, $c->routes);
    }

    /**
     * Delete routes
     */
    public function test_delete()
    {

        $c = Di::get(Cluster::class);
        $this->assertEquals(Cluster::class, $c::class);
        $this->assertCount(4, $c->routes);

        // Delete
        $c->deleteRoutes('', '', '', 'app4');
        $this->assertCount(3, $c->routes);

        // Delete again
        $c->deleteRoutes('core.template.parse');
        $this->assertCount(2, $c->routes);
    }


    /**
     * Purge routes
     */
    public function test_purge()
    {

        // Get routes
        $c = Di::get(Cluster::class);
        $this->assertCount(2, $c->routes);

        // Purge routes
        $c->purgeRoutes();
        $this->assertCount(0, $c->routes);

    }

    /**
     * Validate route
     */
    private function validateRoute(array $r, string $routing_key, string $php_class = '', string $msg_type = '', string $instance = '', int $in_count = 0, string $queue_name = '')
    {
        // Check first route
        $this->assertIsArray($r);
        if ($routing_key != '') { 
            $this->assertEquals($routing_key, $r['routing_key']);
        }

        if ($php_class != '') { 
            $this->assertEquals($php_class, $r['php_class']);
        }

        if ($msg_type != '') { 
            $this->assertEquals($msg_type, $r['type']);
        }

        if ($in_count > 0) { 
            $this->assertCount($in_count, $r['instances']);
        }

        if ($instance != '') { 
            $this->assertContains($instance, $r['instances']);
        }

        if ($queue_name != '') { 
            $this->assertEquals($queue_name, $r['queue_name']);
        }

    }

}


