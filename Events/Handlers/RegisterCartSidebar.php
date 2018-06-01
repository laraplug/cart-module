<?php

namespace Modules\Cart\Events\Handlers;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Core\Events\BuildingSidebar;
use Modules\User\Contracts\Authentication;

class RegisterCartSidebar implements \Maatwebsite\Sidebar\SidebarExtender
{
    /**
     * @var Authentication
     */
    protected $auth;

    /**
     * @param Authentication $auth
     *
     * @internal param Guard $guard
     */
    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    public function handle(BuildingSidebar $sidebar)
    {
        $sidebar->add($this->extendWith($sidebar->getMenu()));
    }

    /**
     * @param Menu $menu
     * @return Menu
     */
    public function extendWith(Menu $menu)
    {
        // $menu->group(config('asgard.cart.config.sidebar-group'), function (Group $group) {
        //     $group->item(trans('cart::cartitems.title.cartitems'), function (Item $item) {
        //         $item->icon('fa fa-shopping-cart');
        //         $item->weight(10);
        //         $item->route('admin.cart.cartitem.index');
        //         $item->authorize(
        //             $this->auth->hasAccess('cart.cartitems.index')
        //         );
        //     });
        // });

        return $menu;
    }
}
