<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $userId = Auth::user()->id;
        $user = User::where('id', $userId)->first();
        $address = CustomerAddress::where('user_id', $userId)->first();
        $countries = Country::orderBy('name', 'ASC')->get();
        $data['user'] = $user;
        $data['countries'] = $countries;
        $data['address'] = $address;
        return view('front.user_account.profile', $data);
    }

    //Update profile info
    public function updateProfile(Request $request)
    {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $userId . 'id',
            'phone' => 'required',
        ]);

        if ($validator->passes()) {
            $user = User::find($userId);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->save();

            session()->flash('Success', 'Profile updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    //Update profile address
    public function updateProfileAddress(Request $request)
    {
        $userId = Auth::user()->id;
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

        if ($validator->passes()) {
            CustomerAddress::updateOrCreate(
                ['user_id' => $userId],
                [
                    'user_id' => $userId,
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

            session()->flash('Success', 'Profile address updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Profile address updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
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

    //Change Password
    public function showChangePassword()
    {
        return view('front.user_account.change-password');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'old_password' => 'required',
                'new_password' => 'required|min:5',
                'confirm_password' => 'required | same:new_password'
            ]
        );
        if ($validator->passes()) {
            $user = User::select('id', 'password')->where('id', Auth::user()->id)->first();
            if (!Hash::check($request->old_password, $user->password)) {
                session()->flash('Fail', 'Incorrect Password!Please try again.');
                return response()->json([
                    'status' => true,
                ]);
            }
            User::where('id', $user->id)->update(
                [
                    'password' => Hash::make($request->new_password)
                ]
            );
            session()->flash('Success', 'Password changed successfully.');
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

    //Forgot Password
    public function showForgotPassword()
    {
        return view('front.user_account.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email|exists:users,email'
            ]
        );
        if ($validator->fails()) {
            return redirect()->route('front.showForgotPassword')->withInput()->withErrors($validator);
        }
        //Save email and token into db
        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        //Send mail here
        $user = User::where('email', $request->email)->first();
        $fromData = [
            'token' => $token,
            'user' => $user,
            'mailSubject' => 'You have requested to reset your password'
        ];
        Mail::to($request->email)->send(new ResetPasswordEmail($fromData));

        return redirect()->route('front.showForgotPassword')->with('Success', 'Please check your email to reset your password');
    }

    public function resetPassword($token)
    {
        $tokenExist = DB::table('password_reset_tokens')->where('token', $token)->first();

        if ($tokenExist == null) {
            return redirect()->route('front.showForgotPassword')->with('Fail', 'Invalid request');
        }
        $data['token'] = $token;
        return view('front.user_account.reset-password', $data);
    }

    public function processResetPassword(Request $request)
    {
        $token = $request->token;
        $tokenobj = DB::table('password_reset_tokens')->where('token', $token)->first();

        if ($tokenobj == null) {
            return redirect()->route('front.showForgotPassword')->with('Fail', 'Invalid request');
        }
        $user = User::where('email', $tokenobj->email)->first();

        $validator = Validator::make(
            $request->all(),
            [
                'new_password' => 'required|min:5',
                'confirm_password' => 'required | same:new_password'
            ]
        );
        if ($validator->fails()) {
            return redirect()->route('front.resetPassword', $token)->withErrors($validator);
        }
        User::where('id', $user->id)->update(
            [
                'password' => Hash::make($request->new_password)
            ]
        );
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('user_account.login')->with('Success', 'You have successfully reset your password');
    }
}