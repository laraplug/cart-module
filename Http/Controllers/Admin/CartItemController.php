<?php

namespace Modules\Cart\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Cart\Entities\CartItem;
use Modules\Cart\Http\Requests\CreateCartItemRequest;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;
use Modules\Cart\Repositories\CartItemRepository;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;

class CartItemController extends AdminBaseController
{
    /**
     * @var CartItemRepository
     */
    private $cartitem;

    public function __construct(CartItemRepository $cartitem)
    {
        parent::__construct();

        $this->cartitem = $cartitem;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //$cartitems = $this->cartitem->all();

        return view('cart::admin.cartitems.index', compact(''));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('cart::admin.cartitems.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateCartItemRequest $request
     * @return Response
     */
    public function store(CreateCartItemRequest $request)
    {
        $this->cartitem->create($request->all());
        return redirect()->route('admin.cart.cartitem.index')
            ->withSuccess(trans('core::core.messages.resource created', ['name' => trans('cart::cartitems.title.cartitems')]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  CartItem $cartitem
     * @return Response
     */
    public function edit(CartItem $cartitem)
    {
        return view('cart::admin.cartitems.edit', compact('cartitem'));
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
        $this->cartitem->update($cartitem, $request->all());

        return redirect()->route('admin.cart.cartitem.index')
            ->withSuccess(trans('core::core.messages.resource updated', ['name' => trans('cart::cartitems.title.cartitems')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CartItem $cartitem
     * @return Response
     */
    public function destroy(CartItem $cartitem)
    {
        $this->cartitem->destroy($cartitem);

        return redirect()->route('admin.cart.cartitem.index')
            ->withSuccess(trans('core::core.messages.resource deleted', ['name' => trans('cart::cartitems.title.cartitems')]));
    }
}
