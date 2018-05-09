<?php

namespace Modules\Cart\Entities;

use Modules\Shop\Contracts\ShopProductOptionInterface;
use Modules\Product\Entities\Option;
use Illuminate\Database\Eloquent\Model;

class CartItemOption extends Model implements ShopProductOptionInterface
{
    protected $table = 'cart__cart_item_options';
    protected $fillable = [
        'product_option_id',
        'value',
    ];
    protected $appends = [
        'name',
        'slug',
        'type',
        'sort_order',
        'required',
        'values',
        'is_collection',
        'is_system',
    ];

    public function productOption()
    {
        return $this->belongsTo(Option::class, 'product_option_id');
    }

    public function getNameAttribute(): string
    {
        return $this->productOption->name;
    }

    public function getSlugAttribute(): string
    {
        return $this->productOption->slug;
    }

    public function getTypeAttribute(): string
    {
        return $this->productOption->type;
    }

    public function getSortOrderAttribute(): int
    {
        return $this->productOption->sort_order;
    }

    public function getRequiredAttribute()
    {
        return $this->productOption->required;
    }

    public function getValuesAttribute()
    {
        return $this->productOption->values;
    }

    public function getIsCollectionAttribute(): int
    {
        return $this->productOption->is_collection;
    }

    public function getIsSystemAttribute(): int
    {
        return $this->productOption->is_system;
    }
}
