<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $totalOrders = Order::where('status', '!=', 'cancellled')->count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 1)->count();
        $totalRevenue = Order::where('status', '!=', 'cancellled')->sum('grand_total');

        //Current month revenue
        $startOfTheMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        $totalRevenueOfTheMonth = Order::where('status', '!=', 'cancellled')
            ->whereDate('created_at', '>=', $startOfTheMonth)
            ->whereDate('created_at', '<=', $currentDate)
            ->sum('grand_total');

        //Last month revenue
        $startOfTheLastMonth = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endOfTheLastMonth = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMonthName = Carbon::now()->subMonth()->startOfMonth()->format('M');

        $totalRevenueOfTheLastMonth = Order::where('status', '!=', 'cancellled')
            ->whereDate('created_at', '>=', $startOfTheLastMonth)
            ->whereDate('created_at', '<=', $endOfTheLastMonth)
            ->sum('grand_total');

        //Last 30 days revenue
        $startOfTheLastThirtyDays = Carbon::now()->subDays(30)->format('Y-m-d');
        $totalRevenueOfTheLastThirtyDays = Order::where('status', '!=', 'cancellled')
            ->whereDate('created_at', '>=', $startOfTheLastThirtyDays)
            ->whereDate('created_at', '<=', $currentDate)
            ->sum('grand_total');


        $data['totalOrders'] = $totalOrders;
        $data['totalProducts'] = $totalProducts;
        $data['totalCustomers'] = $totalCustomers;
        $data['totalRevenue'] = $totalRevenue;
        $data['totalRevenueOfTheMonth'] = $totalRevenueOfTheMonth;
        $data['totalRevenueOfTheLastMonth'] = $totalRevenueOfTheLastMonth;
        $data['totalRevenueOfTheLastThirtyDays'] = $totalRevenueOfTheLastThirtyDays;
        $data['lastMonthName'] = $lastMonthName;


        return view('admin.dashboard', $data);
    }
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
