
# Cluster - Load Balancer / Router for Horizontal Scaling

Cluster provides a simple yet intuitive interface to implement horizontal scaling across your application via RabbitMQ or any message broker.  With the local message broker, easily develop your software with horizontal scaling fully implemented while running on one server, and split into multiple server instances within minutes when necessary.  It supports:

* General round robin and routing of designated messages to specific server instances.
* One-way queued messages, two-way RPC calls, and system wide broadcasts.
* Parameter / header based routing.
* Easy to configure YAML router file.
* Optional centralized redis storage of router configuration for maintainability across server instances.
* Standardized immutable request and response objects for ease-of-use and interopability.
* Front-end handlers for streamlined communication back to front-end servers allowing execution of events to the client side (eg. set template variables, et al).
* Timeout and message preparation handlers, plus concurrency settings.
* Interchangeable with any other message broker including the ability to easily implement your own.
* Includes local message broker, allowing implementation of logic for horizontal scaling while remaining on one server instance.
* Optional auto-routing allowing messages to be automatically routed to correct class and method that correlates to named routing key.

## Table of Contents

1. [Cluster class / Container Definitions](cluster.md)
2. [Router Overview](router.md)
    1. [Adding Routes in PHP](router_php.md)
    2. [Router YAML Configuration File](router_yaml.md)
    3. [Auto Routing](router_auto.md)
    4. [Parameter Based Routing](router_params.md)
    5. [Enable redis Autoloading](redis.md)
3. Message Handling
    1. [Listen / Consume Messages](listen.md)
    2. [Dispatch Messages](dispatch.md)
    3. [Fetch Messages from Queues](fetch.md)
4. Messages
    1. [Message Requests](message_requests.md)
    2. [Message Responses](message_responses.md)
5. [Front-End Handlers](fe_handlers.md)

## Installation

Install via Composer with:
> `composer require apex/cluster`


## Basic Usage

Please see the /examples/ directory for more in-depth examples.

**Save Math.php Class**
~~~php

namespace App;

class Math {

    public function add(MessageRequestInterface $msg)
    {
        list($x, $y) = $msg->getParams();
        return ($x + $y);
    }
}
~~~


**Define Listener**
~~~php
use Apex\Cluster\Cluster;
use Apex\Cluster\Listener;
use Apex\Cluster\Brokers\RabbitMQ;


// Start cluster
$cluster = new Cluster('app1');
$cluster->setBroker(new RabbitMQ('localhost', 5672, 'guest', 'guest'));
$cluster->addRoute('basic.math.*', App\Math::class);

// Start listener
$listener = new Listener();
$listener->listen();
~~~

**Define Dispatcher**
~~~php
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Define message
$msg = new MessageRequest('basic.math.add', 6, 9);

// Dispatch message
$dispatcher = new Dispatcher('web1');
$sum = $dispatcher->dispatch($msg)->getResponse();

// Print result
echo "Sum is: $sum\n";
~~~


## Follow Apex

Loads of good things coming in the near future including new quality open source packages, more advanced articles / tutorials that go over down to earth useful topics, et al.  Stay informed by joining the <a href="https://apexpl.io/">mailing list</a> on our web site, or follow along on Twitter at <a href="https://twitter.com/mdizak1">@mdizak1</a>.



