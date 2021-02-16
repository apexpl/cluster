
# Enable redis Auto-Loading

Cluster includes support for redis allowing easy centralization of configuration across all server instances.  When enabled, both the local container definitions file and YAML router file will be ignored, and instead will be loaded from redis, meaning when the configuration is updated on one server instance it will be updated across all server instances.


## Saving Configuration

First you need to save the configuration into redis, which can be done via the `saveRedis()` method.  Setup configuration as desired on one server instance, and once complete save the configuration to redis with the following example code:

~~~php
use Apex\Cluster\Cluster;
use redis

// Connect to redis
$redis = new redis();
$redis->connect('localhost', 6379);
$redis->auth('my_password');

// Start cluster, and save to redis
$cluster = new Cluster('app1');
$cluster->saveRedis($redis, true);

echo "Configuration saved to redis.\n";
~~~

Simple as that.  Whatever the current configuration is at the time `saveRedis()` method is called, is the configuration that will be saved into redis and shared across all server instances.  This includes the DI container file itself, all routes within the YAML configuration file, any routes you added via the PHP code, and all other configuration.

The `true` boolean as the second argument if defined will send a broadcast message to all listeners prompting them to restart and reload configuration upon doing so.  Please note, this is only applicable if the listeners are listening on the "broadcast" channel.


## Load Configuration

Once configuration is saved into redis, loading it is as simple as passing the redis connection object as the second argument when instantiating Cluster.  For example:

~~~php
use Apex\Cluster\Cluster;
use Apex\Cluster\Listener;
use redis;

// Connect to redis
$redis = new redis();
$redis->connect('localhost', 6379);
$redis->auth('my_password');

// Start cluster
$cluster = new Cluster('app1', $redis);

// Start listening
$listener = new Listener();
$listener->listen();
~~~

With the redis object being passed as the second argument, it will load all configuration from redis and ignore all local configuration.  If the local DI container file is outdated, it will be overwritten by the contents of the DI container file within redis.

Please note, when saving redis configuration do NOT pass the redis object to the constructor when instiantiating Cluster.  Leave it as null, otherwise you will be saving an obsolete configuration.


