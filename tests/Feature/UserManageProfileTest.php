<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserManageProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createBasicCustomerUser(array $overrides = []): User
    {
        $default = [
            'name' => 'Basic User',
            'email' => 'basic_user@mail.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
        ];

        $userData = array_merge($default, $overrides);

        return User::create($userData);
    }

    protected function createCompleteCustomerUser(array $overrides = []): User
    {
        $default = [
            'name' => 'Complete User',
            'email' => 'complete_user@mail.com',
            'password' => bcrypt('password'),
            'role' => 'Customer',
            'dateOfBirth' => '1990-01-01',
            'genderMale' => true,
            'profilePath' => fake()->word() . '.jpg',
        ];

        return User::create(array_merge($default, $overrides));
    }

    /** @test */
    public function test_tc1_profile_page_shows_correct_data()
    {
        $user = $this->createCompleteCustomerUser([
            'name' => 'Alice',
            'dateOfBirth' => '1995-06-20 00:00:00',
            'genderMale' => false,
        ]);

        $this->actingAs($user);
        $response = $this->get('/manage-profile');

        $response->assertStatus(200)
            ->assertSee('Alice')
            ->assertSee('1995')
            ->assertSee('Female');
    }

    /** @test */
    public function test_tc2_valid_profile_update()
    {

        $user = $this->createCompleteCustomerUser();
        $this->actingAs($user);

        $payload = [
            'nameInput' => 'Amira Frami',
            'dateOfBirth' => '1999-09-11',
            'gender' => 'female',
        ];

        $before = now()->timestamp;
        $response = $this->patch('/manage-profile', $payload);
        $response->assertRedirect('/manage-profile');

        $this->assertDatabaseHas('users', [
            'userId' => $user->userId,
            'name' => 'Amira Frami',
            'dateOfBirth' => '1999-09-11 00:00:00',
            'genderMale' => 0,
        ]);
    }


    /** @test */
    public function test_tc3_name_is_left_blank()
    {
        $user = $this->createBasicCustomerUser();
        $this->actingAs($user);

        $payload = ['nameInput' => ''];

        $response = $this->patch('/manage-profile', $payload);
        $response->assertSessionHasErrors('nameInput');
    }

    /** @test */
    public function test_tc4_date_of_birth_in_future()
    {
        $user = $this->createBasicCustomerUser();
        $this->actingAs($user);

        $tomorrow = now()->addDay();

        $payload = [
            'nameInput' => 'Future User',
            'dateOfBirth' => Carbon::now()->addDay(),
            'gender' => 'female',
        ];

        $response = $this->patch('/manage-profile', $payload);
        $response->assertSessionHasErrors(['dateOfBirth']);
    }

    /** @test */
    public function test_tc5_gender_not_selected()
    {
        $user = $this->createBasicCustomerUser();
        $this->actingAs($user);

        $payload = [
            'nameInput' => 'No Gender',
            'dob_year' => '1990',
            'dob_month' => '01',
            'dob_day' => '01',
            // No 'gender'
        ];

        $response = $this->patch('/manage-profile', $payload);
        $response->assertSessionHasErrors(['gender']);
    }

    /** @test */
    public function test_tc6_script_injection_in_name_field()
    {
        $user = $this->createBasicCustomerUser();
        $this->actingAs($user);

        $maliciousName = "<script>alert('XSS')</script>";

        $payload = [
            'nameInput' => $maliciousName,
            'dob_year' => '1990',
            'dob_month' => '01',
            'dob_day' => '01',
            'gender' => 'male',
        ];

        $response = $this->patch('/manage-profile', $payload);
        $response->assertSessionHasErrors(['nameInput']);
    }
}
