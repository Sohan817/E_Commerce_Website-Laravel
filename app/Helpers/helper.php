<?php

use App\Mail\OrderEmail;
use App\Models\Category;
use App\Models\Country;
use App\Models\Order;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Mail;

//Return category on dashboard
function getCategories()
{
    return Category::orderBy('name', 'ASC')->with('sub_category')->orderBy('id', 'DESC')->where('status', 1)->where('showHome', 'Yes')->get();
}

function getProductImage($poductId)
{
    return ProductImage::where('product_id', $poductId)->first();
}

function orderEmail($orderId)
{
    $order = Order::where('id', $orderId)->with('items')->first();

    $mailData = [
        'subject' => 'Thank You!Your order has placed successfully',
        'order' => $order
    ];

    Mail::to($order->email)->send(new OrderEmail($mailData));
}

function getCountry($id)
{
    return Country::where('id', $id)->first();
}
