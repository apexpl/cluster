<?php

use Apex\Cluster\Cluster;
use Apex\Cluster\Listener;

/**
 * Aside from autoload.php, generally these files will be automatically loaded via PSR4, but due to
 * the examples they must be manually loaded.
 */
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/App/Users/Profiles.php');
require_once(__DIR__ . '/App/Users/Login.php');
require_once(__DIR__ . '/App/Shop/Orders.php');
require_once(__DIR__ . '/App/Shop/Users.php');
require_once(__DIR__ . '/App/Wallets/Bitcoin.php');

// Get listener configuration to use
$router_file = getListenerConfiguration();
$instance_name = $argv[2] ?? 'app1';

// Start cluster
$cluster = new Cluster(
    instance_name: $instance_name, 
    router_file: $router_file
);

// Start listening
$listener = new Listener();
$listener->listen();


/**
 * Get listener configuration to use.
 */
function getListenerConfiguration():string
{

    // Get command line arg
    global $argv;
    $config = $argv[1] ?? '';

    // Check for invalid arg
    if ($config == '' || !file_exists(__DIR__ . '/yaml/' . $config . '.yml')) { 
        echo "Invalid or no configuration specified.  Please run 'php listen.php <CONFIG>' where <<CONFIG> is one of the following:\n\n";
        echo "    [basic] - Basic RPC\n";
        echo "    [multi] - Multiple PHP Classes\n";
        echo "    [autorouting] - Auto-Routing\n";
        echo "    [params] - Parameter Based Routing\n";
        echo "    [queue] - Queue\n";
        echo "    [instances] - Specific Server Instances\n";
        exit(0);
    }

    // Return
    return __DIR__ . '/yaml/' . $config . '.yml';
}




