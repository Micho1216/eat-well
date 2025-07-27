@extends('master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/manageAddress.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endsection

@section('content')
    @if (session('delete_success'))
        <div class="modal fade" id="deleteSuccessModal" tabindex="-1" aria-labelledby="deleteSuccessModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSuccessModalLabel">Berhasil!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>{{ session('delete_success') }}</p>
                        <i class="material-symbols-outlined text-success" style="font-size: 50px;">check_circle</i>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Oke</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

     {{-- Modal untuk Sukses Update --}}
    @if (session('update_success'))
        <div class="modal fade" id="updateSuccessModal" tabindex="-1" aria-labelledby="updateSuccessModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateSuccessModalLabel">Berhasil!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>{{ session('update_success') }}</p>
                        <i class="material-symbols-outlined text-success" style="font-size: 50px;">check_circle</i>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Oke</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="container d-flex justify-content-center align-items-center">
        <div class="address-container text-center">
            <div class="text-start mb-3">
                <a href="/manage-profile" style="text-decoration: none; color: black">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
            </div>

            <div class="divider"></div>
            <h4 class="section-title">Address Management</h4>
            <img src="https://img.icons8.com/ios-filled/100/000000/home--v1.png" alt="icon" width="60" class="my-3">

            <p class="text-muted small mb-4">Places where healthy foods will be delivered to. Have multiple places you wish
                food could be delivered? This page will help you to manage your multiple of your addresses.
            </p>

            <!-- Main Address Card -->
            @foreach ($user->addresses as $address)
                <div class="main-address text-start mb-3">
                    @if ($address->is_default)
                        <span class="badge mb-2" style="background-color: #D96323;">Main Address</span>
                    @else
                        <span class="badge mb-2" style="background-color: #909090;">Main Address</span>
                    @endif
                    <div class="fw-semibold">{{ $address->recipient_name }} <span class="text-muted">|
                            {{ $address->recipient_phone }}</span></div>
                    <div class="fw-semibold mt-2 detail-alamat">
                        {{ $address->jalan }}, {{ $address->kelurahan }}, {{ $address->kecamatan }}, {{ $address->kota }},
                        {{ $address->provinsi }}, {{ $address->kode_pos }}
                    </div>

                    @if ($address->notes)
                        <div class="note-text mt-2 detail-note">
                            <strong>Notes:</strong><br>
                            {{ $address->notes }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-end align-items-center mt-3 gap-4">
                        <div class="action-icons d-flex gap-3" style="flex-direction: row;">
                            <a href="{{ route('edit-address', $address->addressId) }}">
                                <button type="button" class="btn btn-primary"
                                    style="background-color: #185640; border-color: #185640;">✏️ Ubah</button>
                            </a>
                            <form action="{{ route('delete-address', $address->addressId) }}" method="POST"
                                class="delete-address-form {{ $address->is_default ? 'd-none' : '' }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger delete-address-btn">🗑️ Hapus</button>
                            </form>
                        </div>

                        <div class="form-check form-switch toggle-switch">
                            <input class="form-check-input set-default-address" type="checkbox" role="switch"
                                data-address-id="{{ $address->addressId }}" {{ $address->is_default ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            @endforeach

            <form id="set-default-address-url-form" action="{{ route('set-default-address') }}" method="POST"
                style="display:none;">
                @csrf
            </form>

            <div class="add-address mt-3 text-start">
                <button class="button-tambah-alamat" style="border: none; background-color: transparent">
                    <a href="{{ route('add-address') }}" style="text-decoration: none; color: black">+ Tambah Alamat</a>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal untuk Sukses --}}
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Berhasil!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Alamat utama berhasil diubah.</p>
                    <i class="material-symbols-outlined text-success" style="font-size: 50px;">check_circle</i>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Oke</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Error/Peringatan (ketika user mencoba menonaktifkan alamat utama) --}}
    <div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="warningModalLabel">Peringatan!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p id="warningMessage">Alamat utama tidak bisa dinonaktifkan langsung. Pilih alamat lain sebagai utama
                        terlebih dahulu.</p>
                    <i class="material-symbols-outlined text-warning" style="font-size: 50px;">warning</i>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="background-color: orange; border-color: orange;">Oke</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Error Umum (dari AJAX error) --}}
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Terjadi Kesalahan!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p id="errorMessage">Terjadi kesalahan saat menghubungi server.</p>
                    <i class="material-symbols-outlined text-danger" style="font-size: 50px;">error</i>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal delete --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Apakah Anda yakin ingin menghapus alamat ini?</p>
                    <i class="material-symbols-outlined text-danger" style="font-size: 50px;">delete</i>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('js/customer/manageAddress.js') }}"></script>
    <script>
        $(document).ready(function() {
            @if (session('delete_success'))
                $('#deleteSuccessModal').modal('show');
            @endif

            @if (session('update_success'))
                $('#updateSuccessModal').modal('show');
            @endif
        });
    </script>
@endsection
