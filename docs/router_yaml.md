
# Router YAML Configuration

All routes can be defined via a YAML file, the default location of which is `~/config/router.yml`, but can be defined via the `$router_file` variable as the third argument when instantiating the `Cluster` class.  Please view the /examples/yaml/ directory for example configuration files.

The below table lists all supported root elements within the YAML file:

Key | Description
------------- |------------- 
ignore | Optional boolean, and if set to false the file will be ignored and not loaded upon instantiation of Cluster.
routes | All route definitions.  See below for details.
instances | Properties of specific instances.  See below for details.


## routes

The root `routes` element is an array of router definitions, defining exactly how and where incoming messages are routed.  Each definition is an associative array which must have a unique top-level name, and supports the following elements:

Element |  Required | Type | Description
------------- |------------- |------------- |------------- 
type | Yes | string | The type of message, supported types are:  rpc, queue, broadcast
instances | No | string or array | Defines the specific instances messages will be routed to.  This can be a string if there is only one instance, or a one-dimensional array if multiple instances.  You may also prefix instances with a tilda ~ to exclude the instance (ie. all instances except the ones defined here).  Defaults to "all" instances if undefined.
params | No | array | Used for parameter based routing, and is an associative array of the conditions that must be met.  Please see the [Parameter Based Routing](router_params.md) page for details.
routing_keys | Yes | array | An associative array with the keys being the routing key and the values being the full PHP classes messages will be routed to.  The PHP classes can either be a string for only one class, or if messages are routed to multiple PHP classes, an associative array with the keys being the response alias and the values being the full PHP class.  See below for examples.


#### Basic Example

~~~
  rpc.default:
    type: rpc
    instances: all
    routing_keys:
      users.profile: App\Users\Profiles
      financial.orders | App\Shop\Orders
      all: App\CatchAll
~~~

Above is a standard RPC definition that does round robin to all server instances listening which contains two specific routes for the two specified routing keys, with a catch all added at the bottom for all incoming RPC calls that don't match either of the routes.


#### Multiple PHP Classes Example

~~~
  rpc.users:
    type: rpc
    instances: all
    routing_keys:
      users.profile:
        default: App\Users\Profiles
        wallet: App\Wallets\Users
      financial.orders | App\Shop\Orders
      all: App\CatchAll
~~~

Extending on the first example, above specifies two PHP classes for the "users.profile.*" routing key.  Incoming messages to the routing key will call both PHP classes, and below is an example of how you'd read the response from a dispatched message:

~~~php
use Apex\Cluster\Dispatcher;
use Apex\Message\MessageRequest;

// Set message
$msg = new MessageRequest('users.profile.load', $var1, $var2);

// Dispatch message
$dispatcher = new Dispatcher('web1');
$res = $dispatcher->dispatch($msg);

// Default response from Apex\Users\Profiles
$profile = $res->get_response();

// Response from App\Wallets\Users
$wallet_info = $res->getResponse('wallet');
~~~


#### Specific Instance Example

~~~

  images_uploaded:
    type: queue;
    instances:
      - app3
      - app4
    routing_keys:
      images.processor.upload: App\Gallery\Upload

  rpc.default:
    type: rpc
    instances:
      - ~app3
      - ~app4
    routing_keys:
      users.profile:
        default: App\Users\Profiles
        wallet: App\Wallets\Users
      financial.orders | App\Shop\Orders
      all: App\CatchAll
~~~

The above creates an "images_uploaded" queue, which is only processed by the consumer instances "app3" and "app4".  The same RPC definition as above is also there, except modified to handle incoming messages on all listening consumers except for "app3" and "app4".


#### Auto-Routing

~~~
  rpc.users:
    type: rpc
    instances: all
    routing_keys:
      users.profile:
        default: App\Users\Profiles
        wallet: App\Wallets\Users
      financial.orders | App\Shop\Orders
      all: App\~package.title~\~module.title~
~~~

Using the same RPC example, this route definition has been slightly modified with merge fields placed within the "all" routing key.  These merge fields will be replaced by the named routing key, forced to titlecase.  For example, a message sent to the routing key "supports.tickets.create" will call the PHP method at `App\Support\Tickets::create()` providing the ability to quickly develop out consumer methods without having to constantly keep the router configuration updated.


## instances

Although mainly here for extensibility, the root `instances` element allows for per-instance properties to be defined.  This element consists of associative arrays, each named the unique instance name.  Currently, each instance supports the following properties;

Key | Type | Description
------------- |------------- |------------- 
max_msg | int | Only applicable for instances that accept messages with "queue" type, and defines the maximum number of concurrent messages the instance will accept.  Defaults to 1.

For example:

~~~
  instances:

    app3:
      max:msg: 5
    app4:
      max:msg: 5
~~~

With the above example, the instances "app3" and "app4" will only accept five concurrent messages at a time.

