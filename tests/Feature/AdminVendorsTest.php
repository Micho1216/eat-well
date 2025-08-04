<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\AddressSeeder;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\VendorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

// Admin view all vendors
class AdminVendorsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed only required classes
        $this->seed(PackageCategorySeeder::class);
        $this->seed(CuisineTypeSeeder::class);
    }
    
    /** @test */
    protected function loginAsAdmin()
    {
        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $admin
         */
        $admin = User::factory()->create([
            'role' => 'Admin'
        ]);
        $this->actingAs($admin);
    }
    /** @test */
    protected function loginAsCustomer()
    {
        /**
         * @var User|\Illuminate\Contracts\Auth\Authenticatable $user
         */
        $user = User::factory()->create(
            ['role' => 'Customer']
        );
        $this->actingAs($user);
    }
    /** @test */
    public function tc1_check_access_for_admin(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/view-all-vendors');

        $response->assertStatus(200);
       
        if (app()->getLocale() === 'en') {
            $response->assertSee('All Vendor');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSeeText('Semua Vendor');
        }
        

    }
    /** @test */
    public function tc2_check_no_vendor_exist()
    {
        $this->loginAsAdmin();

        $response = $this->get('/view-all-vendors');

        $response->assertStatus(200);
        if (app()->getLocale() === 'en') {
            $response->assertSee('No vendor found');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSeeText('Tidak menemukan vendor!');
        }
        
    }

    /** @test */
    public function tc3_check_correct_vendor_data()
    {
        $this->loginAsAdmin();

        $vendor = Vendor::factory()->create([
            'name' => 'Test Vendor A'
        ]);

        $response = $this->get('/view-all-vendors');

        $response->assertStatus(200);
        $response->assertSee('Test Vendor A');
    }

    /** @test */
    public function tc4_handle_special_character_vendor_name_rendered()
    {
        $this->loginAsAdmin();

        $vendor = Vendor::factory()->create([
            'name' => '@Mega&Co.™ "<script>VeryLongNameThatKeepsGoingAndGoing...rawrrrrrrrrrrrrrrrrrrrrrrrrr</script>'
        ]);

        $response = $this->get('/view-all-vendors');

        $response->assertStatus(200);
        $response->assertSee($vendor->name);
    }

    /** @test */
    public function tc5_non_admin_cannot_access()
    {
        $this->loginAsCustomer();
        
        $response = $this->get('/view-all-vendors');
        $response->assertRedirect('/home');
    }
}
