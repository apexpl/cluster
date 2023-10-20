<?php
declare(strict_types = 1);

namespace App\Wallets;

use Apex\Cluster\Interfaces\{MessageRequestInterface, FeHandlerInterface};


/**
 * Example class
 */
class Bitcoin
{


    /**
     * create wallet
     */
    public function create(MessageRequestInterface $msg, FeHandlerInterface $hander)
    {

        // Return
        return "Bitcoin wallet created with seed: " . bin2hex(random_bytes(8));

    }

}



