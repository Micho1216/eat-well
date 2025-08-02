@extends('master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/customerVendorFirstPage.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
@endsection


@section('content')
    <div id="translation-data"
        data-province-text="{{ __('vendor-first-page.province') }}"
        data-city-text="{{ __('vendor-first-page.city') }}"
        data-district-text="{{ __('vendor-first-page.district') }}"
        data-village-text="{{ __('vendor-first-page.village') }}">
    </div>
    <div class="position-fixed bg-black w-100 h-100 content opacity-50 disabled-area fix-margin"></div>
    <div class="container-fluid content content-1 min-vh-100">
        <div class="row align-items-center justify-content-center px-0 py-5">
            <div class="col-auto col-sm-10 col-md-9 z-3">
                <div class="card p-5 rounded-4">
                    <form action="{{ route('vendor.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row justify-content-center align-self-center py-3 gy-1">
                            <div class="col-auto mb-0 mt-0">
                                <span class="material-symbols-outlined">add_home</span>
                            </div>
                            <div class="w-100"></div>
                            <hr class="border border-blackx` order-2 align-self-center w-50 my-0 opacity-100">
                            <h2 class="h2 text-center account-sertup-title p-2">{{ __('vendor-first-page.fill_data') }}</h2>
                        </div>
                        <div class="row d-flex justify-content-center" style="margin-bottom: 20px;">
                            <div class="col d-flex flex-column align-items-center justify-content-center">
                                <span class="form-label mt-2">Vendor Logo</span>
                                <div class="position-relative" style="width: 120px; height: 120px;">
                                    <img id="vendorLogoPreview"
                                        src= "asset/profile/noPict.jpg"
                                        alt="Vendor Logo" class="rounded-circle border vendor"
                                        style="width: 120px; height:120px; object-fit:cover">
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary mt-2" id="logoUploadBtn">
                                        <span>Add Logo</span>
                                    </button>
                                    <input type="file" id="vendorLogoInput" name="logo" accept="image/*"
                                    style="display: none;" value="{{ old('logo') }}">
                                    
                                    @error('logo')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-8 d-flex flex-column align-items-space-between mb-4" style="margin-top:20px">
                                <label for="vendorName" class="form-label">{{ __('vendor-first-page.vendor_name') }}</label>
                                <input type="text" value="{{ old('name') }}"class="form-control" id="vendorName"
                                    placeholder="Vendor Name" name="name">
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row fw-bold mb-2">
                            <div class="col-12">{{ __('vendor-first-page.delivery_schedule') }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 fw-bold">{{ __('vendor-first-page.breakfast') }}</div>
                            <div class="col-md-6">
                                <label for="fromBreakfast" class="form-label">{{ __('vendor-first-page.from') }}</label>
                                <input type="time" value="{{ old('startBreakfast') }}" id="fromBreakfast" min="00:00"
                                    max="23:59" step="60" class="form-control" name="startBreakfast">
                                @error('startBreakfast')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="untilBreakfast" class="form-label">{{ __('vendor-first-page.until') }}</label>
                                <input type="time" value="{{ old('closeBreakfast') }}" id="untilBreakfast" min="00:00"
                                    max="23:59" step="60" class="form-control" name="closeBreakfast">
                                @error('closeBreakfast')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 fw-bold">{{ __('vendor-first-page.lunch') }}</div>
                            <div class="col-md-6">
                                <label for="fromLunch" class="form-label">{{ __('vendor-first-page.from') }}</label>
                                <input type="time" value="{{ old('startLunch') }}" id="fromLunch" min="00:00"
                                    max="23:59" step="60" class="form-control" name="startLunch">
                                @error('startLunch')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="untilLunch" class="form-label">{{ __('vendor-first-page.until') }}</label>
                                <input type="time" value="{{ old('closeLunch') }}" id="untilLunch" min="00:00"
                                    max="23:59" step="60" class="form-control" name="closeLunch">
                                @error('closeLunch')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 fw-bold">{{ __('vendor-first-page.dinner') }}</div>
                            <div class="col-md-6">
                                <label for="fromDinner" class="form-label">{{ __('vendor-first-page.from') }}</label>
                                <input type="time" value="{{ old('startDinner') }}" id="fromDinner" min="00:00"
                                    max="23:59" step="60" class="form-control" name="startDinner">
                                @error('startDinner')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="untilDinner" class="form-label">{{ __('vendor-first-page.until') }}</label>
                                <input type="time" value="{{ old('closeDinner') }}" id="untilDinner" min="00:00"
                                    max="23:59" step="60" class="form-control" name="closeDinner">
                                @error('closeDinner')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 gy-3 py-2">
                            <div class="col">
                                <label for="provinsi" class="form-label">{{ __('vendor-first-page.province') }}</label>
                                <select id="provinsi" name="provinsi" class="form-select"
                                    aria-label="Small select example">
                                    <option selected>{{ __('vendor-first-page.province') }}</option>
                                </select>
                                <input type="hidden" name="provinsi_name" id="provinsi_name">
                                @error('provinsi_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="kota" class="form-label">{{ __('vendor-first-page.city') }}</label>
                                <select id="kota" name="kota" class="form-select">
                                    <option selected>{{ __('vendor-first-page.city') }}</option>
                                </select>
                                <input type="hidden" name="kota_name" id="kota_name">
                                @error('kota_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="kecamatan" class="form-label">{{ __('vendor-first-page.district') }}</label>
                                <select id="kecamatan" name="kecamatan" class="form-select">
                                    <option selected>{{ __('vendor-first-page.district') }}</option>
                                </select>
                                <input type="hidden" name="kecamatan_name" id="kecamatan_name">
                                @error('kecamatan_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row gy-3 py-2">
                            <div class="col">
                                <label for="kelurahan" class="form-label">{{ __('vendor-first-page.village') }}</label>
                                <select id="kelurahan" name="kelurahan" class="form-select">
                                    <option selected>{{ __('vendor-first-page.village')}}</option>
                                </select>
                                <input type="hidden" name="kelurahan_name" id="kelurahan_name">
                                @error('kelurahan_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="zipCode" class="form-label">{{ __('vendor-first-page.zip_code') }}</label>
                                <input type="text" value="{{ old('kode_pos') }}" class="form-control" id="zipCode"
                                    placeholder="28162" name="kode_pos">
                                </select>
                                @error('kode_pos')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4 phonum">
                                <label for="recipientTel" class="form-label">{{ __('vendor-first-page.phone_number') }}</label>
                                <input type="tel" class="form-control" value="{{ old('phone_number') }}"
                                    id="phoneNumber" placeholder="081212393219" name="phone_number">
                                @error('phone_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-between gy-3 py-2">
                            <div class="col-12">
                                <label for="address" class="form-label">{{ __('vendor-first-page.address') }}</label>
                                <input type="text" class="form-control" value="{{ old('jalan') }}" id="address"
                                    placeholder="1234 Main St" name="jalan">
                                @error('jalan')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-8 align-self-center mx-auto mt-5">
                            <button type="submit" class="btn btn-success" id='submit-button'>{{ __('vendor-first-page.continue') }}</button>
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
    <script src="{{ asset('js/vendorFirstPage.js') }}"></script>

    <script>
        const oldProv = "{{ old('provinsi') }}";
        const oldKota = "{{ old('kota') }}";
        const oldKec = "{{ old('kecamatan') }}";
        const oldKel = "{{ old('kelurahan') }}";
    </script>
@endsection
