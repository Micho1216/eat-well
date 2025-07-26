<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class registerTest extends TestCase
{
    use RefreshDatabase;

    // CUSTOMER
    /** @test */
    public function tc1_registration_page_displays_all_ui_elements()
    {
        $response = $this->get('/register/customer');

        // Title
        $response->assertSee('Join EatWell');

        // Form fields and labels
        $response->assertSee('Name');
        $response->assertSee('Email');
        $response->assertSee('Password');
        $response->assertSee('Password Confirmation');

        // Register button
        $response->assertSee('Register');

        // Links/buttons for login and vendor registration
        $response->assertSee('Already has an account?');
        $response->assertSee('Login now!');
        $response->assertSee('Join Eatwell as a');
        $response->assertSee('vendor!');
    }

    /** @test */
    public function tc2_user_can_register_and_is_saved_in_database()
    {
        $response = $this->post('/register/customer', [
            'name' => 'Test Customer',
            'email' => 'testcustomer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Assert user is redirected (adjust as needed)
        $response->assertRedirect('/home'); // or wherever your app redirects after registration

        // Assert user is in the database
        $this->assertDatabaseHas('users', [
            'email' => 'testcustomer@example.com',
            'name' => 'Test Customer',
            'role' => 'Customer',
        ]);
    }

    
    /** @test */
    public function tc3_register_with_all_fields_empty_shows_validation_errors()
    {
        $response = $this->post('/register/customer', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // The page should return with validation errors and not redirect
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // Optionally, check that the user is not created in the database
        $this->assertDatabaseMissing('users', [
            'email' => '',
        ]);
    }

    /** @test */
    public function tc4_register_with_empty_name_shows_validation_error()
    {
        $response = $this->post('/register/customer', [
            'name' => '',
            'email' => 'validuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function tc5_register_with_empty_email_shows_validation_error()
    {
        $response = $this->post('/register/customer', [
            'name' => 'Valid User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // 'g-recaptcha-response' => 'test', // Uncomment if you have reCAPTCHA validation and can mock it
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function tc6_register_with_empty_password_shows_validation_error()
    {
        $response = $this->post('/register/customer', [
            'name' => 'Valid User',
            'email' => 'testuser@example.com',
            'password' => '',
            'password_confirmation' => '',
            
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function tc7_register_with_short_password_shows_validation_error()
    {
        $response = $this->post('/register/customer', [
            'name' => 'Valid User',
            'email' => 'validuser@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function tc8_register_with_google_button_is_present()
    {
        $response = $this->get('/register/customer');

        // Check for the "Register with Google" button or link
        $response->assertSeeText('Register with Google');
    }


    // VENDOR
    public function tc9_vendor_registration_page_displays_all_ui_elements()
    {
        $response = $this->get('/register/vendor');

        // Title
        $response->assertSee('Join EatWell');

        // Form fields and labels
        $response->assertSee('Name');
        $response->assertSee('Email');
        $response->assertSee('Password');
        $response->assertSee('Password Confirmation');

        // Register button
        $response->assertSee('Register');

        // Links/buttons for login and vendor registration
        $response->assertSee('Already has an account?');
        $response->assertSee('Login now!');
        $response->assertSee('Join Eatwell as a');
        $response->assertSee('customer!');
    }

    /** @test */
    public function tc10_vendor_user_can_register_and_is_saved_in_database()
    {
        $response = $this->post('/register/vendor', [
            'name' => 'Test Vendor',
            'email' => 'testvendor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Assert user is redirected (adjust as needed)
        $response->assertRedirect('/home'); // or wherever your app redirects after registration

        // Assert user is in the database
        $this->assertDatabaseHas('users', [
            'email' => 'testvendor@example.com',
            'name' => 'Test Vendor',
            'role' => 'Vendor'
        ]);
    }

    
    /** @test */
    public function tc11_vendor_register_with_all_fields_empty_shows_validation_errors()
    {
        $response = $this->post('/register/vendor', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // The page should return with validation errors and not redirect
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // Optionally, check that the user is not created in the database
        $this->assertDatabaseMissing('users', [
            'email' => '',
        ]);
    }

    /** @test */
    public function tc12_vendor_register_with_empty_name_shows_validation_error()
    {
        $response = $this->post('/register/vendor', [
            'name' => '',
            'email' => 'validuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function tc13_vendor_register_with_empty_email_shows_validation_error()
    {
        $response = $this->post('/register/vendor', [
            'name' => 'Valid User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // 'g-recaptcha-response' => 'test', // Uncomment if you have reCAPTCHA validation and can mock it
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function tc14_vendor_register_with_empty_password_shows_validation_error()
    {
        $response = $this->post('/register/vendor', [
            'name' => 'Valid User',
            'email' => 'testuser@example.com',
            'password' => '',
            'password_confirmation' => '',
            
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function tc15_vendor_register_with_short_password_shows_validation_error()
    {
        $response = $this->post('/register/vendor', [
            'name' => 'Valid User',
            'email' => 'validuser@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function tc16_vendor_register_with_google_button_is_present()
    {
        $response = $this->get('/register/vendor');

        // Check for the "Register with Google" button or link
        $response->assertSeeText('Register with Google');
    }

}