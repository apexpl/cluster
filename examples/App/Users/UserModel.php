<?php
declare(strict_types = 1);

namespace App\Users;


/**
 * Simple model classs to showcase passing objects within Cluster.  Used by 
 * the App\Users\Profiles::create() method.
 */
class UserModel
{

    /**
     * Construct
     */
    public function __construct(
        public string $username, 
        public string $email, 
        public int $userid = 0
    ) { 
        $this->userid = rand(1000, 9999);
    }

}


