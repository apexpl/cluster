<?php
declare(strict_types = 1);

namespace Apex\Cluster\Message;

use Apex\Cluster\Interfaces\{MessageResponseInterface, MessageRequestInterface, FeHandlerInterface};


/**
 * The response message passed back to dispatchers from 
 * listeners for two-way RPC calls.
 */
class MessageResponse implements MessageResponseInterface
{


    // Response properties
    private int $status = 200;
    private array $response = [];
    private array $called = [];
    private string $consumer_name = '';
    private ?FeHandlerInterface $fe_handler = null;

    // Request properties
    private string $type = 'rpc';
    private string $instance_name = '';
    private string $routing_key;
    private array $caller;
    private mixed $request;

    /**
     * Constructor
     */
    public function __construct(MessageRequestInterface $msg, ?FeHandlerInterface $fe_handler = null)
    {

        // Clone properties over from request
        $this->fe_handler = $fe_handler;
        $this->type = $msg->getType();
        $this->instance_name = $msg->getInstanceName();
        $this->routing_key = $msg->getRoutingKey();
        $this->caller = $msg->getCaller();
        $this->request = $msg->getRequest();

        // Get params
        $this->params = $msg->getParams();

    }

    /**
     * Set consumer name
     */
    public function setConsumerName(string $name):void
    {
        $this->consumer_name = $name;
    }

    /**
     * Set status
     */
    public function setStatus(int $status):void
    {
        $this->status = $status;
    }

    /**
     * Set front-end handler
     */
    public function setFeHandler(FeHandlerInterface $fe_handler):void
    {
        $this->fe_handler = $fe_handler;
    }

    /**
     * Add response
     */
    public function addResponse(string $alias, mixed $data):void
    {
        $this->response[$alias] = $data;
    }

    /**
     * Add called
     */
    public function addCalled(string $alias, string $php_class):void
    {
        $this->called[$alias] = $php_class;
    }

    /**
     * Get consumer name
     */
    public function getConsumerName():string { return $this->consumer_name; }

    /**
     * Get status
     */
    public function getStatus():int { return $this->status; }

    /**
     * Get front-end handler
     */
    public function getFeHandler():?FeHandlerInterface { return $this->fe_handler; }

    /**
     * Get response
     */
    public function getResponse(string $alias = 'default'):mixed
    {
        return $this->response[$alias] ?? null;
    }

    /**
     * Get all responses
     */ 
    public function getAllResponses():array { return $this->response; }

    /**
     * Get instance name
     */
    public function getInstanceName():string { return $this->instance_name; }

    /**
     * Get called
     */
    public function getCalled():array { return $this->called; }

    /**
     * Get the message type. 
     */
    public function getType():string { return $this->type; }

    /**
     * Get the routing key 
     */
    public function getRoutingKey():string { return $this->routing_key; }

    /**
     * Get the caller array. 
     */
    public function getCaller():array { return $this->caller; }

    /**
     * Get request
     */
    public function getRequest():array { return $this->request; }

    /**
     * Get the params of the request. 
     */
    public function getParams():mixed
    { 
        return count($this->params) == 1 ? $this->params[0] : $this->params;
    }


}




