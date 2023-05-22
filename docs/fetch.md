
# Fetch Messages from Queues

Using the `Fetcher` class you can fetch messages from a queue that have been previously dispatched and are awaiting processing.  Please note, if you plan on using this functionality such as allowing messages to build up in a queue, then process via a crontab job or similar, you should use a separate YAML router file to define the queues.  This will ensure the messages don't get passed on to an active listener.

Instead of instantiating the `Cluster` class as you normally would, write a separate YAML router file only for the queues that will be fetched from, and instantiate `Cluster` with that file:

~~~php
use Apex\Cluster\Cluster;

$cluster = new Cluster(
    instance_name: fetch1, 
    router_file: /path/to/queues.yml
);
~~~


## Fetching From Queues

Fetching pending messages from queus is very simplistic, and for example:

~~~php
use Apex\Cluster\Fetcher;

$fetcher = new Fetcher('fetch1');
while ($msg = $fetcher->fetch('some_queue_name)) { 

    $params = $msg->getParams();

    // Process message...
}
~~~

That's all there is to it.  The one argument passed to the `Fetcher::fetch()` method needs to be the queue name which the route definition was created as.  Within the YAML router file, every route definition has a unique name, and this is the queue name you pass to fetch messages.


