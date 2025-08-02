@extends('components.vendor-nav')

@section('title', 'Penjualan')

@section('css')
    @vite(['resources/js/app.js', 'resources/sass/app.scss'])
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catering/salesTable.css') }}">
@endsection

@php
    use Carbon\Carbon;
@endphp

@section('content')
    {{-- TITLE --}}
    <section class="container mt-3">
        <h2 class="fw-bold text-center" style="color: #f1f5eb;">
            {{ __('catering/sales.sales_head', ['name' => $vendor->name]) }}</h2>
    </section>

    {{-- FILTER CONTAINER --}}
    <section class="container d-flex flex-row flex-wrap justify-content-between align-items-end gap-2">
        <form action="{{ route('sales.show') }}" method="GET" class="d-flex flex-row flex-wrap gap-2 align-items-end">
            @csrf
            <div>
                <label for="startDate" class="form-label mb-0">{{ __('catering/sales.start_date') }}</label>
                <input type="date" name="startDate" id="startDate" class="form-control"
                    value="{{ request()->query('startDate') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label for="endDate" class="form-label mb-0">{{ __('catering/sales.end_date') }}</label>
                <input type="date" name="endDate" id="endDate" class="form-control"
                    value="{{ request()->query('endDate') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('catering/sales.filter') ?? 'Filter' }}</button>

                <a href="{{ route('sales.show') }}" class="btn btn-secondary" id="clearFilterBtn">
                    {{ __('catering/sales.clear') ?? 'Clear' }}
                </a>
            </div>

        </form>
        <div>
            @if ($orders->isEmpty())
                <button class="btn btn-green" disabled>
                    {{ __('catering/sales.export') }}
                </button>
            @else
                <a href="{{ route('sales.export', ['startDate' => request()->query('startDate'), 'endDate' => request()->query('endDate')]) }}"
                    class="btn btn-green">
                    {{ __('catering/sales.export') }}
                </a>
            @endempty
    </div>
</section>

{{-- SALES TABLE --}}
<div class="container px-2 my-1 my-sm-3">
    @if ($errors->has('endDate'))
        <div class="text-danger">{{ $errors->first('endDate') }}</div>
    @endif
    @if (!$orders->isEmpty())
        @include('catering.salesTable', [$orders, $totalSales, $startDate, $endDate])
    @else
        <h4>{{ __('catering/sales.no_sales') }}</h4>
    @endif
</div>
@endsection
