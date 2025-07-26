@extends('components.vendor-nav')

@section('title', 'EatWell | My Packages')

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #0b3d2e;
            display: flex;
            justify-content: center;
            align-items: center;
            /* min-height: 100vh; */
            flex-direction: column;
            color: white;
        }

        .heading-title {
            font-size: 2rem;
            text-align: center;
            color: #ffffff;
            margin-top: 2rem;
            font-weight: 600;
        }

        .text-muted-subheading {
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
            font-size: 1rem;
            text-align: center;
            padding: 0 10px;
        }

        @media (max-width: 576px) {
            .heading-title {
                font-size: 1.5rem;
            }

            .text-muted-subheading {
                font-size: 0.9rem;
            }
        }

        .custom-container {
            padding: 30px;
            background-color: #fff;
            color: #000;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 80%;
            margin-top: 20px;
        }

        .custom-containers {
            padding: 30px;
            background-color: #fff;
            color: #000;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 1140px;
            /* Lebar penuh untuk desktop */
            margin: 20px auto;
            box-sizing: border-box;
        }

        @media (max-width: 576px) {
            .custom-containers {
                padding: 20px;
                max-width: 75%;
            }
        }

        @media (min-width: 577px) and (max-width: 768px) {
            .custom-containers {
                max-width: 80%;
            }
        }

        /* @media (min-width: 769px) {
                                                                            .custom-containers {
                                                                                max-width: 360px;
                                                                            }
                                                                        } */

        .modal-lg {
            max-width: 800px;
        }

        .dropzone {
            border: 2px dashed #ccc;
            background: transparent;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .carousel-wrapper {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 20px;
        }

        .carousel-item {
            width: 180px;
            height: 120px;
            border: 2px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-button {
            position: absolute;
            bottom: 6px;
            right: 6px;
            background: #28a745;
            border: none;
            color: white;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-button {
            width: 180px;
            height: 120px;
            border: 2px dashed #aaa;
            border-radius: 10px;
            background-color: #fff;
            color: #555;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        input[type="file"] {
            display: none;
        }

        .dropzone {
            border: 2px dashed #aaa !important;
            background-color: #fafafa;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: #777;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            transition: background-color 0.3s;
        }

        .dropzone:hover {
            background-color: #f0f0f0;
        }

        /* Untuk teks "Drop here..." */
        .dropzone .dz-message {
            font-size: 16px;
            color: #777;
        }

        @media (min-width: 308px) {
            .modal-dialog {
                margin: 1.75rem auto;
            }

            .modal-content {
                border-radius: 15px;
                padding: 20px;
                max-width: 80%;
                /* agar tidak terlalu mepet */
                margin: auto;
            }
        }

        @media (max-width: 768px) {
            .modal-content {
                padding: 1rem;
            }
        }

        td {
            vertical-align: middle;
        }

        th {
            vertical-align: middle;
        }
    </style>
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
        function downloadTemplateCSV() {
            const link = document.createElement("a");

            // Ganti path‑nya sesuai lokasi file di server
            link.href = "/asset/catering/homePage/template_package_import.csv";
            link.download = "template_package_import.csv"; // nama file saat disimpan user

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: '{{ __('manage-catering-package.success') }}',
                text: message,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'OK'
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: '{{ __('manage-catering-package.failed') }}',
                text: message,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Coba Lagi'
            });
        }

        function downloadTemplateCSV() {
            const link = document.createElement("a");
            link.href = "/asset/catering/homePage/template_package_import.csv";
            link.download = "template_package_import.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }


        document.addEventListener('DOMContentLoaded', function() {
            const uploadInput = document.getElementById('import');
            if (!uploadInput) return;

            uploadInput.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = async (evt) => {
                    const workbook = XLSX.read(new Uint8Array(evt.target.result), {
                        type: 'array'
                    });
                    const rows = XLSX.utils.sheet_to_json(workbook.Sheets[workbook.SheetNames[
                        0]], {
                        defval: ''
                    });

                    if (!rows.length) {
                        showError('{{ __('manage-catering-package.empty_file') }}');
                        return;
                    }

                    const requiredFields = ['name', 'categoryId'];
                    const hasRequiredColumns = requiredFields.every(field => field in rows[0]);

                    if (!hasRequiredColumns) {
                        showError('{{ __('manage-catering-package.wrong_column_format') }}');
                        return;
                    }

                    const postUrl = '/manageCateringPackage';
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;

                    const requests = rows.map(async row => {
                        const fd = new FormData();
                        fd.append('_token', csrf);
                        fd.append('name', row['name']);
                        fd.append('categoryId', row['categoryId']);
                        fd.append('averageCalories', row['averageCalories']);
                        fd.append('breakfastPrice', row['breakfastPrice']);
                        fd.append('lunchPrice', row['lunchPrice']);
                        fd.append('dinnerPrice', row['dinnerPrice']);

                        try {
                            const res = await fetch(postUrl, {
                                method: 'POST',
                                body: fd,
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });

                            if (!res.ok) return {
                                success: false
                            };
                            return {
                                success: true
                            };
                        } catch (err) {
                            console.error('Request failed:', err);
                            return {
                                success: false
                            };
                        }
                    });

                    try {
                        const results = await Promise.all(requests);
                        const ok = results.filter(r => r.success).length;
                        showSuccess(
                            `{{ __('manage-catering-package.import_success') }} ${ok}, {{ __('manage-catering-package.import_failed') }} ${results.length - ok}`
                        );
                        setTimeout(() => location.reload(), 1500);
                    } catch (err) {
                        console.error(err);
                        showError('{{ __('manage-catering-package.import_error') }}');
                    }
                };
                reader.readAsArrayBuffer(file);
            });
        });


        function handleEditClick(btn) {
            const data = JSON.parse(btn.dataset.package);
            console.log('DATA PACKAGE:', data); // ← Tambahkan ini dulu
            openEditModal(data);
        }

        function openEditModal(data) {
            document.getElementById('packageModalLabel').innerText = '{{ __('manage-catering-package.edit_package') }}';

            // Isi form
            document.getElementById('packageName').value = data.name;
            document.getElementById('category').value = data.categoryId;
            document.getElementById('breakfastPrice').value = (+data.breakfastPrice).toFixed(2);
            document.getElementById('lunchPrice').value = (+data.lunchPrice).toFixed(2);
            document.getElementById('dinnerPrice').value = (+data.dinnerPrice).toFixed(2);
            document.getElementById('averageCalories').value = data.averageCalories;

            // Cuisine
            const cuisineInputs = document.getElementById('cuisineInputs');
            cuisineInputs.innerHTML = '';
            document.querySelectorAll('#cuisine-buttons button').forEach(b => {
                b.classList.replace('btn-success', 'btn-outline-secondary');
            });
            data.cuisines.forEach(id => {
                const btn = document.querySelector(`#cuisine-buttons button[onclick*="${id}"]`);
                if (btn) {
                    btn.classList.replace('btn-outline-secondary', 'btn-success');
                }
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'cuisine_types[]';
                hidden.value = id;
                cuisineInputs.appendChild(hidden);
            });

            // Reset Dropzone file lama
            menuDropzone.removeAllFiles(true);
            imageDropzone.removeAllFiles(true);

            // Tambahkan preview file lama
            if (data.menuPDFPath) {
                let mockFile = {
                    name: data.menuPDFPath,
                    size: 123456
                };
                menuDropzone.emit("addedfile", mockFile);
                menuDropzone.emit("complete", mockFile);
            }

            if (data.imgPath) {
                let mockFile = {
                    name: data.imgPath,
                    size: 123456
                };
                imageDropzone.emit("addedfile", mockFile);
                imageDropzone.emit("thumbnail", mockFile, `/asset/menus/${data.imgPath}`);
                imageDropzone.emit("complete", mockFile);
            }

            // Update form action
            const form = document.getElementById('packageForm');
            form.action = `/packages/${data.id}`; // target update
            form.setAttribute('data-method', 'PUT'); // buat tahu ini edit

            // Tambah _method hidden input (PUT)
            form.querySelectorAll('input[name="_method"]').forEach(el => el.remove());
            const spoof = document.createElement('input');
            spoof.type = 'hidden';
            spoof.name = '_method';
            spoof.value = 'PUT';
            form.appendChild(spoof);

            new bootstrap.Modal(document.getElementById('packageModal')).show();
        }
    </script>



    <script>
        document.getElementById('excelUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {
                    type: 'array'
                });
                const sheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[sheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);

                // Ambil baris pertama
                const firstRow = jsonData[0];

                // Masukkan ke form
                if (firstRow) {
                    document.getElementById('packageName').value = firstRow["name"];
                    document.getElementById('category').value = firstRow["categoryId"];
                    document.getElementById('breakfastPrice').value = firstRow["breakfastPrice"];
                    document.getElementById('lunchPrice').value = firstRow["lunchPrice"];
                    document.getElementById('dinnerPrice').value = firstRow["dinnerPrice"];
                    document.getElementById('averageCalories').value = firstRow["averageCalories"];
                }
            };

            reader.readAsArrayBuffer(file);
        });

        function mapCategory(name) {
            const categoryMap = {
                "Vegetarian": 1,
                "Gluten-Free": 2,
                "Halal": 3,
                "Low Carb": 4,
                "Low Calorie": 5,
                "Organic": 6,
            };
            return categoryMap[name] || '';
        }

        // function mapCuisineNames(cuisineStr) {
        //     const cuisineMap = {
        //         "Indonesian": 1,
        //         "Chinese": 2,
        //         "Japanese": 3,
        //         "Korean": 4,
        //         "Western": 5,
        //         "Fusion": 6
        //     };
        //     const names = cuisineStr.split(',').map(n => n.trim());
        //     return names.map(n => cuisineMap[n]).filter(Boolean);
        // }

        // function toggleCuisine(id, event = null) {
        //     const existingInput = document.getElementById(`cuisine-${id}`);
        //     if (!existingInput) {
        //         const input = document.createElement('input');
        //         input.type = 'hidden';
        //         input.name = 'cuisineIds[]';
        //         input.value = id;
        //         input.id = `cuisine-${id}`;
        //         document.getElementById('cuisineInputs').appendChild(input);
        //     }

        //     if (event) {
        //         event.target.classList.toggle('btn-outline-secondary');
        //         event.target.classList.toggle('btn-success');
        //     }
        // }

        function showConfirm(message) {
            return Swal.fire({
                title: '{{ __('manage-catering-package.confirm') }}',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            });
        }

        function deletePackage(id) {
            showConfirm('{{ __('manage-catering-package.confirm_delete_msg') }}').then((result) => {
                if (result.isConfirmed) {
                    fetch(`/packages/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showSuccess('{{ __('manage-catering-package.delete_success') }}');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showError('{{ __('manage-catering-package.delete_failed') }}');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showError('{{ __('manage-catering-package.delete_error') }}');
                        });
                }
            });
        }
    </script>

    <script>
        const selectedCuisineIds = new Set();

        function toggleCuisine(id, event) {
            const inputContainer = document.getElementById('cuisineInputs');
            const button = event.target;

            if (selectedCuisineIds.has(id)) {
                selectedCuisineIds.delete(id);
                document.getElementById('cuisine-input-' + id).remove();
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            } else {
                selectedCuisineIds.add(id);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cuisine_types[]';
                input.value = id;
                input.id = 'cuisine-input-' + id;
                inputContainer.appendChild(input);

                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
            }
        }


        function openAddModal() {
            document.getElementById('packageModalLabel').innerText = '{{ __('manage-catering-package.add_package') }}';
            document.getElementById('packageForm').reset();

            selectedCuisineIds.clear();
            document.getElementById('cuisineInputs').innerHTML = '';
            document.querySelectorAll('#cuisine-buttons button').forEach(btn => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            });
        }

        Dropzone.autoDiscover = false;

        /* PDF */
        var menuDropzone = new Dropzone("#menuDropzone", {
            url: "#",
            autoProcessQueue: false,
            maxFiles: 1,
            paramName: "menuPDFPath",
            acceptedFiles: ".pdf",
            addRemoveLinks: true,
            dictRemoveFile: "{{ __('manage-catering-package.dz_change_file') }}",
            dictDefaultMessage: "{{ __('manage-catering-package.dz_drop_files_here') }}",

            // dictDefaultMessage: "{{ __('manage-catering-package.dz_drop_files_here') }}",
        });

        /* Image */
        var imageDropzone = new Dropzone("#imageDropzone", {
            url: "#",
            autoProcessQueue: false,
            maxFiles: 1,
            paramName: "imgPath",
            acceptedFiles: ".png,.jpg,.jpeg",
            maxFilesize: 10,
            addRemoveLinks: true,
            dictRemoveFile: "{{ __('manage-catering-package.dz_change_image') }}",
            dictDefaultMessage: "{{ __('manage-catering-package.dz_drop_image') }}",
        });

        document.getElementById("packageForm").addEventListener("submit", function(e) {
            e.preventDefault();
            e.stopPropagation();

            const form = e.target;
            const formData = new FormData(form);

            // Ambil file dari Dropzone
            if (menuDropzone.getAcceptedFiles().length > 0) {
                formData.append("menuPDFPath", menuDropzone.getAcceptedFiles()[0]);
            }

            if (imageDropzone.getAcceptedFiles().length > 0) {
                formData.append("imgPath", imageDropzone.getAcceptedFiles()[0]);
            }

            const isEdit = form.getAttribute('data-method') === 'PUT';
            const url = form.action;
            const method = isEdit ? 'POST' : 'POST'; // method fetch tetap POST, spoof _method yg atur PUT

            fetch(url, {
                    method: method,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error("Gagal simpan");
                    return res.text(); // bisa diganti json()
                })
                .then(response => {
                    console.log("Sukses:", response);
                    window.location.href = "{{ route('manageCateringPackage') }}"; // redirect sesuka lo
                })
                .catch(err => {
                    console.error("Gagal:", err);
                    alert("Ada error waktu simpan paket");
                });
        });
        const VENDOR_ID = document.getElementById('vendorId').value;
        const carousel = document.getElementById("carousel-wrapper");
        const imageInput = document.getElementById("imageInput");
        const MAX_IMAGES = 5;

        function loadPreviews() {
            fetch(`/vendor-previews?vendorId=${VENDOR_ID}`)
                .then(res => res.json())
                .then(data => {
                    carousel.innerHTML = '';
                    data.previews.forEach(pv => {
                        const item = createImageItem('/' + pv.previewPicturePath, pv.vendorPreviewId);
                        carousel.appendChild(item);
                    });
                    renderAddButton();
                });
        }

        function createImageItem(src, previewId) {
            const item = document.createElement("div");
            item.className = "carousel-item";
            const img = document.createElement("img");
            img.src = src;

            const button = document.createElement("button");
            button.className = "remove-button";
            button.dataset.previewId = previewId;

            updateButtonText(button);

            button.addEventListener("click", () => {
                const total = carousel.querySelectorAll(".carousel-item").length;
                if (total <= 3) {
                    imageInput.dataset.replaceTarget = src;
                    imageInput.dataset.replaceId = previewId;
                    imageInput.click();
                } else {
                    fetch(`/vendor-previews/${previewId}`, {
                        method: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    }).then(() => {
                        item.remove();
                        renderAddButton();
                    });
                }
            });

            item.appendChild(img);
            item.appendChild(button);
            return item;
        }

        function updateButtonText(button) {
            const total = carousel.querySelectorAll(".carousel-item").length;
            button.textContent = total <= 3 ? "↻" : "✖";
        }

        function createAddButton() {
            const addBtn = document.createElement("div");
            addBtn.className = "add-button";
            addBtn.textContent = "+";
            addBtn.addEventListener("click", () => {
                delete imageInput.dataset.replaceTarget;
                delete imageInput.dataset.replaceId;
                imageInput.click();
            });
            return addBtn;
        }

        function renderAddButton() {
            const existingAdd = carousel.querySelector(".add-button");
            if (existingAdd) existingAdd.remove();

            carousel.querySelectorAll(".remove-button").forEach(btn => updateButtonText(btn));

            const total = carousel.querySelectorAll(".carousel-item").length;
            if (total < MAX_IMAGES) {
                carousel.appendChild(createAddButton());
            }
        }

        imageInput.addEventListener("change", e => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            const replaceId = imageInput.dataset.replaceId;

            if (replaceId) {
                formData.append('_method', 'PUT');

                fetch(`/vendor-previews/${replaceId}`, {
                        method: "POST",
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        const imgs = carousel.querySelectorAll("img");
                        imgs.forEach(img => {
                            if (img.src.includes(imageInput.dataset.replaceTarget)) {
                                img.src = '/' + data.preview.previewPicturePath;
                            }
                        });
                        delete imageInput.dataset.replaceTarget;
                        delete imageInput.dataset.replaceId;
                        imageInput.value = "";
                    });
            } else {
                formData.append("vendorId", VENDOR_ID);

                fetch("/vendor-previews/upload", {
                        method: "POST",
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        const item = createImageItem('/' + data.preview.previewPicturePath, data.preview.id);
                        const addButton = carousel.querySelector(".add-button");
                        if (addButton) carousel.insertBefore(item, addButton);
                        else carousel.appendChild(item);
                        renderAddButton();
                        imageInput.value = "";
                    });
            }
        });

        document.addEventListener("DOMContentLoaded", loadPreviews);
    </script>
@endsection
