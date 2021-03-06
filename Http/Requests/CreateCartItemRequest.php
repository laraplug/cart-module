<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCartItemRequest extends FormRequest
{
    public function rules()
    {
        return [
            'shop_id' => 'numeric',
            'product_id' => 'numeric',
            'quantity' => 'numeric',
            'option_values' => 'array',
            'note' => 'string',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [];
    }

}
