<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Exports\SalesExport;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        $vendorId = $vendor->vendorId;

        $validated = $request->validate([
            'startDate' => 'nullable|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d|after:startDate',
        ], [
            'endDate.after' => 'Invalid date range',
        ]);

        $startDate = $validated['startDate'] ?? null;
        $endDate = $validated['endDate'] ?? null;

        // Call helper method to fetch filtered data
        [$orders, $totalSales] = $this->getVendorSalesRanged($vendorId, $startDate, $endDate);

        return view('catering.vendorSales', compact('orders', 'totalSales', 'vendor', 'startDate', 'endDate'));
    }

    public function export_sales(Request $request)
    {
        $user = Auth::user();
        $vendorId = $user->vendor->vendorId;

        $validated = $request->validate([
            'startDate' => 'nullable|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d',
        ], [
            'endDate.after' => 'Invalid date range',
        ]);

        $startDate = $validated['startDate'] ?? null;
        $endDate = $validated['endDate'] ?? null;

        // Call helper method to fetch filtered data
        [$orders, $totalSales] = $this->getVendorSalesRanged($vendorId, $startDate, $endDate);

        $start = Carbon::parse($startDate)->format('d M Y') ?? null;
        $end = Carbon::parse($endDate)->format('d M Y') ?? null;

        return Excel::download(new SalesExport($orders, $totalSales, $start, $end), "laporan_penjualan.xlsx");
    }

    public function generateMonthlyPeriods($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();

        $periods = [];

        while ($start->lessThanOrEqualTo($end)) {
            $monthYear = $start->format('M Y');
            $startOfMonth = $start->copy()->startOfMonth()->format('d-m-Y');
            $endOfMonth = $start->copy()->endOfMonth()->format('d-m-Y');

            $periods[$monthYear] = [$startOfMonth, $endOfMonth];

            $start->addMonth();
        }

        return $periods;
    }

    public function generateQuarterlyPeriods($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfQuarter();
        $end = Carbon::parse($endDate)->endOfQuarter();

        $periods = [];

        while ($start->lessThanOrEqualTo($end)) {
            $quarter = 'Q' . $start->quarter;
            $year = $start->year;
            $label = "{$quarter} {$year}";

            $startOfQuarter = $start->copy()->startOfQuarter()->format('d-m-Y');
            $endOfQuarter = $start->copy()->endOfQuarter()->format('d-m-Y');

            $periods[$label] = [$startOfQuarter, $endOfQuarter];

            $start->addQuarter();
        }

        return $periods;
    }

    public function getQuarterRangeFromLabel(string $label)
    {
        // Example label: "Q1 2025"
        [$quarter, $year] = explode(' ', $label);
        $year = (int) $year;
        $quarter = (int) str_replace('Q', '', $quarter);

        // Map quarters to start months
        $startMonth = match ($quarter) {
            1 => 1,
            2 => 4,
            3 => 7,
            4 => 10,
        };

        $start = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
        $end = (clone $start)->addMonths(2)->endOfMonth();

        return [
            'start' => $start->format('d-m-Y'),
            'end' => $end->format('d-m-Y'),
        ];
    }

    private function getVendorSalesData($vendorId, $period)
    {
        if ($period == 'All') {
            $orders = Order::where('vendorId', $vendorId)->get();
        } else {
            $range = $this->getQuarterRangeFromLabel($period);
            // dd($range);
            $orders = Order::where('vendorId', '=', $vendorId)
                ->whereDate('startDate', '>=', Carbon::parse($range['start'])->format('Y-m-d'))
                ->whereDate('startDate', '<=', Carbon::parse($range['end'])->format('Y-m-d'))
                ->get();
            // dd($orders);
        }

        $orders->load(['user', 'orderItems.package']);

        $totalSales = $orders->sum('totalPrice');

        foreach ($orders as $order) {
            $grouped = $order->orderItems
                ->groupBy('packageId')
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'packageName' => $first->package->name,
                        'timeSlots' => $items->pluck('packageTimeSlot')
                            ->map(fn($ts) => ucfirst(strtolower($ts->name)))
                            ->unique()
                            ->join(', '),
                    ];
                });

            $order->groupedPackages = $grouped->values();
        }

        $periodText = $period;

        return compact('orders', 'totalSales', 'periodText');
    }

    private function getVendorSalesRanged($vendorId, $startDate = null, $endDate = null)
    {
        $orders = Order::where('vendorId', $vendorId)
            ->whereHas('payment', function ($query) use ($startDate, $endDate) {
                $query->whereNotNull('paid_at');

                if ($startDate) {
                    $query->whereDate('paid_at', '>=', $startDate);
                }

                if ($endDate) {
                    $query->whereDate('paid_at', '<=', $endDate);
                }
            })
            ->get();

        $orders->load(['user', 'orderItems.package', 'payment']);

        $totalSales = $orders->sum('totalPrice');

        foreach ($orders as $order) {
            $grouped = $order->orderItems
                ->groupBy(function ($item) {
                    return $item->packageId . '|' . strtolower($item->packageTimeSlot); // group by both packageId & timeSlot
                })
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'packageName' => $first->package->name,
                        'timeSlots' => ucfirst(strtolower($first->packageTimeSlot)),
                        'quantity' => $items->sum('quantity'),
                    ];
                });

            $order->groupedPackages = $grouped->values();
        }


        return [$orders, $totalSales];
    }

}
