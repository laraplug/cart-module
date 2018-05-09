<?php

use Illuminate\Routing\Router;
/** @var Router $router */

$router->group(['prefix' =>'/cart'], function (Router $router) {
    $router->bind('cartitem', function ($id) {
        return app('Modules\Cart\Repositories\CartItemRepository')->find($id);
    });
    $router->get('cartitems', [
        'as' => 'admin.cart.cartitem.index',
        'uses' => 'CartItemController@index',
        'middleware' => 'can:cart.cartitems.index'
    ]);
    $router->get('cartitems/create', [
        'as' => 'admin.cart.cartitem.create',
        'uses' => 'CartItemController@create',
        'middleware' => 'can:cart.cartitems.create'
    ]);
    $router->post('cartitems', [
        'as' => 'admin.cart.cartitem.store',
        'uses' => 'CartItemController@store',
        'middleware' => 'can:cart.cartitems.create'
    ]);
    $router->get('cartitems/{cartitem}/edit', [
        'as' => 'admin.cart.cartitem.edit',
        'uses' => 'CartItemController@edit',
        'middleware' => 'can:cart.cartitems.edit'
    ]);
    $router->put('cartitems/{cartitem}', [
        'as' => 'admin.cart.cartitem.update',
        'uses' => 'CartItemController@update',
        'middleware' => 'can:cart.cartitems.edit'
    ]);
    $router->delete('cartitems/{cartitem}', [
        'as' => 'admin.cart.cartitem.destroy',
        'uses' => 'CartItemController@destroy',
        'middleware' => 'can:cart.cartitems.destroy'
    ]);
// append

});
