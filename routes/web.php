<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AccountSetup\CustomerFirstPageController;
use App\Http\Controllers\AddPasswordController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminViewOrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SearchCateringController;
use App\Http\Controllers\VendorController;
use App\Http\Middleware\NoCateringDataMiddleware;
use App\Models\Order;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CustomerRatingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeliveryStatusController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ManageTwoFactorController;
use App\Http\Controllers\OrderVendorController;
use App\Http\Controllers\ProvinceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Socialite\ProviderCallbackController;
use App\Http\Controllers\Socialite\ProviderRedirectController;
use App\Http\Controllers\VendorPreviewController;
use App\Http\Middleware\EnsureVendor;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\VillageController;
use App\Http\Middleware\CheckNoPasswordExist;
use App\Models\User;
use App\Http\Controllers\VerifyOtpController;
use App\Http\Controllers\illageController;
use App\Http\Middleware\AccountSetup\EnsureAddressExists;
use App\Http\Middleware\AccountSetup\EnsureNoAddressExist;
use App\Http\Middleware\CheckAuthenticatedUserPasswordResetToken;
use App\Http\Middleware\CheckPasswordResetToken;
use App\Http\Middleware\EnsureNoPasswordExist;
use App\Http\Middleware\EnsurePasswordExists;
use App\Notifications\OneTimePassword;

/* --------------------
GUEST ROUTES
-------------------- */
Route::post('/lang', LanguageController::class);
Route::post('/forgot-password', [ForgotPasswordController::class, 'email'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'reset'])->middleware([
    CheckAuthenticatedUserPasswordResetToken::class,
    CheckPasswordResetToken::class
])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'update'])->middleware([
    CheckAuthenticatedUserPasswordResetToken::class,
    CheckPasswordResetToken::class
])->name('password.update');
Route::get('/invalid-reset-password', [ForgotPasswordController::class, 'invalid'])->name('password.invalid');

Route::middleware(['guest'])->group(function () {
    Route::get('/', LandingPageController::class)->name('landingPage');

    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store']);

    Route::get('/register/{role}', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register/{role}', [RegisteredUserController::class, 'store']);

    Route::get('/auth/{provider}/redirect/{role?}', ProviderRedirectController::class)->name('auth.redirect');
    Route::get('/auth/{provider}/callback/', ProviderCallbackController::class)->name('auth.callback');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'request'])->name('password.request');

    Route::fallback(function () {
        return redirect()->route('landingPage');
    });
});

Route::get('/about-us', AboutUsController::class);


/* --------------------
 NORMAL USERS ROUTES
---------------------*/

Route::middleware(['auth'])->group(function () {
    Route::post('api/fetch-provinces', [ProvinceController::class, 'fetchProvinces'])->name('api-provinces');
    Route::post('api/fetch-cities', [CityController::class, 'fetchCities'])->name('api-cities');
    Route::post('api/fetch-districts', [DistrictController::class, 'fetchDistricts'])->name('api-districts');
    Route::post('api/fetch-villages', [VillageController::class, 'fetchVillages'])->name('api-villages');

    Route::post('/manage-profile', [SessionController::class, 'destroy'])->name('logout');
    Route::post('/manage-two-factor', [ManageTwoFactorController::class, 'index'])->name('manage-two-factor');

    Route::get('/verify-otp', [VerifyOtpController::class, 'create'])->name('auth.verify');
    Route::post('/verify-otp', [VerifyOtpController::class, 'check'])->name('auth.check');
    Route::post('/resend-otp', [VerifyOtpController::class, 'resendOtp'])->name('auth.resend-otp');
});
/* ---------------------
    CUSTOMER ROUTES
---------------------- */
// Customer Account Setup

