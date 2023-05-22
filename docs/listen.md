
# Listen / Consume Messages

Starting a new listener to consume messages is easily done via the `Listener` class, and can be as simple as:

~~~php
use Apex\Cluster\Listener;

// Start listening
$listener = new Listener('app3');
$listener->listen();
~~~

The `Listener` class accepts one argument being the unique name for this instance, which may affect the messages routed to it depending on your router configuration.  The above works fine assuming both, you are using YAML based router configuration, and are using the default ~/config/router.yml location for your router file.  Otherwise, you will need to instantiate the `Cluster` class first, such as:

~~~php
use Apex\Cluster\Cluster;
use Apex\Cluster\Listener;

// Start cluster
$cluster = new Cluster(
    instance_name: 'app1', 
    router_file: /path/to/router.yml
);

// Add necessary routes
$cluster->addRoute(....);

// Start listening
$listener = new Listener();
$listener->listen();
~~~

The `Listener::listen()` method accepts three optional arguments, described in the below table:

Variable | Type | Description
------------- |------------- |------------- 
`$screen_logging` | bool | Whether or not to print logs to the screen.  Defaults to true.
`$max_msg` | int | Only applies to routes of message type "queue", and is the number of concurrent messages the listener will accept.  Defaults to YAML configuration if defined, or to 1.
`$prepare_msg_handler` | callable | If defined, will be invoked for every message received to prepare environment for processing.  This will override any "prepare_msg_handler" closure set within the container definitions file.  Defaults to null.


## PHP Consumers

A few notes regarding PHP consumers:

* The PHP classes that act as consumers may be located anywhere, and please review documentation regarding the router for details on how to specify which PHP classes get invoked for incoming messages.
* All routing keys comprise of three segments seperated by periods.  The third segment is always the name of the method that will be called within the PHP consumers. 
* Two arguments are passed to every method, a `MessageRequestInterface` and `FeHandlerInterface`.  Please see the [Message Requests](message_requests.md) and [Front-End Handlers](fe_handers.md) pages for full details on both arguments.
* The methods may return anything you wish that can be serialized, and for PRC calls it will be returned to the dispatcher.  This includes variables, arrays and objects, but does not include closures or resources.

For example, if the router is configured so all messages to the routing key "users.profile.*" are routed to the PHP class `App\Users\Profiles`:

~~~php
use Apex\Cluster\Interfaces\{MessageRequestInterface, FeHandlerInterface};
class Profiles
{

    /**
    * Create
    */
    public function create(MessageRequestInterface $msg, FeHandlerInterface $handler)
    {

        // Get messge info
        list($var1, $var2, $var3) = $msg->getParams();
        $request = $msg->getRequest();

        // Get all form POST fields
        $post = $request['post'];

        // Create user
        $user = createUserModelObject();

        // Return
        return $user;

    }

    /**
     * Load
     */
    public function get(MessageRequestInterface $msg, FeHandlerInterface $handler)
    {

        // Get id# of user to retrieve
        $userid = $msg->getParams();

        // Load user profile
        $user = loadUserModelObject();

        // Return
        return $user;
    }
}
~~~

In the above example, all messages sent to the routing key "users.profile.create" will invoke the above `create()` method, and all messages sent to "users.profile.load" will invoke the `load()` method.  Again, these methods may return anything you wish as long as it can be serialized.  Please see the [Message Requests](message_requests.md) page for details regarding the `MessageRequestInterface` object that is passed to each method. 


## Message Preparation Handler

Within the container definitions file (eg. ~/config/container.php) you may define a "prepare_msg_handler" closure, and if defined, this closure will be invoked against every incoming message received.  Alternatively, you may also pass a closure as the third argument to the `Listener::listen()` method when starting a listener, which will override any closure defined within the container definitions file.

This provides the ability to setup your specific environment as necessary for processing of incming messages, such as if you need to inject inputs into your specific framework, instantiate an object for the authenticated user, and things of that nature.


