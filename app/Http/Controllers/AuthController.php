<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    //User registration
    public function register()
    {
        return view('front.user_account.register');
    }
    public function registerProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|confirmed',
        ]);
        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('Success', 'Your registration is successfull');

            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    //User login
    public function login()
    {
        return view('front.user_account.login');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->passes()) {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
                if (session()->has('url.intended')) {
                    return redirect(session()->get('url.intended'));
                }
                return redirect()->route('user_account.profile');
            } else {
                return redirect()->route('user_account.login')
                    ->withInput($request->only('email'))
                    ->with('Fail', 'Your email or password is incorrect');
            }
        } else {
            return redirect()
                ->route('user_account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    //User profile
    public function profile()
    {
        return view('front.user_account.profile');
    }

    //User orders
    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->OrderBy('created_at', 'DESC')->get();
        $data['orders'] = $orders;
        return view('front.user_account.order', $data);
    }

    //Order detail
    public function orderDetail($id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->where('id', $id)->first();
        $orderItems = OrderItem::where('order_id', $id)->get();
        $orderItemsCount = OrderItem::where('order_id', $id)->get()->count();
        $data['orderItemsCount'] = $orderItemsCount;
        $data['order'] = $order;
        $data['orderItems'] = $orderItems;
        return view('front.user_account.order-detail', $data);
    }

    //User logout
    public function logout()
    {
        Auth::logout();
        return redirect()->route('user_account.login');
    }
    //Wishlists
    public function wishlist()
    {
        $wishlists = Wishlist::where('user_id', Auth::user()->id)->with('product')->get();
        $data['wishlists'] = $wishlists;
        return view('front.user_account.wishlist', $data);
    }

    public function removeFromWishlist(Request $request)
    {
        $wishlist = Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->id)->first();

        if ($wishlist == null) {
            session()->flash('Fail', 'Product not found!');
            return response()->json([
                'status' => true,
            ]);
        } else {
            Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->id)->delete();
            session()->flash('Success', 'Product removed successfully!');
            return response()->json([
                'status' => true,
            ]);
        }
    }
}
