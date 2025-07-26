@extends('components.vendor-nav')

@section('title', 'EatWell | Vendor Orders')

@section('css')
    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/ratingAndReviewVendor.css') }}">
    {{-- bootstrap --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@1" rel="stylesheet" />
@endsection

@section('content')
    <div class="container all-container">
        <div class="title-and-num-sold">
            <div class="back-and-cate-name-wrapper">
                <a href="{{ route('catering-detail', $vendor) }}">
                    <span class="material-symbols-outlined back-icon">arrow_circle_left</span>
                </a>
                <h1 class="lexend judul-gede">{{ $vendor->name }}</h1>
            </div>
            <h5 class="num-sold inter m-0">{{ $numSold }} {{ __('review.sold') }}</h5>
        </div>

        <div class="review-card-container">
            @forelse ($vendorReviews as $review)
                <div class="review-card-wrapper">
                    <div class="profile-rating-wrapper">
                        <div class="profile-wrapper">
                            <div class="profile">
                                <img src="{{ asset($review->user->profilePath) }}" alt="User Profile" class="profile-user">
                            </div>
                            <h5 class="username inter">
                                {{ substr($review->user->name, 0, 1) . str_repeat('*', strlen($review->user->name) - 1) }}
                            </h5>
                        </div>
                        <div class="rating-wrapper">
                            <span class="material-symbols-outlined star-icon">star</span>
                            <span class="inter rating">{{ $review->rating }}</span>
                        </div>
                    </div>
                    @if ($review->review)
                        <div class="review-wrapper inter">
                            <p>
                                {{ $review->review }}
                            </p>
                        </div>
                    @endif
                    <div class="order-label">
                        <p class="inter text-white">{{ __('review.ordered_on') }}
                            {{ \Carbon\Carbon::parse($review->order->created_at)->format('jS M Y') }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center p-4">
                    <p class="inter text-white">{{ __('review.no_reviews') }}</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
