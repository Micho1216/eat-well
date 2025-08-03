<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminViewAllUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin()
    {
        /** @var User|Authenticatable $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        return $this->actingAs($admin);
    }

    /** @test */
    public function tc01_displays_all_users_in_table()
    {
        $this->actingAsAdmin();

        $users = User::factory()->count(3)->create();

        $response = $this->get(route('view-all-users'));

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
            $response->assertSee($user->role);
        }
    }

    /** @test */
    public function tc02_verifies_table_column_headers()
    {
        $this->actingAsAdmin();

        $response = $this->get(route('view-all-users'));

        $response->assertSee(__('view-all-users.no'));
        $response->assertSee(__('view-all-users.id'));
        $response->assertSee(__('view-all-users.username'));
        $response->assertSee(__('view-all-users.email'));
        $response->assertSee(__('view-all-users.role'));
        $response->assertSee(__('view-all-users.created_at'));
    }

    /** @test */
    public function tc03_displays_pagination_when_many_users_exist()
    {
        $this->actingAsAdmin();

        User::factory()->count(25)->create();

        $response = $this->get(route('view-all-users'));

        $response->assertSee('&laquo;', false);
        $response->assertSee('&raquo;', false);
        $response->assertSee('page-link', false); // Class of pagination links
    }

    /** @test */
    public function tc04_displays_translations_from_view_all_users_file()
    {
        $this->actingAsAdmin();

        $response = $this->get(route('view-all-users'));

        $response->assertSee(__('view-all-users.heading'));
        $response->assertSee(__('view-all-users.no'));
        $response->assertSee(__('view-all-users.email'));
        $response->assertSee(__('view-all-users.created_at'));
    }

    /** @test */
    public function tc05_displays_admin_navbar_and_footer()
    {
        $this->actingAsAdmin();

        $response = $this->get(route('view-all-users'));

        // You may check for specific text or HTML structure from <x-admin-nav> and <x-admin-footer>
        $response->assertSee('<nav', false);
        $response->assertSee('<footer', false);
    }

    /** @test */
    public function tc06_displays_correct_loop_iteration_numbers()
    {
        $this->actingAsAdmin();

        User::factory()->count(3)->create();

        $response = $this->get(route('view-all-users'));

        $response->assertSeeText('1');
        $response->assertSeeText('2');
        $response->assertSeeText('3');
    }
}

