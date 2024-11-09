<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $product = Product::with('product_images')->find($request->id);
        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Prouct not found'

            ]);
        }
        if (Cart::count() > 0) {
            //Product found in cart
            //Check if this product already in the cart
            $cartContent = Cart::content();
            $productAlreadyExist = false;
            foreach ($cartContent as $iteam) {
                if ($iteam->id == $request->id) {
                    $productAlreadyExist = true;
                }
            }
            if ($productAlreadyExist == false) {
                Cart::add(
                    $product->id,
                    $product->title,
                    1,
                    $product->price,
                    ['productImage' => (!empty($product->product_images) ? $product->product_images->first() : '')]
                );
                $status = true;
                $message = $product->title . ' added in the cart';
            } else {
                $status = false;
                $message = $product->title . ' already added in the cart';
            }
        } else {
            //Card is empty
            Cart::add(
                $product->id,
                $product->title,
                1,
                $product->price,
                ['productImage' => (!empty($product->product_images) ? $product->product_images->first() : '')]
            );
            $status = true;
            $message = $product->title . ' added in the cart';
        }
        return response()->json([
            'status' => $status,
            'message' => $message

        ]);
    }
    public function cart()
    {
        $cartContent = Cart::content();
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }
}
