<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiscountCouponController extends Controller
{
    public function index() {}
    public function create()
    {
        return view('admin.coupon.create');
    }
    public function store() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
