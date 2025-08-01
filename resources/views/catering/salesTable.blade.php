@php
    use Carbon\Carbon;
@endphp

<div class="table-responsive">
    <table class="table table-striped custom-sales-table">
        <thead>
            <tr>
                <th class="d-flex flex-row justify-content-between">
                    <span>{{__('catering/sales.from')}}</span>
                    <span>:</span>
                </th>
                <th>
                    @if ($startDate)
                        {{ $startDate }}
                    @else
                        -                
                    @endif
                </th>
                <th class="d-flex flex-row justify-content-between">
                    <span>{{__('catering/sales.until')}}</span>
                    <span>:</span>
                </th>
                <th>
                    @if ($endDate)
                        {{ $endDate }}
                    @else
                        -                
                    @endif
                </th>
            </tr>
            <tr class="order-tr">
                <th class="order-th">{{__('catering/sales.th_id')}}</th>
                <th class="order-th">{{__('catering/sales.th_cust')}}</th>
                <th class="order-th">{{__('catering/sales.th_period')}}</th>
                <th class="order-th">{{__('catering/sales.th_paidat')}}</th>
                <th class="order-th">{{__('catering/sales.th_method')}}</th>
                <th class="order-th">{{__('catering/sales.th_pkg')}}</th>
                <th class="order-th">{{__('catering/sales.th_sales')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr class="order-tr">
                    <td class="order-td">ORD{{ $order->orderId }}</td>
                    <td class="order-td">{{ $order->user->name }}</td>
                    <td class="order-td">{{ Carbon::parse($order->startDate)->format('d/m/Y') }} - {{ Carbon::parse($order->endDate)->format('d/m/Y') }}</td>
                    <td class="order-td">{{ $order->payment->paid_at}}</td>
                    <td class="order-td">{{ $order->payment->paymentMethod->name}}</td>
                    <td class="order-td">
                        @foreach ($order->groupedPackages as $pkg)
                            {{ $pkg['packageName'] }} ({{__('catering/sales.' . $pkg['timeSlots'])}} {{$pkg['quantity'] . 'x' }}){{ !$loop->last? ', ' : ''}} <br>
                        @endforeach
                    </td>
                    <td class="order-td">Rp {{ number_format($order->totalPrice, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end fw-bold">{{__('catering/sales.total')}}</td>
                <td class="fw-bold">Rp{{ number_format($totalSales, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
