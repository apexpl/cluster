<?php

/**
 * Example using the multi YML router file, and provides example of how to 
 * receive responses from routes which route messages to multiple PHP classes.
 */
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Load needed files
require_once(__DIR__ . '/init.php');
init('multi');


// Start dispatcher
$dispatcher = new Dispatcher('web1');

// Load user, 'johndoe'
$msg = new MessageRequest('users.profile.load', 'jhondoe');
$res = $dispatcher->dispatch($msg);

// Get default UserModel object returned by App\Users\Profiles class.
$user = $res->getResponse();
echo "Got user id# ", $user->userid, " with e-mail ", $user->email . "\n";

// Get array of OrderObjects returned by App\Shop\Users class.
$orders = $res->getResponse('shop');
foreach ($orders as $order) { 
    echo "Order ID# ", $order->id, " product: ", $order->product, " for ", $order->amount, "\n";
}



