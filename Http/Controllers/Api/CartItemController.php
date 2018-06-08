<?php

namespace Modules\Cart\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Modules\Cart\Entities\Cart;
use Modules\Cart\Entities\CartItem;
use Modules\Cart\Http\Requests\CreateCartItemRequest;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;

class CartItemController extends Controller
{
    /**
     * @var Cart
     */
    private $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return response()->json([
            'errors' => false,
            'data' => $this->cart->items()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateCartItemRequest $request
     * @return Response
     */
    public function store(CreateCartItemRequest $request)
    {
        $errors = '';
        try {
            $this->cart->add(
                $request->shop_id,
                $request->product_id,
                $request->quantity,
                $request->get('option_values', []),
                $request->note
            );

            return response()->json([
                'errors' => false,
                'data' => $this->cart->items()
            ]);
        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        return response()->json([
            'errors' => true,
            'message' => $errors,
            'data' => $this->cart->items()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CartItem $cartitem
     * @param  UpdateCartItemRequest $request
     * @return Response
     */
    public function update(CartItem $cartitem, UpdateCartItemRequest $request)
    {
        $errors = '';
        try {

            $this->cart->update($cartitem->id, $request->quantity);

            return response()->json([
                'errors' => false,
                'data' => $this->cart->items()
            ]);

        } catch (\Exception $e) {
            $errors = $e->getMessage();
        }

        return response()->json([
            'errors' => true,
            'message' => $errors,
            'data' => $this->cart->items()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CartItem $cartitem
     * @return Response
     */
    public function destroy(CartItem $cartitem)
    {
        $this->cart->remove($cartitem->id);

        return response()->json([
            'errors' => false,
            'data' => $this->cart->items()
        ]);
    }
}
