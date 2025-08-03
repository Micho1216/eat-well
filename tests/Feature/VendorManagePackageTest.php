<?php

namespace Tests\Feature;

use App\Models\CuisineType;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class VendorManagePackageTest extends TestCase
{
    /**
     * @var User|Authenticatable $vendorAUser 
     * @var User|Authenticatable $vendorBUser 
     */
    protected $vendorAUser;
    protected $vendorBUser;
    protected Vendor $vendorA;
    protected Vendor $vendorB;
    /**
     * @test
     */

    public function setUp(): void
    {
        parent::setUp();
        // $this->withoutExceptionHandling();

        $this->artisan('migrate:fresh');

        $this->seed([
            CuisineTypeSeeder::class,
            PackageCategorySeeder::class,
        ]);

        // Create Vendor A user
        $this->vendorAUser = User::factory()->create([
            'email' => 'vendor1@mail.com',
            'name' => 'Green Catering',
            'password' => bcrypt('Test@1234'),
            'role' => 'Vendor',
        ]);

        // Create Vendor B user
        $this->vendorBUser = User::factory()->create([
            'email' => 'vendor2@mail.com',
            'name' => 'Blue Catering',
            'password' => bcrypt('Test4321'),
            'role' => 'Vendor',
        ]);

        // Create Vendor A catering data
        $this->vendorA = Vendor::factory()->create([
            'userId' => $this->vendorAUser->userId,
            'name' => 'Green Catering',
            'breakfast_delivery' => '06:30-08:00',
            'lunch_delivery' => '11:30-13:00',
            'dinner_delivery' => '17:30-19:00',
            // Address and phone using factory default
        ]);

        // Vendor B has no catering data (can be added later for negative tests)
    }

    /**
     * @test
     */
    public function tc1_handles_empty_package_list_gracefully()
    {
        // 1. Log in as Vendor A
        $this->actingAs($this->vendorAUser);

        // 2. Open the "Manage Package" page
        $response = $this->get(route('manageCateringPackage'));

        // Assert: shows no packages found message
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function tc2_authenticated_vendor_sees_only_their_own_packages()
    {
        // 1. Log in as Vendor A
        $this->actingAs($this->vendorAUser);

        // 2. Create some packages for Vendor A
        $package1 = Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'A Package 1',
            'breakfastPrice' => 45000,
            'lunchPrice' => null,
            'dinnerPrice' => null,
        ]);

        $package2 = Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'A Package 2',
            'breakfastPrice' => null,
            'lunchPrice' => 55000,
            'dinnerPrice' => null,
        ]);

        // 3. Access Manage Package page
        $response = $this->get('/manageCateringPackage');

        // Assert: page displays A Package 1 and A Package 2
        $response->assertStatus(200)
            ->assertSee('A Package 1')
            ->assertSee('A Package 2');
    }

    /**
     * @test
     */
    public function tc3_other_vendors_packages_are_not_shown()
    {
        // 1. Log in as Vendor B
        $this->actingAs($this->vendorBUser);

        // 2. Create some packages for Vendor A
        Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'A Package 1',
            'breakfastPrice' => 45000,
        ]);

        Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'A Package 2',
            'lunchPrice' => 55000,
        ]);

        // And one for Vendor B
        $bPackage = Vendor::factory()->create([
            'userId' => $this->vendorBUser->userId,
            'name' => 'Blue Catering',
        ]);

        $ownPackage = Package::factory()->create([
            'vendorId' => $bPackage->vendorId,
            'name' => 'B Package 1',
            'dinnerPrice' => 65000,
        ]);

        // 3. Access Manage Package page
        $response = $this->get('/manageCateringPackage');

        // Assert: page shows only B Package 1; Vendor A’s packages are hidden
        $response->assertStatus(200)
            ->assertSee('B Package 1')
            ->assertDontSee('A Package 1')
            ->assertDontSee('A Package 2');
    }


    /**
     * @test
     */
    public function tc4_add_valid_package_manual()
    {
        $this->actingAs($this->vendorAUser);

        // Create a dummy category and cuisine types
        $category = PackageCategory::create(['categoryName' => 'Cat A']);

        // Simulate file uploads
        $pdf = UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf');
        $image = UploadedFile::fake()->create('package.jpg', 100, 'image/jpeg');

        $response = $this->post('/manageCateringPackage', [
            'name' => 'Vegan Delight',
            'categoryId' => $category->categoryId,
            'averageCalories' => 450,
            'breakfastPrice' => 15000,
            'lunchPrice' => 20000,
            'dinnerPrice' => 18000,
            'menuPDFPath' => $pdf,
            'imgPath' => $image,
        ]);

        $response->assertRedirect('/manageCateringPackage');

        $this->assertDatabaseHas('packages', [
            'name' => 'Vegan Delight',
            'categoryId' => $category->categoryId,
            // 'vendorId' => $this->vendorA->vendorId,
            'averageCalories' => 450,
            'breakfastPrice' => 15000,
            'lunchPrice' => 20000,
            'dinnerPrice' => 18000,
        ]);
    }

    /**
     * @test
     */
    public function tc5_add_package_with_invalid_input()
    {
        $this->actingAs($this->vendorAUser);

        $response = $this->post('/manageCateringPackage', [
            'name' => '',
            'categoryId' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'categoryId']);
    }

    /**
     * @test
     */
    public function tc6_add_package_with_only_required_fields()
    {
        $this->actingAs($this->vendorAUser);

        $category = PackageCategory::create(['categoryName' => 'Cat B',]);

        $response = $this->post(route('packages.store'), [
            'name' => 'Basic Pack',
            'categoryId' => $category->categoryId,
            'averageCalories' => 200,

        ]);

        $response->assertRedirect('/manageCateringPackage');

        $this->assertDatabaseHas('packages', [
            'name' => 'Basic Pack',
            'categoryId' => $category->categoryId,
            'vendorId' => $this->vendorA->vendorId,
            'averageCalories' => 200,
        ]);
    }


    /**
     * @test
     */
    public function tc10_standard_valid_update()
    {
        $this->actingAs($this->vendorAUser);
        $cat = PackageCategory::create(['categoryName' => 'Cat A']);

        $package = Package::create([
            'vendorId' => $this->vendorA->vendorId,
            'categoryId' => $cat->categoryId,
            'name' => 'Old Package',
            'lunchPrice' => 15000,
        ]);

        $response = $this->from("/manageCateringPackage")->put("/packages/{$package->packageId}", [
            'name' => 'Updated Package',
            'categoryId' => $package->categoryId,
            'lunchPrice' => 20000,
        ]);

        $response->assertRedirect('/manageCateringPackage');
        $this->assertDatabaseHas('packages', [
            'packageId' => $package->packageId,
            'name' => 'Updated Package',
            'lunchPrice' => 20000,
        ]);
    }

    /**
     * @test
     */
    public function tc11_required_name_missing()
    {
        $this->actingAs($this->vendorAUser);

        $package = Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
        ]);

        $response = $this->from("/manageCateringPackage")->put("/packages/{$package->packageId}", [
            'name' => '',
            'categoryId' => $package->categoryId,
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseHas('packages', ['packageId' => $package->packageId, 'name' => $package->name]);
    }

    /**
     * @test
     */
    public function tc12_price_is_non_numeric()
    {
        $this->actingAs($this->vendorAUser);

        $package = Package::factory()->create(['vendorId' => $this->vendorA->vendorId]);

        $response = $this->from("/manageCateringPackage")->put("/packages/{$package->packageId}", [
            'name' => $package->name,
            'categoryId' => $package->categoryId,
            'lunchPrice' => 'abc',
        ]);

        $response->assertSessionHasErrors(['lunchPrice']);
        $this->assertDatabaseHas('packages', ['packageId' => $package->packageId, 'lunchPrice' => $package->lunchPrice]);
    }

    /**
     * @test
     */
    public function tc13_price_is_negative()
    {
        $this->actingAs($this->vendorAUser);

        $package = Package::factory()->create(['vendorId' => $this->vendorA->vendorId]);

        $response = $this->from("/manageCateringPackage")->put("/packages/{$package->packageId}", [
            'name' => $package->name,
            'categoryId' => $package->categoryId,
            'dinnerPrice' => -1000,
        ]);

        $response->assertSessionHasErrors(['dinnerPrice']);
        $this->assertDatabaseHas('packages', ['packageId' => $package->packageId]);
    }

    /**
     * @test
     */
    public function tc14_name_exceeds_character_limit()
    {
        $this->actingAs($this->vendorAUser);

        $package = Package::factory()->create(['vendorId' => $this->vendorA->vendorId]);

        $longName = str_repeat('A', 256);

        $response = $this->from("/manageCateringPackage")->put("/packages/{$package->packageId}", [
            'name' => $longName,
            'categoryId' => $package->categoryId,
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseHas('packages', ['packageId' => $package->packageId, 'name' => $package->name]);
    }

    /**
     * @test
     */
    public function tc15_vendor_b_cannot_update_vendor_a_package()
    {
        // Create Vendor B catering data
        $this->vendorB = Vendor::factory()->create([
            'userId' => $this->vendorBUser->userId,
            'name' => 'Green Catering',
            'breakfast_delivery' => '07:30-08:00',
            'lunch_delivery' => '11:00-13:00',
            'dinner_delivery' => '18:00-19:00',
            // Address and phone using factory default
        ]);

        $this->actingAs($this->vendorBUser);

        $package = Package::factory()->create(['vendorId' => $this->vendorA->vendorId]);

        $response = $this->put("/packages/{$package->packageId}", [
            'name' => 'Hacked Name',
            'categoryId' => $package->categoryId,
        ]);

        $response->assertForbidden(); // or use ->assertStatus(403);
        $this->assertDatabaseMissing('packages', ['name' => 'Hacked Name']);
    }

    /**
     * @test
     */
    public function tc16_vendor_can_soft_delete_own_package()
    {
        $this->actingAs($this->vendorAUser);

        $package = Package::create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'Deletable Package',
            'categoryId' => PackageCategory::create(['categoryName' => 'cat a'])->categoryId,
        ]);

        $response = $this->delete("/packages/{$package->packageId}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('packages', [
            'packageId' => $package->packageId,
            'name' => 'Deletable Package',
        ]);
    }

    /**
     * @test
     */
    public function tc17_customer_cannot_delete_vendor_package()
    {
        /**
         * @var User | Authenticatable $customer
         */
        $customer = User::factory()->create(['role' => 'Customer', 'email' => 'mailabc@123']);
        $this->actingAs($customer);

        $package = Package::create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'Protected Package',
            'categoryId' => PackageCategory::create(['categoryName' => 'cat a'])->categoryId,
        ]);

        $response = $this->delete("/packages/{$package->packageId}");

        $response->assertRedirect('/home');
        $this->assertDatabaseHas('packages', ['packageId' => $package->packageId]);
    }

    /**
     * @test
     */
    public function tc18_deleted_package_not_listed()
    {
        $this->actingAs($this->vendorAUser);

        $package = Package::create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'Will be hidden',
            'categoryId' => PackageCategory::create(['categoryName' => 'cat a'])->categoryId,
        ]);

        $package->delete(); // Simulate soft delete

        $response = $this->get('/manageCateringPackage');
        $response->assertDontSee('Will be hidden');
    }

    /**
     * @test
     */
    public function tc19_vendor_b_cannot_delete_vendor_a_package()
    {
        $this->actingAs($this->vendorBUser); // Logged in as vendor B

        $package = Package::create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'Vendor A Protected',
            'categoryId' => PackageCategory::create(['categoryName' => 'cat a'])->categoryId,
        ]);

        $response = $this->delete("/packages/{$package->packageId}");

        $response->assertStatus(302);
        $this->assertDatabaseHas('packages', ['packageId' => $package->packageId]);
    }
}
