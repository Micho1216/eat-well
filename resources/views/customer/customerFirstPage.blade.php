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
    <div class="position-fixed bg-black w-100 h-100 content opacity-50 disabled-area fix-margin"></div>
    <div class="container-fluid content content-1 p-5 min-vh-100">
        <div class="card p-5 m-5 rounded-3">
            <div class="row align-items-center py-3 gy-2">
                <div class="position-relative col-12 mb-3 mt-0">
                    <span class="material-symbols-outlined justify-content-center d-flex">add_home</span>
                    <div class="w-100"></div>
                    <hr class="border border-black order-2 align-self-center w-50 my-1 opacity-100 start-50 position-absolute translate-middle">
                    <h2 class="h2 text-center account-sertup-title p-2">Fill Your Data to be an EatWell Customer</h2>
                </div>
                
                <div class="col-12">
                    <form action="{{ route('account-setup.customer-store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-2 mx-5 p-3">
                                <div class="row justify-content-center h-25">
                                    <div class="col-12">
                                        <img id="profilePicturePreview"
                                            src="{{ asset('asset/profile/noPict.jpg') }}"
                                            alt="Customer Profile Picture" 
                                            class="rounded-circle border w-100 h-100 border-black">
                                    </div>
                                    <input type="file" class="d-none" name="profilePath" id="profilePictureInput" accept="image/*">
                                    <button type="button" class="w-50 btn btn-outline-secondary mt-2" id="profilePictureUploadBtn">
                                        <span>Add Profile</span>
                                    </button>
                
                                    @error('profilePath')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-3 d-flex flex-column align-items-space-between mb-5">
                                <label for="name" class="form-label">Customer Name</label>
                                <input type="text" value="{{ old('name') }}"class="form-control" id="name"
                                    placeholder="Customer Name" name="name">
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="w-100"></div>
                            <div class="col">
                                <label for="provinceSelect" class="form-label">Province</label>
                                <select id="provinceSelect" name="province" class="form-select">
                                    <option selected>Provinsi</option>
                                </select>
                                @error('province')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="citySelect" class="form-label">City/Town</label>
                                <select id="citySelect" name="city" class="form-select">
                                    <option selected>Kota</option>
                                </select>
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="districtSelect" class="form-label">District</label>
                                <select id="districtSelect" name="district" class="form-select">
                                    <option selected>Kecamatan</option>
                                </select>
                                @error('district')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row gy-3 py-2">
                                <div class="col">
                                    <label for="villageSelect" class="form-label">Municipality/Village</label>
                                    <select id="villageSelect" name="village" class="form-select">
                                        <option selected>Kelurahan</option>
                                    </select>
                                    @error('village')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="zipCode" class="form-label">Zip Code</label>
                                    <input type="text" value="{{ old('zipCode') }}" class="form-control" id="zipCode"
                                        placeholder="28162" name="zipCode">
                                    </select>
                                    @error('zipCode')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-4 phonum">
                                    <label for="phoneNumber" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" value="{{ old('phoneNumber') }}"
                                        id="phoneNumber" placeholder="0812-1239-3219" name="phoneNumber">
                                    @error('phoneNumber')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" value="{{ old('address') }}" id="address"
                                        placeholder="1234 Main St" name="address">
                                    @error('address')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            <div class="col-12 col-md-8 align-self-center mx-auto mt-5">
                                <button type="submit" class="btn btn-success" id='submit-button'>Continue</button>
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
