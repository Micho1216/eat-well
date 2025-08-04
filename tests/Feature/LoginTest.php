<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;


class LoginTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
     public function tc1_login_with_valid_credentials(): void
    {
        // Create a user with a known, HASHED password (Laravel automatically hashes passwords)
        $user = User::factory()->create([
            'password' => bcrypt('password123'), // Passwords must be hashed in the database
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123', // Send the plain-text password for authentication
        ]);

        // Assert that the request results in a redirect (HTTP status 302)
        $response->assertStatus(302);
        // Assert redirection to the home page (or dashboard, adjust as per your application's routes)
        $response->assertRedirect('/home');

        // Assert that the user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function tc2_login_with_invalid_email(): void
    {
        // Scenario 1: Unregistered email
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'any-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Scenario 2: Invalid email format (e.g., missing '@' or domain)
        $response = $this->post('/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function tc3_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(302);
        // $response->assertSessionMissing('errors');
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function tc4_login_with_empty_fields()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /** @test */
    public function tc5_password_field_is_hidden_on_login_page()
    {
        $response = $this->get('/login');
        $response->assertSee('type="password"', false);
    }

    /** @test */
    public function tc6_remember_me_checkbox_functionality(): void
    {
        $user = User::factory()->create([
            'email' => 'tc5_remember_me_email@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => Carbon::now(),
            'enabled_2fa' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => true, // Ensure this is boolean true
        ]);

        $response->assertRedirect('/home');
        $response->assertSessionHas('remember', true);
    }


    /** @test */
    public function tc7_google_login_button_exists()
    {
        $response = $this->get('/login');
        $response->assertSee('Continue with Google');
    }

    /** @test */
    public function tc8_register_link_exists()
    {
        $response = $this->get('/login');
        $response->assertSee("Register now!");
    }

    /** @test */
    public function tc9_register_as_vendor_link_exists()
    {
        $response = $this->get('/login');
        $response->assertSeeText("Join EatWell as a vendor");
    }

    /** @test */
    public function tc10_google_redirect_route()
    {
        $response = $this->get('/auth/google/redirect');
        $response->assertRedirect(); // Akan redirect ke Google
    }

    /** @test */
     public function tc11_the_form_has_a_csrf_token()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
         $response->assertSee('<input type="hidden" name="_token"', false);
    }

}
