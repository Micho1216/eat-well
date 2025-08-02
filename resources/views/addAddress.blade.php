@extends('master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/addAddress.css') }}">
    <script>
        window.APP_LANG = "{{ app()->getLocale() }}";
    </script>
@endsection

@section('content')
    <div class="address-container text-center">
        <div class="text-start mb-3">
            <a href="/manage-address" style="text-decoration: none; color: black"> {{-- Ubah i menjadi a --}}
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
        </div>

        <div class="divider"></div>
        <h4 class="section-title">{{ __('add-address.title') }}</h4>
        <img src="https://img.icons8.com/ios-filled/100/000000/home--v1.png" alt="icon" width="60" class="my-3">

        <p class="text-muted small mb-4">{{ __('add-address.description') }}</p>

        <form action="{{ route('store-address') }}" method="POST" id="addressForm" novalidate>
            @csrf
            <div class="row justify-content-center mb-4 Provinsi_Dll">
                <div class="col-sm-3 pilihan_provinsi_dll mb-2">
                    <select id="provinsi" class="form-select form-select-sm" name="provinsi_id" required>
                        <option value="">{{ __('add-address.select_province') }}</option>
                    </select>
                    <input type="hidden" name="provinsi_name" id="provinsi_name">
                    <div class="invalid-feedback" data-message-required="{{ __('add-address.required.province') }}"></div>
                    @error('provinsi_name')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-3 pilihan_provinsi_dll mb-2">
                    <select id="kota" class="form-select form-select-sm" name="kota_id" required disabled>
                        <option value="">{{ __('add-address.select_city') }}</option>
                    </select>
                    <input type="hidden" name="kota_name" id="kota_name">
                    <div class="invalid-feedback" data-message-required="{{ __('add-address.required.city') }}"></div>
                    @error('kota_name')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-3 pilihan_provinsi_dll mb-2">
                    <select id="kecamatan" class="form-select form-select-sm" name="kecamatan_id" required disabled>
                        <option value="">{{ __('add-address.select_district') }}</option>
                    </select>
                    <input type="hidden" name="kecamatan_name" id="kecamatan_name">
                    <div class="invalid-feedback" data-message-required="{{ __('add-address.required.district') }}"></div>
                    @error('kecamatan_name')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-3 pilihan_provinsi_dll mb-2">
                    <select id="kelurahan" class="form-select form-select-sm" name="kelurahan_id" required disabled>
                        <option value="">{{ __('add-address.select_subdistrict') }}</option>
                    </select>
                    <input type="hidden" name="kelurahan_name" id="kelurahan_name">
                    <div class="invalid-feedback" data-message-required="{{ __('add-address.required.subdistrict') }}">
                    </div>
                    @error('kelurahan_name')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-9">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text"
                            placeholder="{{ __('add-address.address') }}" name="jalan" required maxlength="255">
                        <div class="invalid-feedback">{{ __('add-address.required.address') }}</div>
                        @error('jalan')
                            <div class="text-danger d-flex flex-start">
                                <p style="font-size: 12px">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-sm-3">
                    <input class="form-control form-control-sm" type="text"
                        placeholder="{{ __('add-address.zipcode') }}" name="kode_pos" required pattern="[0-9]{5}"
                        minlength="5" maxlength="5">
                    <div class="invalid-feedback" data-message-required="{{ __('add-address.required.zipcode') }}"
                        data-message-pattern="{{ __('add-address.validation.zipcode_format') }}">
                    </div>
                    @error('kode_pos')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-12">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text"
                            placeholder="{{ __('add-address.note') }}" name="notes" maxlength="255">
                        <div class="invalid-feedback">
                            Catatan maksimal 255 karakter.
                        </div>
                    </div>
                    @error('notes')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-3">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text"
                            placeholder="{{ __('add-address.recipient_name') }}" name="recipient_name" required
                            maxlength="100">
                        <div class="invalid-feedback">
                            {{ __('add-address.required.recipient_name') }}
                        </div>
                    </div>
                    @error('recipient_name')
                        <div class="text-danger d-flex flex-start">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text"
                            placeholder="{{ __('add-address.recipient_phone') }}" name="recipient_phone" required
                            pattern="[0-9]+" minlength="10" maxlength="15">
                        <div class="invalid-feedback"
                            data-message-required="{{ __('add-address.required.recipient_phone') }}"
                            data-message-pattern="{{ __('add-address.validation.phone_format') }}"
                            data-message-minlength="{{ __('add-address.validation.phone_min') }}"
                            data-message-maxlength="{{ __('add-address.validation.phone_max') }}">
                        </div>
                    </div>
                    @error('recipient_phone')
                        <div class="text-danger">
                            <p style="font-size: 12px">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success btn-sm"
                            style="width: 140px">{{ __('add-address.save') }}</button>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <button type="button" class="btn btn-danger btn-sm" style="width: 140px"
                            onclick="window.location.href='{{ route('manage-address') }}'">{{ __('add-address.cancel') }}</button>
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
