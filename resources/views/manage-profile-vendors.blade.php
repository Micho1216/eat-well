@extends('components.vendor-nav')

@section('title')
    {{ __('manage-profile-vendor.title') }}
@endsection

@section('css')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/manageProfilevendor.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
@endsection

@section('content')
    <div class="container profile-edit-container">
        <div class="form-wrapper">

            <!-- Back Button -->
            <div class="row">
                <div class="col-2">
                    <a href="{{ route('manage-profile-vendor-account') }}"
                        style="text-decoration: none;" >
                        <img src="{{ asset('asset/backbutton.png') }}" alt="">
                    </a>
                </div>
                <div class="col-8">
                    <h2 class="lexend font-medium text-center mb-4">
                        {{ __('manage-profile-vendor.manage_profile') }}
                    </h2>
                </div>
            </div>


            <div class="form-inner-box">
                <form action="{{ route('manage-profile-vendor.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="text-center mb-4">
                        @if ($vendor->logo)
                            <img src="{{ asset('asset/vendorLogo/' . $vendor->logo) }}" alt="Profile Picture adas"
                                class="profile-picture" id="profilePicPreview">
                        @else
                            <img src="{{ asset('asset/profile/no-profile.png') }}" alt="Profile Picture"
                                class="profile-picture" id="profilePicPreview">
                        @endif

                        <div>
                            <label for="profilePicInput" class="btn btn-secondary mt-2">
                                {{ __('manage-profile-vendor.edit') }}
                            </label>
                            <input type="file" id="profilePicInput" name="profilePicInput" accept="image/*"
                                style="display: none;">
                        </div>
                        @error('profilePicInput')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror

                    </div>

                    <div class="form-group">
                        <label>{{ __('manage-profile-vendor.email') }}</label>
                        <input type="text" class="form-control" value="{{ $user->email }}" disabled>

                         <label>{{ __('manage-profile.address') }}</label>
                        <input type="text" class="form-control" value="{{ $address }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="nameInput">{{ __('manage-profile-vendor.name') }}</label>
                        <input type="text" name="nameInput" id="nameInput"
                            value="{{ old('nameInput', $vendor->name) }}">

                        @error('nameInput')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="telpInput">{{ __('manage-profile-vendor.telephone') }}</label>
                        <input type="text" name="telpInput" id="telpInput"
                            value="{{ old('telpInput', $vendor->phone_number) }}">
                    </div>
                    @error('telpInput')
                        <p style="color: red">{{ $message }}</p>
                    @enderror

                    <div class="form-group">
                        <label for="breakfast_time_start">{{ __('manage-profile-vendor.breakfast_delivery') }}</label>
                        <div class="d-flex gap-2">
                            <input type="time" name="breakfast_time_start" id="breakfast_time_start"
                                value="{{ old('breakfast_time_start', $breakfast_start ?? '') }}">
                            <span class="mt-3">-</span>
                            <input type="time" name="breakfast_time_end" id="breakfast_time_end"
                                value="{{ old('breakfast_time_end', $breakfast_end ?? '') }}">
                        </div>
                    </div>
                    @error('breakfast_time_start')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    @error('breakfast_time_end')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                    <div class="form-group">
                        <label for="lunch_time_start">{{ __('manage-profile-vendor.lunch_delivery') }}</label>
                        <div class="d-flex gap-2">
                            <input type="time" name="lunch_time_start" id="lunch_time_start"
                                value="{{ old('lunch_time_start', $lunch_start ?? '') }}">
                            <span class="mt-3">-</span>
                            <input type="time" name="lunch_time_end" id="lunch_time_end"
                                value="{{ old('lunch_time_end', $lunch_end ?? '') }}">

                        </div>
                    </div>
                    @error('lunch_time_start')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    @error('lunch_time_end')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                    <div class="form-group">
                        <label for="dinner_time_start">{{ __('manage-profile-vendor.dinner_delivery') }}</label>
                        <div class="d-flex gap-2">
                            <input type="time" name="dinner_time_start" id="dinner_time_start"
                                value="{{ old('dinner_time_start', $dinner_start ?? '') }}">
                            <span class="mt-3">-</span>
                            <input type="time" name="dinner_time_end" id="dinner_time_end"
                                value="{{ old('dinner_time_end', $dinner_end ?? '') }}">

                        </div>
                    </div>
                    @error('dinner_time_start')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    @error('dinner_time_end')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                    <div class="text-center">
                        <button type="submit" class="submit-btn">{{ __('manage-profile-vendor.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const inputFile = document.getElementById('profilePicInput');
        const preview = document.getElementById('profilePicPreview');

        inputFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection
