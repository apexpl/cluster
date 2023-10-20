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
class Profiles
{


    /**
     * Create user
     */
    public function create(MessageRequestInterface $msg, FeHandlerInterface $handler)
    {

        // Get params
        list($user, $email) = $msg->getParams();

        // Create user
        $user = new UserModel($user, $email);

        // Return
        return $user;
    }

    /**
     * Load
     */
    public function load(MessageRequestInterface $msg, FeHandlerInterface $handler) {

        // Get params
        $username = $msg->getParams();

        // Create user object
        $user = new UserModel($username, $username . '@domain.com');

        // Return
    return $user;
    }



}

