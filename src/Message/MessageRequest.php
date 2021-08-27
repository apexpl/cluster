<?php
declare(strict_types = 1);

namespace Apex\Cluster\Message;

use Apex\Cluster\Router\Validator;
use Apex\Cluster\Interfaces\MessageRequestInterface;
use Apex\Cluster\Exceptions\ClusterInvalidRoutingKeyException;

/**
 *Message Request model.
 */
class MessageRequest implements MessageRequestInterface 
{

    // Properties
    private string $type = 'rpc';
    private string $instance_name = '';
    private ?string $target = null;
    private array $caller;
    private array $request;

    /**
     * Constructor
     */
    public function __construct(
        private string $routing_key, 
        ...$params
    ) {

    // Set params
    $this->params = $params;

        // Start request
        $this->request = [
            'mode' => php_sapi_name() == 'cli' ? 'cli' : 'http', 
            'host' => $_SERVER['HTTP_HOST'] ?? '', 
            'port' => $_SERVER['SERVER_PORT'] ?? 80, 
            'uri' => $_SERVER['REQUEST_URI'] ?? '', 
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET', 
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '', 
            'post' => filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING), 
            'get' => filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING), 
            'cookie' => filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING), 
            'server' => filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING), 
            'http_headers' => function_exists('getAllHeaders') ? getAllHeaders() : []
        ];

        // Add CLI args to request, if needed
        if ($this->request['mode'] == 'cli') {  
            global $argv;
            $this->request['script_file'] = array_unshift($argv);
            $this->request['argv'] = $argv;
        }

        // Get caller function / class
        $trace = debug_backtrace();
        $this->caller = array(
            'file' => $trace[0]['file'] ?? '',  
            'line' => $trace[0]['line'] ?? 0,
            'function' => $trace[1]['function'] ?? '',
            'class' => $trace[1]['class'] ?? ''
        );

        // Parse routing key
        if (!preg_match("/^(\w+?)\.(\w+?)\.(\w+)$/", strtolower($routing_key), $match)) { 
            throw new ClusterInvalidRoutingKeyException("Invalid routing key, $routing_key.  Must be formatted as x.y.z");
        }

    }

    /**
     * Set instance name
     */
    public function setInstanceName(string $name):void
    {
        $this->instance_name = $name;
    }

    /**
    * Set the message type. 
    */
    public function setType(string $type):void
    {
        Validator::validateMsgType($type);
        $this->type = $type; 
    }

    /**
     * Set target
     */
    public function setTarget(string $target):void
    {
        $this->target = $target;
    }

    /**
     * Get instance name
     */
    public function getInstanceName():string { return $this->instance_name; }

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
     * Get target
     */
    public function getTarget():?string
    {
        return $this->target;
    }

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

