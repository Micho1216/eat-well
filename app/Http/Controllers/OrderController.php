<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCheckoutRequest;
use App\Http\Requests\ShowPaymentPageRequest;
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

    public function showPaymentPage(ShowPaymentPageRequest $request, Vendor $vendor) // Menggunakan Route Model Binding untuk Vendor
    {
        $userId = Auth::id();
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

        // $selectedAddressId = $request->query('address_id');
        $selectedAddressId = $request->validated('address_id');
        $selectedAddress = null;

        if ($selectedAddressId) {
            $selectedAddress = Address::find($selectedAddressId);
            // Opsional: Pastikan alamat ini milik user yang sedang login
            if ($selectedAddress && Auth::check() && $selectedAddress->userId !== Auth::id()) {
                $selectedAddress = null; // Abaikan jika bukan milik user
                return redirect()->back()->with('error', 'The selected address does not belong to your account.');
            }
            // $selectedAddress = Address::find($selectedAddressId);
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
    public function processCheckout(ProcessCheckoutRequest $request)
    {
        $userId = Auth::id();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validatedData = $request->validated();

        $vendorId = $validatedData['vendor_id'];
        $paymentMethodId = $validatedData['payment_method_id'];
        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];
        $password = $validatedData['password'] ?? null;
        $orderAddressData = $this->extractOrderAddressData($validatedData);
        $notes = $validatedData['notes'] ?? null;

        try {
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

            // Dapatkan WellPay methodId dari database
            $wellpayMethod = PaymentMethod::where('name', 'WellPay')->first();
            $wellpayMethodId = $wellpayMethod ? $wellpayMethod->methodId : null;

            // Pastikan WellPay method ditemukan
            if (is_null($wellpayMethodId)) {
                DB::rollBack();
                Log::error('Payment method "WellPay" not found in database.');
                return response()->json(['message' => 'Payment method configuration error. Please try again later.'], 500);
            }

            // Handle Wellpay payment logic
            $this->handleWellpayPayment($user, $paymentMethodId, $orderTotalPrice, $password, $wellpayMethodId);

            // Create Order
            $order = $this->createOrder($userId, $vendorId, $orderTotalPrice, $startDate, $endDate, $orderAddressData, $notes);

            // Move CartItems to OrderItems and generate Delivery Statuses
            $this->processOrderItemsAndDeliveryStatuses($order, $cart);

            // Create Payment entry
            $this->createPaymentEntry($order->orderId, $paymentMethodId);

            // Delete Cart
            $cart->delete();
            Log::info('Cart ' . $cart->cartId . ' deleted after successful checkout.');

            // Notify vendor
            $this->notifyVendor($vendorId, $order);

            DB::commit();

            logActivity('Successfully', 'Processed', 'Checkout for Order ID: ' . $order->orderId);

            return response()->json(['message' => 'Checkout successful!', 'orderId' => $order->orderId], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed for checkout:', $e->errors());
            logActivity('Failed', 'Process', 'Checkout');
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed (General Error): ' . $e->getMessage(), ['exception' => $e]);
            logActivity('Failed', 'Processed', 'Checkout');
            return response()->json(['message' => 'Checkout failed. Please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Extracts address and recipient data from validated request data.
     */
    private function extractOrderAddressData(array $validatedData): array
    {
        return [
            'provinsi' => $validatedData['provinsi'],
            'kota' => $validatedData['kota'],
            'kabupaten' => $validatedData['kabupaten'],
            'kecamatan' => $validatedData['kecamatan'],
            'kelurahan' => $validatedData['kelurahan'],
            'kode_pos' => $validatedData['kode_pos'],
            'jalan' => $validatedData['jalan'],
            'recipient_name' => $validatedData['recipient_name'],
            'recipient_phone' => $validatedData['recipient_phone'],
        ];
    }

    /**
     * Handles Wellpay payment logic, including password check and balance deduction.
     *
     * @param User $user The authenticated user.
     * @param int $selectedPaymentMethodId The ID of the payment method selected by the user.
     * @param float $orderTotalPrice The total price of the order.
     * @param string|null $password The user's password (for WellPay).
     * @param int|null $wellpayMethodId The actual methodId for WellPay from the database.
     * @throws \Illuminate\Validation\ValidationException If password is incorrect.
     * @throws \Exception If Wellpay balance is insufficient.
     */
    private function handleWellpayPayment(User $user, int $selectedPaymentMethodId, float $orderTotalPrice, ?string $password, ?int $wellpayMethodId): void
    {
        // Gunakan $wellpayMethodId yang sudah dicari dari database
        if ($selectedPaymentMethodId === $wellpayMethodId) {
            if (!Hash::check($password, $user->getAuthPassword())) {
                logActivity('Failed', 'Processed', 'Checkout due to incorrect Wellpay password');
                throw ValidationException::withMessages([
                    'password' => ['Incorrect password.'],
                ]);
            }

            if ($user->wellpay < $orderTotalPrice) {
                logActivity('Failed', 'Processed', 'Checkout due to insufficient Wellpay balance');
                throw new \Exception('Insufficient Wellpay balance. Please top up.', 402);
            }

            $user->wellpay -= $orderTotalPrice;
            $user->save();
            Log::info('Wellpay balance updated for user ' . $user->userId . '. New balance: ' . $user->wellpay);
        }
    }

    /**
     * Creates a new Order record in the database.
     */
    private function createOrder(
        int $userId,
        int $vendorId,
        float $totalPrice,
        string $startDate,
        string $endDate,
        array $addressData,
        ?string $notes
    ): Order {
        $order = Order::create(array_merge([
            'userId' => $userId,
            'vendorId' => $vendorId,
            'totalPrice' => $totalPrice,
            'startDate' => Carbon::parse($startDate)->startOfDay(),
            'endDate' => Carbon::parse($endDate)->endOfDay(),
            'isCancelled' => false,
            'notes' => $notes,
        ], $addressData));
        Log::info('Order created. Order ID: ' . $order->orderId);
        return $order;
    }

    /**
     * Processes cart items to create order items and corresponding delivery statuses.
     */
    private function processOrderItemsAndDeliveryStatuses(Order $order, Cart $cart): void
    {
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

        $this->generateDeliveryStatuses($order, $selectedTimeSlots);
    }

    /**
     * Generates delivery status entries for a given order and selected time slots.
     */
    private function generateDeliveryStatuses(Order $order, array $selectedTimeSlots): void
    {
        Log::info('Inserting Delivery Statuses for Order ID: ' . $order->orderId);
        $countDeliveryStatuses = 0;

        foreach (array_keys($selectedTimeSlots) as $slot) {
            $currentDeliveryDate = Carbon::parse($order->startDate);
            for ($i = 0; $i < 7; $i++) {
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
    }

    /**
     * Creates a new Payment record in the database.
     */
    private function createPaymentEntry(int $orderId, int $paymentMethodId): void
    {
        Payment::create([
            'methodId' => $paymentMethodId,
            'orderId' => $orderId,
            'paid_at' => Carbon::now(),
        ]);
        Log::info('Payment recorded for Order ID: ' . $orderId);
    }

    /**
     * Notifies the vendor about a new order.
     */
    private function notifyVendor(int $vendorId, Order $order): void
    {
        $vendor = Vendor::find($vendorId);
        $vendorUserId = $vendor->userId;
        $vendorUser = User::find($vendorUserId);

        if ($vendorUser) {
            $vendorUser->notify(new CustomerSubscribed($order));
            Log::info('Vendor user ' . $vendorUser->userId . ' notified for Order ID: ' . $order->orderId);
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
