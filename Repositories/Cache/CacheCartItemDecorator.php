<?php

namespace Modules\Cart\Repositories\Cache;

use Modules\Cart\Repositories\CartItemRepository;
use Modules\Core\Repositories\Cache\BaseCacheDecorator;

class CacheCartItemDecorator extends BaseCacheDecorator implements CartItemRepository
{
    public function __construct(CartItemRepository $cartitem)
    {
        parent::__construct();
        $this->entityName = 'cart.cartitems';
        $this->repository = $cartitem;
    }
}
