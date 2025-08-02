@extends('master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/customerVendorFirstPage.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
@endsection

@section('content')
    <div id="translation-data" 
        data-province-text="{{ __('customer/customer-first-page.province') }}"
        data-city-text="{{ __('customer/customer-first-page.city') }}"
        data-district-text="{{ __('customer/customer-first-page.district') }}"
        data-village-text="{{ __('customer/customer-first-page.village') }}">
    </div>

    <div class="position-fixed bg-black w-100 h-100 content opacity-50 disabled-area fix-margin"></div>
    <div class="container-fluid content-1 content-2 min-vh-100 px-2 py-5 p-md-5">
        <div class="card px-3 py-5 p-md-5 mx-0 my-5 m-md-5 rounded-3">
            <div class="row align-items-center">
                <div class="position-relative col-12 mb-3 mt-0">
                    <span class="material-symbols-outlined justify-content-center d-flex">add_home</span>
                    <div class="w-100"></div>
                    <hr class="border border-black order-2 align-self-center w-50 my-1 opacity-100 start-50 position-absolute translate-middle">
                    <h2 class="h2 text-center account-sertup-title p-2">{{ __('customer/customer-first-page.title') }}</h2>
                </div>
                
                <div class="col-12">
                    <form action="{{ route('account-setup.customer-store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="d-flex flex-column justify-content-center align-items-center">
                                    <img id="profilePicturePreview"
                                        src="{{ asset(Auth::user()->profilePath) }}"
                                        class="rounded-circle border border-black"
                                        width="150"
                                        height="150">
                                    <input type="file" class="d-none" name="profile" id="profilePictureInput" accept="image/*">
                                    <button type="button" class="w-50 btn btn-outline-secondary mt-2" id="profilePictureUploadBtn">
                                        <span>{{ __('customer/customer-first-page.add_profile') }}</span>
                                    </button>

                                    @error('profile')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6 d-flex flex-column align-items-space-between mt-5">
                                <label for="name" class="form-label">Customer Name</label>
                                <input type="text" value="{{ old('name') }}"class="form-control" id="name"
                                    placeholder="Ryan Gosling" name="name">
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="w-100"></div>
                            <div class="col-12 col-md-4 mt-2">
                                <label for="provinceSelect" class="form-label">{{ __('customer/customer-first-page.province') }}</label>
                                <select id="provinceSelect" name="province" class="form-select">
                                    <option selected>Provinsi</option>
                                </select>
                                @error('province')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4 mt-2">
                                <label for="citySelect" class="form-label">{{ __('customer/customer-first-page.city') }}</label>
                                <select id="citySelect" name="city" class="form-select">
                                    <option selected>Kota</option>
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4 mt-2">
                                <label for="districtSelect" class="form-label"> {{ __('customer/customer-first-page.district') }}</label>
                                <select id="districtSelect" name="district" class="form-select">
                                    <option selected>Kecamatan</option>
                                </select>
                                @error('district')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col mt-2">
                                <label for="villageSelect" class="form-label">{{ __('customer/customer-first-page.village') }}</label>
                                <select id="villageSelect" name="village" class="form-select">
                                    <option selected>Kelurahan</option>
                                </select>
                                @error('village')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4 mt-2">
                                <label for="zipCode" class="form-label">{{ __('customer/customer-first-page.zipcode') }}</label>
                                <input type="tel" maxlength="5" value="{{ old('zipCode') }}" class="form-control" id="zipCode"
                                    placeholder="28162" name="zipCode">
                                </select>
                                @error('zipCode')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4 mt-2">
                                <label for="phoneNumber" class="form-label">{{ __('customer/customer-first-page.phone_number') }}</label>
                                <input type="text" pattern="[0-9]*" class="form-control" value="{{ old('phoneNumber') }}"
                                    id="phoneNumber" placeholder="081212393219" name="phoneNumber" maxlength="13">
                                @error('phoneNumber')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mt-2">
                                <label for="address" class="form-label">{{ __('customer/customer-first-page.address') }}</label>
                                <input type="text" class="form-control" value="{{ old('address') }}" id="address"
                                    placeholder="{{ __('customer/customer-first-page.address_hint') }}" name="address">
                                @error('address')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-8 align-self-center mx-auto mt-5">
                                <button type="submit" class="btn btn-success" id='submit-button'>{{ __('customer/customer-first-page.continue') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('js/customer/customerFirstPage.js') }}"></script>
@endsection
