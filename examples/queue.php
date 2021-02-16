<?php

/**
 * Example using the queue YML router file, and provides example of fetching from a queue
 */
use Apex\Cluster\{Cluster, Dispatcher, Fetcher};
use Apex\Cluster\Message\MessageRequest;
use App\Shop\Orders;

// Load needed files
require_once(__DIR__ . '/init.php');
init('queue');


// Start cluster to load .yml file
$cluster = new Cluster('app1', null, __DIR__ . '/yaml/queue.yml');

// Start fetcher to ensure queue is declared
$fetcher = new Fetcher();
$res = $fetcher->fetch('image_upload');

// Wait for enter.
echo "Ensure listener is off, and press Enter to dispatch messages to queue...\n";
readline();

// Dispatch messages
$dispatcher = new Dispatcher('web1');
for ($x=1; $x <= 3; $x++) { 
    $dispatcher->dispatch(new MessageRequest('orders.images.upload', "Image $x"), 'queue');
}

// Wait again
echo "Messages dispatched, press Enter to fetch them via Fetcher class...\n";
readline();

while ($msg = $fetcher->fetch('image_upload')) { 
    echo "Got msg with params: " . $msg->getParams() . "\n";
}

echo "All out of messages\n";


