<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group([
    'prefix' => '/cart',
    'middleware' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        'bindings',
    ]
], function (Router $router) {

    $router->bind('item', function ($id) {
        return app('Modules\Cart\Repositories\CartItemRepository')->find($id);
    });
    $router->get('items', [
        'as' => 'api.cart.items.index',
        'uses' => 'CartItemController@index',
    ]);
    $router->post('items', [
        'as' => 'api.cart.items.store',
        'uses' => 'CartItemController@store',
    ]);
    $router->put('items/{item}', [
        'as' => 'api.cart.items.update',
        'uses' => 'CartItemController@update',
    ]);
    $router->delete('items/{item}', [
        'as' => 'api.cart.items.destroy',
        'uses' => 'CartItemController@destroy',
    ]);

});
