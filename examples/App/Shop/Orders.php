<?php
declare(strict_types = 1);

namespace App\Shop;

use Apex\Cluster\Interfaces\{MessageRequestInterface, FeHandlerInterface};

/**
 * Load files as PSR4 is not available for examples.
 */
require_once(__DIR__ . '/OrderModel.php');


/**
 * Example class
 */
class Orders
{

    /**
     * Add new order
     */
    public function add(MessageRequestInterface $msg, FeHandlerInterface $handler)
    {

        // Get params
        list($user, $product, $amount) = $msg->getParams();

        // Create order
        $order = new OrderModel($user, $product, (float) $amount);

        // Return
        return $order;
    }

    /**
     * Upload
     */
    public function upload(MessageRequestInterface $msg, FeHandlerInterface $handler)
    {
        sleep(2);
        return true;
    }

}



