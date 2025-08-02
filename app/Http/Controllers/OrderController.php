<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterOrderHistoryRequest;
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
use Illuminate\Contracts\Auth\Authenticatable;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterOrderHistoryRequest $request)
    {
        $validated = $request->validated();

        $userId = Auth::id();

        $status = $validated['status'] ?? 'all';
        $query = $validated['query'] ?? null;
        $now = Carbon::now();

        $orders = Order::with(['orderItems.package', 'vendor', 'vendorReview'])
            ->where('userId', $userId)
            // ->latest('endDate')
            ->latest()
            ->get();

        if ($query) {
            $orders = $orders->filter(function ($order) use ($query) {
                return str_contains($order->orderId, $query)
                    || str_contains(strtolower($order->vendor?->name), strtolower($query))
                    || $order->orderItems->contains(function ($item) use ($query) {
                        return str_contains(strtolower($item->package?->name), strtolower($query));
                    });
            });
        }

        if ($status != 'all') {
            $orders = $orders->filter(function (Order $order) use ($status) {
                return $order->getOrderStatus() === $status;
            });
        }

        // dd($filteredOrders);

        // logActivity('Successfully', 'Visited', 'Order History Page');
        return view('customer.orderHistory', compact('orders', 'status'));
    }

    public function showPaymentPage() // Menggunakan Route Model Binding untuk Vendor
    {
        // User should be authorized from middleware
        $user = Auth::user();
        $userId = $user->userId;
        $vendorId = session('selected_vendor_id');

        if(!$vendorId) {
            return redirect()->back();
        }

        $vendor = Vendor::find($vendorId);

        if(!$vendor) {
            return redirect()->back();
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
        $selectedAddressId = session('address_id');
        $selectedAddress = null;

        // dd($selectedAddressId);
        if ($selectedAddressId) {
            $selectedAddress = Address::find($selectedAddressId);
            if ($selectedAddress && $userId && $selectedAddress->userId !== $userId) {
                $selectedAddress = null;
                logActivity('Failed', '', 'Payment with invalid address');
                return redirect()->back()->with('error', 'The selected address does not belong to your account.');
            }
            
            if($selectedAddress->provinsi != $vendor->provinsi) {
                logActivity('Failed', '', 'Payment, Catering is too far from customer');
                return redirect()->back()->with('error', 'Catering is too far from you.');
            }
        } else {
            if (method_exists($user, 'defaultAddress')) {
                $selectedAddress = $user->defaultAddress;
            } else {
                $selectedAddress = Address::where('userId', $user->userId)
                    ->where('is_default', 1)
                    ->first();
            }
        }

        if (!$selectedAddress) {
            logActivity('Failed', '', 'Payment with no address selected');
            return redirect()->back()->with('error', 'Alamat pengiriman tidak valid atau tidak dipilih.');
        }

        // logActivity('Successfully', 'Visited', 'Vendor Payment Page');

        $paymentMethod = PaymentMethod::all();

        return view('payment', compact('vendor', 'cart', 'cartDetails', 'totalOrderPrice', 'startDate', 'endDate', 'selectedAddress', 'paymentMethod'));
    }

    public function getUserWellpayBalance()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
        // logActivity('Successfully', 'Viewed', 'Wellpay Balance');
        return response()->json(['wellpay' => $user->wellpay]); // <-- Menggunakan 'wellpay'
    }

    /**
     * Proses Checkout: Memindahkan data dari Cart ke Order dan OrderItems, termasuk validasi Wellpay.
     */
    public function processCheckout(ProcessCheckoutRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->userId;

        $validatedData = $request->validated();

        $vendorId = session('selected_vendor_id');
        if(!$vendorId) {
            return response()->json(['message' => 'No vendor selected.'], 400);
        }
        $paymentMethodId = $validatedData['payment_method_id'];
        $startDate = $validatedData['start_date'];
        $endDate = $validatedData['end_date'];
        $password = $validatedData['password'] ?? null;
        $addressId = session('address_id');
        $address = Address::find($addressId);
        if(!$address){
            return response()->json(['message' => 'No address selected.'], 400);
        }
        $orderAddressData = $this->extractOrderAddressData($address);
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
    private function extractOrderAddressData(Address $address): array
    {
        return [
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->recipient_phone,
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $order = Order::findOrFail($id)
            ->load(['payment', 'deliveryStatuses', 'orderItems.package', 'vendor', 'vendorReview']);

        if($order->userId != $user->userId) {
            return redirect()->route('order-history')->with('error', 'Invalid order!');
        }

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

        $status = '';
        if ($order->isCancelled == 1) {
            $status = 'cancelled';
        } else if (Carbon::now()->greaterThan($order->endDate)) {
            $status = 'finished';
        } else if (Carbon::now()->lessThan($order->startDate)) {
            $status = 'upcoming';
        } else {
            $status = 'active';
        }

        return view('customer.orderDetail', compact('order', 'paymentMethod', 'slots', 'statusesBySlot', 'status'));
    }

    public function cancelOrder(string $id)
    {
        /**
         * @var User | Authenticatable $user
         */
        $user = Auth::user();
        $order = Order::findOrFail($id);

        if($order->userId != $user->userId) {
            return redirect()->back()->with('error', 'Invalid order to cancel!');
        }

        if($order->payment->paymentMethod->name === 'WellPay') {
            $user->wellpay += $order->totalPrice;
            $user->save();
        }

        $order->isCancelled = true;
        $order->save();

        logActivity('Successfullyy', 'Cancelled', "Order ". $order->orderId);
        return redirect()->back()->with('message', 'Success cancelling order!');
    }
}
