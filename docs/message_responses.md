
# Message Responses

Every RPC call dispatched will return a `EventResponseInterface` object, and this class will never need to be instantiated by you.  It contains all properties and methods of the `MessageRequestInterface` object, so please ensure to read the [Message Requests](message_requests.md) details for those.

Aside from cloning the message request, this object also contains additional properties and methods as described below.


## Properties

On top of the message request properties, the response object also contains the following:

Property | Type | Description
------------- |------------- |------------- 
`$status` | int | The response status, mimics HTTP status codes (eg. 200 = ok, 505 = error, et al).
`$consumer_name` | string | The name of the consuming server instance that processed the request.
`$response` | array | The responses from each PHP class called.
`$called` | array | An array of all PHP classes called during processing of the request.
`$fe_handler` | FeHandlerInterface | The front-end handler, see [Front-End Handlers](fe_handlers.md) page for details.

## Methods

On top of the message request methods, the response object also contains the following methods:

Method | Description
------------- |-------------
`getResponse(string $alias):mixed` | Returns the response from the PHP class with the corresponding `$alias`. 
`getAllResponses():array` | Returns an associative array of all responses, the keys being the aliases, and the values being what was returned by the corresponding PHP class.
`getCalled():array` | Returns associative array of all PHP classes called, the keys being the response alias, and the values being the PHP class called.
`getStatus():int` | Returns the status of the response.
`getConsumerName():string` | Returns the name of the server instance who consumed the message.
`setStatus(int $status)` | Set the status of the response.
`setConsumerName(string $name)` | Set the name of the server instance consuming the message.
`addResponse(string $alias, mixed $data)` | You will never need to call this directly, but sets an element within the `$response` property.
`addCalled(string $alias, string $php_class)` | You will never need to call this directly, but will add an element to the `$called` array for which PHP class was called.


