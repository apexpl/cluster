<?php
declare(strict_types = 1);

namespace App\Shop;

use App\Users\UserModel;

/**
 * Example order model.
 */
class OrderModel
{

    /**
     * Construct
     */
    public function __construct(
        public UserModel $user, 
        public string $product, 
        public float $amount, 
        public string $id = ''
    ) {
        $this->id = $user->userid . '-' . rand(100000, 99999);
    }

}



