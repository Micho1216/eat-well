@extends('components.vendor-nav')

@section('title')
    {{ __('manage-profile-vendor.title') }}
@endsection

@section('css')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/manageProfilevendor.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection

@section('content')
    <div class="container" style="margin-top: 5vh">
        <div class="p-4" style="background-color: white; border-radius: 50px">
            @if (session('success'))
                @if (session('success'))
                    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
                        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert"
                            aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    {{ session('success') }}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const toastEl = document.getElementById('successToast');
                            const toast = new bootstrap.Toast(toastEl, {
                                autohide: true,
                                delay: 3000 // 3 detik
                            });
                            toast.show();
                        });
                    </script>
                @endif
            @endif
            <h2 class="text-center fw-bold lexend">Vendor Profile</h2>
            <p class = "text-center lexend">This page for customizing your catering profile</p>
            <hr>

            <div class="profileInputForm" style="border: solid black 2px; border-radius: ">

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('js/vendors/manageProfileVendor.js') }}"></script>
@endsection
