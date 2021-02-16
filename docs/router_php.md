
# Adding Routes in PHP


The main `Cluster` class extends the `Router` class, meaning all methods outlined below are available via the main `Cluster` object.  

## Add Route

Routes can be easily added via the `addRoute()` method.  Although Cluster does allow multiple PHP methods to be executed for a single message to provide extensibility, this method only allows one PHP class to be defined at a time.  If you need multiple PHP classes, call this method for each of them.

**Parameters**

Variable | required | Type | Description
------------- |------------- |------------- |------------- 
`$routing_key` | Yes | string | The routing key, 1 to 3 segments delimited by periods.  Please see the [Router Overview](router.md) page for details on routing keys.  You may use "all" to specify all keys.
`$php_class` | Yes | string | The full PHP namespace / class that will be instantiated when a message matching the routing key is received.
`$alias` | No | string | The alias the response from the PHP class will be stored in.  Defaults to "default", but can differ if multiple PHP classes are being called for the same routing key.
`$msg_type` | No | string | The message type to listen for.  Supported values are: rpc, queue, broadcast.  Defaults to "rpc".
`$queue_name` | No | string | Only applicable if the message type is "queue", and is the name of the queue to create.  This is the name you can fetch messages from using the `Cluster\Fetcher` class.
`$params` | No | array | Used for parameter based routing, and is an associative array of the parameter conditions that must be met for the message to be accepted.  Please see [Parameter Based Routing](router_params.md) page for details.
`$instances` | No | array | The instance aliases to route incoming messages to.  Only applicable if only select instances will process messages sent to this route, and defaults to "all" instances.
`$is_config` | No | bool | Should always be left at false when creating via PHP with this method.


#### Basic Example

Here's a basic example of adding a route:

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster('app1');
$cluster->addRoute('users.profile.*', App\Users\Profiles::class);
~~~

This adds a single route, so for example, when a message is sent to the routing key "users.profile.create", the receiving consumer will execute the `App\Users\Profiles::create()` method, and return the response.


#### Multiple PHP Classes Example

Extending on the above example, maybe the system has an additional package installed for managing user wallets, and upon loading a user's profile we want to retrieve both, the general profile plus the additional wallet information on the user.  Below shows an example of this:

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster('app1');
$cluster->addRoute('users.profile.*', App\Users\Profiles::class);
$cluster->addRoute('users.profile.*', App\Wallets\Users::class, 'wallet');
~~~

When a message is sent to the routing key "users.profile.load" for example, the method at `App\Users\Profiles::load()` will be called with the response added as the "default" response.  The method at `App\Wallets\Users::load()` will also be called, with its response being stored as "wallet" within the message response.  

Here's an example of dispatching said message:

~~~php
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Dispatch message
$dispatcher = new Dispatcher('web1');
$res = $dispatcher->dispatch(new MessageRequest('users.profile.load', $var1, $var2));

// Default response from App\Users\Profile::load()
$profile = $res->getResponse();

// Response from App\Wallets\Users::load()
$wallet_info = $res->getResponse('wallet');
~~~

#### Specific Instance Examples

Maybe you have several server instances and want only two of them to handle the processing of all image uploads.  This can be accomplished with the following:

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster('app1');
$cluster->addRoute('images.processor.upload', App\Gallery\Images::class, 'default', 'queue', 'uploaded_images', [], ['app3', 'app4']);
~~~ 

All messages sent to the routing key "images.processor.upload" will be sent only to the server instances "app3" and "app4" in round robin fashion, and will execute the method at `App\Gallery\Images::upload()`.

## Delete Routes

There are two methods to delete routes.  Upon adding a route via the `addRoute()` method, it will return a unique id# for that specific route.  The specific route can later be deleted via the `deleteRouteId($id)` method, and for example:

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster('app1');
$id = $cluster->addRoute('users.profile.*', App\Users\Profiles::class);

// Delete route
$cluster->deleteRouteId($id);
~~~

You may also use the `deleteRoutes()` method which will delete all routes matching a given criteria.  This method accepts the following parameters, all of which are optional:

Variable | Type | Description
------------- |------------- |------------- 
`$routing_key` | string | If specified, only routes matching this routing key will be deleted.
`$php_class` | string | If specified, only routes matching this PHP class will be deleted.
`$msg_type` | string | If specified, only routes matching this message type will be deleted.
`$instance` | string | If specified, only routes matching this instance will be deleted.

Please note, if more than one criteria is specified, only routes matching all criteria will be deleted.  For example:

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster('app1');
$cluster->add('users.profile.*', App\Profiles\Users::class);
$cluster->addRoute('financial.orders.*', Shop\Orders::class);

// Delete
$cluster->deleteRoutes('users.profile.*');
~~~

The above would delete the one route, leaving the rout with the routing key "shop.orders.*".


## Purge Routes

If ever needed, you may purge all routes with the `purgeRoutes()` method.  This will remove all routes leaving a blank router.  

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster('app1');
$cluster->purgeRoutes();
~~~



