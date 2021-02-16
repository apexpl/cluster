<?php
declare(strict_types = 1);

namespace Apex\Cluster\Sys;

use Apex\Cluster\Cluster;
use Apex\Container\Di;
use Apex\Cluster\Interfaces\{BrokerInterface, MessageRequestInterface};


/**
 * Handles broadcast system messages to cluster listeners, such as shutdown, reload configuration, etc.
 */
class Sys
{

    /**
     * Shutdown
     */
    public function shutdown(MessageRequestInterface $req):void
    {

        // Get broker
        $cluster = Di::get(Cluster::class);
        $broker = Di::get(BrokerInterface::class);

        // Shutdown
        $cluster->addLog("Received shutdown command.  Closing connection...");
        $broker->closeChannel();
        exit(0);
    }


}



