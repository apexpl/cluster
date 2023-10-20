<?php

/**
 * Basic example using the basic.yml router file, and 
 * shows how to receive responses, including the ability to send and receive objects.
 */
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Load needed files
require_once(__DIR__ . '/init.php');
init('basic');

// Start dispatcher
$dispatcher = new Dispatcher('web1');

// Create user, and get UserModel object as response
$msg = new MessageRequest('users.profile.create', 'johndoe', 'john@domain.com');
$user = $dispatcher->dispatch($msg)->getResponse();

// Echo
echo "Created user ", $user->username, " who has the id# ", $user->userid, "\n";


// Add order to user, giving UserModel object in request, getting OrderModel object back.
$msg = new MessageRequest('financial.orders.add', $user, 'iPhone', 889.95);
$order = $dispatcher->dispatch($msg)->getResponse();

// Echo
echo "Created order id# ", $order->id, " on user id# ", $user->userid, " for product: ", $order->product . " at amount ", $order->amount, "\n";

