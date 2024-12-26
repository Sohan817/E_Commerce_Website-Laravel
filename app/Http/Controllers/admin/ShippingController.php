<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function create()
    {
        $countries = Country::get();
        $shippingCharges = ShippingCharge::select('shipping_charges.*', 'countries.name')
            ->leftJoin('countries', 'countries.id', 'shipping_charges.country_id')->get();
        $data['countries'] = $countries;
        $data['shippingCharges'] = $shippingCharges;
        return view('admin.shipping.create', $data);
    }
    public function store(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($Validator->passes()) {
            $count = ShippingCharge::where('country_id', $request->country_id)->count();
            if ($count > 0) {
                session()->flash('Fail', "Shipping already added");
                return response()->json([
                    'status' => true,
                ]);
            }
            $shipping = new ShippingCharge();
            $shipping->country_id = $request->country_id;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('Success', "Shipping charge added successfully");

            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $Validator->errors()
            ]);
        }
    }
    public function edit($id)
    {
        $shippingCharge = ShippingCharge::find($id);
        $countries = Country::get();
        $data['countries'] = $countries;
        $data['shippingCharge'] = $shippingCharge;
        return view('admin.shipping.edit', $data);
    }
    public function update($id, Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($Validator->passes()) {
            $shipping = ShippingCharge::find($id);
            $shipping->country_id = $request->country_id;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('Success', "Shipping charge updated successfully");

            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $Validator->errors()
            ]);
        }
    }
}
