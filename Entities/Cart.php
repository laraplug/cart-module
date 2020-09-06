<?php

namespace Modules\Cart\Entities;

use Illuminate\Session\SessionManager;
use Modules\Cart\Repositories\CartItemRepository;
use Modules\Shop\Repositories\ShippingMethodManager;

use Modules\Product\Repositories\ProductRepository;
use Modules\Shop\Contracts\ShopCartInterface;
use Modules\Shop\Facades\Shop;
use Modules\User\Contracts\Authentication;
use PhpParser\Node\Expr\Array_;

/**
 * Cart Model
 */
class Cart implements ShopCartInterface
{
    /**
     * Default Instance
     * @var string
     */
    const DEFAULT_INSTANCE = 'default';

    /**
     * @var CartItemRepository
     */
    private $cartItem;

    /**
     * @var ProductRepository
     */
    private $product;

    /**
     * @var Authentication
     */
    private $auth;

    /**
     * Instance of the session manager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    private $session;

    /**
     * Holds the current cart instance.
     *
     * @var string
     */
    private $instance;

    /**
     * @param CartItemRepository $cartItem
     * @param ProductRepository $product
     * @param Authentication $auth
     * @param SessionManager $session
     */
    public function __construct(CartItemRepository $cartItem, ProductRepository $product, Authentication $auth, SessionManager $session)
    {
        $this->cartItem = $cartItem;
        $this->product = $product;
        $this->auth = $auth;
        $this->session = $session;

        $this->instance(self::DEFAULT_INSTANCE);
    }

    /**
     * @inheritDoc
     */
    public function getSessionId()
    {
        return $this->session->getId();
    }

    /**
     * @inheritDoc
     */
    public function instance($instance = null)
    {
        $this->instance = $instance ?: self::DEFAULT_INSTANCE;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function user()
    {
        return $this->auth->user();
    }

    /**
     * @inheritDoc
     */
    public function add($shopId, $productId, int $quantity = 1, array $options = [], $note = null)
    {
        $sessionId = $this->getSessionId();
        $userId = $this->auth->id();

        $cartItem = $this->cartItem->firstOrNew($this->instance, $shopId, $sessionId, $userId, $productId, $options);
        // 상품수량 추가 가능한지 확인
        // Check if Product Quantity can be added
        $cartItem->canChangeQuantity($cartItem->quantity + $quantity);
        $cartItem->quantity += $quantity;
        $cartItem->note = $note;
        $cartItem->price = Shop::calculateUnitPrice($cartItem);
        $cartItem->save();

        return $cartItem;
    }

    /**
     * Update the cart item with the given id.
     *
     * @param string $itemId
     * @param mixed  $quantity
     * @return \Modules\Cart\Entities\CartItem
     */
    public function update($itemId, $quantity)
    {
        if ($cartItem = $this->get($itemId)) {
            $cartItem->canChangeQuantity($quantity);
            $cartItem->quantity = $quantity;
            $cartItem->save();
        }

        return $cartItem;
    }

    /**
     * Remove the cart item with the given rowId from the cart.
     *
     * @param string $itemId
     * @return void
     */
    public function remove($itemId)
    {
        if ($item = $this->get($itemId)) {
            $item->delete();
        }
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        $this->items()->map(function ($item) {
            $item->delete();
        });
    }

    /**
     * Get a cart item from the cart by its rowId.
     *
     * @param string $itemId
     * @return \Modules\Cart\Entities\CartItem
     */
    public function get($itemId)
    {
        return $this->cartItem->find($itemId);
    }

    /**
     * Get all cart items
     * @return mixed
     */
    public function items()
    {
        $sessionId = $this->getSessionId();
        $userId = $this->auth->id();
        $items = $this->cartItem->getWithBuilder($this->instance, $this->getSessionId(), $userId)->get();

        return $items->filter(function ($item) {
            // Remove CartItem if product not exists
            if (empty($item->product)) {
                $item->delete();

                return false;
            }

            return true;
        });
    }

    /**
     * Get count of cart items
     * @return int
     */
    public function count()
    {
        $sessionId = $this->getSessionId();
        $userId = $this->auth->id();

        return $this->cartItem->getWithBuilder($this->instance, $this->getSessionId(), $userId)->count();
    }

    /**
     * @inheritDoc
     */
    public function getTotal()
    {
        return $this->getTotalPrice() + $this->getTotalShipping() - $this->getTotalDiscount();
    }

    /**
     * @inheritDoc
     */
    public function getTotalPrice()
    {
        return Shop::calculateTotalPrice($this->items());
    }

    /**Price 를 세금항목으로 나누어 주는 함수 20200905 Ho
     * @inheritDoc
     */
    public function getTotalTax()
    {
        $totalTaxFreeAmount = 0;//면세금액
        $totalSupplyAmount = 0;//공급가
        $totalTaxAmount = 0;//부가가치세(세금)
        foreach ($this->items() as $item){
            $unitPrice = Shop::calculateUnitPrice($item);
            $quantity = (int) $item['quantity'];
            //taxFree 일 경우 면세에만 입력해줌
            if($item['product']['is_tax_free']){
                $totalTaxFreeAmount += $unitPrice*$quantity;
            }else{
                $unitSupplyAmount = floor(($unitPrice * $quantity)/1.1);
                $totalSupplyAmount += $unitSupplyAmount;
                $totalTaxAmount += ($unitPrice * $quantity)-$unitSupplyAmount;
            }
        }

        return [
            'total_tax_free_amount' => $totalTaxFreeAmount,
            'total_supply_amount' => $totalSupplyAmount,
            'total_tax_amount'=> $totalTaxAmount
        ];
    }


    public function getTotalDiscount()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getTotalShipping()
    {
        // 배송타입으로 묶음
        return $this->items()->groupBy('product.shipping_method_id')->sum(function($items) {
            $shippingMethodId = $items->first()->product->shipping_method_id;
            if(!$shippingMethodId) return 0;
            $method = app(ShippingMethodManager::class)->find($shippingMethodId);
            if(!$method) return 0;

            // 배송출발지로 묶어서 계산
            return $items->groupBy('product.shipping_storage_id')->count() * $method->getFee();
        });
    }

    /**
     * @inheritDoc
     */
    public function placeOrder(array $data)
    {
        $data['total_price'] = $this->getTotalPrice();


        $data['total_discount'] = $this->getTotalDiscount();
        $data['total_shipping'] = $this->getTotalShipping();
        //세금관련 칼럼 추가 20200905 Ho
        $taxData = $this->getTotalTax();
        $data = array_merge($data,$taxData);

        $data['total'] = $this->getTotal();
        if ( $order = Shop::placeOrder($data, $this->items()->all()) ) {
            $this->flush();

            return $order;
        }

        return $order;
    }
}
