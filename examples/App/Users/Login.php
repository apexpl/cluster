<?php
declare(strict_types = 1);

namespace App\Users;

use Apex\Cluster\Interfaces\{MessageRequestInterface, FeHandlerInterface};

/**
 * Load files as PSR4 is not available for examples.
 */
require_once(__DIR__ . '/UserModel.php');

/**
 * Example class
 */
class Login
{


    /**
     * Parse template
     */
    public function parse(MessageRequestInterface $msg, FeHandlerInterface $handler)
    {

        // Get request
        $request = $msg->getRequest();

        // Return random number
        return bin2hex(random_bytes(8));

    }

}

