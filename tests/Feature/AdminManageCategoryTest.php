<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Province;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\AddressSeeder;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\VendorSeeder;
use Tests\TestCase;

class AdminManageCategoryTest extends TestCase
{

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $this->admin = User::factory()->create(['role' => 'Admin']);
        $this->seed([
            AddressSeeder::class,
            PackageCategorySeeder::class,
            PaymentMethodSeeder::class,
            CuisineTypeSeeder::class,
            VendorSeeder::class,
        ]);
    }


    /** @test */
    public function tc1_admin_views_all_package_categories()
    {
        $this->actingAs($this->admin);

        $categoryA = PackageCategory::create(['categoryName' => 'Healthy Meals']);
        $categoryB = PackageCategory::create(['categoryName' => 'Vegan Meals']);

        Package::factory()->count(3)->create(['categoryId' => $categoryA->categoryId]);
        Package::factory()->count(2)->create(['categoryId' => $categoryB->categoryId]);

        $response = $this->get(route('categories.show'));

        $response->assertStatus(200);

        if (app()->getLocale() === 'en') {
            $response->assertSeeText('Category Name');
            $response->assertSeeText('Packages Count');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSeeText('Nama Kategori');
            $response->assertSeeText('Jumlah Paket');
        }
        $response->assertSeeText('Healthy Meals');
        $response->assertSeeText('Vegan Meals');
        $response->assertSeeText('3');
        $response->assertSeeText('2');
    }

    /** @test */
    public function tc2_guest_and_non_admin_users_cannot_access_category_view()
    {
        $guestResponse = $this->get(route('categories.show'));
        $guestResponse->assertRedirect(route('login'));
        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $customer
         */
        $customer = User::factory()->create(['role' => 'Customer']);
        $address = Address::factory()->create([
            'userId' => $customer->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);
        $this->actingAs($customer);

        $customerResponse = $this->get(route('categories.show'));
        $customerResponse->assertRedirect(route('home'));

        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $vendor
         */

        $vendor = User::factory()->create(['role' => 'Vendor']);
        $this->actingAs($vendor);


        $vendorResponse = $this->get(route('categories.show'));
        $vendorResponse->assertRedirect('/cateringHomePage');
    }

    /** @test */
    public function tc3_no_categories_exist()
    {
        $this->actingAs($this->admin);

        // Step 2: Ensure the category table is empty
        Package::query()->delete();
        PackageCategory::query()->delete();

        // Step 3: Visit the category page
        $response = $this->get(route('categories.show'));

        // Step 4: Assertions

        $response->assertStatus(200);
        // $response->assertDontSeeHtml('<th scope="col">Category Name</th>'); // column header
        // $response->assertDontSee('Packages count'); // column header
        // $response->assertSeeText('No categories available'); // empty message

        if (app()->getLocale() === 'en') {
            $response->assertDontSeeHtml('<th scope="col">Category Name</th>');
            $response->assertDontSee('Packages count');
            $response->assertSeeText('No categories available');
        } elseif (app()->getLocale() === 'id') {
            $response->assertDontSeeHtml('<th scope="col">Nama Kategori</th>');
            $response->assertDontSee('Jumlah Paket');
            $response->assertSeeText('Tidak ada kategori');
        }
    }

    //ADMIN ADD NEW CATEGORY

    /** @test */
    public function tc4_admin_adds_a_new_unique_category()
    {
        $this->actingAs($this->admin);

        $data = ['categoryName' => 'Healthy Food'];

        $response = $this->post(route('categories.store'), $data);

        // Step 4: Assertions
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Step 5: Check that category exists in DB
        $this->assertDatabaseHas('package_categories', ['categoryName' => 'Healthy Food']);
    }

    /** @test */
    public function tc5_admin_adds_category_with_duplicate_name()
    {
        $this->actingAs($this->admin);

        PackageCategory::create(['categoryName' => 'Snack Meals']);

        $response = $this->post(route('categories.store'), [
            'categoryName' => 'Snack Meals',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('categoryName');

        $categoryName = 'Snack Meals';
        $snackMealCount = PackageCategory::where('categoryName', $categoryName)->count();

        $this->assertEquals(1, $snackMealCount);
    }

    /** @test */
    public function tc6_non_admin_user_cannot_access_add_category_form()
    {
        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $customer
         */
        $customer = User::factory()->create(['role' => 'Customer']);
        $this->actingAs($customer);

        $response = $this->get('/admin/package-categories/create');

        $response->assertRedirect('home');

        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $vendor
         */
        $vendor = User::factory()->create(['role' => 'Vendor']);
        $this->actingAs($vendor);

        $response = $this->get('/admin/package-categories/create');

        $response->assertRedirect('/cateringHomePage');
    }

    public function test_tc7_admin_cannot_add_category_with_empty_name()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('categories.store'), [
            'categoryName' => '',
        ]);

        // Assert: redirected back with validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors(['categoryName']);

        // Assert: no new category with empty name is created
        $this->assertDatabaseMissing('package_categories', [
            'categoryName' => '',
        ]);
    }

    /** @test */
    public function tc8_admin_can_add_trimmed_category_name()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('categories.store'), [
            'categoryName' => ' Vegan Meals ',
        ]);


        // Assert: redirected with success
        $response->assertRedirect();
        $response->assertSessionHas('success'); // or the exact key you use

        // Assert: stored name is trimmed
        $this->assertDatabaseHas('package_categories', [
            'categoryName' => 'Vegan Meals',
        ]);
    }

    //ADMIN DELETE CATEGORY
    /** @test */
    public function test_tc9_admin_can_soft_delete_category_with_no_packages()
    {
        $this->actingAs($this->admin);

        $category = PackageCategory::create(['categoryName' => 'Test Meals']);

        $response = $this->delete(route('category.delete', $category->categoryId));

        // Assert: success redirect with success message
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Refresh model to get soft-deleted version
        $deletedCategory = PackageCategory::withTrashed()->find($category->categoryId);
        $now = '_' . now()->format('Ymd_His');

        // Assert: soft deleted
        $this->assertSoftDeleted('package_categories', [
            'categoryId' => $category->categoryId,
        ]);

        $this->assertEquals('Test Meals' . $now, $deletedCategory->categoryName);


        // Optional: ensure deleted_at is not null
        $this->assertNotNull($deletedCategory->deleted_at);
    }

    /** @test */
    public function tc10_admin_cannot_delete_category_with_packages()
    {
        $this->actingAs($this->admin);

        // Step 1: Create a category
        $category = PackageCategory::create(['categoryName' => 'Main Course']);

        // Step 2: Attach a package to the category
        $category->packages()->create([
            'name' => 'Chicken Rice',
            'price' => 30000,
            'description' => 'Delicious chicken rice',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Step 3: Attempt to delete the category
        $response = $this->delete(route('category.delete', $category->categoryId));

        // Step 4: Assert redirect back with error
        $response->assertRedirect();
        $response->assertSessionHas('error', __('admin/package_category.delete_failed'));


        // Step 5: Assert the category still exists (not soft-deleted)
        $this->assertDatabaseHas('package_categories', [
            'categoryId' => $category->categoryId,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function test_tc11_non_admin_cannot_delete_category()
    {
        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $vendorUser
         */
        $vendorUser = User::factory()->create([
            'role' => UserRole::Vendor,
        ]);

        $category = PackageCategory::create([
            'categoryName' => 'Unauthorized Delete Test'
        ]);

        $this->actingAs($vendorUser);

        $response = $this->delete(route('category.delete', $category->categoryId));

        $response->assertRedirect('/cateringHomePage');

        $this->assertDatabaseHas('package_categories', [
            'categoryId' => $category->categoryId,
            'deleted_at' => null,
        ]);
    }
}
