<?php
declare(strict_types = 1);

namespace Apex\Cluster\Interfaces;


/**
 * Interface for the message responses, returned from the consumers back to the 
 * dispatchers after every completed RPC call.
 */
interface MessageResponseInterface 
{

    /**
     * Set consumer name
     */
    public function setConsumerName(string $name):void;

    /**
     * Set status
     */
    public function setStatus(int $status):void;

    /**
     * Set front-end handler
     */
    public function setFeHandler(FeHandlerInterface $fe_handler):void;

    /**
     * Add response
     */
    public function addResponse(string $alias, mixed $data):void;

    /**
     * Add called
     */
    public function addCalled(string $alias, string $php_class):void;

    /**
     * Get consumer name
     */
    public function getConsumerName():string;

    /**
     * Get status
     */
    public function getStatus():int;

    /**
     * Get front-end handler
     */
    public function getFeHandler():?FeHandlerInterface;

    /**
     * Get response
     */
    public function getResponse(string $alias = 'default'):mixed;

    /**
     * Get all responses
     */
    public function getAllResponses():array;

    /**
     * Get called
    public function getCalled():array;

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


