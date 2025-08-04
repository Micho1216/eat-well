<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\City;
use App\Models\District;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PaymentMethod;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Village;
use Tests\TestCase;

class CartTest extends TestCase
{


    /** @test */
    public function tc1_customer_can_add_packages()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Testing',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);
        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'breakfastPrice' => 25000,
        ]);

        $response = $this->post('/update-order-summary', [
            'vendor_id' => $package->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfast' => 1,
                        'lunch' => 0,
                        'dinner' => 0,
                    ],
                ],
            ],
        ])->assertStatus(200);
        $response->assertJsonFragment([
            'totalItems' => 1,
            'totalPrice' => 25000.0,
        ]);

        $cart = Cart::where('userId', $user->userId)
        ->where('vendorId', $vendor->vendorId)
        ->first();

        $this->assertNotNull($cart);

        $cartItem = CartItem::where('cartId', $cart->cartId)
            ->where('packageId', $package->packageId)
            ->first();
        
        $this->assertNotNull($cartItem);
        $this->assertEquals(1, $cartItem->breakfastQty);
        $this->assertEquals(0, $cartItem->lunchQty);
        $this->assertEquals(0, $cartItem->dinnerQty);
    }

    /** @test */
    public function tc2_decrease_quantity_to_zero()
    {
        $user = User::where('role', 'Customer')->first();
        $this->actingAs($user);

        $package = Package::with('vendor')->first();
        $vendorId = $package->vendor->vendorId;

        // Step 1: Add an item to the cart
        $initialPayload = [
            'vendor_id' => $vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'Breakfast' => 1,
                        'Lunch' => 0,
                        'Dinner' => 0
                    ]
                ]
            ]
        ];
        $this->post('/update-order-summary', $initialPayload)->assertStatus(200);

        // Step 2: Send update with quantity = 0 (i.e., remove)
        $removePayload = [
            'vendor_id' => $vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'Breakfast' => 0,
                        'Lunch' => 0,
                        'Dinner' => 0
                    ]
                ]
            ]
        ];
        $response = $this->post('/update-order-summary', $removePayload);
        $response->assertStatus(200);
        $response->assertJson([
            'totalItems' => 0,
            'totalPrice' => 0,
        ]);

        // Confirm cartItem is removed
        $cart = Cart::where('userId', $user->userId)
            ->where('vendorId', $vendorId)
            ->first();

        // Cart should be deleted if no items remain
        $this->assertNull($cart);
    }

    /** @test */
    public function tc3_add_different_packages_with_meal_types()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Testing',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        $package1 = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'breakfastPrice' => 25000,
        ]);
        $package2 = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'dinnerPrice' => 25000,
        ]);

        // Get 2 packages from that vendor
        $packages = $vendor->packages()->take(2)->get();

        $this->assertCount(2, $packages, 'Vendor must have at least 2 packages.');

        $package1 = $packages[0];
        $package2 = $packages[1];

        $payload = [
            'vendor_id' => $vendor->vendorId,
            'packages' => [
                $package1->packageId => [
                    'items' => [
                        'breakfast' => 1,
                        'lunch' => 0,
                        'dinner' => 0
                    ]
                ],
                $package2->packageId => [
                    'items' => [
                        'breakfast' => 0,
                        'lunch' => 0,
                        'dinner' => 1
                    ]
                ]
            ]
        ];

        $response = $this->post('/update-order-summary', $payload);
        $response->assertStatus(200);

        
        $totalItems = 1 + 1;
        $totalPrice =
        (1 * ($package1->breakfastPrice ?? 0)) +
        (1 * ($package2->dinnerPrice ?? 0));
        
        $response->assertJsonFragment([
            'totalItems' => $totalItems,
            'totalPrice' => $totalPrice,
        ]);

        $cart = Cart::where('userId', $user->userId)
            ->where('vendorId', $vendor->vendorId)
            ->first();

        $this->assertNotNull($cart);

        $this->assertDatabaseHas('cart_items', [
            'cartId' => $cart->cartId,
            'packageId' => $package1->packageId,
            'breakfastQty' => 1,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'cartId' => $cart->cartId,
            'packageId' => $package2->packageId,
            'dinnerQty' => 1,
        ]);
    }


    /** @test */
    public function tc4_price_reflects_selected_package_and_meal_type()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Testing',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'breakfastPrice' => 30000,
            'lunchPrice' => 40000,
            'dinnerPrice' => 20000,
        ]);


        // Select all meal types with quantity 1
        $payload = [
            'vendor_id' => $vendor->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfast' => 1,
                        'lunch' => 1,
                        'dinner' => 1,
                    ]
                ]
            ]
        ];

        $expectedTotalPrice =
            ($package->breakfastPrice ?? 0) +
            ($package->lunchPrice ?? 0) +
            ($package->dinnerPrice ?? 0);

        $response = $this->post('/update-order-summary', $payload)->assertStatus(200);

        $response->assertJsonFragment([
            'totalItems' => 3,
            'totalPrice' => $expectedTotalPrice
        ]);

        // Assert database contains correct quantities
        $cart = Cart::where('userId', $user->userId)
            ->where('vendorId', $vendor->vendorId)
            ->first();

        $this->assertNotNull($cart);

        $cartItem = CartItem::where('cartId', $cart->cartId)
            ->where('packageId', $package->packageId)
            ->first();

        $this->assertEquals(1, $cartItem->breakfastQty);
        $this->assertEquals(1, $cartItem->lunchQty);
        $this->assertEquals(1, $cartItem->dinnerQty);
    }

    /** @test */
    public function tc5_display_order_details_correctly()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);
        
        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Teng',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        // Step 2: Use an existing package
        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'breakfastPrice' => 33000,
        ]);
        // create cart if not exist using the userId and vendorId, then add cart item to the cart, then query check display
        // Step 3: Add package to cart
        $this->post('/update-order-summary', [
            'vendor_id' => $package->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfast' => 1,
                        'lunch' => 0,
                        'dinner' => 0,
                    ],
                ],
            ],
        ])->assertStatus(200);

        $cart = Cart::where('userId', $user->userId)
            ->where('vendorId', $vendor->vendorId)
            ->first();

        $this->assertNotNull($cart);

        $cartItem = CartItem::where('cartId', $cart->cartId)
            ->where('packageId', $package->packageId)
            ->first();

        session([
            'address_id' => $address->addressId,
            'selected_vendor_id' => $vendor->vendorId,
        ]);

        // assert see database Cart Item yang sesuai dengan package dan package time
        $this->assertDatabaseHas('cart_items', [
            'cartId' => $cart->cartId,
            'packageId' => $package->packageId,
            'breakfastQty' => 1,
            'lunchQty' => 0,
            'dinnerQty' => 0,
        ]);

        $checkoutPayload = [
            'vendor_id' => $vendor->vendorId,
            'address_id' => 1,
            'payment_method_id' => 1,
            'start_date' => now()->addWeek()->startOfWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(6)->format('Y-m-d'),
            'password' => "password",
        ];

        $detail = $this->get(route('payment.show', $checkoutPayload));
        $detail->assertStatus(200);
        $detail->assertSee($package->name);
        $detail->assertSeeText('1x Breakfast');
        $detail->assertSee($checkoutPayload['start_date']);
        $detail->assertSee($checkoutPayload['end_date']);
    }


    /** @test */
    public function tc6_package_quantity_reflects_cart()
    {
        // Use existing customer
        $user = User::where('role', 'Customer')->first();
        $this->actingAs($user);

        // Use existing vendor & package
        $package = Package::with('vendor')->first();
        $this->assertNotNull($package);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session([
            'address_id' => $address->addressId,
            'selected_vendor_id' => $package->vendorId,
        ]);
        // Step 1: Add 2x Dinner to cart
        $resp = $this->post('/update-order-summary', [
            'vendor_id' => $package->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfast' => 0,
                        'lunch' => 0,
                        'dinner' => 2
                    ]
                ]
            ]
        ]);


        // Step 2: Checkout
        $this->assertDatabaseHas('payment_methods', [
            'methodId' => 1
        ]);

        $checkoutPayload = [
            'vendor_id' => $package->vendorId,
            'payment_method_id' => 1,
            'start_date' => now()->addWeek()->startOfWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(6)->format('Y-m-d'),
            'password' => "password",
        ];

        // echo "$package->name, $package->vendorId\n";

        $response = $this->post('/checkout', $checkoutPayload);
        $response->assertStatus(200); // Redirect after checkout

        // Step 3: Validate order details
        $order = $user->orders()->latest()->first();
        $this->assertNotNull($order);


        $detail = $this->get("/orders/{$order->orderId}");
        $detail->assertStatus(200);
        $detail->assertSee($package->packageName);
        $expectedPrice = $package->dinnerPrice * 2;
        $formattedPrice = number_format($expectedPrice, 2, ',', '.');
        $detail->assertSee('Rp ' . $formattedPrice);
    }

    /** @test */
    public function tc7_total_price_matches_package_times_quantity()
    {
        // 1. Login as the first customer
        $user = User::where('role', 'Customer')->first();
        $this->actingAs($user);
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        
        // 2. Get a package and vendor
        $package = Package::with('vendor')->first();
        $this->assertNotNull($package);
        session([
            'address_id' => $address->addressId,
            'selected_vendor_id' => $package->vendorId,
        ]);
        
        // 3. Define quantities for each meal type
        $breakfastQty = 1;
        $lunchQty = 2;
        $dinnerQty = 3;

        // 4. Calculate expected total price manually
        $expectedTotalPrice =
            ($package->breakfastPrice ?? 0) * $breakfastQty +
            ($package->lunchPrice ?? 0) * $lunchQty +
            ($package->dinnerPrice ?? 0) * $dinnerQty;

        // 5. Submit to cart
        $response = $this->post('/update-order-summary', [
            'vendor_id' => $package->vendor->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfast' => $breakfastQty,
                        'lunch' => $lunchQty,
                        'dinner' => $dinnerQty
                    ]
                ]
            ]
        ]);

        // 6. Assert response is OK and total price is correct
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'totalItems' => $breakfastQty + $lunchQty + $dinnerQty,
            'totalPrice' => (float) $expectedTotalPrice
        ]);
    }

    /** @test */
    public function tc8_selected_delivery_address_is_displayed_on_payment_page()
    {
        // 1. SETUP: Create a specific address using the CORRECT database columns
        $user = User::query()->where('role', 'Customer')->first();
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);

        session(['address_id' => $address->addressId]);
        // Log the user in for this test
        $this->actingAs($user);

        // 2. SETUP: Add an item to the cart so the payment page is accessible
        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Testing',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);
        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'dinnerPrice' => 25000,
        ]);

        $this->post('/update-order-summary', [
            'vendor_id' => $vendor->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfastQty' => 0,
                        'lunchQty' => 0,
                        'dinnerQty' => 1,
                    ],
                ],
            ],
        ])->assertStatus(200);

        $cart = Cart::create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
            'totalPrice' => 25000,
        ]);

        CartItem::create([
            'cartId' => $cart->cartId,
            'packageId' => $package->packageId,
            'vendorId' => $vendor->vendorId,
            'breakfastQty' => 0,
            'lunchQty' => 0,
            'dinnerQty' => 1,
            'day' => now()->format('Y-m-d'),
        ]);

        session([
            'address_id' => $address->addressId,
            'vendor_id' => $vendor->vendorId,
        ]);

        $payload = [
            'vendor_id' => $vendor->vendorId,
            'payment_method_id' => 1,
            'start_date' => now()->addWeek()->startOfWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(6)->format('Y-m-d'),
            'password' => 'password',
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone_number,
        ];
        session([
            'selected_vendor_id' => $vendor->vendorId,
            'address_id' => $address->addressId,
        ]);
        $response = $this->post('/checkout', $payload);
        // 3. ACTION: Visit the payment/checkout summary page
        $response = $this->get(route('payment.show'));

        // 4. ASSERTION: Check if the page loaded correctly and displays the address
        $response->assertStatus(200);

        // Assert that each part of the address is visible in the page's HTML
        // Use the CORRECT property names from your Address model
        $response->assertSee($address->jalan);
        $response->assertSee($address->kota);
        $response->assertSee($address->provinsi);
        $response->assertSee($address->kode_pos);
    }
    /** @test */
    public function tc9_customer_can_select_wellpay_payment_method()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        $user->Update(['wellpay' => 200000]);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);


        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $oldBalance = $user->wellpay;

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Testt',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'breakfastPrice' => 25000,
        ]);

        // Step 1: Add 1x Breakfast to cart
        $this->post('/update-order-summary', [
            'vendor_id' => $vendor->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfastQty' => 1,
                        'lunchQty' => 0,
                        'dinnerQty' => 0,
                    ]
                ]
            ]
        ])->assertStatus(200);

        $cart = Cart::create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
            'totalPrice' => 25000,
        ]);

        CartItem::create([
            'cartId' => $cart->cartId,
            'packageId' => $package->packageId,
            'vendorId' => $vendor->vendorId,
            'breakfastQty' => 0,
            'lunchQty' => 0,
            'dinnerQty' => 1,
            'day' => now()->format('Y-m-d'),
        ]);

        session([
            'address_id' => $address->addressId,
            'vendor_id' => $vendor->vendorId,
        ]);

        // Step 2: Access payment page
        $response = $this->get("/payment/{$vendor->vendorId}");
        $response->assertStatus(302);


        session([
            'selected_vendor_id' => $vendor->vendorId,
            'address_id' => $address->addressId,
        ]);
        // Optional: you can simulate selecting Wellpay and proceeding
        $checkout = $this->post('/checkout', [
            'vendor_id' => $vendor->vendorId,
            'payment_method_id' => 1, // assuming 1 is Wellpay ID
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'password' => 'password',
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone_number,
        ]);
        $this->assertEquals(
            $oldBalance - 25000,
            User::find($user->userId)->wellpay
        );

        $checkout->assertStatus(200);
        $checkout->assertJsonFragment(['message' => 'Checkout successful!']);
    }

    /** @test */
    public function tc11_checkout_fails_without_payment_method()
    {
        $user = User::where('role', 'Customer')->firstOrFail();
        $this->actingAs($user);

        $vendor = Vendor::factory()->create();

        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'breakfastPrice' => 25000,
        ]);

        // Add something to cart
        $this->post('/update-order-summary', [
            'vendor_id' => $vendor->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'Breakfast' => 1,
                        'Lunch' => 0,
                        'Dinner' => 0,
                    ]
                ]
            ]
        ])->assertStatus(200);

        // Try to checkout WITHOUT payment_method_id
        $response = $this->postJson('/checkout', [ // Note: use postJson here!
            'vendor_id' => $vendor->vendorId,
            // 'payment_method_id' => intentionally missing
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'address_id' => $user->addressId,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_method_id');
    }

    /** @test */
    public function tc12_customer_can_top_up_balance_successfully()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        $initialBalance = $user->wellpay;

        $payload = [
            'amount' => 100000, // Rp 100.000
            'password' => 'password', // assuming this is the correct password
        ];

        $amount = $payload['amount'];


        $response = $this->postJson('/topup', $payload);

        $response->assertJson([
            'message' => 'Top-up of Rp ' . number_format($amount, 0, ',', '.') . ' success!',
        ]);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertEquals($initialBalance + $amount, $user->wellpay);
    }


    /** @test */
    public function t13_top_up_fails_below_minimum_amount()
    {
        $user = User::first(); // or factory
        $this->actingAs($user);

        $response = $this->postJson('/topup', [
            'amount' => 500, // Below Rp 1.000
            'password' => '', // empty to trigger both errors
        ]);

        $response->assertStatus(302);
        $response->assertRedirect();
    }

    /** @test */
    public function t14_top_up_fails_above_maximum_amount()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);


        $response = $this->postJson('/topup', [
            'amount' => 25000000,
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'amount',
            'password'
        ]);

        $response->assertJsonFragment([
            'amount' => ['The maximum top-up amount is Rp 20.000.000.'],
            'password' => ['Please enter your password.'],
        ]);
    }

    /** @test */
    public function tc15_checkout_fails_with_incorrect_password()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        // Ensure user has enough balance
        $user->update(['wellpay' => 1000000]);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Test',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'dinnerPrice' => 40000,
        ]);

        // Add item to cart
        $this->post('/update-order-summary', [
            'vendor_id' => $package->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfastQty' => 0,
                        'lunchQty' => 0,
                        'dinnerQty' => 1,
                    ],
                ],
            ],
        ])->assertStatus(200);

        $cart = Cart::create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
            'totalPrice' => 40000,
        ]);

        CartItem::create([
            'cartId' => $cart->cartId,
            'packageId' => $package->packageId,
            'vendorId' => $vendor->vendorId,
            'breakfastQty' => 0,
            'lunchQty' => 0,
            'dinnerQty' => 1,
            'day' => now()->format('Y-m-d'),
        ]);

        session([
            'address_id' => $address->addressId,
            'vendor_id' => $vendor->vendorId,
        ]);

        // Attempt checkout with WRONG password
        $payload = [
            'vendor_id' => $vendor->vendorId,
            'payment_method_id' => 1,
            'start_date' => now()->addWeek()->startOfWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(6)->format('Y-m-d'),
            'password' => 'passwar',
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone_number,
        ];

        session([
            'selected_vendor_id' => $vendor->vendorId,
            'address_id' => $address->addressId,
        ]);

        $response = $this->post('/checkout', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $response->assertJsonFragment([
            'password' => ['Incorrect password.'],
        ]);
    }
    /** @test */
    public function tc16_checkout_fails_with_insufficient_wellpay_balance()
    {
        $user = User::query()->where('role', 'Customer')->first();
        $this->actingAs($user);

        $user->update(['wellpay' => 1000]);

        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'Jawa Barat',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);


        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $vendor = Vendor::factory()->create([
            'name' => $user->name ?? 'Vendor Testing',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
        ]);

        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'dinnerPrice' => 25000,
        ]);

        // Add item to cart (make sure price is higher than 10)
        $this->post('/update-order-summary', [
            'vendor_id' => $package->vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'breakfastQty' => 0,
                        'lunchQty' => 0,
                        'dinnerQty' => 1,
                    ],
                ],
            ],
        ])->assertStatus(200);

        $cart = Cart::create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
            'totalPrice' => 25000,
        ]);

        CartItem::create([
            'cartId' => $cart->cartId,
            'packageId' => $package->packageId,
            'vendorId' => $vendor->vendorId,
            'breakfastQty' => 0,
            'lunchQty' => 0,
            'dinnerQty' => 1,
            'day' => now()->format('Y-m-d'),
        ]);

        session([
            'address_id' => $address->addressId,
            'vendor_id' => $vendor->vendorId,
        ]);

        // Checkout with correct password but not enough balance
        $payload = [
            'vendor_id' => $vendor->vendorId,
            'payment_method_id' => 1,
            'start_date' => now()->addWeek()->startOfWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(6)->format('Y-m-d'),
            'password' => 'password',
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone_number,
        ];
        session([
            'selected_vendor_id' => $vendor->vendorId,
            'address_id' => $address->addressId,
        ]);
        $response = $this->post('/checkout', $payload);

        $response->assertJson([
            'message' => 'Checkout failed. Please try again.',
            'error' => 'Insufficient Wellpay balance. Please top up.',
        ]);
        $response->assertStatus(500);
    }
}
