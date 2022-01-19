<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CartItemsResource;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\CartRepositoryInterface;
use App\Repositories\CartItemRepositoryInterface;
use DateTime;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

use function PHPUnit\Framework\isEmpty;

class ApiCartController extends Controller
{
    use ApiResponseTrait;
    protected $userRepository;
    protected $productRepository;
    protected $orderRepository;
    protected $orderItemRepository;
    protected $notificationRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        CartItemRepositoryInterface $cartItemRepository
    ) {

        $this->userRepository           = $userRepository;
        $this->productRepository        = $productRepository;
        $this->cartRepository           = $cartRepository;
        $this->cartItemRepository       = $cartItemRepository;
    }

    public function addToCart(Request $request)
    {
        try {

            $user = auth()->user();
            $attributes = $request->all();
            $myCart =  $user->cart;
            $product_model = $this->productRepository->findOne($attributes['product_id']);

            if (!$myCart) {
                $cart = $this->cartRepository->create(['user_id' => $user->id, 'total_amount' => 0]);
            } else {
                $cart = $myCart;
            }

            $productExistInCart = $cart->cart_items->where('product_id', $request->product_id)->first();

            $total_amount = $product_model->price * $request->quantity;
            $cart->total_amount += $total_amount;
            $cart->save();

            if ($productExistInCart) {

                $productExistInCart->quantity += $request->quantity;

                $productExistInCart->save();

                return $this->ApiResponse(null, trans('local.added_to_cart'), 200);
            }

            $this->cartItemRepository->create([
                'cart_id' => $cart->id,
                'product_id' => $product_model->id,
                'quantity' => $attributes['quantity'],
                'price' => $product_model->price,
            ]);

            return $this->ApiResponse(null, trans('local.added_to_cart'), 200);
        } catch (\Exception $e) {
            return $this->ApiResponse(null, $e->getMessage(), 400);
        }
    }

    public function getMyCart()
    {

        try {

            $user = auth()->user();

            $cart = $user->cart;

            $cartItems = $cart->cart_items;

            $sellers = $cartItems->map(function ($item) {
                return $item->product->seller;
            });

            $sellers = $sellers->unique();


            $cartItems = CartItemsResource::collection($cartItems);

            $items = [];

            foreach ($sellers as $seller) {
                $items[] = [
                    'seller' => $seller->name,
                    'items' => $cartItems->where('product.seller_id', $seller->id)->values()->all(),

                ];
            }



            return $this->ApiResponse(['items' => $items, 'total_amount' => $cart->total_amount], '', 200);
        } catch (\Exception $e) {

            return $this->ApiResponse(null, $e->getMessage(), 400);
        }
    }

    public function removeFromCart(Request $request)
    {
        try {

            $user = auth()->user();

            $cart = $user->cart;

            $cartItem = $this->cartItemRepository->findOne($request->cart_item_id);

            $cart->total_amount -= $cartItem->price * $cartItem->quantity;

            $cart->save();

            $cartItem->delete();

            return $this->ApiResponse(null, trans('local.removed_from_cart'), 200);
        } catch (\Exception $e) {
            return $this->ApiResponse(null, $e->getMessage(), 400);
        }
    }

    public function changeQuantity(Request $request)
    {
        try {
            $user = auth()->user();

            $cart = $user->cart;

            $cartItem = $this->cartItemRepository->findOne($request->cart_item_id);

            $cartItem->quantity = $request->quantity;

            $cartItem->save();

            $cart->total_amount = $cartItem->quantity * $cartItem->price;

            $cart->save();

            return $this->ApiResponse(null, trans('local.cart_updated'), 200);
        } catch (\Exception $e) {
            return $this->ApiResponse(null, $e->getMessage(), 400);
        }
    }
}