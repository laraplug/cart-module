<?php

namespace Modules\Cart\Entities;

use Exception;
use Modules\Shop\Repositories\ShippingMethodManager;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Shop\Contracts\ShopItemInterface;
use Modules\Shop\Entities\Shop;

class CartItem extends Model implements ShopItemInterface
{
    protected $table = 'cart__cart_items';
    protected $fillable = [
        'price',
        'quantity',
        'note',
        'option_values',
    ];
    protected $casts = [
        'option_values' => 'collection',
    ];
    protected $appends = [
        'product',
        'total',
        'shipping_method_id',
        'shipping_storage_id',
    ];

    /**
     * @inheritDoc
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @inheritDoc
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->with('options');
    }

    /**
     * @inheritDoc
     */
    public function getProductAttribute()
    {
        if (!$this->relationLoaded('product')) {
            $this->load('product');
        }

        return $this->getRelation('product');
    }

    /**
     * @inheritDoc
     */
    public function getPriceAttribute()
    {
        return $this->getAttributeFromArray('price');
    }

    /**
     * @inheritDoc
     */
    public function getQuantityAttribute()
    {
        return $this->getAttributeFromArray('quantity');
    }

    /**
     * @inheritDoc
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * @inheritDoc
     */
    public function getChildrenAttribute()
    {
        // If product has children items
        if(!empty($this->product->items)) {
            return $this->product->items;
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethodIdAttribute()
    {
        return $this->product->shipping_method_id;
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethodAttribute()
    {
        return $this->shipping_method_id ? app(ShippingMethodManager::class)->find($this->shipping_method_id) : null;
    }

    /**
     * @inheritDoc
     */
    public function getShippingStorageIdAttribute()
    {
        return $this->product->shipping_storage_id ?: 0;
    }

    /**
     * @inheritDoc
     */
    public function toOrderItemArray(ShopItemInterface $parentItem = null)
    {
        return $this->toArray();
    }

    /**
     * 지정된 수량으로변경 가능한지 체크
     * @param  int    $quantity
     * @return int
     */
    public function canChangeQuantity(int $quantity)
    {
        $min = $this->product->min_order_limit;
        $max = $this->product->max_order_limit;
        if($min !== 0 && $quantity < $min) {
            throw new Exception(trans('cart::cartitems.messages.must set at least', ['quantity'=>$min]));
        }
        if($max !== 0 && $quantity > $max) {
            throw new Exception(trans('cart::cartitems.messages.can set up to', ['quantity'=>$max]));
        }
        return $quantity;
    }

}
