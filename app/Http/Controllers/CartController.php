<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Termwind\Components\Dd;

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
            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }
            if ($productAlreadyExist == false) {
                Cart::add(
                    $product->id,
                    $product->title,
                    1,
                    $product->price,
                    ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']
                );
                $status = true;
                $message = '<strong>' . $product->title . '</strong>  added in the cart successfully';
                session()->flash('Success', $message);
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
                ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']
            );
            $status = true;
            $message = '<strong>' . $product->title . '</strong> added in the cart successfully';
            session()->flash('Success', $message);
        }
        return response()->json([
            'status' => $status,
            'message' => $message

        ]);
    }

    //Update item on the cart
    public function updateCart(Request $request)
    {
        $rowId = $request->rowId;
        $qty = $request->qty;

        //Get Product information
        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);

        //Check quantity available in the stock
        if ($product->track_quantity == 'Yes') {
            if ($qty <= $product->quantity) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
            } else {
                $message = 'Requested quantity ' . $qty . ' is not available in the stock';
                $status = false;
                session()->flash('Fail', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
        }
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    //Delete item
    public function deleteItem(Request $request)
    {
        $itemInfo = Cart::get($request->rowId);
        if ($itemInfo == null) {
            $message = 'Item not found in the cart';
            session()->flash('Fail', $message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }
        Cart::remove($request->rowId);
        return response()->json([
            'status' => true,
            'message' => 'Item removed successfully'
        ]);
    }
    public function cart()
    {
        $cartContent = Cart::content();
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }
}