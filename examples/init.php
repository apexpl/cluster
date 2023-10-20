<?php


// Loads all necessary files
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/App/Users/Profiles.php');
require_once(__DIR__ . '/App/Users/Login.php');
require_once(__DIR__ . '/App/Shop/Orders.php');
require_once(__DIR__ . '/App/Shop/Users.php');
require_once(__DIR__ . '/App/Wallets/Bitcoin.php');


// Start cluster
function init($name) { 
    $cluster = new Apex\Cluster\Cluster('web1', null, __DIR__ . '/yaml/' . $name . '.yml');
}



