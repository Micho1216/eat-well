@extends('components.vendor-nav')

@section('title', 'EatWell | My Packages')

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/manageCateringPackage.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
@endsection

@section('content')
    {{-- <x-vendor-nav></x-vendor-nav> --}}
    <div class="heading-title w-50 text-center mx-auto">{{ __('manage-catering-package.find_package') }}</div>
    <div class="text-muted-subheading w-75 text-center mx-auto">{{ __('manage-catering-package.find_package_desc') }}</div>


    <div class="container custom-containers mt-5 mx-auto">
        <div class="d-flex justify-content-between mb-3">

            <div class="container custom-container my-0">
                <div class="row justify-content-center">
                    <div class="d-flex justify-content-center gap-2 mt-3">

                        <!-- Tombol Upload -->
                        <label for="import" class="btn btn-success btn-sm px-4 py-2"
                            style="background-color: #14532d; border-color: #14532d;">
                            <i class="bi bi-upload me-2"></i> {{ __('manage-catering-package.upload_excel') }}
                        </label>
                        <input type="file" class="d-none" id="import" accept=".csv, .xlsx, .xls">


                        <button class="btn btn-outline-secondary btn-sm px-4 py-2" onclick="downloadTemplateCSV()">
                            <i class="bi bi-download me-2"></i> {{ __('manage-catering-package.download_template') }}
                        </button>

                    </div>
                </div>
            </div>



        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>{{ __('manage-catering-package.no') }}</th>
                        <th>{{ __('manage-catering-package.package_name') }}</th>
                        <th>{{ __('manage-catering-package.category') }}</th>
                        <th>{{ __('manage-catering-package.breakfast_price') }}</th>
                        <th>{{ __('manage-catering-package.lunch_price') }}</th>
                        <th>{{ __('manage-catering-package.dinner_price') }}</th>
                        <th>{{ __('manage-catering-package.average_calory') }}</th>
                        <th>{{ __('manage-catering-package.file_menu') }}</th>
                        <th>{{ __('manage-catering-package.package_image') }}</th>
                        <th>{{ __('manage-catering-package.action') }}</th>
                    </tr>
                </thead>

                <tbody id="packageTable">
                    @foreach ($packages as $package)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $package->name }}</td>
                            <td>{{ $package->category->categoryName ?? 'N/A' }}</td>
                            <td>Rp{{ number_format($package->breakfastPrice ?? 0, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($package->lunchPrice ?? 0, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($package->dinnerPrice ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $package->averageCalories }} {{ __('manage-catering-package.kcal') }}</td>
                            <td>
                                @if ($package->menuPDFPath)
                                    <a href="{{ asset('asset/menus/' . $package->menuPDFPath) }}"
                                        target="_blank">{{ $package->menuPDFPath }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if ($package->imgPath)
                                    <img src="{{ asset('asset/menus/' . $package->imgPath) }}" alt="{{ $package->name }}"
                                        width="100">
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-package="{!! htmlspecialchars(
                                    json_encode([
                                        'id' => $package->packageId,
                                        'name' => $package->name,
                                        'categoryId' => $package->categoryId,
                                        'breakfastPrice' => $package->breakfastPrice,
                                        'lunchPrice' => $package->lunchPrice,
                                        'dinnerPrice' => $package->dinnerPrice,
                                        'averageCalories' => $package->averageCalories,
                                        'cuisines' => $package->cuisineTypes->pluck('cuisineId'),
                                        'menuPDFPath' => $package->menuPDFPath,
                                        'imgPath' => $package->imgPath,
                                    ]),
                                    ENT_QUOTES,
                                    'UTF-8',
                                ) !!}"
                                    onclick="handleEditClick(this)">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>

                                <button class="btn btn-danger btn-sm" onclick="deletePackage({{ $package->packageId }})"
                                    title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
        </div>
        </td>
        </tr>
        @endforeach
        </tbody>
        </table>
    </div>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#packageModal"
        onclick="openAddModal()">{{ __('manage-catering-package.add_package') }}</button>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="packageForm" method="post" action="{{ route('packages.store') }}" enctype="multipart/form-data">
                    @csrf
                    {{-- @method('put') --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="packageModalLabel">{{ __('manage-catering-package.add_package') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="excelUpload"
                                class="form-label">{{ __('manage-catering-package.upload_excel') }}</label>
                            <input type="file" class="form-control" id="excelUpload" accept=".csv, .xlsx, .xls">
                        </div>

                        <!-- form fields -->
                        <div class="mb-3">
                            <label for="packageName"
                                class="form-label">{{ __('manage-catering-package.package_name') }}</label>
                            <input type="text" name="name" class="form-control" id="packageName" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('manage-catering-package.select_category') }}</label>
                            <select class="form-select" name="categoryId" id="category">
                                <option value="1">{{ __('manage-catering-package.vegetarian') }}</option>
                                <option value="2">{{ __('manage-catering-package.gluten_free') }}</option>
                                <option value="3">{{ __('manage-catering-package.halal') }}</option>
                                <option value="4">{{ __('manage-catering-package.low_carb') }}</option>
                                <option value="5">{{ __('manage-catering-package.low_calorie') }}</option>
                                <option value="6">{{ __('manage-catering-package.organic') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">

                            <!-- Hidden inputs akan dimasukkan ke sini -->
                            <div id="cuisineInputs"></div>

                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="breakfastPrice"
                                    class="form-label">{{ __('manage-catering-package.breakfast_price') }}</label>
                                <input type="number" name="breakfastPrice" id="breakfastPrice" class="form-control"
                                    step="0.01">
                            </div>

                            <div class="col">
                                <label for="lunchPrice"
                                    class="form-label">{{ __('manage-catering-package.lunch_price') }}</label>
                                <input type="number" name="lunchPrice" id="lunchPrice" class="form-control"
                                    step="0.01" min="0">
                            </div>

                            <div class="col">
                                <label for="dinnerPrice"
                                    class="form-label">{{ __('manage-catering-package.dinner_price') }}</label>
                                <input type="number" name="dinnerPrice" id="dinnerPrice" class="form-control"
                                    step="0.01" min="0">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col">
                                <label for="averageCalories"
                                    class="form-label">{{ __('manage-catering-package.average_calory') }}</label>
                                <input type="number" name="averageCalories" id="averageCalories" class="form-control"
                                    step="0.01" min="0">
                            </div>
                        </div>

                        <div class="dropzone" id="menuDropzone">
                            <input type="file" name="menuPDFPath" id="menuPDFPath">
                        </div>
                        <div class="dropzone" id="imageDropzone">
                            <input type="file" name="imgPath" id="imgPath">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('manage-catering-package.cancel') }}</button>
                        <button type="submit" class="btn btn-success"
                            value="Save Package">{{ __('manage-catering-package.save_package') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    </div>

    <div class="heading-title">{{ __('manage-catering-package.preview_title') }}</div>
    <div class="text-muted-subheading">{{ __('manage-catering-package.preview_desc') }}</div>

    <div class="container custom-container mb-5">
        <div class="carousel-wrapper" id="carousel-wrapper"></div>
        <input type="file" id="imageInput" accept="image/*" />
        <input type="hidden" id="vendorId" value="{{ $vendorId }}">
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        window.locale = {
            success: "{{ __('manage-catering-package.success') }}",
            failed: "{{ __('manage-catering-package.failed') }}",
            empty_file: "{{ __('manage-catering-package.empty_file') }}",
            wrong_column_format: "{{ __('manage-catering-package.wrong_column_format') }}",
            import_success: "{{ __('manage-catering-package.import_success') }}",
            import_failed: "{{ __('manage-catering-package.import_failed') }}",
            import_error: "{{ __('manage-catering-package.import_error') }}",
            edit_package: "{{ __('manage-catering-package.edit_package') }}",
            add_package: "{{ __('manage-catering-package.add_package') }}",
            confirm: "{{ __('manage-catering-package.confirm') }}",
            confirm_delete_msg: "{{ __('manage-catering-package.confirm_delete_msg') }}",
            delete_success: "{{ __('manage-catering-package.delete_success') }}",
            delete_failed: "{{ __('manage-catering-package.delete_failed') }}",
            delete_error: "{{ __('manage-catering-package.delete_error') }}",
            dz_change_file: "{{ __('manage-catering-package.dz_change_file') }}",
            dz_change_image: "{{ __('manage-catering-package.dz_change_image') }}",
            dz_drop_image: "{{ __('manage-catering-package.dz_drop_image') }}",
            dz_drop_files_here: "{{ __('manage-catering-package.dz_drop_files_here') }}",
        };
    </script>

    <script src="{{ asset('js/vendor/manageCateringPackage.js') }}"></script>

@endsection
