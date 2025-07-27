<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminViewOrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()->orderBy('created_at')->paginate(20);
        $orders->load(['payment', 'orderItems']);

        return view('view-order-history', compact('orders'));
    }
}
