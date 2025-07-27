@php
    use Carbon\Carbon;
    Carbon::setLocale(app()->getLocale());
@endphp

@extends('components.admin-nav')

@section('css')
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/adminTable.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/pagination.css') }}">
@endsection

@section('content')
    <section class="container-fluid px-sm-5 pb-sm-4 pt-4">
        <h1 class="text-center">All Orders</h1>
    </section>
    <section class="container-fluid px-sm-5 pb-sm-4">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Vendor Name</th>
                        <th scope="col">Customer Name</th>
                        <th scope="col">Order Items</th>
                        <th scope="col">Order Period</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
                        <tr>
                            <td>{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}</td>
                            <td>{{ $order->orderId }}</td>
                            <td>{{ $order->vendor->name }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>
                                <ul class="mb-0 ps-3">
                                    @foreach ($order->orderItems as $item)
                                        <li>{{ $item->package->name }} ({{ $item->packageTimeSlot}}) x{{ $item->quantity }} {{$loop->last ? '' : ', '}} </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ Carbon::parse($order->startDate)->translatedFormat('d M Y') }} -
                                {{ Carbon::parse($order->endDate)->translatedFormat('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($orders->lastPage() > 1)
                <ul class="catering-pagination pagination justify-content-center my-3">
                    {{-- Previous Page Link --}}
                    <li class="page-item {{ $orders->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $orders->previousPageUrl() ?? '#' }}"
                            tabindex="-1">&laquo;</a>
                    </li>

                    {{-- Pagination Elements --}}
                    @for ($i = 1; $i <= $orders->lastPage(); $i++)
                        <li class="page-item {{ $orders->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $orders->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next Page Link --}}
                    <li class="page-item {{ !$orders->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $orders->nextPageUrl() ?? '#' }}">&raquo;</a>
                    </li>
                </ul>
            @endif
        </div>
    </section>
    <x-admin-footer></x-admin-footer>
@endsection

@section('scripts')
@endsection
