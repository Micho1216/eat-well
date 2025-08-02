@php
    use Carbon\Carbon;
@endphp

<div class="table-responsive mb-3">
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th scope="col">{{ __('admin/order.no') }}</th>
                <th scope="col">{{ __('admin/order.order_id') }}</th>
                <th scope="col">{{ __('admin/order.vendor_name') }}</th>
                <th scope="col">{{ __('admin/order.customer_name') }}</th>
                <th scope="col">{{ __('admin/order.order_items') }}</th>
                <th scope="col">{{ __('admin/order.order_period') }}</th>
                <th scope="col">{{ __('admin/order.order_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $index => $order)
                <tr>
                    @php
                        $index = method_exists($orders, 'currentPage')
                            ? ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration
                            : $loop->iteration;
                    @endphp
                    <td>{{ $index }}</td>
                    <td>{{ $order->orderId }}</td>
                    <td>{{ $order->vendor->name }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>
                        <ul class="mb-0 ps-3">
                            @foreach ($order->orderItems as $item)
                                <li>
                                    {{ $item->package->name }} ({{ $item->packageTimeSlot }})
                                    x{{ $item->quantity }}{{ !$loop->last ? ',' : '' }}
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        {{ Carbon::parse($order->startDate)->translatedFormat('d M Y') }} -
                        {{ Carbon::parse($order->endDate)->translatedFormat('d M Y') }}
                    </td>
                    <td>
                        <span class="label-status status-{{ $order->getOrderStatus() }}">
                            {{ ucfirst(__('customer/order.' . $order->getOrderStatus())) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">{{ __('admin/order.no_orders') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
