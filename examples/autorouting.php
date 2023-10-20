<?php

/**
 * Example using the autorouting YML router file, and provides example of auto-routing.
 */
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Load needed files
require_once(__DIR__ . '/init.php');
init('autorouting');


// Start dispatcher
$dispatcher = new Dispatcher('web1');

// Send message to the undefined "all" route which routes to: app\~package.title~\~module.title~
$msg = new MessageRequest('wallets.bitcoin.create', 52);
$res = $dispatcher->dispatch($msg)->getResponse();

// Echo
echo "GOt response of: ", $res, "\n";


