<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCouponController extends Controller
{
    public function index()
    {
        return view('admin.coupon.list');
    }
    public function create()
    {
        return view('admin.coupon.create');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required | numeric',
            'status' => 'required'
        ]);
        if ($validator->passes()) {
            //Start date must be greater than current date
            if (!empty($request->starts_at)) {
                $now = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                if ($startAt->lte($now) == true) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Starts date cannot be less than current date time']
                    ]);
                }
            }
            if (!empty($request->starts_at) && !empty($request->expires_at)) {
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at);
                $expireAt = Carbon::createFromFormat('Y-m-d H:i:s', $request->expires_at);
                if ($expireAt->lte($startAt) == true) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire date cannot be less than start date time']
                    ]);
                }
            }
            $discountCoupon = new DiscountCupon();
            $discountCoupon->code = $request->code;
            $discountCoupon->name = $request->name;
            $discountCoupon->description = $request->description;
            $discountCoupon->max_uses = $request->max_uses;
            $discountCoupon->max_uses_user = $request->max_uses_user;
            $discountCoupon->type = $request->type;
            $discountCoupon->discount_amount = $request->discount_amount;
            $discountCoupon->min_amount = $request->min_amount;
            $discountCoupon->status = $request->status;
            $discountCoupon->starts_at = $request->starts_at;
            $discountCoupon->expires_at = $request->expires_at;
            $discountCoupon->save();

            $message = 'Discount coupons added successfully';

            session()->flash('Success',  $message);

            return response()->json([
                'status' => true,
                'message' =>  $message
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
