<?php

use App\Mail\OrderEmail;
use App\Models\Category;
use App\Models\Country;
use App\Models\Order;
use App\Models\Page;
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


function orderEmail($orderId, $userType)
{
    $order = Order::where('id', $orderId)->with('items')->first();

    if ($userType == "customer") {
        $subject = "Thank You!Your order has placed successfully";
        $email = $order->email;
    } else {
        $subject = "You have received an order";
        $email = env('ADMIN_EMAIL', "admin@gmail.com");
    }

    $mailData = [
        'subject' => $subject,
        'order' => $order,
        'userType' => $userType
    ];

    Mail::to($email)->send(new OrderEmail($mailData));
}


function getCountry($id)
{
    return Country::where('id', $id)->first();
}

function staticPages()
{
    $pages = Page::orderBy('name', 'ASC')->get();
    return $pages;
}
