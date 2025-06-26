<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::latest('orders.created_at')->select('orders.*', 'users.name', 'users.email');
        $orders = $orders->leftJoin('users', 'users.id', 'orders.user_id');

        if ($request->get('keyword') != '') {
            $orders = $orders->where('users.name', 'like', '%' . $request->keyword . '%');
            $orders = $orders->orWhere('users.email', 'like', '%' . $request->keyword . '%');
            $orders = $orders->orWhere('orders.id', 'like', '%' . $request->keyword . '%');
        }
        $orders = $orders->paginate(10);
        $data['orders'] = $orders;
        return view('admin.orders.list', $data);
    }

    public function detail($order_id)
    {
        $order = Order::select('orders.*', 'countries.name as countryName')
            ->where('orders.id', $order_id)
            ->leftJoin('countries', 'countries.id', 'orders.country_id')
            ->first();

        $orderItems = OrderItem::where('order_id', $order_id)->get();

        $data['order'] = $order;
        $data['orderItems'] = $orderItems;

        return view('admin.orders.detail', $data);
    }

    //Change Order status
    public function changeOrderStatus(Request $request, $orderId)
    {
        $order = Order::find($orderId);
        $order->status = $request->status;
        $order->shipping_date = $request->shipping_date;
        $order->save();

        return response()->json([
            'status' => true,
        ]);
    }

    //Send Envoice Email
    public function sendEnvoiceEmail(Request $request, $orderId)
    {

        orderEmail($orderId, $request->userType);

        session()->flash('Success', 'Email send successfully');

        return response()->json([
            'status' => true,
            'message' => 'Email send successfully'
        ]);
    }
}
