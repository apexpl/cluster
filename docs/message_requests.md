
# Message Requests

All messages dispatched must be an implementation of the `MessageRequestInterface` interface.  Cluster does provide a default `MessageRequest` object, but it is somewhat expected you will copy and modify it as necessary for your own implementation, as you will see in the "Request Property" section below.


## Creating Messages

Creating messages is very simplistic, and for example:

~~~php
use APex\Cluster\Message\MessageRequest;

$msg = new MessageRequest('shop.orders.add', $var1, $var2, $obj1, $obj2, $etc);
~~~

That's all there is to it.  The constructor only requires one argument, being the routing key to dispatch the message to.  Aside from the routing key, you may pass any and all additional parameters you wish as long as they can be serialized, which includes variables, arrays and objects.  However, this does not include closures or resources.

The listening consumer can then retrieve all parameters passed via the `getParams()` method of the message request object.


## Properties

All message request objects have the following properties:

Property | Type | Description
------------- |------------- |------------- 
`$msg_type` | string | The type of message.  Will be either:  rpc, queue, broadcast.
`$instance_name` | string | The name of the server instance dispatching the message.
`$caller` | array | The exact class / method name, file and line number from where the message was dispatched.
`$request` | array | Standardized request information such as URI, IP address, post / get inputs, et al.  See below for details.
`$routing_key` | string | The routing key passed to the constructor.
`$params` | iterable | All parameters passed to the constructor.


## Methods

The message request object contains the following methods:

Method | Description
------------- |-------------
`GetParams():iterable` | Returns all parameters passed to the constructor.
`getRequest():array` | Returns standardized request information.  See the below section for details.
`getCaller():array` | Returns a four element associative array that contains the class name, method name, filename, and line number the message was dispatched from.
`getRoutingKey():string` | Returns the routing key passed to the constructor.
`getType():string` | Returns the message type, will be one of:  rpc, queue, broadcast
`getInstanceName():string` | Returns the instance name of the dispatcher.
`setType(string $type)` | Set the message type.
`setInstanceName(string $name)` | Set the instance name of the dispatcher.


## Request Property

Here in is the reason you may wish to write your own `MessageRequestInterface` class to use when dispatching messages.  The contents of this array are defined within the class, and will be somewhat dependant on the framework / environment you're developing with.  This array is meant to provide standardized information on each request to consumers, such as host, URI, authenticated user, post / get inputs, HTTP headers, et al.

Although Cluster does provide a standard `$request` array, it's somewhat assumed you're developing with your framework of choice which provides its own input sanitization, user authentication, obtains the real IP address instead of `$_SERVER[REMOTE_ADDR]`, et al.  To create your own implementation, simply copy the /src/message/MessageRequest.php file to your software, and modify the constructor to populate the `$request` array as desired.  All objects created with the new class will continue to be dispatched and processed perfectly fine without issue.

Nonetheless, if using the message request object provided by Cluster, the `$request` array will contain the following elements:

Key | Type | Description
------------- |------------- |------------- 
mode | string | Either "http" or "cli", defining the mode of the dispatcher.
host | string | The requested host (eg. domain.com).
port | int | The requested port (ie. 443).
uri | string | The requested URI
method | string | The request method (eg. GET / POST).
ip_address | string | Client's originating IP address.
post | array | All $_POST variables sanitized via `filter_input_array()`.
get | array | All $_GET variables sanitized via `filter_input_array()`.
cookie | array | All $_COOKIE variables sanitized via `filter_input_array()`.
server | array | All $_SERVER variables sanitized via `filter_input_array()`.
http_headers | array | All HTTP headers as retrived by `getAllHeaders()` function if HTTP request, otherwise blank if CLI request.
script_file | string | Only present for CLI requests, and is the filename of dispatching script.
argv | iterable | Only present for CLI requests, and is the `$argv` array, or the command line arguments passed.






