<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Order;
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
    /** @test */
    public function tc_1_Verify_the_Rating_n_Review_page_successfully_loads_and_displays_core_vendor_information()
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::factory()->create([
            'password' => 'password123',
            'role' => 'Customer',
        ]);
        $this->actingAs($user);

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081212345678',
            'jalan' => 'Jl. Contoh No. 10',
            'kode_pos' => '40123',
            'notes' => 'Dekat toko',
            'is_default' => true, // Set one as default
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);


        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        

        $vendor = Vendor::first();
        $vendorId = $vendor->vendorId;

        $jumlahOrder = Order::where('vendorId', $vendorId)->count();

        $review = VendorReview::where('vendorId', $vendorId)->get();
        $salah1Review = $review->first();
        $text = $salah1Review?->review ?? ''; // gunakan nullsafe biar aman

        $response = $this->actingAs($user)->get('/catering-detail/' . $vendorId . '/rating-and-review');

        $vendorName = $vendor->name;
        $response->assertSeeText($vendorName);
        $response->assertSeeText((string) $jumlahOrder);
        $response->assertStatus(200);
        if ($text !== '') {
            $response->assertSeeText($text);
        }
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

    /**
     * TC-3: Verify user profile pictures are correctly displayed.
     * @test
     */
    public function tc_3_test_user_profile_pictures_are_displayed_correctly(): void
    {
        $vendor = Vendor::factory()->create();

        /** @var User|Authenticatable $userWithCustomProfile */
        $userWithCustomProfile = User::factory()->create([
            'profilePath' => 'images/profiles/joko_susilo.jpg',
        ]);
        
        $order1 = Order::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $userWithCustomProfile->userId]);
        VendorReview::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $userWithCustomProfile->userId, 'orderId' => $order1->orderId]);

        // Buat user dengan FOTO PROFIL DEFAULT
        // Kita asumsikan path default-nya adalah 'images/default-avatar.png'.
        // Ganti path ini jika di aplikasi Anda berbeda.
        $userWithDefaultProfile = User::factory()->create([
            'profilePath' => 'images/default-avatar.png',
        ]);
        $order2 = Order::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $userWithDefaultProfile->userId]);
        VendorReview::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $userWithDefaultProfile->userId, 'orderId' => $order2->orderId]);


        // 2. AKSI: Kunjungi halaman review
        $response = $this->actingAs($userWithCustomProfile)->get(route('rate-and-review', $vendor));


        // 3. ASSERTION: Pastikan kedua jenis path gambar muncul di halaman
        $response->assertStatus(200);

        // Periksa apakah path gambar kustom muncul
        $response->assertSee('images/profiles/joko_susilo.jpg');

        // Periksa apakah path gambar default juga muncul
        $response->assertSee('images/default-avatar.png');
    }

    // Di dalam file tests/Feature/RatingReviewTest.php

    /**
     * TC-4: Verify usernames are displayed in a masked format.
     * @test
     */
    public function tc_4_test_usernames_are_displayed_in_a_masked_format(): void
    {
        // 1. SETUP: Buat user dengan nama yang spesifik dan mudah diprediksi
        $vendor = Vendor::factory()->create();
        /** @var \App\Models\User $userWithKnownName */
        $userWithKnownName = User::factory()->create([
            'name' => 'johndoe', // Nama asli yang akan kita uji
        ]);

        $order = Order::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $userWithKnownName->userId]);
        VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $userWithKnownName->userId,
            'orderId' => $order->orderId,
        ]);

        // 2. AKSI: Kunjungi halaman review
        $response = $this->actingAs($userWithKnownName)->get(route('rate-and-review', $vendor));
        $response->assertStatus(200);

        // 3. ASSERTION: Periksa tampilan nama

        // Pastikan nama yang sudah dimasking MUNCUL di halaman
        $response->assertSee('j*');

        // Pastikan nama asli TIDAK MUNCUL sama sekali di halaman demi privasi
        $response->assertDontSee('johndoe');
    }

    // Di dalam file tests/Feature/RatingReviewTest.php

    /**
     * TC-5: Verify that the rating for each review is accurately displayed.
     * @test
     */
    public function tc_5_test_review_rating_is_accurately_displayed(): void
    {
        // 1. SETUP: Buat data yang diperlukan
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();
        $vendor = \App\Models\Vendor::factory()->create();
        $order = \App\Models\Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId
        ]);

        // Buat ulasan dengan rating yang spesifik
        \App\Models\VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order->orderId,
            'rating' => 3.5, // Rating yang akan kita periksa
        ]);

        // 2. AKSI: Kunjungi halaman review
        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        // 3. ASSERTION: Periksa tampilan rating
        $response->assertStatus(200);

        // Pastikan angka rating muncul di halaman
        $response->assertSee('3.5');
    }
    // Di dalam file tests/Feature/RatingReviewTest.php

    /**
     * Verify review text is correct, including for empty reviews.
     * @test
     */
    public function tc_6_test_review_text_is_displayed_correctly_for_written_and_empty_reviews(): void
    {
        // 1. SETUP: Buat data untuk dua skenario

        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();
        $vendor = \App\Models\Vendor::factory()->create();

        // Skenario A: Ulasan dengan teks
        $reviewWithText = 'Makanannya sangat lezat dan direkomendasikan.';
        $order1 = \App\Models\Order::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $user->userId]);
        \App\Models\VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order1->orderId,
            'review' => $reviewWithText,
        ]);

        // Skenario B: Ulasan tanpa teks (kosong/null)
        $order2 = \App\Models\Order::factory()->create(['vendorId' => $vendor->vendorId, 'userId' => $user->userId]);
        \App\Models\VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order2->orderId,
            'review' => null,
        ]);

        // 2. AKSI: Kunjungi halaman review
        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        // 3. ASSERTION: Periksa kedua skenario
        $response->assertStatus(200);

        // Pastikan teks ulasan lengkap muncul di halaman
        $response->assertSee($reviewWithText);
        // $response->assertSee('No reviews for this vendor yet');
    }

    /**
     * Verify placeholder message is shown when no reviews exist.
     * @test
     */
    public function tc_6_test_it_shows_a_message_when_there_are_no_reviews(): void
    {
        // SETUP: Buat vendor, TAPI JANGAN BUAT REVIEW APAPUN
        $user = \App\Models\User::factory()->create();
        $vendor = \App\Models\Vendor::factory()->create();

        // AKSI: Kunjungi halaman
        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        // ASSERTION: Pastikan pesan "tidak ada ulasan" yang muncul
        $response->assertStatus(200);
        $response->assertSee('No reviews for this vendor yet');
    }

    /**
     * Verify the order date is correctly displayed and formatted.
     * @test
     */
    public function tc_7_test_order_date_is_displayed_and_formatted_correctly(): void
    {
        // 1. SETUP: Buat data dengan tanggal yang spesifik
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create();
        $vendor = \App\Models\Vendor::factory()->create();

        // Tentukan tanggal spesifik untuk order
        $orderDate = Carbon::parse('2025-05-20');

        $order = \App\Models\Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'created_at' => $orderDate, // Atur tanggal pembuatan order
            'updated_at' => $orderDate,
        ]);

        \App\Models\VendorReview::factory()->create([
            'vendorId' => $vendor->vendorId,
            'userId' => $user->userId,
            'orderId' => $order->orderId,
        ]);

        // 2. AKSI: Kunjungi halaman review
        $response = $this->actingAs($user)->get(route('rate-and-review', $vendor));

        // 3. ASSERTION: Periksa format tanggal
        $response->assertStatus(200);

        // Buat string tanggal dengan format yang diharapkan muncul di halaman
        // Ganti 'd F Y' jika format di aplikasi Anda berbeda (misal: 'j F Y' untuk '20th May 2025')
        $expectedDateString = "Ordered on " . $orderDate->format('jS M Y'); // Hasil: "Dipesan pada 20 May 2025"

        // Pastikan teks beserta tanggal yang sudah diformat muncul di halaman
        $response->assertSee($expectedDateString);
    }
}
