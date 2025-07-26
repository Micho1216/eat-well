@php
    use Carbon\Carbon;
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
        <h1 class="text-center m-0">Category List</h1>
        <button type="button" onclick="openModal()" class="btn btn-success d-flex gap-1 align-items-center">
            <span class="material-symbols-outlined">
                add
            </span>
            Category
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
            <h4>No categories available</h4>
        @else
            <div class="table-responsive">
                <table class="table custom-table mt-3">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Category Name</th>
                            <th scope="col">Packages count</th>
                            <th scope="col">Created at</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $cat)
                            <tr>
                                <td>{{ $cat->categoryId }}</td>
                                <td>{{ $cat->categoryName }}</td>
                                <td>{{ $cat->packages()->count() }}</td>
                                <td>{{ Carbon::parse($cat->created_at)->format('d M Y') }}</td>
                                <td class="d-flex flex-wrap gap-1">
                                    {{-- <a href="#" class="btn btn-primary btn-sm d-flex gap-1 align-items-center"
                                    onclick="openUpdateModal('{{ $cat->categoryId }}', '{{ $cat->categoryName }}')">
                                    <span class="material-symbols-outlined">edit</span>
                                    Edit
                                </a> --}}
                                    <button type="button" class="btn btn-danger btn-sm d-flex gap-1 align-items-center"
                                        onclick="handleDeleteClick('{{ $cat->categoryId }}', {{ $cat->packages()->count() }})">
                                        <span class="material-symbols-outlined">delete</span>
                                        Delete
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
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="close-modal-btn" onclick="closeModal()">×</button>
                </div>
                <div class="custom-modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
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
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Category</button>
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
                    <h5 class="modal-title text-danger">Confirm Delete</h5>
                    <button type="button" class="close-modal-btn" onclick="closeDeleteModal()">×</button>
                </div>
                <div class="custom-modal-body mt-0">
                    <p style="font-size: 14px;">Are you sure you want to delete this category?</p>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModalBackdrop" class="modal-backdrop hidden" onclick="closeDeleteModal()"></div>

    <!-- Cannot Delete Info Modal -->
    <div id="cannotDeleteModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="modal-title text-danger">Action Not Allowed</h5>
                <button type="button" class="close-modal-btn" onclick="closeCannotDeleteModal()">×</button>
            </div>
            <div class="custom-modal-body mt-0">
                <p style="font-size: 14px;">This category cannot be deleted because it still has associated packages.</p>
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
