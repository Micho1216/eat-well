<?php

namespace App\Http\Controllers;

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

    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $name = $validated['name'];
        // $name = $request->name;

        // $vendors = Vendor::where('name', 'like','%' .$name . '%')->get();
        $vendors = Vendor::where('name', 'like', '%' . $name . '%')->get();
        // $vendors->load('address');
        // $addresses =  Address::where('addressId', $request->addressId)->get();
        $sales = DB::table('orders')
            ->select('vendorId', DB::raw('SUM(totalPrice) as totalSales'))
            ->groupBy('vendorId')
            ->pluck('totalSales', 'vendorId');

        logActivity('Successfully', 'Searched', 'Vendor by Name');
        return view('viewAllVendor', compact('vendors', 'sales'));


        // return view('viewAllVendor', compact('vendors'));
    }

    public function view_all_logs()
    {
        $all_logs = UserActivity::orderBy('accessed_at', 'desc')->paginate(25);

        // logActivity('Successfully', 'Visited', 'View All Logs Page');
        return view('view-all-logs', compact('all_logs'));
    }

    public function view_all_payment()
    {
        $payments = PaymentMethod::all();

        // logActivity('Successfully', 'Visited', 'View All Payment Page');
        return view('view-all-payment', compact('payments'));
    }

    public function delete_payment(string $id)
    {
        $payment = PaymentMethod::query()->find($id);
        $payment->delete();

        $payments = PaymentMethod::all();


        // Session::flash('message', 'Successfully delete payment !');
        // return view('view-all-payment', compact('payments'));
        // return redirect()->route('view-all-payment');
        logActivity('Successfully', 'Deleted', 'Payment Method');
        return redirect()->route('view-all-payment')->with('message_del', 'Successfully delete payment method!');
    }

    public function add_new_payment(Request $request)
    {
        // $name = $request->payment-method;
        // $newPayment = PaymentMethod::create();

        // $newPayment->name = $request->paymentMethod;

        // $newPayment->save();
        try {


            $validated = $request->validate([
                'paymentMethod' => 'string|max:255|unique:payment_methods,name'
            ]);

            $newPayment = PaymentMethod::create([
                'name' => $validated['paymentMethod']
            ]);

            $payments = PaymentMethod::all();

            // Session::flash('message_add', 'Successfully added payment !');
            // return view('view-all-payment', compact('payments'));
            logActivity('Successfully', 'Added', 'Payment Method');
            return redirect()->route('view-all-payment')->with('message_add', 'Successfully added payment method!');
        } catch (\Exception $e) {
            // Log::error('Error adding new payment method: ' . $e->getMessage());
            logActivity('Failed', 'Added', 'Payment Method due to error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to add payment method.']);
        }

        // return redirect()->route('view-all-payment');
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
