
# Cluster class / Container Definitions

The central class within Cluster, and facilitates all functionality.  Although required for all functionality, the `Dispatcher`, `Listener` and `Fetcher` classes will automatically instantiate the `Cluster` class if has not already been done.  However, if you're utilizing either redis or non-default locations for either the container definitions or YAML router files, you will need to instantiate the `Cluster` class each time.

The constructor accepts four arguments:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$instance_name` | Yes | string | The unique name of the server instance (eg. app1, app2, web1, web2, etc.)
`$redis` | No | redis | If a redis connection is passed, configuration will be auto-loaded from within redis while ignoring local container definition and YAML router files.
`$router_file` | No | string | Location of the YAML router file to load.  Defaults to ~/config/router.yml
`$container_file` | No | string | Container definitions file to load.  Defaults to ~/config/container.php

For example:

~~~php
use Apex\Cluster\Cluster;
use Apex\Cluster\Listener;

// Start cluster
$cluster = new Cluster(
    instance_name: 'app1',  
    router_file: /path/to/my/router.yml, 
    container_file: /path/to/container.php
);

// Start listening
$listener = new Listener();
$listener->listen();
~~~


## Container Definitions File

The default location of the container definitions file is at ~/config/container.php, although a different location can be defined via the `$container_file` variable as the fourth argument when instantiating the Cluster class.

The below table describes all items found within this file, and modify accordingly to your specific needs.  Most importantly, you may want to modify the message broker being used as it defaults to local, and the timeout handler which is called when RPC calls timeout.

Item | Description
------------- |------------- 
`BrokerInterface::class` | The message broker being used, defaults to the local broker but can easily be switched to RabbitMQ as shown in the default container file.
`FeHandlerInterface::class` | The front-end handler being used, which helps allow back-end consumer instances to effect changes on the front-end output (eg. assign template variables, change http status, et al).  Please view the <a href="fe_handlers.md">Front-end Endhandlers</a> page of this documentation for details.
`LoggerInterface::class` | Any PSR-3 compliant logger, defaults to the popular Monolog package.  If left as is, logs will appear within the ~/cluster.log file.  To disable logging, simply comment out this line.
`timeout_seconds` | The number of seconds without a response before a RPC call times out.
timeout_handler | Must be a callable / closure, and accepts one argument being a `MessageRequestInterface` of the message being sent.  If defined, will be called when an RPC call times out.
prepare_msg_handler | If this closure is defined, every incoming message received will be passed through it, and is meant to prepare your environment as necessary to process the message, such as injecting inputs into a framework, instantiating an auth session, et al.  Note, a callback may also be passed to the `Listener::listen()` method which overrides this setting.
fe_handler_callback | If this closure is defined, it will be invoked every time a dispatcher receives a response to a RPC call, and is meant to process all actions within the front-end handler to change output to the end-user.  Note, a callback can be passed to the `Dispatcher::dispatch()` method which overrides this setting.  Please see the [Front-End Handlers](fe_handlers.md) page for details.
`custom_router` | Generally not required, but must be a callable / closure and only needed if you wish to override the built-in router altogether.  Takes one argument being an instance of `MessageRequestInterface`, and must return an associative array, keys being the response alias (eg. "default") and the values being full class name to the PHP class to execute.
`ReceiverInterface::class` | Should almost always be left as is, and only modified if you plan to write your own version of the /src/Receiver.php file.


