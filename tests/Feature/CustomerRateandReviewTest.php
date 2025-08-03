<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Order;
use App\Models\PackageCategory;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorReview;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class CustomerRateandReviewTest extends TestCase
{
   use RefreshDatabase;

    protected $user;
    protected $vendor;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        PackageCategory::create(['categoryName' => 'Default Category']);

        // Create related address data
        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        // Create user
        $this->user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 'Customer',
        ]);

        // Create default address for the user
        Address::factory()->create([
            'userId' => $this->user->userId,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081212345678',
            'jalan' => 'Jl. Contoh No. 10',
            'kode_pos' => '40123',
            'notes' => 'Dekat toko',
            'is_default' => true,
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        // Create vendor
        $this->vendor = Vendor::factory()->create([
            'name' => 'Nusantara Delights',
        ]);

        // Create past order
        $this->order = Order::factory()->create([
            'vendorId' => $this->vendor->vendorId,
            'userId' => $this->user->userId,
            'endDate' => now()->subDay(),
        ]);
    }

    /** @test */
    public function tc_1_Verify_the_Rating_n_Review_page_successfully_loads_and_displays_core_vendor_information()
    {
        $this->actingAs($this->user);

        $reviewText = 'Excellent catering and friendly service.';
        VendorReview::factory()->create([
            'vendorId' => $this->vendor->vendorId,
            'userId' => $this->user->userId,
            'orderId' => $this->order->orderId,
            'review' => $reviewText,
            'rating' => 4.8,
        ]);

        $response = $this->get('/catering-detail/' . $this->vendor->vendorId . '/rating-and-review');

        $response->assertStatus(200);
        $response->assertSeeText($this->vendor->name);
        $response->assertSeeText($reviewText);
        $response->assertSeeText((string) Order::where('vendorId', $this->vendor->vendorId)->count());
    }



    /** @test */
    public function tc_2_test_back_button_link_navigates_to_the_correct_page(): void
    {
        $user = User::where('role', 'Customer')->first();
        $vendor = Vendor::all()->first();

        $expectedBackUrl = route('catering-detail', $vendor->vendorId);

        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        $response->assertStatus(200);

        $response->assertSee("href=\"{$expectedBackUrl}\"", false);
    }

    /** @test */
    public function tc_3_user_profile_pictures_are_displayed_correctly(): void
    {
        $vendor = Vendor::factory()->create([
            'name' => 'Delicious Bites Catering',
            'phone_number' => '0897765443321',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Sudirman No. 1',
            'logo' => 'vendor_logo.jpg',
        ]);

        /** 
         * @var User|Authenticatable $userWithCustomProfile
         * */
        $userWithCustomProfile = User::factory()->create([
            'profilePath' => 'images/profiles/joko_susilo.jpg',
            'role' => 'Customer',
        ]);
        $this->actingAs($userWithCustomProfile);
        Address::create([
            'userId' => $userWithCustomProfile->userId,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'jalan' => 'Jl. Sudirman No. 1',
            'kode_pos' => '12920',
            'recipient_name' => 'Joko Susilo',
            'recipient_phone' => '08123456789',
        ]);
        $finishedOrder1 = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithCustomProfile->userId,
            'endDate' => now()->subDay(), 
        ]);

        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithCustomProfile->userId,
            'orderId' => $finishedOrder1->orderId,
            'rating' => 4.0,
        ]);


        $userWithDefaultProfile = User::factory()->create([
            'profilePath' => 'images/default-avatar.png',
        ]);

        $finishedOrder2 = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithDefaultProfile->userId,
            'endDate' => now()->subDay(),
        ]);

        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithDefaultProfile->userId,
            'orderId' => $finishedOrder2->orderId,
            'rating'=>3.0,
        ]);

        /** 
         * @var User|Authenticatable $userWithCustomProfile 
         * */
        $response = $this->actingAs($userWithCustomProfile)->get(route('rate-and-review', $vendor));
        $response = $this->actingAs($userWithCustomProfile)->get(route('rate-and-review', $vendor));

        $response->assertStatus(200);
        $response->assertSee('images/profiles/joko_susilo.jpg');
        $response->assertSee('images/default-avatar.png');
    }

    /** @test */
    public function tc_4_test_usernames_are_displayed_in_a_masked_format(): void
    {
        $vendor = Vendor::factory()->create([
            'name' => 'Delicious ring',
            'phone_number' => '0897765443321',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Sudirman No. 1',
            'logo' => 'vendor_logo.jpg',
        ]);

        /** @var User|Authenticatable $userWithKnownName */
        $userWithKnownName = User::factory()->create([
            'name' => 'johndoe',
            'role' => 'Customer',
        ]);

        Address::create([
            'userId' => $userWithKnownName->userId,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'jalan' => 'Jl. Sudirman No. 1',
            'kode_pos' => '12920',
            'recipient_name' => 'John Doe',
            'recipient_phone' => '08123456789',
        ]);

        $order = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithKnownName->userId,
            'endDate' => now()->subDay(),
        ]);

        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithKnownName->userId,
            'orderId' => $order->orderId,
            'rating' => 4.5,
        ]);

        $response = $this->actingAs($userWithKnownName)->get(route('rate-and-review', $vendor));
        $response->assertStatus(200);

        $response->assertSee('j*');

        $response->assertDontSee('johndoe');
    }


    /** @test */
    public function tc_5_test_review_rating_is_accurately_displayed(): void
    {
        $vendor = Vendor::factory()->create([
            'name' => 'Yummy Bites',
            'phone_number' => '08123456789',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Makan Enak No. 12',
            'logo' => 'vendor_logo.jpg',
        ]);

        /** @var User|Authenticatable $user */
        $user = User::factory()->create([
            'role' => 'Customer',
        ]);
        Address::create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'jalan' => 'Jl. Sudirman No. 1',
            'kode_pos' => '12920',
            'recipient_name' => 'John Doe',
            'recipient_phone' => '08123456789',
        ]);
        $order = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'endDate' => now()->subDay(),
        ]);

        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order->orderId,
            'rating' => 3.5,
        ]);

        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        $response->assertStatus(200);
        $response->assertSee('3.5');
    }

    /** @test */
    public function tc_6_test_review_text_is_displayed_correctly_for_written_and_empty_reviews(): void
    {
        /** @var User|Authenticatable $user */
        $user = User::factory()->create([
            'role' => 'Customer',
        ]);
        Address::create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'jalan' => 'Jl. Sudirman No. 1',
            'kode_pos' => '12920',
            'recipient_name' => 'John',
            'recipient_phone' => '08123456789',
        ]);
        $vendor = Vendor::factory()->create([
            'name' => 'Yummy Bites',
            'phone_number' => '08123456789',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Makan Enak No. 12',
            'logo' => 'vendor_logo.jpg',
        ]);

        // Skenario A: Ulasan dengan teks
        $reviewWithText = 'Makanannya sangat lezat dan direkomendasikan.';
        $order1 = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'endDate' => now()->subDay(),
        ]);
        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order1->orderId,
            'rating' => 4.3,
            'review' => $reviewWithText,
        ]);

        // Skenario B: Ulasan tanpa teks (kosong/null)
        $order2 = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'endDate' => now()->subDay(),
        ]);
        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order2->orderId,
            'rating' => 4.5,
            'review' => null,
        ]);

        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        $response->assertStatus(200);
        $response->assertSee($reviewWithText);
    }

    /** @test */
    public function tc_6_test_it_shows_a_message_when_there_are_no_reviews(): void
    {
        $user = User::factory()->create([
            'role' => 'Customer',
        ]);
        Address::create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'jalan' => 'Jl. Sudirman No. 1',
            'kode_pos' => '12920',
            'recipient_name' => 'John',
            'recipient_phone' => '08123456789',
        ]);
        $vendor = Vendor::factory()->create([
            'name' => 'Yummy Time',
            'phone_number' => '08123456789',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Makan Enak No. 12',
            'logo' => 'vendor_logo.jpg',
        ]);

        /** @var User|Authenticatable $user */
        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        $response->assertStatus(200);
        if (app()->getLocale() === 'en') {
            $response->assertSee('No reviews for this vendor yet.');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSee('Belum ada ulasan untuk vendor ini.');
        }
    }

    /** @test */
    public function tc_7_test_order_date_is_displayed_and_formatted_correctly(): void
    {
        /** @var User|Authenticatable $user */
        $user = User::factory()->create([
            'role' => 'Customer',
        ]);
        Address::create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'jalan' => 'Jl. Sudirman No. 1',
            'kode_pos' => '12920',
            'recipient_name' => 'John',
            'recipient_phone' => '08123456789',
        ]);
        $vendor = Vendor::factory()->create([
            'name' => 'Yummy Yummy',
            'phone_number' => '08123456789',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Makan Enak No. 12',
            'logo' => 'vendor_logo.jpg',
        ]);


        $order = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'endDate' => now()->subDay(),
        ]);

        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order->orderId,
            'rating' => 3.8,
        ]);

        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        $response->assertStatus(200);
        if (app()->getLocale() === 'en') {
            $expectedDateString = "Ordered on " . $order->created_at->translatedFormat('jS F Y');
        } elseif (app()->getLocale() === 'id') {
            $expectedDateString = "Dipesan pada " . $order->created_at->translatedFormat('j F Y');
        }
        
        $response->assertSeeText($expectedDateString);
    }
}
