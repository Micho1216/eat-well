<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Util\Percentage;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $orders = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get();

        // ini total sales bulan ini
        $totalPrice = $orders->sum(function ($order) {
            return (float) $order->totalPrice;
        });

        $lastMonthOrder = Order::whereMonth('created_at', Carbon::now()->subMonth()->month())
            ->whereYear('created_at', Carbon::now()->year)
            ->get();

        $lastMonthSale = $lastMonthOrder->sum(function ($lastMonthOrder) {
            return (float) $lastMonthOrder->totalPrice;
        });

        // $lastMonthSale = 100000000000;
        // perbedaan sales bulan ini dan bulan lalu
        $increment = $totalPrice - $lastMonthSale;

        // $increment = -10000;

        $percentage = 0;
        if ($lastMonthSale == 0) {
            $percentage = 100;
        } else {
            $percentage = ($increment / $lastMonthSale) * 100;
        }

        // dd($lastMonthSale);

        // dd($totalPrice);

        $lmprofit = 0.05 * $lastMonthSale;

        $profit = 0.05 * $totalPrice;

        $lmprofit = 0.05 * $lastMonthSale;;
        

        
        $percentageprofit = 0;
        if ($lmprofit == 0) {
            $percentageprofit = 100;
        } else
        {
            $percentageprofit = (($profit - $lmprofit) / $lmprofit) * 100;
        }

        // dd($lmprofit);

        $profit = number_format($profit, 0, ',', '.');

        $totalPrice = number_format($totalPrice, 0, ',', '.');

        // Ambil total penjualan per bulan tahun ini
        $monthlySales = Order::selectRaw('MONTH(created_at) as month, SUM(totalPrice) as total')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month'); // hasil: [1 => 12345, 2 => 23456, ...]

        // Format hasil agar semua bulan 1–12 terisi (meskipun 0)
        $allMonths = collect(range(1, 12))->map(function ($month) use ($monthlySales) {
            return $monthlySales->get($month, 0); // isi 0 kalau tidak ada data
        });

        // Contoh kirim ke Blade (dalam bentuk array biasa)
        $chartData = $allMonths->values()->toArray();

        $labels = collect(range(1, 12))->map(function ($month) {
            return Carbon::create()->month($month)->locale('id')->translatedFormat('F');
        })->toArray();


        // $logs = UserActivity::all();
        $logs = UserActivity::query()->orderBy('accessed_at', 'desc')->limit(10)->get();

        logActivity('Successfully', 'Visited', 'Admin Dashboard Page');

        return view('adminDashboard', compact('totalPrice', 'percentage', 'profit', 'increment', 'percentageprofit', 'chartData', 'labels', 'logs'));
    }

    public function view_all_logs()
    {
        $all_logs = UserActivity::all();
        return view('view-all-logs', compact('all_logs'));
    }
}
