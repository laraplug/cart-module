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
        'options',
    ];
    protected $casts = [
        'options' => 'array',
    ];
    protected $appends = [
        'options',
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
        return $this->belongsTo(Product::class);
    }

    /**
     * @inheritDoc
     */
    public function options()
    {
        return $this->hasMany(CartItemOption::class, 'cart_item_id');
    }

    /**
     * @inheritDoc
     */
    public function getProductAttribute()
    {
        return $this->product()->first();
    }

    /**
     * @inheritDoc
     */
    public function getOptionsAttribute()
    {
        return $this->options()->get();
    }

    /**
     * Save Options
     * @param array $options
     */
    public function setOptionsAttribute(array $options = [])
    {
        static::saved(function ($model) use ($options) {
            $savedOptionIds = [];
            foreach ($options as $slug => $value) {
                if (empty($value) || !$productOption = $this->product->options()->where('slug', $slug)->first()) {
                    continue;
                }
                $option = $this->options()->updateOrCreate([
                    'product_option_id' => $productOption->id,
                ],[
                    'value' => $value
                ]);
                $savedOptionIds[] = $option->id;
            }
            $this->options()->whereNotIn('id', $savedOptionIds)->delete();
        });
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
        $product = $this->product()->first();
        $min = $product->min_order_limit;
        $max = $product->max_order_limit;
        if($min !== 0 && $quantity < $min) {
            throw new Exception(trans('cart::cartitems.messages.must set at least', ['quantity'=>$min]));
        }
        if($max !== 0 && $quantity > $max) {
            throw new Exception(trans('cart::cartitems.messages.can set up to', ['quantity'=>$max]));
        }
        return $quantity;
    }

}
