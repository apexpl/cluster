<?php

/**
 * Example using the instances YML router file, and provides example of routing messages to difference instances.
 *
 * For this example to work, you must start two listener instances with:
 *     php listen.php instances app1
 *     php listen.php instances app2
 * 
 * Once listeners are running, run this script and you will see the logging of the two 
 * different messages, one to each listener.
 */
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Load needed files
require_once(__DIR__ . '/init.php');
init('instances');


// Start dispatcher
$dispatcher = new Dispatcher('web1');

// Send message to the undefined "all" route which routes to: app\~package.title~\~module.title~
$msg = new MessageRequest('wallets.bitcoin.create', 52);
$res = $dispatcher->dispatch($msg)->getResponse();

// Echo
echo "GOt response of: ", $res, "\n";

// Send queue message
$msg = new MessageRequest('orders.images.upload', 'whatever');
$res = $dispatcher->dispatch($msg, 'queue');


echo "Both messages sent, which you should see within logging on the two listeners.\n";


