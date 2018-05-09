<?php

namespace Modules\Cart\Repositories\Eloquent;

use Modules\Cart\Repositories\CartItemRepository;
use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;

class EloquentCartItemRepository extends EloquentBaseRepository implements CartItemRepository
{
    /**
     * @inheritDoc
     */
    public function getWithBuilder($instance, $sessionId, $userId = 0, $productId = 0)
    {
        $query = $this->allWithBuilder()->where('instance', $instance);
        if($productId) $query = $query->where('product_id', $productId);

        $query = $query->where('session_id', $sessionId);
        if ($userId) $query = $query->orWhere('user_id', $userId);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew($instance, $shopId, $sessionId, $userId, $productId, array $options = [])
    {
        $items = $this->getWithBuilder($instance, $sessionId, $userId, $productId)->where('shop_id', $shopId)->get();
        $model = $items->filter(function($item) use ($options) {
            // 옵션정보가 같은 상품이 있는지 확인
            // Check if there's same option product
            $itemOptions = collect($item->options)->mapWithKeys(function($option) {
                return [$option['slug'] => $option['value']];
            });
            return collect($options)->diffAssoc($itemOptions)->count() === 0;
        })->first();
        if(!$model) {
            $model = $this->model->newInstance();
            $model->shop_id = $shopId;
            $model->instance = $instance;
            $model->product_id = $productId;
            $model->options = $options;
        }
        // Override session&user id
        $model->session_id = $sessionId;
        $model->user_id = $userId;
        return $model;
    }
}
