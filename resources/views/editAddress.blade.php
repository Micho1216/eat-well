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
        <h4 class="section-title">{{ __('edit-address.title') }}</h4>
        <img src="https://img.icons8.com/ios-filled/100/000000/home--v1.png" alt="icon" width="60" class="my-3">

        <p class="text-muted small mb-4">{{ __('edit-address.subtitle') }}</p>

        <form action="{{ route('update-address', $address->addressId) }}" method="POST" id="addressForm" novalidate>
            @csrf
            @method('PATCH')
            <div class="row justify-content-center mb-4">
                <div class="col-sm-3">
                    <select id="provinsi" class="form-select form-select-sm" name="provinsi_id" required>
                        <option value="">{{ __('edit-address.select.provinsi') }}</option>
                    </select>
                    <input type="hidden" name="provinsi_name" id="provinsi_name">
                    <div class="invalid-feedback" data-message-required="{{ __('edit-address.validation.required') }}"></div>
                </div>
                <div class="col-sm-3">
                    <select id="kota" class="form-select form-select-sm" name="kota_id" required disabled>
                        <option value="">{{ __('edit-address.select.kota') }}</option>
                    </select>
                    <input type="hidden" name="kota_name" id="kota_name">
                    <div class="invalid-feedback" data-message-required="{{ __('edit-address.validation.required') }}"></div>
                </div>
                <div class="col-sm-3">
                    <select id="kecamatan" class="form-select form-select-sm" name="kecamatan_id" required disabled>
                        <option value="">{{ __('edit-address.select.kecamatan') }}</option>
                    </select>
                    <input type="hidden" name="kecamatan_name" id="kecamatan_name">
                    <div class="invalid-feedback" data-message-required="{{ __('edit-address.validation.required') }}"></div>
                </div>
                <div class="col-sm-3">
                    <select id="kelurahan" class="form-select form-select-sm" name="kelurahan_id" required disabled>
                        <option value="">{{ __('edit-address.select.kelurahan') }}</option>
                    </select>
                    <input type="hidden" name="kelurahan_name" id="kelurahan_name">
                    <div class="invalid-feedback" data-message-required="{{ __('edit-address.validation.required') }}"></div>
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-9">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="{{ __('edit-address.alamat') }}"
                            name="jalan" required maxlength="255" value="{{ $address->jalan }}">
                        <div class="invalid-feedback">
                            {{ __('edit-address.validation.alamat_required') }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <input class="form-control form-control-sm" type="text" placeholder="{{ __('edit-address.kode_pos') }}"
                        name="kode_pos" required pattern="[0-9]{5}" minlength="5" maxlength="5" value="{{ $address->kode_pos }}">
                    <div class="invalid-feedback"
                        data-message-required="{{ __('edit-address.validation.kode_pos_required') }}"
                        data-message-pattern="{{ __('edit-address.validation.kode_pos_invalid') }}">
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-12">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="{{ __('edit-address.catatan') }}"
                            name="notes" maxlength="255" value="{{ $address->notes ?? '' }}">
                        <div class="invalid-feedback">
                            {{ __('edit-address.validation.required') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mb-4">
                <div class="col-sm-3">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="{{ __('edit-address.nama_penerima') }}"
                            name="recipient_name" required maxlength="100" value="{{ $address->recipient_name }}">
                        <div class="invalid-feedback">
                            {{ __('edit-address.validation.nama_required') }}
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <input class="form-control form-control-sm" type="text" placeholder="{{ __('edit-address.telepon') }}"
                            name="recipient_phone" required pattern="[0-9]+" minlength="10" maxlength="15" value="{{ $address->recipient_phone }}">
                        <div class="invalid-feedback"
                            data-message-required="{{ __('edit-address.validation.telepon_required') }}"
                            data-message-pattern="{{ __('edit-address.validation.telepon_invalid') }}"
                            data-message-minlength="{{ __('edit-address.validation.telepon_min') }}"
                            data-message-maxlength="{{ __('edit-address.validation.telepon_max') }}">
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <button type="submit" class="btn btn-success btn-sm" style="width: 140px">{{ __('edit-address.save') }}</button>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="mb-3">
                        <button type="button" class="btn btn-danger btn-sm" style="width: 140px"
                            onclick="window.location.href='{{ route('manage-address') }}'">{{ __('edit-address.cancel') }}</button>
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
    <script>
        window.currentAddress = @json($address);

        window.oldInput = {
            provinsi_id: "{{ old('provinsi_id') }}",
            provinsi_name: "{{ old('provinsi_name') }}",
            kota_id: "{{ old('kota_id') }}",
            kota_name: "{{ old('kota_name') }}",
            kecamatan_id: "{{ old('kecamatan_id') }}",
            kecamatan_name: "{{ old('kecamatan_name') }}",
            kelurahan_id: "{{ old('kelurahan_id') }}",
            kelurahan_name: "{{ old('kelurahan_name') }}"
        };
    </script>
    <script src="{{ asset('js/customer/editAddress.js') }}"></script>
@endsection