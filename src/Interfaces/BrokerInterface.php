<?php
declare(strict_types = 1);

namespace Apex\Cluster\Interfaces;

use Apex\Cluster\Interfaces\{MessageRequestInterface, MessageResponseInterface};

/**
 * Broker interface which handles various functionality with the message 
 * broker itself such an opening / closing connections.
 */
interface BrokerInterface
{


    /**
     * Open connection, and return new channel.
     */
    public function openChannel();


    /**
     * Close connection and channel.
     */
    public function closeChannel():void;


    /**
     * Declare exchange
     */
    public function declareExchange(string $name, string $type, bool $durable, bool $auto_delete):void;


    /**
     * Delete exchange
     */
    public function deleteExchange(string $name):void;

    /**
     * Declare queue
     */
    public function declareQueue(string $name, bool $durable, bool $exclusive, bool $auto_delete);


    /**
     * Bind queue to an exchange.
     */
    public function bindQueue(string $queue, string $exchange, string $routing_key):void;


    /**
     * Unbind queue from an exchange.
     */
    public function unbindQueue(string $queue, string $exchange, string $routing_key = ''):void;


    /**
     * Delete queue
     */
    public function deleteQueue(string $name):void;


    /**
     * Purge queue
     */
    public function purgeQueue(string $name):void;


    /**
     * Consume
     */
    public function consume(string $queue, bool $no_ack, bool $exclusive, callable $callback, int $max_msg):void;

    /**
     * Wait
     */
    public function wait(bool $is_rpc_dispatcher, int $timeout):void;

    /**
     * Publish message
     */
    public function publish(MessageRequestInterface $msg):?MessageResponseInterface;

    /**
     * Extract MessageRequestInterface object from body of message received by listener / consumer.
     */
    public function extractMessage($msg):MessageRequestInterface;


    /**
     * Ack
     */
    public function ack($msg):void;


    /**
     * Nack
     */
    public function nack($msg):void;

    /**
     * Send reply for PRC call.
     */
    public function reply($data, $msg);

    /**
     * Fetch next message in queue.
     */
    public function fetch (string $queue, bool $no_ack = false):?MessageRequestInterface;


}

