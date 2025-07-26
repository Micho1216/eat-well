<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\DeliveryStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentMethod;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\CustomerSubscribed;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // validate request
        $validated = $request->validate([
            'status' => 'nullable|string|in:all,active,upcoming,cancelled,finished',
            'query' => 'nullable|string|max:255'
        ]);

        // get userId
        $userId = Auth::id();

        $status = $request->query('status', 'all');
        $query = $request->query('query');
        $now = Carbon::now();

        $orders = Order::with(['orderItems.package', 'vendor', 'vendorReview'])
            ->where('userId', $userId)
            ->when($status === 'active', function ($q) use ($now) {
                $q->where('isCancelled', 0)
                    ->whereDate('startDate', '<=', $now)
                    ->whereDate('endDate', '>=', $now);
            })
            ->when($status === 'upcoming', function ($q) use ($now) {
                $q->where('isCancelled', 0)
                    ->whereDate('startDate', '>', $now);
            })
            ->when($status === 'finished', function ($q) use ($now) {
                $q->where('isCancelled', 0)
                    ->whereDate('endDate', '<', $now);
            })
            ->when($status === 'cancelled', function ($q) use ($now) {
                $q->where('isCancelled', 1);
            })
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('orderId', 'like', "%$query%")
                        ->orWhereHas('vendor', function ($vendorQ) use ($query) {
                            $vendorQ->where('name', 'like', "%$query%");
                        })
                        ->orWhereHas('orderItems.package', function ($packageQ) use ($query) {
                            $packageQ->where('name', 'like', "%$query%");
                        });
                });
            })
            ->orderByDesc('endDate')
            ->get();
        // dd($orders);

        logActivity('Successfully', 'Visited', 'Order History Page');
        return view('customer.orderHistory', compact('orders', 'status'));
    }

    public function showPaymentPage(Request $request, Vendor $vendor) // Menggunakan Route Model Binding untuk Vendor
    {
        $userId = Auth::id();
        // $userId = 1;
        if (!$userId) {
            // Arahkan ke halaman login atau tampilkan error
            // return redirect()->route('login')->with('error', 'Please log in to view your cart.');
            return redirect()->route('landingPage');
        }

        // Ambil cart user untuk vendor tertentu
        $cart = Cart::with(['cartItems.package']) // Eager load cartItems dan package untuk performa
            ->where('userId', $userId)
            ->where('vendorId', $vendor->vendorId)
            ->first();

        // Jika tidak ada cart atau cart kosong, arahkan kembali
        if (!$cart || $cart->cartItems->isEmpty()) {
            return redirect()->back()->with('warning', 'Your cart is empty. Please add items before proceeding to payment.');
        }

        $orderDateTime = Carbon::now();
        // startDate: Selalu Senin minggu depan dari waktu order
        $startDate = $orderDateTime->copy()->next(Carbon::MONDAY)->toDateString();
        // endDate: Minggu seminggu setelah startDate (yaitu Minggu dari minggu depannya)
        $endDate = Carbon::parse($startDate)->copy()->next(Carbon::SUNDAY)->toDateString();

        // Data yang akan diteruskan ke view
        $cartDetails = [];
        $totalOrderPrice = 0;

        foreach ($cart->cartItems as $item) {
            $package = $item->package;
            if ($package) {
                $itemPrice = ($item->breakfastQty * ($package->breakfastPrice ?? 0)) +
                    ($item->lunchQty * ($package->lunchPrice ?? 0)) +
                    ($item->dinnerQty * ($package->dinnerPrice ?? 0));

                $cartDetails[] = [
                    'package_id' => $package->packageId,
                    'package_name' => $package->name,
                    'breakfast_qty' => $item->breakfastQty,
                    'lunch_qty' => $item->lunchQty,
                    'dinner_qty' => $item->dinnerQty,
                    'breakfast_price' => $item->breakfastQty * ($package->breakfastPrice ?? 0),
                    'lunch_price' => $item->lunchQty * ($package->lunchPrice ?? 0),
                    'dinner_price' => $item->dinnerQty * ($package->dinnerPrice ?? 0),
                    'item_total_price' => $itemPrice,
                ];
                $totalOrderPrice += $itemPrice;
            }
        }

        $selectedAddressId = $request->query('address_id');
        $selectedAddress = null;

        if ($selectedAddressId) {
            $selectedAddress = Address::find($selectedAddressId);
            // Opsional: Pastikan alamat ini milik user yang sedang login
            if ($selectedAddress && Auth::check() && $selectedAddress->userId !== Auth::id()) {
                $selectedAddress = null; // Abaikan jika bukan milik user
            }
        }

        // Fallback jika tidak ada address_id di query string atau tidak valid
        if (!$selectedAddress && Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'defaultAddress')) {
                $selectedAddress = $user->defaultAddress;
            } else {
                $selectedAddress = Address::where('userId', $user->userId)
                                         ->where('is_default', 1)
                                         ->first();
            }
        }

        // Pastikan $selectedAddress tidak null
        if (!$selectedAddress) {
            // Redirect kembali atau tampilkan pesan error jika alamat tidak ditemukan/tidak valid
            return redirect()->back()->with('error', 'Alamat pengiriman tidak valid atau tidak dipilih.');
        }

        logActivity('Successfully', 'Visited', 'Vendor Payment Page');
        return view('payment', compact('vendor', 'cart', 'cartDetails', 'totalOrderPrice', 'startDate', 'endDate', 'selectedAddress'));
    }

    public function getUserWellpayBalance()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
        logActivity('Successfully', 'Viewed', 'Wellpay Balance');
        return response()->json(['wellpay' => $user->wellpay]); // <-- Menggunakan 'wellpay'
    }

    /**
     * Proses Checkout: Memindahkan data dari Cart ke Order dan OrderItems, termasuk validasi Wellpay.
     */
    public function processCheckout(Request $request)
    {
        $userId = Auth::id();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $vendorId = $request->input('vendor_id');
        $paymentMethodId = $request->input('payment_method_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $password = $request->input('password');
        $provinsi = $request->input('provinsi');
        $kota = $request->input('kota');
        $kabupaten = $request->input('kabupaten');
        $kecamatan = $request->input('kecamatan');
        $kelurahan = $request->input('kelurahan');
        $kode_pos = $request->input('kode_pos');
        $jalan = $request->input('jalan');
        $recipient_name = $request->input('recipient_name');
        $recipient_phone = $request->input('recipient_phone'); 
        $notes = $request->input('notes');

        try {
            // Initial validation for all incoming request data
            $request->validate([
                'vendor_id' => 'required|exists:vendors,vendorId',
                'payment_method_id' => 'required|exists:payment_methods,methodId',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
                // 'password' is required only if payment_method_id is 1
                'password' => 'required_if:payment_method_id,1|string',
                'provinsi' => 'required|string|max:255',
                'kota' => 'required|string|max:255',
                'kabupaten' => 'required|string|max:255',
                'kecamatan' => 'required|string|max:255',
                'kelurahan' => 'required|string|max:255',
                'kode_pos' => 'required|string|digits:5',
                'jalan' => 'required|string|max:255',
                'recipient_name' => 'required|string|max:255',
                'recipient_phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
                'notes' => 'nullable|string|max:255',
            ]);

            if (!$user) {
                return response()->json(['message' => 'User not authenticated.'], 401);
            }

            DB::beginTransaction();

            $cart = Cart::with('cartItems.package')
                ->where('userId', $userId)
                ->where('vendorId', $vendorId)
                ->first();

            if (!$cart || $cart->cartItems->isEmpty()) {
                DB::rollBack();
                return response()->json(['message' => 'Your cart is empty or expired.'], 400);
            }

            $orderTotalPrice = $cart->totalPrice;

            // --- Logika Validasi dan Pembayaran Wellpay ---
            $wellpayMethodId = 1;

            if ((int)$paymentMethodId === $wellpayMethodId) {
                // Check password first
                if (!Hash::check($password, $user->getAuthPassword())) {
                    DB::rollBack();
                    // Throw ValidationException for password error
                    logActivity('Failed', 'Processed', 'Checkout due to incorrect password');

                    throw ValidationException::withMessages([
                        'password' => ['Incorrect password.'],
                    ]);
                }

                // Then check balance
                if ($user->wellpay < $orderTotalPrice) {
                    DB::rollBack();
                    // This is a specific business logic error, not a validation error on input format.
                    // Returning a 402 is appropriate here.
                    logActivity('Failed', 'Processed', 'Checkout due to insufficient Wellpay balance');
                    return response()->json(['message' => 'Insufficient Wellpay balance. Please top up.'], 402);
                }

                // Deduct Wellpay balance
                $user->wellpay -= $orderTotalPrice;
                $user->save();
                Log::info('Wellpay balance updated for user ' . $userId . '. New balance: ' . $user->wellpay);
            }
            // --- Akhir Logika Wellpay ---

            // 1. Create new Order
            $order = Order::create([
                'userId' => $userId,
                'vendorId' => $vendorId,
                'totalPrice' => $orderTotalPrice,
                'startDate' => Carbon::parse($startDate)->startOfDay(),
                'endDate' => Carbon::parse($endDate)->endOfDay(),
                'isCancelled' => false,
                'provinsi' => $provinsi,
                'kota' => $kota,
                'kabupaten' => $kabupaten,
                'kecamatan' => $kecamatan,
                'kelurahan' => $kelurahan,
                'kode_pos' => $kode_pos,
                'jalan' => $jalan,
                'recipient_name' => $recipient_name,
                'recipient_phone' => $recipient_phone,
                'notes' => $notes,
            ]);
            Log::info('Order created. Order ID: ' . $order->orderId);

            // 2. Move CartItems to OrderItems
            $selectedTimeSlots = [];
            foreach ($cart->cartItems as $cartItem) {
                $package = $cartItem->package;
                if ($package) {
                    if ($cartItem->breakfastQty > 0) {
                        OrderItem::create([
                            'orderId' => $order->orderId,
                            'packageId' => $package->packageId,
                            'packageTimeSlot' => 'Morning',
                            'price' => ($cartItem->breakfastQty * ($package->breakfastPrice ?? 0)),
                            'quantity' => $cartItem->breakfastQty,
                        ]);
                        $selectedTimeSlots['Morning'] = true;
                    }
                    if ($cartItem->lunchQty > 0) {
                        OrderItem::create([
                            'orderId' => $order->orderId,
                            'packageId' => $package->packageId,
                            'packageTimeSlot' => 'Afternoon',
                            'price' => ($cartItem->lunchQty * ($package->lunchPrice ?? 0)),
                            'quantity' => $cartItem->lunchQty,
                        ]);
                        $selectedTimeSlots['Afternoon'] = true;
                    }
                    if ($cartItem->dinnerQty > 0) {
                        OrderItem::create([
                            'orderId' => $order->orderId,
                            'packageId' => $package->packageId,
                            'packageTimeSlot' => 'Evening',
                            'price' => ($cartItem->dinnerQty * ($package->dinnerPrice ?? 0)),
                            'quantity' => $cartItem->dinnerQty,
                        ]);
                        $selectedTimeSlots['Evening'] = true;
                    }
                }
            }
            Log::info('OrderItems created for Order ID: ' . $order->orderId);

            Log::info('Inserting Delivery Statuses for Order ID: ' . $order->orderId);
            $countDeliveryStatuses = 0;

            foreach (array_keys($selectedTimeSlots) as $slot) {
                $currentDeliveryDate = Carbon::parse($order->startDate); // Reset for each slot
                for ($i = 0; $i < 7; $i++) { // Loop for the correct number of days
                    DeliveryStatus::create([
                        'orderId' => $order->orderId,
                        'deliveryDate' => $currentDeliveryDate->toDateString(),
                        'slot' => $slot,
                        'status' => 'Prepared',
                    ]);
                    $currentDeliveryDate->addDay();
                    $countDeliveryStatuses++;
                }
            }
            Log::info('Total Delivery Statuses created: ' . $countDeliveryStatuses);

            // 3. Create Payment entry
            Payment::create([
                'methodId' => $paymentMethodId,
                'orderId' => $order->orderId,
                'paid_at' => Carbon::now(),
            ]);
            Log::info('Payment recorded for Order ID: ' . $order->orderId);

            // 4. Delete Cart and related CartItems
            $cart->delete();
            Log::info('Cart ' . $cart->cartId . ' deleted after successful checkout.');

            // 5. Notify vendor
            $vendor = Vendor::find($vendorId);
            $vendorUserId = $vendor->userId;
            $vendorUser = User::find($vendorUserId);

            if($vendorUser)
            {
                $vendorUser->notify(new CustomerSubscribed($order));
            }
            DB::commit();

            logActivity('Successfully', 'Processed', 'Checkout for Order ID: ' . $order->orderId);

            return response()->json(['message' => 'Checkout successful!', 'orderId' => $order->orderId], 200);

        } catch (ValidationException $e) {
            // This catches the initial validation (e.g., missing field)
            // OR the specific ValidationException thrown for incorrect password
            DB::rollBack(); // Rollback if validation failed AFTER transaction began
            Log::error('Validation failed for checkout:', $e->errors());
            logActivity('Failed', 'Process', 'Checkout');
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // This catches any other unexpected errors (database issues, server errors, etc.)
            DB::rollBack();
            Log::error('Checkout failed (General Error): ' . $e->getMessage(), ['exception' => $e]);
            logActivity('Failed', 'Processed', 'Checkout');
            return response()->json(['message' => 'Checkout failed. Please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::findOrFail($id)
            ->load(['payment', 'deliveryStatuses', 'orderItems.package', 'vendor', 'vendorReview']);

        $paymentMethod = $order->payment ? PaymentMethod::find($order->payment->methodId) : null;

        // Define slots
        $slots = [
            ['key' => 'morning', 'label' => 'Morning', 'icon' => 'partly_cloudy_day'],
            ['key' => 'afternoon', 'label' => 'Afternoon', 'icon' => 'wb_sunny'],
            ['key' => 'evening', 'label' => 'Evening', 'icon' => 'nights_stay'],
        ];

        // Group delivery statuses by slot and date
        $statusesBySlot = [];
        foreach ($order->deliveryStatuses as $status) {
            $slotKey = strtolower($status->slot->value ?? $status->slot);
            $dateKey = Carbon::parse($status->deliveryDate)->format('l, d M Y');
            $statusesBySlot[$slotKey][$dateKey] = $status;
        }
        // dd($statusesBySlot);
        $status = '';
        if($order->isCancelled == 1) {
            $status = 'cancelled';
        } else if (Carbon::now()->greaterThan($order->endDate)){
            $status = 'finished';
        } else if (Carbon::now()->lessThan($order->startDate)){
            $status = 'upcoming';
        } else {
            $status = 'active';
        }

        logActivity('Successfully', 'Visited', "Order #{$order->orderId} Detail Page");
        return view('customer.orderDetail', compact('order', 'paymentMethod', 'slots', 'statusesBySlot', 'status'));
    }

    public function cancelOrder(string $id)
    {
        $order = Order::findOrFail($id);
        $order->isCancelled = true;
        $order->save();

        return redirect()->back()->with('message', 'Success cancelling order!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
