<?php

use Apex\Cluster\Interfaces\{BrokerInterface, ReceiverInterface, MessageRequestInterface, FeHandlerInterface};
use Psr\Log\LoggerInterface;
use Apex\Cluster\Brokers\{Local, RabbitMQ};
use Apex\Cluster\Receiver;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Below this line are the actual definitions used by the DI container.  You may 
 * modify these as desired, such as the broker interface if using something other than 
 * the default of Local (ie. RabbitMQ).
 *
 * See the /docs/container.md file for details on the below items.
 */
return [

    /**
     * The message broker used.  This defaults to Local for new installations, but if already operating on multiple servers, 
     * modify this to RabbitMQ or any other message broker you are using.
     */
    //BrokerInterface::class => Local::class, 
    BrokerInterface::class => [RabbitMQ::class, ['host' => 'localhost', 'port' => 5672, 'user' => 'guest', 'password' => 'guest']], 

    /**
     * Front-end handler.  Generally the template engine you're using if supported, and allows front-end actions such as 
     * assigning template variables to be passed back to and processed by dispatcher.  See /docs/fe_handlers.md file for details.
     */
    FeHandlerInterface::class => Apex\Cluster\FeHandlers\Syrus::class,

    /**
     * PSR3 compliant logger, defaults to the popular Monolog package.  Set to null to disable logging.
     */
    LoggerInterface::class => function() { return new Logger('cluster', [new StreamHandler(__DIR__ . '/../cluster.log')]); },  

    /**
     * Timeout handler.  If RPC call times out, this is called.
     */
    'cluster.timeout_seconds' => 3,  
    //'cluster.timeout_handler' => function (MessageRequestInterface $msg) { echo "We've timed out with key: " . $msg->getRoutingKey() . "\n"; exit; }, 

    /**
     * Message preparation handler.  If defined, this closure will be invoked for every incoming message and is meant to 
     * prepare your specific envrionment for processing of messages.
     */
    //'cluster.prepare_msg_handler' => function (MessageRequestInterface $msg) { }, 

    /**
     * Front-end Handler Callback.  If defined, will be invoked for every message dispatched upon receiving a response with 
     * a FeHandlerInterface object passed.  Used to update output to end-users.  See docs for details.
     */
    //'cluster.fe_handler_callback' => function (FeHandlerInterface $handler) { }, 

    /**
     * Custom router.  This should almost always be left commented out, but allows you to utilize a custom router.  Should returnan 
     * associative array, keys being the response alias (eg. default) and values being the full namespace / class name that should be called for the given routing key.
     */
    //'cluster.custom_router' => function(MessageRequestInterface $msg) { }, 

    /**
     * Additional items, you probably don't need to modify these.
     */
    ReceiverInterface::class => Receiver::class 
];




