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
        $data['countries'] = $countries;
        return view('admin.shipping.create', $data);
    }
    public function store(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($Validator->passes()) {
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
}
