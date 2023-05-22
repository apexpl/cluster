
# Front-End Handlers

Cluster provides support for front-end handlers, helping streamline the process of back-end consumer instances affecting output of the front-end instances (eg. assign template variables, change HTTP status code, et al).

The front-end handler being utilized can be set within the container definitions file (ie. ~/config/container.php) as the `FeHandlerInterface::class` item.  All supported front-end handlers can be found within the ~/src/FeHandlers directory, the default being Generic.

## Usage

In short, the process flow for front-end handlers is:

1. When a consumer receives a message, it will instantiate the `FeHandlerInterface`, and pass it as the second argument to all consumer methods being called.
2. The `FeHandlerInterface` object acts as a queue for all actions that need to be performed on the front-end.  The methods available depend on the handler class being utilized, but the generic `addAction($action, $data)` method can also be used to add actions to the queue (see below).
3. Upon the dispatcher receiving a response it will invoke the necessary callback and pass the `FeHandlerInterface` object to it.  The callback invoked will either be the callable passed as the third argument to the `Dispatcher::dispatch()` method, or the "fe_hander_callback" closure defined within the container definitions file.
4. The closure should retrieve all actions from the `FeHandlerInterface` class and perform the necessary actions on the front-end.

Simple as that, and below is a brief example:

**Dispatcher**
~~~php
use Apex\Cluster\Dispatcher;
use Apex\Cluster\Message\MessageRequest;
use Apex\Cluster\Interfaces\FeHandlerInterface;

// Define callback
$callback = function(FeHandlerInterface $handler) {

    $actions = $handler->getActions();
    foreach ($actions as $vars) { 
        $action = $vars[0];
        $data = $vars[1];

        if ($action == 'set_var') { 
            echo "Assigning Template Variable: $data[0] = $data[1]<br />\n";
        }
};

// Set message
$msg = new MessageRequest('transaction.orders.add', 58.35, 'XYZ Product');

// Dispatch a message
$dispatcher = new Dispatcher('web1');
$response = $dispatcher->dispatch($msg, 'rpc', $callback);

// Get response
$order_id = $response->getResponse();
echo "Order ID: $order_id\n";
~~~


**Consumer**

This assumes you already have the appropriate [routing](router.md) in place so messages sent to the routing key "transaction.orders.*" are routed to this PHP class.

~~~php

use Apex\Cluster\Interfaces\{MessageRequestInterface, FeHandlerInterface};

class orders 
{

    public function add(MessageRequestInterface $msg, FeHandlerInterface $fe_handler)
    {

        // Add new order, and generate order id
        $order_id = 12345;

        // Assign template variable to front-end
        $fe_handler->addAction('set_var', ['order_id', $order_id]);

        // Return
        return $order_id;
    }

}
~~~


Running the above dispatcher would result in:

~~~
Assigning Template Variable: order_id = 12345<br />
Order ID: 12345
~~~

The consumer method added an action to the `FeHandlerInterface` to assign a template variable.  Upon the dispatcher receiving the response, it invoked the callback passed, which should go through all actions within the `FeHandlerInterface` object and assign any necessary template variables, or complete any necessary actions that affect the output to the end-user.


