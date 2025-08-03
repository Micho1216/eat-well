<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OTP_Test extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_can_register_and_redirects_to_verify()
    {
        Session::start();

        $response = $this->post('/register/customer', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'passworddsdsd123',
            'password_confirmation' => 'passworddsdsd123',
            'phoneNumber' => '08123456789',
            'address' => 'Test Address',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('auth.verify'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_otp_email_is_sent_after_register()
    {
        Mail::fake();
        Session::start();

        $email = 'otp@example.com';

        $this->post('/register/customer', [
            'name' => 'OTP Test',
            'email' => $email,
            'password' => 'passworddsdsd123',
            'password_confirmation' => 'passworddsdsd123',
            'phoneNumber' => '08123456789',
            'address' => 'OTP Street',
            '_token' => csrf_token(),
        ]);

        Mail::assertSent(function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function test_otp_is_saved_in_database()
    {
        Session::start();
        $email = 'otpcheck@example.com';

        $this->post('/register/customer', [
            'name' => 'OTP Save',
            'email' => $email,
            'password' => 'passworddsdsd123',
            'password_confirmation' => 'passworddsdsd123',
            'phoneNumber' => '08123456789',
            'address' => 'Save Street',
            '_token' => csrf_token(),
        ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user->otp);
        $this->assertNotNull($user->otp_expires_at);
        $this->assertTrue($user->otp_expires_at->gt(Carbon::now()));
    }

    public function test_session_contains_user_email_after_register()
    {
        Session::start();
        $email = 'session@example.com';

        $response = $this->post('/register/customer', [
            'name' => 'Session Test',
            'email' => $email,
            'password' => 'passworddsdsd123',
            'password_confirmation' => 'passdsdsword123',
            'phoneNumber' => '08123456789',
            'address' => 'Session Street',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('auth.verify'));
        $response->assertSessionHas('email', $email);
    }
}
