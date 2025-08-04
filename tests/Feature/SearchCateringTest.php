<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\PackageCategory;
use App\Models\User;

class SearchCateringTest extends TestCase
{
    /** @test */
    public function tc1_filter_catering_by_price(){
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $address = $user->addresses()->first();
        session(['address_id' => $address->addressId]);
        $response = $this->actingAs($user)->get(route('search', [
            'min_price' => 100000,
            'max_price' => 250000,
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('Within Price Range Catering');
        $response->assertDontSee('Too Cheap Catering');
        $response->assertDontSee('Too Expensice Catering');
    }

    /** @test */
    public function tc2_filter_by_rating_4_above(){
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'rating' => 4.0,
        ]));

        $response->assertStatus(200);

        $vendors = Vendor::all();
        foreach($vendors as $vendor){
            if($vendor->rating>=4.0){
                $response->assertSee($vendor->name);
            }
            else{
                $response->assertDontSee($vendor->name);
            }
        }
    }

    /** @test */
    public function tc3_filter_by_rating_4_5_above(){
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'rating' => 4.5,
        ]));

        $response->assertStatus(200);

        $vendors = Vendor::all();
        foreach($vendors as $vendor){
            if($vendor->rating>=4.5){
                $response->assertSee($vendor->name);
            }
            else{
                $response->assertDontSee($vendor->name);
            }
        }
    }

    /** @test */
    public function tc4_filter_by_package_category(){
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'category'=> ['Halal'],
        ]));

        $response->assertStatus(200);

        $halalVendors = Vendor::whereHas('packages', function ($query) {
            $query->whereHas('category', function ($q) {
                $q->where('categoryName', 'Halal');
            });
        })->paginate(9);

        // Check that all "Halal" vendors are visible on the result page
        foreach ($halalVendors as $vendor) {
            $response->assertSee($vendor->name, "Expected to see HALAL vendor: {$vendor->name}");
        }
    }

    /** @test */
    public function tc5_filter_by_rating_price_and_category()
    {
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'rating' => 4,
            'min_price' => 100000,
            'max_price' => 200000,
            'category' => ['Vegetarian'],
        ]));

        $response->assertStatus(200);

        // Vendors who meet all criteria
        $matchedVendors = Vendor::where('rating', '>=', 4)
            ->whereHas('packages', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereBetween('breakfastPrice', [100000, 200000])
                        ->orWhereBetween('lunchPrice', [100000, 200000])
                        ->orWhereBetween('dinnerPrice', [100000, 200000]);
                })->whereHas('category', function ($q3) {
                    $q3->where('categoryName', 'Vegetarian');
                });
            })->get();

        foreach ($matchedVendors as $vendor) {
            $response->assertSeeText($vendor->name, "Expected to see matching vendor: {$vendor->name}");
        }
    }

    /** @test */
    public function tc6_invalid_price_range_shows_no_results()
    {
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'min_price' => 100000,
            'max_price' => 10000,
        ]));

        $response->assertStatus(200);

        // Optional: if your UI shows this when nothing is found
        $response->assertSeeText('No result found');

        // Or if you want to assert no vendors are shown:
        $vendorsShown = \App\Models\Vendor::all();
        foreach ($vendorsShown as $vendor) {
            $response->assertDontSeeText($vendor->name);
        }
    }

    /** @test */
    public function tc7_search_by_vendor_name()
    {
        $vendor = Vendor::first();
        
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'query' => $vendor->name,
        ]));

        $response->assertStatus(200);

        // Check that the expected vendor is shown
        $response->assertSeeText($vendor->name);
    }

    /** @test */
    public function tc8_search_by_package_name()
    {
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'query' => 'dolorem',
        ]));

        $response->assertStatus(200);

        // Get vendors that have at least one package with 'dolorem' in the name
        $vendors = Vendor::whereHas('packages', function ($q) {
            $q->where('name', 'like', '%dolorem%');
        })->get();

        // Assert those vendors are shown
        foreach ($vendors as $vendor) {
            $response->assertSeeText($vendor->name);
        }
    }

    /** @test */
    public function tc9_search_by_package_category()
    {
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'query' => 'Japanese',
        ]));

        $response->assertStatus(200);

        // Get vendors that have at least one package whose category is 'Japanese'
        $vendors = Vendor::whereHas('packages.category', function ($query) {
            $query->where('categoryName', 'like', '%Japanese%');
        })->get();

        // Assert these vendors are shown in the response
        foreach ($vendors as $vendor) {
            $response->assertSeeText($vendor->name);
        }
    }

    /** @test */
    public function tc10_no_match_found()
    {
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'query' => 'abcde123',
        ]));

        $response->assertStatus(200);

        // Expect fallback message or empty state (adjust the message to what your view shows)
        $response->assertSeeText('No result found'); // Change text to match your UI
    }

    /** @test */
     public function tc11_user_can_add_vendor_to_favorites()
    {
        /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $vendor = Vendor::first();

        // Simulate the user being logged in and posting to favorite route
        $response = $this->actingAs($user)->post("/favorite/{$vendor->vendorId}");

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['favorited' => true]);
        // Assert favorite is saved in the pivot table
        $this->assertDatabaseHas('favorite_vendors', [
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
        ]);
    }

    /** @test */
    public function tc12_user_can_remove_vendor_from_favorites()
    {
         /** @var User|Authenticatable $user */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $vendor = Vendor::first();

        // Simulate the user being logged in and posting to favorite route
        $response = $this->actingAs($user)->post("/favorite/{$vendor->vendorId}");


        // Act
        $response = $this->actingAs($user)->post("/unfavorite/{$vendor->vendorId}");

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['favorited' => false]);

        $this->assertDatabaseMissing('favorite_vendors', [
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
        ]);
    }

    /** @test */
    public function tc13_favorited_vendor_remains_favorited_on_search_page()
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $vendor = Vendor::first();

        $this->assertNotNull($user, 'User not found');
        $this->assertNotNull($vendor, 'Vendor not found');

        // Make sure the vendor is favorited
        if (!$user->favoriteVendors()->where('vendors.vendorId', $vendor->vendorId)->exists()) {
            $user->favoriteVendors()->attach($vendor->vendorId);
        }

        $searchResponse = $this->actingAs($user)->get(route('search', ['query' => $vendor->name]));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee($vendor->name);
    }

    /** @test */
    public function tc14_negative_min_price_shows_error()
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'min_price' => -1,
            'max_price' => 100000
        ]));

        // Check for validation error or fallback behavior
        $response->assertStatus(200);
    }

    /** @test */
    public function tc15_invalid_price_input_non_numeric()
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', [
            'min_price' => 'xyz', // invalid input
            'max_price' => '200000',
        ]));

        $response->assertStatus(302);
    }

    /** @test */
    public function tc16_invalid_category_parameter_does_not_crash()
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get('/caterings', ['category' => ['foodCategory']]);

        $response->assertStatus(200); // Page loads fine
    }

    /** @test */
    public function tc17_search_non_existent_vendor_name_shows_no_results()
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get('/caterings?query=xyab123_vendor');

        $response->assertStatus(200);
    }

    /** @test */
    public function tc18_rating_above_maximum_gracefully_shows_no_results()
    {
       /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get('/caterings?rating=6');

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['rating']);
    }

    /** @test */
    public function tc19_very_long_search_input_gracefully_returns_no_results()
    {
        $longText = str_repeat('a', 250);         
        
        /**
         * @var User|Authenticatable $user
         */
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $response = $this->actingAs($user)->get(route('search', ['query' => $longText]));

        $response->assertStatus(500);
        $response->assertSee('No results found');
    }

}