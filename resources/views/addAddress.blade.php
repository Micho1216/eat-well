@extends('master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/addAddress.css') }}">
@endsection

@section('content')
    <div class="address-container text-center">
        <div class="text-start mb-3">
            <a href="/manage-address" style="text-decoration: none; color: black"> {{-- Ubah i menjadi a --}}
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
        </div>

        <div class="divider"></div>
        <h4 class="section-title">Address Management</h4>
        <img src="https://img.icons8.com/ios-filled/100/000000/home--v1.png" alt="icon" width="60" class="my-3">

        <p class="text-muted small mb-4">Places where healthy foods will be delivered to. Have multiple places you wish
            food could be delivered? This page will help you to manage your multiple of your addresses.
        </p>

        <form action="{{ route('store-address') }}" method="POST" id="addressForm" novalidate>
            @csrf
            <div class="row justify-content-center mb-4">
                <div class="col-sm-3">
                    <select id="provinsi" class="form-select form-select-sm" aria-label="Small select example"
                        name="provinsi_id" required>
                        <option value="">Pilih Provinsi</option>
                    </select>
                    <input type="hidden" name="provinsi_name" id="provinsi_name">
                    <div class="invalid-feedback" data-message-required="Provinsi tidak boleh kosong."></div>
                </div>
                <div class="col-sm-3">
                    <select id="kota" class="form-select form-select-sm" aria-label="Small select example"
                        name="kota_id" required disabled>
                        <option value="">Pilih Kota</option>
                    </select>
                    <input type="hidden" name="kota_name" id="kota_name">
                    <div class="invalid-feedback" data-message-required="Kota tidak boleh kosong."></div>
                </div>
                <div class="col-sm-3">
                    <select id="kecamatan" class="form-select form-select-sm" aria-label="Small select example"
                        name="kecamatan_id" required disabled>
                        <option value="">Pilih Kecamatan</option>
                    </select>
                    <input type="hidden" name="kecamatan_name" id="kecamatan_name">
                    <div class="invalid-feedback" data-message-required="Kecamatan tidak boleh kosong."></div>
                </div>
                <div class="col-sm-3">
                    <select id="kelurahan" class="form-select form-select-sm" aria-label="Small select example"
                        name="kelurahan_id" required disabled>
                        <option value="">Pilih Kelurahan</option>
                    </select>
                    <input type="hidden" name="kelurahan_name" id="kelurahan_name">
                    <div class="invalid-feedback" data-message-required="Kelurahan tidak boleh kosong."></div>
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-9">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="Alamat"
                            aria-label=".form-control-sm example" name="jalan" required maxlength="255">
                        <div class="invalid-feedback">
                            Alamat tidak boleh kosong.
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <input class="form-control form-control-sm" type="text" placeholder="Kode pos"
                        aria-label=".form-control-sm example" name="kode_pos" required pattern="[0-9]{5}" minlength="5" maxlength="5">
                    <div class="invalid-feedback"
                        data-message-required="Kode pos tidak boleh kosong."
                        data-message-pattern="Kode pos harus 5 digit angka.">
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-12">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="Catatan (Opsional)"
                            aria-label=".form-control-sm example" name="notes" maxlength="255">
                        <div class="invalid-feedback">
                            Catatan maksimal 255 karakter.
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-3">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="Nama Penerima"
                            aria-label=".form-control-sm example" name="recipient_name" required maxlength="100">
                        <div class="invalid-feedback">
                            Nama penerima tidak boleh kosong.
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="Nomor Telepon"
                            aria-label=".form-control-sm example" name="recipient_phone" required pattern="[0-9]+" minlength="10" maxlength="15">
                        <div class="invalid-feedback"
                            data-message-required="Nomor telepon tidak boleh kosong."
                            data-message-pattern="Nomor telepon harus angka."
                            data-message-minlength="Nomor telepon minimal 10 digit."
                            data-message-maxlength="Nomor telepon maksimal 15 digit.">
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success btn-sm" style="width: 140px">Save</button>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <button type="button" class="btn btn-danger btn-sm" style="width: 140px"
                            onclick="window.location.href='{{ route('manage-address') }}'">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('js/customer/addAddress.js') }}"></script>
@endsection