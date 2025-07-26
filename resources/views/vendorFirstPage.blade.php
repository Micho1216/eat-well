@extends('master')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/customerVendorFirstPage.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap">
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
@endsection


@section('content')
    @yield('content')
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
                            <h2 class="h2 text-center account-sertup-title p-2">Fill Your Data to be an EatWell Vendor</h2>
                        </div>
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-4 d-flex flex-column align-items-center justify-content-center">
                                <span class="form-label mt-2">Vendor Logo</span>
                                <div class="position-relative" style="width: 120px; height: 120px;">
                                    <img id="vendorLogoPreview"
                                        src="{{ old('logo_path')
                                            ? asset('storage/' . old('logo_path'))
                                            : (session('logo_path')
                                                ? asset('storage/' . session('logo_path'))
                                                : (isset($vendor) && $vendor->logo
                                                    ? asset('storage/' . $vendor->logo)
                                                    : asset('asset/profile/noPict.jpg'))) }}"
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

                            <div class="col-8 d-flex flex-column align-items-space-between" style="margin-top:20px">
                                <label for="vendorName" class="form-label">Vendor Name</label>
                                <input type="text" value="{{ old('name') }}"class="form-control" id="vendorName"
                                    placeholder="Vendor Name" name="name">
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row fw-bold">
                            Delivery Schedule
                        </div>
                        <div class="row fw-bold">
                            <div class="col-4">Breakfast</div>
                            <div class="col-4">Lunch</div>
                            <div class="col-4">Dinner</div>
                        </div>
                        <div class="row">
                            <div class="col-2">
                                <label for="fromBreakfast" class="form-label">From</label>
                                <input type="time" value="{{ old('startBreakfast') }}" id="fromBreakfast" min="00:00"
                                    max="23:59" step="60" placeholder="00.00" class="form-control"
                                    name="startBreakfast">
                                @error('startBreakfast')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-2">
                                <label for="untilBreakfast" class="form-label">Until</label>
                                <input type="time" value="{{ old('closeBreakfast') }}" min="00:00" max="23:59"
                                    step="60" id="untilBreakfast" class="form-control" name="closeBreakfast">
                                @error('closeBreakfast')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-2">
                                <label for="fromLunch" class="form-label">From</label>
                                <input type="time" value="{{ old('startLunch') }}" id="fromLunch" placeholder="00.00"
                                    class="form-control" name="startLunch">
                                @error('startLunch')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-2">
                                <label for="untilLunch" class="form-label">Until</label>
                                <input type="time" value="{{ old('closeLunch') }}" min="00:00" max="23:59"
                                    step="60" id="untilLunch" class="form-control" name="closeLunch">
                                @error('closeLunch')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-2">
                                <label for="fromDinner" class="form-label">From</label>
                                <input type="time" value="{{ old('startDinner') }}" id="fromDinner"
                                    placeholder="00.00" class="form-control" name="startDinner">
                                @error('startDinner')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-2">
                                <label for="untilDinner" class="form-label">Until</label>
                                <input type="time" value="{{ old('closeDinner') }}" min="00:00" max="23:59"
                                    step="60" id="untilDinner" class="form-control" name="closeDinner">
                                @error('closeDinner')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 gy-3 py-2">
                            <div class="col">
                                <label for="provinsi" class="form-label">Province</label>
                                <select id="provinsi" name="provinsi" class="form-select"
                                    aria-label="Small select example">
                                    <option selected>Provinsi</option>

                                </select>
                                @error('provinsi')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="kota" class="form-label">City/Town</label>
                                <select id="kota" name="kota" class="form-select">
                                    <option selected>Kota</option>
                                </select>
                                @error('kota')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="kecamatan" class="form-label">District</label>
                                <select id="kecamatan" name="kecamatan" class="form-select">
                                    <option selected>Kecamatan</option>
                                </select>
                                @error('kecamatan')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row gy-3 py-2">
                            <div class="col">
                                <label for="kelurahan" class="form-label">Municipality/Village</label>
                                <select id="kelurahan" name="kelurahan" class="form-select">
                                    <option selected>Kelurahan</option>
                                </select>
                                @error('kelurahan')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="zipCode" class="form-label">Zip Code</label>
                                <input type="text" value="{{ old('kode_pos') }}" class="form-control" id="zipCode"
                                    placeholder="28162" name="kode_pos">
                                </select>
                                @error('kode_pos')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-4 phonum">
                                <label for="recipientTel" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="{{ old('phone_number') }}"
                                    id="phoneNumber" placeholder="0812-1239-3219" name="phone_number">
                                @error('phone_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-between gy-3 py-2">
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" value="{{ old('jalan') }}" id="address"
                                    placeholder="1234 Main St" name="jalan">
                                @error('jalan')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-8 align-self-center mx-auto mt-5">
                            <button type="submit" class="btn btn-success" id='submit-button'>Continue</button>
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
    <script src="{{ asset('js/vendorFirstPage.js') }}"></script>
    <script src="{{ asset('js/customer/customerFirstPage.js') }}"></script>

    <script>
        const oldProv = "{{ old('provinsi') }}";
        const oldKota = "{{ old('kota') }}";
        const oldKec = "{{ old('kecamatan') }}";
        const oldKel = "{{ old('kelurahan') }}";
    </script>
@endsection
