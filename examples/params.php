<?php

/**
 * Example using the param YML router file, and provides example of parameter based routing.
 */
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Load needed files
require_once(__DIR__ . '/init.php');
init('params');

// Start dispatcher
$dispatcher = new Dispatcher('web1');

// Send message we know will not route
$msg = new MessageRequest('syrus.template.parse');
$res = $dispatcher->dispatch($msg)->getResponse();
if ($res === null) { 
    echo "Null response, as expected.  Now let's modify the message...\n";
} else { 
    echo "Got a response, routing successful -- $res\n";
}

/**
 * Modify $_SERVER to match parameter criteria.  Natrually, this would 
 * be done automatically via other avenues such as your framework which automatically sanitizes the $_SERVER array.
 */
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/members/login';


// Send message again, this time expecting a response
$msg = new MessageRequest('syrus.template.parse');
$res = $dispatcher->dispatch($msg)->getResponse();
if ($res === null) { 
    echo "Received null response, did not route.\n";
} else { 
    echo "Got a response, routing successful -- $res\n";
}

