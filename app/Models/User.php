<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

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

    // Renamed from cuisineReviews to match ERD if any, but ERD shows 'cuisine_reviews' having 'cuisineId' and 'userId'
    // It's a Many-to-Many through 'package_cuisine', so a User wouldn't directly have cuisine reviews in this structure.
    // If 'cuisine_reviews' is meant for user reviews of cuisine types, then the ERD doesn't show a direct link from user.
    // Assuming 'cuisine_reviews' from ERD is 'package_cuisine' in migrations for now.
    // If 'cuisine_reviews' table exists for user reviews, you'd need a model for it and link it.
    // Based on your provided 'cuisine_reviews' table in the ERD with `cuisineId` and `userId` only,
    // and no corresponding migration for it, I'll omit a direct `cuisineReviews` relationship on User.
    // If the 'package_cuisine' table in your migration was meant to be 'cuisine_reviews', please clarify.

    // INI UDAH GA DIPAKE
    // public function relationCustomerAddresses()
    // {
    //     return $this->hasMany(RelationCustomerAddress::class, 'customerId', 'userId'); // customerId in relation_customer_addresses links to userId in users
    // }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'userId', 'userId');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'userId', 'userId');
    }

    public function payments()
    {
        // ERD shows payments linked to userId. Your payment migration only has orderId and methodId, no userId directly.
        // If payments should be directly linked to user, you need to add userId to payments migration.
        // Based on current payment migration, user has many payments via order, but not directly.
        // I'll add this assuming you might add userId to payments table later.
        // For now, removing direct user->payments relationship based on your migrations.
        // If a userId column is added to 'payments' table, uncomment and adjust:
        // return $this->hasMany(Payment::class, 'userId', 'userId');
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