Route::middleware(['role:customer', 'ensureAddress', 'ensureUserVerifiedOtp'])->group(function () {
    Route::get('/customer-first-page', [CustomerFirstPageController::class, 'index'])->middleware(EnsureNoAddressExist::class)
        ->withoutMiddleware(['ensureAddress'])
        ->name('account-setup.customer-view');
    Route::post('/customer-first-page', [CustomerFirstPageController::class, 'store'])->middleware(EnsureNoAddressExist::class)
        ->withoutMiddleware(['ensureAddress'])
        ->name('account-setup.customer-store');

    Route::get('/manage-profile', [UserController::class, 'showProfile'])->name('manage-profile');
    Route::patch('/manage-profile', [UserController::class, 'updateProfile'])->name('manage-profile.update');

    // Customer Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/topup', [UserController::class, 'topUpWellPay'])->middleware(EnsurePasswordExists::class)->name('wellpay.topup');

    // Favorite
    Route::post('favorite/{vendorId}', [FavoriteController::class, 'favorite'])->name('favorite');
    Route::post('unfavorite/{vendorId}', [FavoriteController::class, 'unfavorite'])->name('unfavorite');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorite.show');

    // Search Caterings
    Route::get('/caterings', [SearchCateringController::class, 'search'])->name('search');
    Route::post('/set-address', [SearchCateringController::class, 'setAddress'])->name('set.address');

    // Catering Details
    Route::get('/catering-detail/{vendor}/rating-and-review', [VendorController::class, 'review'])->name('rate-and-review');

    Route::get('/catering-detail/{vendor}', [VendorController::class, 'show'])->name('catering-detail');
    Route::post('/update-order-summary', [CartController::class, 'updateOrderSummary'])->name('update.order.summary');
    Route::get('/load-cart', [CartController::class, 'loadCart'])->name('load.cart');

    // Order History
    Route::get('/orders', [OrderController::class, 'index'])->name('order-history');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('order-detail');
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/orders/{order}/review', [CustomerRatingController::class, 'store']);

    Route::get('/payment', [OrderController::class, 'showPaymentPage'])->name('payment.show');
    Route::post('/checkout', [OrderController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/user/wellpay-balance', [OrderController::class, 'getUserWellpayBalance'])->name('user.wellpay.balance');

    Route::get('/manage-address', [AddressController::class, 'index'])->name('manage-address');
    Route::post('/set-default-address', [AddressController::class, 'setDefaultAddress'])->name('set-default-address');
    Route::get('/add-address', [AddressController::class, 'create'])->name('add-address');
    Route::post('/add-address', [AddressController::class, 'store'])->name('store-address');

    Route::get('/edit-address/{address}', [AddressController::class, 'edit'])->name('edit-address');
    Route::patch('/edit-address/{address}', [AddressController::class, 'update'])->name('update-address');

    Route::delete('/delete-address/{address}', [AddressController::class, 'destroy'])->name('delete-address');

    Route::fallback(function () {
        return redirect()->route('home');
    });
});

/* ---------------------
     VENDOR ROUTES
---------------------- */
Route::middleware(['role:vendor', 'ensureUserVerifiedOtp'])->group(function () {
    Route::middleware(NoCateringDataMiddleware::class)->group(function () {
        Route::get('/vendor-first-page', function () {
            return view('vendorFirstPage');
        })->name('vendor.first.page');
        Route::post('/new-vendor', [VendorController::class, 'store'])->name('vendor.store');
    });

    Route::middleware(EnsureVendor::class)->group(function () {
        // Catering dashboard
        Route::get('/cateringHomePage', [OrderVendorController::class, 'totalOrder'])->name('vendor.home');
        Route::get('/catering-detail', [VendorController::class, 'reviewVendor'])->name('vendor.review');

        // Manage Packages
        Route::get('/manageCateringPackage', [PackageController::class, 'index'])->name('manageCateringPackage');
        Route::post('/manageCateringPackage', [PackageController::class, 'store'])->name('packages.store');
        Route::put('/packages/{id}', [PackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('/packages/import', [PackageController::class, 'import'])->name('packages.import');

        // Manage Order
        Route::get('/manageOrder', [OrderVendorController::class, 'index'])
            ->name('orders.index');

        Route::post(
            '/delivery-status/{orderId}/{slot}',
            [OrderVendorController::class, 'updateStatus']
        )->name('delivery-status.update');

        Route::post(
            '/orders/{order}/cancel',
            [OrderVendorController::class, 'cancel']
        )->name('orders.cancel');

        Route::get('/manage-profile-vendor', [VendorController::class, 'manageProfile'])->name('manage-profile-vendor');
        Route::patch('/manage-profile-vendor', [VendorController::class, 'updateProfile'])->name('manage-profile-vendor.update');


        Route::get('/vendor-previews', [VendorPreviewController::class, 'index']);

        Route::delete('/vendor-previews/{id}', [VendorPreviewController::class, 'destroy']);

        Route::post('/vendor-previews/upload', [VendorPreviewController::class, 'upload']);
        Route::put('/vendor-previews/{id}', [VendorPreviewController::class, 'update']);

        Route::get('/vendor-manage', [VendorPreviewController::class, 'showVendorDetail']);

        Route::get('/manage-profile-vendor-account', [VendorController::class, 'manage_profile'])->name('manage-profile-vendor-account');
        Route::patch('/manage-profile-vendor-account', [VendorController::class, 'updateProfileUser'])->name('manage-profile-vendor-account.updateUser');
        
        // Catering Sales
        Route::get('/vendor/sales', [SalesController::class, 'index'])->name('sales.show');
        Route::get('/vendor/sales/export', [SalesController::class, 'export_sales'])->name('sales.export');
        
        Route::fallback(function () {
            return redirect()->route('cateringHomePage');
        });
    });


});
/* ---------------------
     ADMIN ROUTES
---------------------- */
Route::middleware(['role:admin', 'ensureUserVerifiedOtp'])->group(function () {
    Route::get('/view-all-vendors', [AdminController::class, 'viewAllVendors'])->name('view-all-vendors');
    Route::post('/view-all-vendors', [AdminController::class, 'search'])->name('view-all-vendors.search');

    Route::get('/admin-dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');

    Route::get('/view-all-transactions', [AdminController::class, 'view_all_transactions'])->name('view-all-transactions');

    Route::get('/view-all-users', [AdminController::class, 'view_all_users'])->name('view-all-users');


    Route::get('/view-all-logs', [AdminController::class, 'view_all_logs'])
        ->name('view-all-logs');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.show');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('category.delete');

    Route::get('/view-order-history', [AdminViewOrderController::class, 'index'])
        ->name('view-order-history');

    Route::get('/admin/orders/export', [AdminViewOrderController::class, 'export'])->name('admin.order.export');


    Route::fallback(function () {
        return redirect()->route('admin-dashboard');
    });
});
