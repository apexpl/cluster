<?php
declare(strict_types = 1);

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use PHPUnit\Framework\TestCase;

// Load test files
require_once(__DIR__ . '/../examples/App/Users/Profiles.php');
require_once(__DIR__ . '/../examples/App/Users/Login.php');
require_once(__DIR__ . '/../examples/App/Shop/Orders.php');
require_once(__DIR__ . '/../examples/App/Shop/Users.php');
require_once(__DIR__ . '/../examples/App/Wallets/Bitcoin.php');




/**
 * YAML loader tests
 */
class yaml_loader_test extends TestCase
{

    /**
     * Test ignore
     */
    public function test_ignore()
    {

        $c = new Cluster('app1');
        $this->assertCount(0, $c->routes);
        $this->assertCount(0, $c->instances);

    }

    /**
     * Test full.yml example load
     */
    public function test_full_load()
    {

        // Start cluster
        $c = new Cluster('app1', null, __DIR__ . '/../examples/yaml/full.yml');
        $this->assertCount(7, $c->routes);

    }

}


