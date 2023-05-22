
# Dispatch Messages

Dispatching messages and receiving responses is very simplistic with the `Dispatcher` class, and as easy as:

~~~php
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Define message
$msg = new MessageRequest('users.profile.load', $var1, $var2, $obj1, $obj2

// Send message, and get response
$dispatcher = new Dispatcher('web1');
$user = $dispatcher->dispatch($msg)->getResponse();
~~~

That's all there really is to it.  Few notes regarding the above:

* The `MessageRequest` object can take as many parameters as desired, and may be anything you wish as long as it can be serialized.  Please see the [Message Requests](message_requests.md) page for details.
* For RPC calls, will always return a `MessageResponseInterface` object.  Please see the [Message Responses](message_responses.md) page for details.
* If sending a "queue" or "broadcast" message type, will return null as both are one-way only messages.
* The first argument when instantiating the `Dispatcher` class is the optional unique name of the server instance, allowing listening consumers to know which server instance the message originated from.


The `Dispatcher::dispatch()` method takes three arguments:

Variable: | Required | Type | Description
------------- |------------- |------------- |------------- 
`$msg` | Yes | MessageRequestInterface | The message being dispatched.  Please see the [Message Requests](message_requests.md) page for details.
`$msg_type` | No | string | The message type to dispatch, supported types are -- rpc, queue, broadcast.  Defaults to "rpc".
`$fe_handler_callback` | No | callable | If defined, will override any "fe_handler_callback" closure defind within the container definitions file, and will be invoked with the `FeHandlerInterface` object passed upon receiving a response from the consumer.  Used to update the output to end-user, and please see [Front-End Handlers](fe_handlers.md) page for details.


## Dispatch queue / broadcast Messages

Three different types of messages can be dispatched -- rpc, queue, broadcast.  The default is "rpc", but you may send a "queue" or "broadcast" message type by specifying it as the second argument of the `Dispatcher::dispatch()` method, such as:

~~~php
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;

// Define message
$msg = new MessageRequest('gallery.images.upload', $image_filename);

// Dispatch
$dispatcher = new Dispatcher('web1');
$dispatcher->dispatch($msg, 'queue');
~~~

Please note, "queue" and "broadcast" messages do not return a response, and only return null.


## Front-End Handler Callback

Within the container definitions file (eg. ~/config/container.php) you may define a "fe_handler_callback" closure, which if defined, will be invoked for every message dispatched with a `FeHandlerInterface` object passed to it.  Alternatively, you may pass a callback as the third argument to the `Dispatcher::dispatch()` method, which will override any closure specified within the container definitions file.

This helps streamline the process of the back-end consumer instances affecting change on the output to the front-end (eg. assign template variables, add callouts, etc.).  For full information, please visit the [Front-End Handlers](fe_handlers.md) page of this documentation.



