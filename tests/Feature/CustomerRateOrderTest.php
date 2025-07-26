<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\AddressSeeder;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\OrderItemSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PackageCuisineSeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\VendorPreviewSeeder;
use Database\Seeders\VendorReviewSeeder;
use Database\Seeders\VendorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CustomerRateOrderTest extends TestCase
{
    // use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed([
            UserSeeder::class,
            AddressSeeder::class,
            PackageCategorySeeder::class,
            CuisineTypeSeeder::class,
            PaymentMethodSeeder::class,
            VendorSeeder::class,
            PackageSeeder::class,
            PackageCuisineSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            VendorPreviewSeeder::class,
        ]);
    }

    public function getOneFinishedOrderForSpecificCustomer(String $id)
    {
        $order = Order::query()
            ->where('userId', $id)
            ->where('isCancelled', 0)
            ->whereDate('endDate', '<', now())
            ->inRandomOrder()
            ->first();

        return $order;
    }
    public function getOneUpcomingOrderForSpecificCustomer(String $id)
    {
        $order = Order::query()
            ->where('userId', $id)
            ->where('isCancelled', 0)
            ->whereDate('startDate', '>', now())
            ->inRandomOrder()
            ->first();

        return $order;
    }
    /** @test */
    public function tc1_customer_can_rate_a_finished_order_with_valid_data()
    {
        // Step 1: Get first customer
        $customer = User::where('role', 'Customer')->first();

        // Step 2: Get an existing order for that customer
        $order = $this->getOneFinishedOrderForSpecificCustomer($customer->userId);

        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Submit rating
        $response = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 5,
            'review' => 'Excellent!',
        ])->assertStatus(200);


        // Step 5: Assert DB has the new review
        $this->assertDatabaseHas('vendor_reviews', [
            'userId' => $customer->userId,
            'vendorId' => $order->vendorId,
            'orderId' => $order->orderId,
            'rating' => 5,
            'review' => 'Excellent!',
        ]);

        // Step 6: Optional redirect/assert message (adjust if API returns JSON)
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function tc2_customer_can_rate_a_finished_order_with_rating_only()
    {
        // Step 1: Get first customer
        $customer = User::where('role', 'Customer')->where('userId', 2)->first();
        // dump($customer);

        // Step 2: Get an existing order for that customer
        $order = $this->getOneFinishedOrderForSpecificCustomer($customer->userId);

        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Submit rating
        $response = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 5,
            'review' => '',
        ]);

        // dump($response);

        // Step 5: Assert DB has the new review
        $this->assertDatabaseHas('vendor_reviews', [
            'userId' => $customer->userId,
            'vendorId' => $order->vendorId,
            'orderId' => $order->orderId,
            'rating' => 5,
            'review' => null,
        ]);

        // Step 6: Optional redirect/assert message (adjust if API returns JSON)
        $response->assertJson(['success' => true]);
    }
    /** @test */
    public function tc3_customer_cannot_submit_review_without_rating()
    {
        // Step 1: Get a customer
        $customer = User::where('role', 'Customer')->first();

        // Step 2: Get an order for that customer
        $order = Order::where('userId', $customer->userId)->first();

        // Update the order to be 'Finished' so it's valid for rating
        $order->startDate = '2025-07-13 02:51:23';
        $order->endDate = '2025-07-20 02:51:23';
        $order->save();


        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Submit review without rating
        $response = $this->postJson("/orders/{$order->orderId}/review", [
            // 'rating' is intentionally omitted
            'review' => 'Good',
        ]);

        // Step 5: Assert JSON validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);

        // Optional: Assert exact error message
        $response->assertJsonFragment([
            'rating' => ['The rating field is required.'],
        ]);
    }

    /** @test */
    public function tc4_customer_cannot_submit_rating_below_minimum()
    {
        $customer = User::where('role', 'Customer')->first();

        $order = Order::where('userId', $customer->userId)->first();

        // Update the order to be 'Finished' so it's valid for rating
        $order->startDate = '2025-07-13 02:51:23';
        $order->endDate = '2025-07-20 02:51:23';
        $order->save();


        // Step 4: Act as the customer
        $this->actingAs($customer);

        // Step 5: Try to submit rating = 0
        $response = $this->postJson("/orders/{$order->orderId}/review", [
            'rating' => 0,
            'review' => 'Too bad',
        ]);

        // Step 6: Assert validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);

        $response->assertJsonFragment([
            'rating' => ['The rating must be at least 1.'],
        ]);
    }

    /** @test */
    public function tc5_customer_cannot_submit_rating_above_maximum()
    {
        $customer = User::where('role', 'Customer')->first();

        $order = Order::where('userId', $customer->userId)->first();

        // Update the order to be 'Finished' so it's valid for rating
        $order->startDate = '2025-07-13 02:51:23';
        $order->endDate = '2025-07-20 02:51:23';
        $order->save();

        // Step 4: Act as the customer
        $this->actingAs($customer);

        // Step 5: Submit invalid rating (6)
        $response = $this->postJson("/orders/{$order->orderId}/review", [
            'rating' => 6,
            'review' => 'Too perfect',
        ]);

        // Step 6: Assert validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);

        $response->assertJsonFragment([
            'rating' => ['The rating may not be greater than 5.'],
        ]);
    }

    /** @test */
    public function tc6_customer_cannot_rate_an_unfinished_order()
    {
        // Step 1: Get a customer
        $customer = User::where('role', 'Customer')->first();

        // Step 2: Get one of the customer's orders and make sure it's NOT finished
        $order = $this->getOneUpcomingOrderForSpecificCustomer($customer->userId);

        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Try to submit a rating
        $response = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 4,
            'review' => 'Looks good so far',
        ]);

        // Step 5: Assert that the response blocks the action
        $response->assertStatus(403); // or 400 or custom status depending on your controller
        $response->assertJson([
            'message' => 'You can only rate finished orders.',
        ]);
    }

    /** @test */
    public function tc7_customer_cannot_rate_the_same_order_twice()
    {
        // Step 1: Get a customer
        $customer = User::where('role', 'Customer')->first();

        // Step 2: Get one of their orders
        $order = $this->getOneFinishedOrderForSpecificCustomer($customer->userId);

        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Submit the first review
        $firstResponse = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 4,
            'review' => 'Nice experience',
        ]);

        $firstResponse->assertJson(['success' => true]);

        // Step 6: Try submitting a second review for the same order
        $secondResponse = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 5,
            'review' => 'Actually, perfect!',
        ]);

        // Step 7: Assert rejection
        $secondResponse->assertStatus(409);
        $secondResponse->assertJson([
            'message' => 'You have already reviewed this order.',
        ]);
    }

    /** @test */
    public function tc8_vendor_or_admin_cannot_rate_an_order()
    {
        // Step 1: Get a vendor or admin user
        $nonCustomer = User::whereIn('role', ['Vendor', 'Admin'])->first();
        $this->assertNotNull($nonCustomer, 'No Vendor or Admin found.');

        // Step 2: Get any existing order
        $order = Order::first();
        $this->assertNotNull($order, 'No order found.');

        // Step 3: Act as the vendor or admin
        $this->actingAs($nonCustomer);

        // Step 4: Attempt to submit a rating
        $response = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 5,
            'review' => 'Trying to review as non-customer',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function tc9_guest_cannot_submit_rating()
    {
        $orderId = 1; // atau pakai factory jika perlu

        $response = $this->post("/orders/{$orderId}/review", [
            'rating' => 5,
            'review' => 'Great service!',
        ]);

        $response->assertRedirect('/login'); // Laravel default redirect for unauthenticated user
    }

    /** @test */
    public function tc10_customer_can_submit_rating_with_long_review_text()
    {
        // Step 1: Get first customer
        $customer = User::where('role', 'Customer')->first();

        // Step 2: Get an existing order for that customer
        $order = Order::where('userId', $customer->userId)->first();

        // Update the order to be 'Finished' so it's valid for rating
        $order->startDate = '2025-07-13 02:51:23';
        $order->endDate = '2025-07-20 02:51:23';
        $order->save();

        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Submit rating with long review (e.g., 350 characters)
        $longReview = str_repeat('A', 350);

        $response = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 5,
            'review' => $longReview,
        ]);

        // Step 5: Assert DB has the long review
        $this->assertDatabaseHas('vendor_reviews', [
            'userId' => $customer->userId,
            'vendorId' => $order->vendorId,
            'orderId' => $order->orderId,
            'rating' => 5,
            'review' => $longReview,
        ]);

        // Step 6: Assert JSON success
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function tc11_backend_rejects_zero_star_rating_even_if_ui_is_bypassed()
    {
        // Step 1: Get a valid customer
        $customer = User::where('role', 'Customer')->first();

        // Step 2: Get a finished order for that customer
        $order = Order::where('userId', $customer->userId)->first();
        $order->startDate = now()->subDays(10);
        $order->endDate = now()->subDays(2);
        $order->save();

        // Step 3: Act as the customer
        $this->actingAs($customer);

        // Step 4: Try to submit a 0-star rating (invalid)
        $response = $this->post("/orders/{$order->orderId}/review", [
            'rating' => 0,
            'review' => 'Trying to submit zero star',
        ], [
            'Accept' => 'application/json', // 👈 force JSON response
        ]);

        // Step 5: Assert validation fails
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
        $response->assertJsonFragment([
            'rating' => ['The rating must be at least 1.'],
        ]);
    }



}
