
# Cluster Examples

This directory contains various examples for all facets of Cluster.  To test via RabbitMQ, please ensure to modify the ~/config/container.php file and change the BrokerInterface::class to RabbitMQ by simply commenting out the Local::class line, and uncommenting the line for RabbitMQ.

If you do not have RabbitMQ running on this system, you can start an instance via Docker.  Within the root directory on Cluster, run the command:
    sudo docker-compose up -d

## Run Examples

To run the examples, first start a listener with "php listen.php <CONFIG>" where <CONFIG> is one of the following:
    basic - Basic RPC Configuration
    multi - Multiple PHP Classes
    autorouting - Auto-Routing
    params - Parameter Based Routing
    queue - Fetch Messages from Queue
    instances - Specific Server Instances

With each listener type there is a correspoding PHP file within this directory with the same name.  For example, if you start a listener with "php listen.php basic", then you the basic.php script in this directory to run the example.

Although not required, it's recommended you go through all examples in the order listed above as they expand on each other to cover all facets of Cluster.



