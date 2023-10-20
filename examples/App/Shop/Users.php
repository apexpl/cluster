<?php
declare(strict_types = 1);

namespace App\Shop;

use Apex\Cluster\Interfaces\{MessageRequestInterface, FeHandlerInterface};
use App\Users\UserModel;

/**
 * Load files as PSR4 is not available for examples.
 */
require_once(__DIR__ . '/OrderModel.php');
require_once(__DIR__ . '/../Users/UserModel.php');


/**
 * Example class
 */
class Users
{


    /**
     * Load
     */
    public function load(MessageRequestInterface $msg, FeHandlerInterface $hander)
    {

        // Get params
        $username = $msg->getParams();

        // Create user
        $user = new UserModel($username, $username . '@domain.com');

        // Create some orders
        $orders = [
            new OrderModel($user, 'Dell Inspiron Laptop', 949.95), 
            new OrderModel($user, 'Coffee Table', 89.90), 
            new OrderModel($user, 'Mechanical Keyboard', 129.95)
        ];

        // Return
        return $orders;

    }

}



