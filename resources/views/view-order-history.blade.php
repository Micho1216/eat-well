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
        <h1 class="text-center">{{ __('admin/order.title') }}</h1>

        <section
            class="container-fluid px-sm-5 pb-sm-4 pt-4 d-flex flex-row flex-wrap justify-content-between align-items-end gap-2">
            <form action="{{ route('view-order-history') }}" method="GET"
                class="d-flex flex-row flex-wrap gap-2 align-items-end">
                @csrf
                <div>
                    <label for="startDate" class="form-label mb-0">{{ __('admin/order.start_date') }}</label>
                    <input type="date" name="startDate" id="startDate" class="form-control"
                        value="{{ request()->query('startDate') }}" max="{{ now()->format('Y-m-d') }}">
                </div>
                <div>
                    <label for="endDate" class="form-label mb-0">{{ __('admin/order.end_date') }}</label>
                    <input type="date" name="endDate" id="endDate" class="form-control"
                        value="{{ request()->query('endDate') }}" max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('admin/order.filter') ?? 'Filter' }}</button>

                    <a href="{{ route('view-order-history') }}" class="btn btn-secondary" id="clearFilterBtn">
                        {{ __('admin/order.clear') ?? 'Clear' }}
                    </a>
                </div>
            </form>
            <div>
                @if ($orders->isEmpty())
                    <button class="btn btn-green" disabled>
                        {{ __('admin/order.export') ?? 'Export' }}
                    </button>
                @else
                    <a href="{{ route('admin.order.export', ['startDate' => request()->query('startDate'), 'endDate' => request()->query('endDate')]) }}"
                        class="btn btn-green">
                        {{ __('admin/order.export') ?? 'Export' }}
                    </a>
                @endif
            </div>
        </section>

        <section class="container-fluid px-sm-5 pb-sm-4 content-section d-flex flex-column justify-content-between"
            style="min-height: 60vh;">
            @if ($errors->has('endDate'))
                <div class="text-danger">{{ $errors->first('endDate') }}</div>
            @endif

            @if (!$orders->isEmpty())
                @include('admin.order-table')
                {{-- Pagination --}}
                @if ($orders->lastPage() > 1)
                    <ul class="catering-pagination pagination justify-content-center mt-auto">
                        <li class="page-item {{ $orders->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $orders->previousPageUrl() ?? '#' }}">&laquo;</a>
                        </li>
                        @for ($i = 1; $i <= $orders->lastPage(); $i++)
                            <li class="page-item {{ $orders->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $orders->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        <li class="page-item {{ !$orders->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $orders->nextPageUrl() ?? '#' }}">&raquo;</a>
                        </li>
                    </ul>
                @endif
            @else
                <h4>{{ __('catering/sales.no_sales') }}</h4>
            @endif

        </section>
    </section>

    <x-admin-footer></x-admin-footer>
@endsection

@section('scripts')
@endsection
