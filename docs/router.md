
# Router Overview

Being the main component, the router is where you define how incoming messages are routed, and which PHP classes are called.  You can easily define routes within the PHP code using the `addRoute()` method or through an easy to follow YAML configuration file, auto-route messages based on the named routing key, audo-load router configuration from redis ensuring multiple server instances are always using the same configuration, and more.

Additional pages on routing:

1. [Adding Routes in PHP](router_php.md)
2. [Router YAML Configuration File](router_yaml.md)
3. [Auto Routing](router_auto.md)
4. [Parameter Based Routing](router_params.md)
5. [Enable redis Autoloading](redis.md)


## Local Message Broker

By default Cluster is configured to utilize the local message broker, meaning dispatching and consuming messages will work perfectly fine on one server instance without RabbitMQ installed.  This allows you to develop the software with horizontal scaling fully implemented while running on one server instance, then as needed and when volume increases, easily split the system into multiple instances with RabbitMQ in the middle.  You can easily switch from the local broker to RabbitMQ by modifying the `BrokerInterface::class` item within the container definitions file (eg. ~/config/container.php).

## Message Types

Three types of messages are supported, which are:

* rpc - Two-way RPC call where response is expected from the consumer.  This is the default.
* queue - A one-way message that is queued for processing, and only requires acknowledgement by message broker without providing a response.  Used for resource intensive operations that don't require an immediate response, and can be processed either immediately if a consumer is listening, or fetched from queue later (ie. via crontab job).
* broadcast - Broadcasts messages to all consumers listening.  Useful for actions such as reload configuration, or any other action required by each individual consumer.


## Routing Keys

Every message dispatched has a routing key assigned, which is a three segment string delimited by periods, such as "users.profile.create" for example.  The three segments are broken up as follows:

* First Segment - The overall package / module.
* Second Segment - Class or subset name of message destination.
* Third Segment - The name of the method within the PHP class that will be executed.

The first two segments are simply identifiers to help keep routing keys readable and memorable, and can be routed to any PHP class(es) desired.  The third segment however is the name of the method that will be called within each PHP class the message is routed to.

Wildcards can be designated by using an asterisk such as "users.profile.*".  Any segments left undefined will also be treated as wildcards, so "users.profile" is the same as "users.profile.*".  You may also use "all" as a routing key which is the equivalent of specifying "*.*.*", and is useful for auto-routing.


## Routing Destinations

All messages can be routed to one or more PHP classes, providing extensibility to the software by allowing a message to be processed by more than one PHP class.  In the same vein, there can be multiple responses provided for each message, although only one marked as the "default" response.  


