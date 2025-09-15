<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        if ($product->track_qty == 'Yes') {
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
    public function checkout()
    {
        $discount = 0;
        //If cart is empty redirect to cart page
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }
        //if user is not logged in redirect to login page
        if (Auth::check() == false) {
            session(['url.intended' => url()->current()]);
            return redirect()->route('user_account.login');
        }

        $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();

        session()->forget('url.intended');
        $countries = Country::orderBy('name', 'ASC')->get();

        $subTotal = Cart::subtotal(2, '.', '');

        //Calculate discount here
        $discount = 0;
        if (session()->has('code')) {
            $code = session()->get('code');
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }

        //Shipping calculate here
        if ($customerAddress != '') {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

            $totalShippingCharges = 0;
            $totalQty = 0;
            $grandTotal = 0;

            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            $totalShippingCharges = $totalQty * $shippingInfo->amount;
            $grandTotal = ($subTotal - $discount) + $totalShippingCharges;
        } else {
            $grandTotal = ($subTotal - $discount);
            $totalShippingCharges = 0;
        }

        $data['countries'] = $countries;
        $data['customerAddress'] = $customerAddress;
        $data['totalShippingCharges'] = $totalShippingCharges;
        $data['grandTotal'] = $grandTotal;
        $data['discount'] = $discount;
        return view('front.checkout', $data);
    }

    //Process checkout
    public function processCheckout(Request $request)
    {
        //Apply validation
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3',
            'last_name' => 'required',
            'email' => 'required|email',
            'country_id' => 'required',
            'address' => 'required|min:20',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the error',
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
        //Save User address
        $user = Auth::user();
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country_id,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        //Store data in order table
        if ($request->payment_method == 'cod') {
            $discountCodeId = Null;
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2, '.', '');

            //Calculate discount here
            if (session()->has('code')) {
                $code = session()->get('code');
                if ($code->type == 'percent') {
                    $discount = ($code->discount_amount / 100) * $subTotal;
                } else {
                    $discount = $code->discount_amount;
                }
                $discountCodeId = $code->id;
                $promoCode = $code->code;
            }

            //Shipping Calculate
            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if ($shippingInfo != null) {
                $shipping =  $shippingInfo->amount * $totalQty;
                $grandTotal = ($subTotal - $discount) + $shipping;
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shipping =  $shippingInfo->amount * $totalQty;
                $grandTotal = ($subTotal - $discount) + $shipping;
            }


            $order = new Order();
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->discount = $discount;
            $order->cupon_code_id = $discountCodeId;
            $order->cupon_code = $promoCode;
            $order->grand_total = $grandTotal;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
            $order->user_id = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->country_id = $request->country_id;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->city = $request->city;
            $order->state = $request->state;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->save();

            //Store order items in order items table
            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem();
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->product_name = $item->name;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price * $item->qty;
                $orderItem->save();

                //Update product stock
                $productData = Product::find($item->id);
                if ($productData->track_qty == 'Yes') {
                    $currentQty = $productData->quantity;
                    $updatedQty = $currentQty - $item->qty;
                    $productData->quantity = $updatedQty;
                    $productData->save();
                }
            }

            //SEnt order email
            orderEmail($order->id, 'customer');

            session()->flash('Success', "Your order placed succesfully");

            Cart::destroy();
            session()->forget('code');

            return response()->json([
                'status' => true,
                'orderId' => $order->id,
                'message' => 'Order saved successfully'
            ]);
        } else {
        }
    }
    public function getOrderSummary(Request $request)
    {
        $subTotal = Cart::subtotal(2, '.', '');

        //Calculate discount here
        $discount = 0;
        $removeDiscount = '';
        if (session()->has('code')) {
            $code = session()->get('code');
            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
            $removeDiscount = ' <div class="mt-4" id="discount_response">
                                <strong>' . session()->get('code')->code . '</strong>
                                <a class="btn btn-sm btn-danger" id="remove_discount"><i class="fa fa-times"></i>
                                </a>
                            </div>';
        }
        if ($request->country_id > 0) {
            $subTotal = Cart::subtotal(2, '.', '');
            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();
            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            if ($shippingInfo != null) {
                $shippingCharge =  $shippingInfo->amount * $totalQty;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;
                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'discount' => number_format($discount, 2),
                    'removeDiscount' => $removeDiscount,
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shippingCharge =  $shippingInfo->amount * $totalQty;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;
                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'discount' => number_format($discount, 2),
                    'removeDiscount' => $removeDiscount,
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            }
        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => Cart::subtotal(2, '.', '') - ($discount),
                'discount' => number_format($discount, 2),
                'removeDiscount' => $removeDiscount,
                'shippingCharge' => number_format(0, 2)
            ]);
        }
    }
    public function thankYou($id)
    {
        return view('front.thanks', [
            'id' => $id,
        ]);
    }

    //Apply discount
    public function applyDiscount(Request $request)
    {
        $code = DiscountCupon::where('code', $request->code)->first();
        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon'
            ]);
        }

        //Check if discount coupons srarts date is valid or note
        $now = Carbon::now();
        if ($code->starts_at != '') {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->starts_at);
            if ($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon'
                ]);
            }
        }

        //Check if discount coupons end date is valid or note
        if ($code->expires_at != '') {
            $endtDate = Carbon::createFromFormat('Y-m-d H:i:s', $code->expires_at);
            if ($now->gt($endtDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon'
                ]);
            }
        }

        //Max coupons code used
        if ($code->max_uses > 0) {
            $couponsUsed = Order::where('cupon_code_id', $code->id)->count();
            if ($couponsUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'Promo code used limit is over'
                ]);
            }
        }

        //Max uses user check
        if ($code->max_uses_user > 0) {
            $couponsUsedByUser = Order::where(['cupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();
            if ($couponsUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already used this Promo code'
                ]);
            }
        }

        //Minimum amount condition check
        $subTotal = Cart::subtotal(2, '.', '');
        if ($code->min_amount > 0) {
            if ($code->min_amount >= $subTotal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your minimum amount must be $' . $code->min_amount
                ]);
            }
        }

        session()->put('code', $code);
        return $this->getOrderSummary($request);
    }
    //Remove discount coupons
    public function removeCoupon(Request $request)
    {
        session()->forget('code');
        return $this->getOrderSummary($request);
    }
}
