<?php

namespace App\Http\Controllers;

use App\Exports\AdminOrderExport;
use App\Http\Requests\FilterSalesRequest;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminViewOrderController extends Controller
{
    public function index(FilterSalesRequest $request)
    {
        $query = Order::with(['payment', 'orderItems', 'vendor', 'user']);

        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        $orders = $query->orderBy('created_at')->paginate(20);

        return view('view-order-history', compact('orders'));
    }

    public function export(FilterSalesRequest $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $query = Order::with(['user', 'vendor', 'orderItems.package']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->get();

        $totalSales = $orders->sum('totalPrice');

        foreach ($orders as $order) {
            $grouped = $order->orderItems
                ->groupBy('packageId')
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'packageName' => $first->package->name,
                        'timeSlots' => $items->pluck('packageTimeSlot')
                            ->map(fn($ts) => ucfirst(strtolower($ts)))
                            ->unique()
                            ->join(', '),
                        'quantity' => $items->sum('quantity'),
                    ];
                });

            $order->groupedPackages = $grouped->values();
        }

        $start = $startDate ? Carbon::parse($startDate)->format('d M Y') : null;
        $end = $endDate ? Carbon::parse($endDate)->format('d M Y') : null;

        $orders = Order::with(['user', 'vendor', 'orderItems.package'])->get();

        return Excel::download(new AdminOrderExport($orders, $start, $end), 'admin_order_export.xlsx');
    }
}
