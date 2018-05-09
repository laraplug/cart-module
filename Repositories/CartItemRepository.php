<?php

namespace Modules\Cart\Repositories;

use Modules\Core\Repositories\BaseRepository;

interface CartItemRepository extends BaseRepository
{

    /**
     * Get with Builder
     * @param  string   $instance
     * @param  string   $sessionId
     * @param  int      $userId
     * @param  int      $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getWithBuilder($instance, $sessionId, $userId = 0, $productId = 0);

    /**
     * First or New Instance
     * @param  string  $instance
     * @param  int  $shopId
     * @param  int  $sessionId
     * @param  int  $userId
     * @param  int  $productId
     * @param  array $options
     * @return \Modules\Cart\Entities\CartItem
     */
    public function firstOrNew($instance, $shopId, $sessionId, $userId, $productId, array $options = []);

}
