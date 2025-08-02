<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminSearchRequest;
use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    //
    public function viewAllVendors()
    {
        $vendors = Vendor::all();

        $sales = DB::table('orders')
            ->select('vendorId', DB::raw('SUM(totalPrice) as totalSales'))
            ->groupBy('vendorId')
            ->pluck('totalSales', 'vendorId');

        // logActivity('Successfully', 'Visited', 'View All Vendor Page');
        return view('viewAllVendor', compact('vendors', 'sales'));
    }

    public function search(AdminSearchRequest $request)
    {
        $validated = $request->validated();
        $name = $validated['name'];

        // Cari vendor berdasarkan nama
        $vendors = Vendor::where('name', 'like', '%' . $name . '%')->get();

        // Ambil total penjualan (sales) berdasarkan vendor
        $sales = DB::table('orders')
            ->select('vendorId', DB::raw('SUM(totalPrice) as totalSales'))
            ->groupBy('vendorId')
            ->pluck('totalSales', 'vendorId');

        // Catat aktivitas pencarian
        logActivity('Successfully', 'Searched', 'Vendor by Name');

        // Kirim data ke view
        return view('viewAllVendor', compact('vendors', 'sales'));
    }


    public function view_all_logs()
    {
        $all_logs = UserActivity::orderBy('accessed_at', 'desc')->paginate(25);

        // logActivity('Successfully', 'Visited', 'View All Logs Page');
        return view('view-all-logs', compact('all_logs'));
    }



    public function view_all_transactions()
    {

        // $payments = Payment::all()->paginate(10);
        $payments = Payment::orderBy('paid_at', 'asc')->paginate(25);

        logActivity('Successfully', 'Visited', 'View all transaction');
        return view('view-all-transactions', compact('payments'));
    }

    public function view_all_users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(25);
        logActivity('Successfully', 'Visited', 'View all users');
        return view('view-all-users', compact('users'));
    }
}
