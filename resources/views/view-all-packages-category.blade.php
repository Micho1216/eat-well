@php
    use Carbon\Carbon;
    Carbon::setLocale(app()->getLocale());
@endphp

@extends('components.admin-nav')

@section('css')
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/adminTable.css') }}">
@endsection

@section('content')
    <section class="container-fluid pt-3 pt-sm-4 px-sm-5 d-flex justify-content-between align-items-center">
        <h1 class="text-center m-0">{{__('admin/package_category.header')}}</h1>
        <button type="button" onclick="openModal()" class="btn btn-success d-flex gap-1 align-items-center">
            <span class="material-symbols-outlined">
                add
            </span>
            {{__('admin/package_category.category')}}
        </button>
    </section>
    @if (session('success'))
        <section class="container-fluid px-sm-5">
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </section>
    @endif

    <section class="container-fluid px-sm-5 pb-sm-4">
        @if ($categories->isEmpty())
            <h4>{{__('admin/package_category.no_category')}}</h4>
        @else
            <div class="table-responsive">
                <table class="table custom-table mt-3">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">{{__('admin/package_category.th_cat')}}</th>
                            <th scope="col">{{__('admin/package_category.th_pkgcount')}}</th>
                            <th scope="col">{{__('admin/package_category.th_created')}}</th>
                            <th scope="col">{{__('admin/package_category.th_action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $cat)
                            <tr>
                                <td>{{ $cat->categoryId }}</td>
                                <td>{{ $cat->categoryName }}</td>
                                <td>{{ $cat->packages()->count() }}</td>
                                <td>{{ Carbon::parse($cat->created_at)->translatedFormat('d F Y') }}</td>
                                <td class="d-flex flex-wrap gap-1">
                                    {{-- <a href="#" class="btn btn-primary btn-sm d-flex gap-1 align-items-center"
                                    onclick="openUpdateModal('{{ $cat->categoryId }}', '{{ $cat->categoryName }}')">
                                    <span class="material-symbols-outlined">edit</span>
                                    Edit
                                </a> --}}
                                    <button type="button" class="btn btn-danger btn-sm d-flex gap-1 align-items-center"
                                        onclick="handleDeleteClick('{{ $cat->categoryId }}', {{ $cat->packages()->count() }})">
                                        <span class="material-symbols-outlined">delete</span>
                                        {{__('admin/package_category.delete')}}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="custom-modal-header">
                    <h5 class="modal-title">{{__('admin/package_category.add_cat')}}</h5>
                    <button type="button" class="close-modal-btn" onclick="closeModal()">×</button>
                </div>
                <div class="custom-modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">{{__('admin/package_category.cat_name')}}</label>
                        <input type="text" name="categoryName" id="categoryName"
                            class="form-control @error('categoryName') is-invalid @enderror"
                            value="{{ old('categoryName') }}" required>
                        @error('categoryName')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">{{__('admin/package_category.cancel')}}</button>
                    <button type="submit" class="btn btn-success">{{__('admin/package_category.submit')}}</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalBackdrop" class="modal-backdrop hidden" onclick="closeModal()"></div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteCategoryModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <form id="deleteCategoryForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="custom-modal-header">
                    <h5 class="modal-title text-danger">{{__('admin/package_category.del_header')}}</h5>
                    <button type="button" class="close-modal-btn" onclick="closeDeleteModal()">×</button>
                </div>
                <div class="custom-modal-body mt-0">
                    <p style="font-size: 14px;">{{__('admin/package_category.del_body')}}</p>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">{{__('admin/package_category.cancel')}}</button>
                    <button type="submit" class="btn btn-danger">{{__('admin/package_category.del_submit')}}</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModalBackdrop" class="modal-backdrop hidden" onclick="closeDeleteModal()"></div>

    <!-- Cannot Delete Info Modal -->
    <div id="cannotDeleteModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="modal-title text-danger">{{__('admin/package_category.canotdel_header')}}</h5>
                <button type="button" class="close-modal-btn" onclick="closeCannotDeleteModal()">×</button>
            </div>
            <div class="custom-modal-body mt-0">
                <p style="font-size: 14px;">{{__('admin/package_category.canotdel_body')}}</p>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeCannotDeleteModal()">OK</button>
            </div>
        </div>
    </div>

    <div id="cannotDeleteBackdrop" class="modal-backdrop hidden" onclick="closeCannotDeleteModal()"></div>
@endsection

@section('scripts')
    <script>
        function openModal() {
            document.getElementById('addCategoryModal').classList.remove('hidden');
            document.getElementById('modalBackdrop').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('addCategoryModal').classList.add('hidden');
            document.getElementById('modalBackdrop').classList.add('hidden');

            // Clear the input value and remove validation state
            const input = document.getElementById('categoryName');
            input.value = '';
            input.classList.remove('is-invalid');

            // Also remove any error message if present
            const errorFeedback = input.nextElementSibling;
            if (errorFeedback && errorFeedback.classList.contains('invalid-feedback')) {
                errorFeedback.innerText = '';
            }
        }

        function openDeleteModal(categoryId) {
            const form = document.getElementById('deleteCategoryForm');
            form.action = `/categories/${categoryId}`; // adjust route prefix if needed
            document.getElementById('deleteCategoryModal').classList.remove('hidden');
            document.getElementById('deleteModalBackdrop').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteCategoryModal').classList.add('hidden');
            document.getElementById('deleteModalBackdrop').classList.add('hidden');
        }

        function handleDeleteClick(categoryId, packageCount) {
            if (packageCount > 0) {
                openCannotDeleteModal();
            } else {
                openDeleteModal(categoryId);
            }
        }

        function openCannotDeleteModal() {
            document.getElementById('cannotDeleteModal').classList.remove('hidden');
            document.getElementById('cannotDeleteBackdrop').classList.remove('hidden');
        }

        function closeCannotDeleteModal() {
            document.getElementById('cannotDeleteModal').classList.add('hidden');
            document.getElementById('cannotDeleteBackdrop').classList.add('hidden');
        }

        @if ($errors->has('categoryName'))
            window.addEventListener('DOMContentLoaded', () => {
                openModal();
            });
        @endif
    </script>
@endsection
