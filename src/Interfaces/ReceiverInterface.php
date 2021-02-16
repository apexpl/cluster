<?php
declare(strict_types = 1);

namespace Apex\Cluster\Interfaces;


/**
 * Receiver interface used only for recieving messages from RabbitMQ to help
 * keep AmqpMessages as amsll as possible.
 */
interface ReceiverInterface
{
    /**
     * onMessage
     */
    public function receive($msg);



}


