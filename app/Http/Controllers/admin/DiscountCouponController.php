<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class DiscountCouponController extends Controller
{
    public function index(Request $request)
    {
        $discountCoupons = DiscountCupon::latest();
        if (!empty($request->get('keyword'))) {
            $discountCoupons = $discountCoupons->where('name', 'like', '%' . $request->get('keyword') . '%');
            $discountCoupons = $discountCoupons->orWhere('code', 'like', '%' . $request->get('keyword') . '%');
        }
        $discountCoupons = $discountCoupons->paginate(10);
        $data['discountCoupons'] = $discountCoupons;
        return view('admin.coupon.list', $data);
    }

    //Create discount coupons

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

            //Expire date must be greater than start date
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
    public function edit($couponsId)
    {
        $discountCoupon = DiscountCupon::find($couponsId);
        if (empty($discountCoupon)) {
            return redirect()->route('categories.index');
        }
        $data['discountCoupon'] = $discountCoupon;
        return view('admin.coupon.edit', $data);
    }

    //Update discount coupons 

    public function update($couponsId, Request $request)
    {
        $discountCoupon = DiscountCupon::find($couponsId);

        if (empty($discountCoupon)) {
            session()->flash('Fail', 'Record not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Record not found',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required | numeric',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            //Expire date must be greater than start date
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

            $message = 'Discount coupons updated successfully';

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
    public function destroy($couponsId)
    {

        $discountCoupon = DiscountCupon::find($couponsId);

        if ($discountCoupon == null) {

            session()->flash('Fail', "Coupons not found");

            return response()->json([
                'status' => true,
            ]);
        }
        $discountCoupon->delete();

        session()->flash('Success', "Coupons deleted successfully");

        return response()->json([
            'status' => true,
        ]);
    }
}
