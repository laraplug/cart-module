<?php

namespace Modules\Cart\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Cart\Entities\Cart;
use Modules\Cart\Events\Handlers\RegisterCartSidebar;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Core\Traits\CanPublishConfiguration;

class CartServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->app['events']->listen(BuildingSidebar::class, RegisterCartSidebar::class);

        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            $event->load('cartitems', array_dot(trans('cart::cartitems')));
            // append translations
        });
    }

    public function boot()
    {
        $this->publishConfig('cart', 'config');
        $this->publishConfig('cart', 'permissions');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    private function registerBindings()
    {
        $this->app->bind(
            'Modules\Cart\Repositories\CartItemRepository',
            function () {
                $repository = new \Modules\Cart\Repositories\Eloquent\EloquentCartItemRepository(new \Modules\Cart\Entities\CartItem());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Cart\Repositories\Cache\CacheCartItemDecorator($repository);
            }
        );
        // add bindings

        $this->app->singleton('shop.cart', function ($app) {
            return new Cart(
                $app['Modules\Cart\Repositories\CartItemRepository'],
                $app['Modules\Product\Repositories\ProductRepository'],
                $app['Modules\User\Contracts\Authentication'],
                $app['Illuminate\Session\SessionManager']
            );
        });
    }
}
