<?php
declare(strict_types = 1);

namespace Apex\Cluster\FeHandlers;

use Apex\Cluster\Interfaces\FeHandlerInterface;


/**
 * Generic front-end handler, and simply provides a place to 
 * add to and read from the front-end action queue.
 */
class Generic implements FeHandlerInterface
{

    // Properties
    private array $actions = [];


    /**
     * Assign template variable
     */
    final public function addAction(string $action, mixed $data):void
    {
        $this->actions[] = [$action, $data];
    }

    /**
     * Purge all actions
     */
    final public function purgeActions(string $action = ''):void
    {
        $this->actions = [];
    }

    /**
     * Get actions
     */
    final public function getActions():array
    {
        return $this->actions;
    }

}



