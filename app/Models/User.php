<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Illuminate\Auth\Passwords\CanResetPassword as PasswordsCanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\CanResetPassword;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail, CanResetPassword
{
    use HasFactory, Notifiable, SoftDeletes, PasswordsCanResetPassword;

    protected $table = 'users'; // Matches your migration

    protected $primaryKey = 'userId'; // Matches your migration

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'enabled2FA',
        'profilePath',
        'remember_token',
        'dateOfBirth',
        'genderMale',
        'wellpay',
        'provider_id',
        'provider_name',
        'provider_token',
        'provider_refresh_token',
        'otp',
        'otp_expires_at',
        'enabled_2fa',
        'locale'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
        'enabled2FA' => 'boolean', // Matches your migration
        'dateOfBirth' => 'datetime', // Matches your migration
        'genderMale' => 'boolean', // Matches your migration
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'role' => UserRole::class,
        'wellpay' => 'decimal:2',
    ];
    
    public function preferredLocale() : string
    {
        return $this->locale;
    }

    public function setLocale(string $lang)
    {
        $this->locale = $lang;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'userId', 'userId'); // Foreign key and local key
    }

    public function carts()
    {
        return $this->hasOne(Cart::class, 'userId', 'userId');
    }

    public function favoriteVendors()
    {
        return $this->belongsToMany(Vendor::class, 'favorite_vendors','userId', 'vendorId')->withTimestamps();
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'userId', 'userId');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'userId', 'userId');
    }

    public function vendorReviews()
    {
        return $this->hasMany(VendorReview::class, 'userId', 'userId');
    }

    public function defaultAddress()
    {
        return $this->hasOne(\App\Models\Address::class, 'userId', 'userId')
            ->where('is_default', true);
    }
}
