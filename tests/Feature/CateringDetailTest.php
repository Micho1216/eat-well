<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Models\Vendor;



class CateringDetailTest extends TestCase
{
    /** @test */
    public function tc1_catering_detail_displays_vendor_info()
    {
        $vendor = Vendor::first(); // or use where() to get a specific vendor
        
        $this->assertNotNull($vendor, 'No vendor found in the database.');
        
        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        $response->assertStatus(200);
        $response->assertSee($vendor->name);
        $response->assertSee($vendor->phone_number);
        $response->assertSee((string)$vendor->rating);
        $response->assertSee($vendor->jalan);
        $response->assertSee($vendor->kelurahan);
        $response->assertSee($vendor->kecamatan);
        $response->assertSee($vendor->provinsi);
        $response->assertSee($vendor->kode_pos);
    }

    /** @test */
    public function tc2_package_total_nominal_is_displayed()
    {
        $vendor = Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            // Adjust 'total_price' to your actual field name for the package total
            $response->assertSee((string)$package->total_price);
        }
    }

    /** @test */
    public function tc3_add_button_is_visible_for_each_package()
    {
        $vendor = Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');
        
        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            // Adjust the button text or selector as needed
            $response->assertSee('Add');
        }
    }

    /** @test */
    public function tc4_displays_vendor_packages()
    {
        $vendor = Vendor::with('packages.category')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");;

        foreach ($vendor->packages as $package) {
            $response->assertSee($package->name);
            $response->assertSee($package->category->categoryName ?? 'N/A');
            // Check for prices (adjust field names as needed)
            if (!is_null($package->breakfastPrice)) {
                $response->assertSee((string)$package->breakfastPrice);
            }
            if (!is_null($package->lunchPrice)) {
                $response->assertSee((string)$package->lunchPrice);
            }
            if (!is_null($package->dinnerPrice)) {
                $response->assertSee((string)$package->dinnerPrice);
            }
        }
    }
    
    /** @test */
    public function tc5_displays_package_images_and_menu_pdf()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            // Check for image (either custom or default)
            if ($package->imgPath) {
                $response->assertSee(htmlspecialchars($package->imgPath));
            } else {
                $response->assertSee('asset/catering-detail/logo-packages.png');
            }
            // Check for menu PDF download icon (by data-pdf attribute)
            if ($package->menuPDFPath) {
                $response->assertSee('data-pdf="' . asset('asset/menus/'.$package->menuPDFPath) . '"', false);
            }
        }
    }

    /** @test */
    public function tc6_displays_meal_prices_for_each_package()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            if (!is_null($package->breakfastPrice)) {
                $response->assertSee((string)$package->breakfastPrice);
            }
            if (!is_null($package->lunchPrice)) {
                $response->assertSee((string)$package->lunchPrice);
            }
            if (!is_null($package->dinnerPrice)) {
                $response->assertSee((string)$package->dinnerPrice);
            }
        }
    }

    /** @test */
    public function tc7_displays_shipping_times_for_available_meals()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            if (!is_null($package->breakfastShippingTime)) {
                $response->assertSee((string)$package->breakfastShippingTime);
            }
            if (!is_null($package->lunchShippingTime)) {
                $response->assertSee((string)$package->lunchShippingTime);
            }
            if (!is_null($package->dinnerShippingTime)) {
                $response->assertSee((string)$package->dinnerShippingTime);
            }
        }
    }

    /** @test */
    public function tc8_handles_vendor_not_found()
    {
        // Use a vendorId that does not exist (e.g., 999999)
        $invalidVendorId = 999999;

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$invalidVendorId}");

        $response->assertStatus(404);
    }

    /** @test */
    public function tc9_displays_order_now_button()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        $response->assertSee('Order Now');
    }

    /** @test */
    public function tc10_displays_carousel_with_food_preview_images()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        // Check for carousel container (adjust selector/class as needed)
        $response->assertSee('carousel', false);

        // Optionally, check for at least one food image in the carousel
        foreach ($vendor->packages as $package) {
            if ($package->imgPath) {
                $response->assertSee(htmlspecialchars($package->imgPath));
            }
        }
    }

    /** @test */
    public function tc11_handles_missing_package_images_gracefully()
    {
        $vendor = Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            if (empty($package->imgPath)) {
                // Check for the default image path in the HTML
                $response->assertSee('asset/catering-detail/logo-packages.png');
            }
        }
    }

    /** @test */
    public function tc12_rate_and_review_button_links_correctly()
    {
        $vendor = Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        // Check that the "Rate and Review" button links to the correct route
        $rateReviewUrl = route('rate-and-review', $vendor->vendorId);
        $response->assertSee('href="' . $rateReviewUrl . '"', false);
    }

    /** @test */
    public function tc13_dropdown_for_package_selection_lists_all_packages()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            // Check that each package name is present in the dropdown menu
            $response->assertSee($package->name);
        }
    }

    /** @test */
    public function tc14_modal_pdf_viewer_opens_on_download_icon()
    {
        $vendor = \App\Models\Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");;

        foreach ($vendor->packages as $package) {
            if ($package->menuPDFPath) {
                // Check that the download icon with the correct data-pdf attribute is present
                $response->assertSee('data-pdf="' . asset('asset/menus/' . $package->menuPDFPath) . '"', false);
            }
        }
    }

    /** @test */
    public function tc15_vendor_logo_displayed_or_default()
    {
        $vendor = Vendor::first();
        $this->assertNotNull($vendor, 'No vendor found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        $response->assertSee(htmlspecialchars($vendor->logoPath));
        
    }

    /** @test */
    public function tc16_package_meal_prices_are_displayed_correctly()
    {
        $vendor = Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            if (!is_null($package->breakfastPrice)) {
                $response->assertSeeText(number_format($package->breakfastPrice, 0, ',', '.'));
            }
            if (!is_null($package->lunchPrice)) {
                $response->assertSeeText(number_format($package->lunchPrice, 0, ',', '.'));
            }
            if (!is_null($package->dinnerPrice)) {
                $response->assertSeeText(number_format($package->dinnerPrice, 0, ',', '.'));
            }
        }
    }

    /** @test */
    public function tc16_meal_checklist_buttons_are_rendered_for_each_package()
    {
        $vendor = Vendor::with('packages')->has('packages')->first();
        $this->assertNotNull($vendor, 'No vendor with packages found in the database.');

        /**
        * @var User|Authenticatable $user
        */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        foreach ($vendor->packages as $package) {
            // Check for breakfast controls
            if (!is_null($package->breakfastPrice)) {
                $response->assertSee('Breakfast');
                $response->assertSee('class="qty-control"', false);
                $response->assertSee('class="decrement"', false);
                $response->assertSee('class="increment"', false);
            }
            // Check for lunch controls
            if (!is_null($package->lunchPrice)) {
                $response->assertSee('Lunch');
                $response->assertSee('class="qty-control"', false);
                $response->assertSee('class="decrement"', false);
                $response->assertSee('class="increment"', false);
            }
            // Check for dinner controls
            if (!is_null($package->dinnerPrice)) {
                $response->assertSee('Dinner');
                $response->assertSee('class="qty-control"', false);
                $response->assertSee('class="decrement"', false);
                $response->assertSee('class="increment"', false);
            }
        }
    }
}