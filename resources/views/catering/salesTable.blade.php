@php
    use Carbon\Carbon;
@endphp

<div class="table-responsive">
    <table class="table custom-sales-table">
        <thead>
            <tr>
                <th class="d-flex flex-row justify-content-between">
                    <span>{{ __('catering/sales.from') }}</span>
                    <span>:</span>
                </th>
                <th>
                    {{ $startDate ?? '-' }}
                </th>
                <th class="d-flex flex-row justify-content-between">
                    <span>{{ __('catering/sales.until') }}</span>
                    <span>:</span>
                </th>
                <th colspan="6">
                    {{ $endDate ?? '-' }}
                </th>
            </tr>
            <tr class="order-tr">
                <th class="order-th">No.</th>
                <th class="order-th">{{ __('catering/sales.th_id') }}</th>
                <th class="order-th">{{ __('catering/sales.th_cust') }}</th>
                <th class="order-th">{{ __('catering/sales.th_period') }}</th>
                <th class="order-th">{{ __('catering/sales.th_pkg') }}</th>
                <th class="order-th">{{ __('catering/sales.th_timeslot') }}</th>
                <th class="order-th">{{ __('catering/sales.th_qty') }}</th>
                <th class="order-th">{{ __('catering/sales.th_sales') }}</th>
                <th class="order-th">{{ __('catering/sales.th_total_sales') }}</th> {{-- NEW --}}
            </tr>
        </thead>
        <tbody>
            @php $row = 1; @endphp
            @foreach ($orders as $order)
                @php
                    $orderItemsCount = $order->groupedPackages->count();
                    $totalQty = $order->orderItems->sum('quantity');
                @endphp
                @foreach ($order->groupedPackages as $pkgIndex => $pkg)
                    <tr class="order-tr">
                        <td class="order-td">{{ $row++ }}</td>
                        <td class="order-td">ORD{{ $order->orderId }}</td>
                        <td class="order-td">{{ $order->user->name }}</td>
                        <td class="order-td">
                            {{ Carbon::parse($order->startDate)->format('d/m/Y') }} -
                            {{ Carbon::parse($order->endDate)->format('d/m/Y') }}
                        </td>
                        <td class="order-td">{{ $pkg['packageName'] }}</td>
                        <td class="order-td">{{ $pkg['timeSlots'] }}</td>
                        <td class="order-td">{{ $pkg['quantity'] }}</td>
                        <td class="order-td">
                            Rp {{ number_format(($order->totalPrice / $totalQty) * $pkg['quantity'], 2, ',', '.') }}
                        </td>

                        {{-- Show Total Sales only on first item row using rowspan --}}
                        @if ($pkgIndex === 0)
                            <td class="order-td" rowspan="{{ $orderItemsCount }}">
                                Rp {{ number_format($order->totalPrice, 2, ',', '.') }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="8" class="text-end fw-bold">{{ __('catering/sales.total') }}</td>
                <td class="fw-bold">Rp{{ number_format($totalSales, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
