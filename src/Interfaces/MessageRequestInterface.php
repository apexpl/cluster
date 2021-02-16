<?php
declare(strict_types = 1);

namespace Apex\Cluster\Interfaces;


/**
 * Interface for the RequestMessage object, which is 
 * the messages that are dispatched to the message broker.
 */
interface MessageRequestInterface 
{

    /**
     * Set instance name
     */
    public function setInstanceName(string $name):void;

    /**
    * Set the message type. 
    */
    public function setType(string $type):void;

    /**
     * Get instance name
     */
    public function getInstanceName():string;

    /**
     * Get the message type. 
     */
    public function getType():string;

    /**
     * Get the routing key 
     */
    public function getRoutingKey():string;

    /**
     * Get the caller array. 
     */
    public function getCaller():array;

    /**
     * Get request
     */
    public function getRequest():array;

    /**
     * Get the params of the request. 
     */
    public function getParams():mixed;

}


