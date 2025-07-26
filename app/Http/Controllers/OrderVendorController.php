<?php

namespace App\Http\Controllers;

use App\Enums\TimeSlot;
use App\Models\DeliveryStatus;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Notifications\OrderArrived;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderPrepared;

class OrderVendorController extends Controller
{
    public function cancel(Order $order)
    {
        // pastikan vendor yang login memang pemilik pesanan
        if ($order->vendorId !== (Auth::user()->vendor->vendorId)) {
            abort(403);
        }

        if ($order->isCancelled) {
            return response()->json(['success' => false, 'message' => 'Order already canceled.'], 400);
        }

        if ($order->endDate <= today()) {
            return response()->json(['success' => false, 'message' => 'Completed orders cannot be canceled.'], 400);
        }

        if ($order->startDate <= today()) {
            return response()->json(['success' => false, 'message' => 'Only upcoming orders can be canceled.'], 400);
        }


        $order->isCancelled = 1;
        $order->save();

        // $order->orderItems()->delete();
        $order->deliveryStatuses()->delete();

        return response()->json(['success' => true], 200);
    }

    /* ------------------------------------------------------------
     *  Helper: batas minggu
     * ---------------------------------------------------------- */
    private function weekBounds(string $which = 'current'): array
    {
        $monday = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY); // Senin

        return $which === 'next'
            ? [$monday->copy()->addWeek(), $monday->copy()->endOfWeek()->addWeek()]
            : [$monday, $monday->copy()->endOfWeek()];
    }

    /* ------------------------------------------------------------
     *  Daftar order (this week / next week lewat query ?week=)
     * ---------------------------------------------------------- */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'week' => 'nullable|string|in:current,next',
        ]);

        $vendor = Auth::user()->vendor;
        $vendorId = $vendor->vendorId;

        $whichWeek = $request->query('week', 'current');       // current | next
        [$from, $to] = $this->weekBounds($whichWeek);

        $orders = Order::with([
            'user.defaultAddress',
            'orderItems.package',
            'deliveryStatuses',
        ])
            ->where('vendorId', $vendorId)
            ->where('isCancelled', 0)
            ->whereDate('startDate', '>=', $from)   // ← pakai startDate
            ->whereDate('startDate', '<=', $to)
            ->get()
            ->map(function ($order) {
                // $addr = $order->user->defaultAddress;

                return [
                    'id' => $order->orderId,
                    'startDate' => Carbon::parse($order->startDate)->format('Y-m-d'),

                    'user' => [
                        'name' => $order->recipient_name,
                        'phone' => $order->recipient_phone,
                        'address' => $order->jalan,
                        'notes' => $order->notes,
                    ],

                    'order_items' => $order->orderItems->map(fn($it) => [
                        'package' => ['name' => $it->package->name ?? 'Paket'],
                        'quantity' => $it->quantity,
                        'package_time_slot' => $it->packageTimeSlot,
                    ]),

                    'delivery_statuses' => $order->deliveryStatuses
                        ->filter(fn($ds) => $ds->deliveryDate->isToday())
                        ->map(fn($ds) => [
                            'slot' => strtolower($ds->slot),
                            'status' => $ds->status,
                            'delivery_date' => $ds->deliveryDate->toDateString(),
                        ])->values(),
                ];
            });

        $packages = $orders
            ->flatMap(fn($o) => $o['order_items']->pluck('package.name'))
            ->unique()
            ->values();

        return view('manageOrder', compact('orders', 'packages', 'vendor'));
    }

    /* ------------------------------------------------------------
     *  Rekap total (hari ini)
     * ---------------------------------------------------------- */
    public function totalOrder()
    {
        $vendor = Auth::user()->vendor ?? Vendor::find(39);
        $vendorId = Auth::user()->vendor->vendorId ?? 39;
        $today = Carbon::today('Asia/Jakarta');

        $orders = Order::with([
            'orderItems.package',
            'deliveryStatuses' => fn($q) =>
                $q->whereDate('deliveryDate', $today),
        ])
            ->where('vendorId', $vendorId)
            ->where('isCancelled', 0)
            ->whereDate('startDate', '<=', $today)
            ->whereDate('endDate', '>=', $today)
            ->get();

        /* ---- bangun $slotCounts persis seperti kode lama ---- */
        $slotEnumToLabel = [
            TimeSlot::Morning->value => 'breakfast',
            TimeSlot::Afternoon->value => 'lunch',
            TimeSlot::Evening->value => 'dinner',
        ];

        $slotCounts = ['breakfast' => [], 'lunch' => [], 'dinner' => []];

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $slotLabel = $slotEnumToLabel[$item->packageTimeSlot] ?? null;
                if (!$slotLabel)
                    continue;

                $pkg = $item->package->name ?? 'Package';
                $slotCounts[$slotLabel][$pkg] =
                    ($slotCounts[$slotLabel][$pkg] ?? 0) + $item->quantity;
            }
        }
        foreach ($slotCounts as &$pkgs)
            ksort($pkgs);

        /* ----- Net‑sales bulan berjalan ----- */
        $today = now('Asia/Jakarta');   // sekali saja
        $stats = Payment::join('orders', 'orders.orderId', '=', 'payments.orderId')
            ->where('orders.vendorId', $vendorId)
            ->whereYear('payments.paid_at', $today->year)
            ->whereMonth('payments.paid_at', $today->month)
            ->selectRaw("( WEEK(payments.paid_at,3) - WEEK(DATE_SUB(payments.paid_at, INTERVAL DAYOFMONTH(payments.paid_at)-1 DAY),3) + 1 ) AS wk,
                     SUM(orders.totalPrice * 0.95) AS nett")
            ->groupBy('wk')
            ->pluck('nett', 'wk')
            ->toArray();

        /* normalisasi 4 minggu – pakai closure agar aman PHP 7.3 */
        $salesData = array_map(function ($w) use ($stats) {
            return (float) ($stats[$w] ?? 0);
        }, range(1, 4));

        $salesMonth = $today->format('F Y');

        return view('cateringHomePage', compact('slotCounts', 'vendor', 'salesData', 'salesMonth'));
    }


    /* ------------------------------------------------------------
     *  Update status delivery
     * ---------------------------------------------------------- */
    public function updateStatus(Request $request, $orderId, $slot)
    {
        // Force JSON validation response
        $validated = validator($request->all(), [
            'status' => 'required|in:Prepared,Delivered,Arrived',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validated->errors(),
            ], 422);
        }

        $ds = DeliveryStatus::where('orderId', $orderId)
            ->where('slot', $slot)
            ->whereDate('deliveryDate', today())
            ->first();

        if (!$ds) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery status not found for this order and slot.'
            ], 404);
        }
        $ds->status = $request->status;
        $ds->save();

        #Note: Get the order, user and status
        $order = Order::find($orderId);
        $userId = $order->userId;
        $user = User::find($userId);
        $orderStatus = $request->status;

        #Note: generating appropriate notification to be sent based on the status;
        $toBeNotified = null;
        if($orderStatus === 'Prepared')
        {
           $toBeNotified = new OrderPrepared($order);
        }
        else if($orderStatus === 'Delivered')
        {
            $toBeNotified = new OrderDelivered($order);
        }
        else if($orderStatus === 'Arrived')
        {
            $toBeNotified = new OrderArrived($order);
        }

        if($toBeNotified !== null)
        {
            #Note: only send when notification is successfuly created;
            $user->notify($toBeNotified);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'updated_status' => $ds
        ]);
    }
}
