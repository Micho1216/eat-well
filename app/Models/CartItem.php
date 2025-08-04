<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'cartId',
        'packageId',
        'breakfastQty',
        'lunchQty',
        'dinnerQty',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cartId', 'cartId');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'packageId', 'packageId');
    }

    protected static function booted()
    {
        static::deleted(function (CartItem $cartItem) {
            Log::info('CartItem deleted event triggered for cartId: ' . $cartItem->cartId . ' packageId: ' . $cartItem->packageId);
            $cart = $cartItem->cart; 

            if ($cart) {
                $cart->load('cartItems');
                $remainingItemsCount = $cart->cartItems->count();

                Log::info('Cart ' . $cart->cartId . ' has ' . $remainingItemsCount . ' remaining items AFTER CartItem DELETE event.');

                if ($remainingItemsCount === 0) {
                    $cart->delete();
                    Log::info('MAIN CART ' . $cart->cartId . ' DELETED SUCCESSFULLY by CartItem event.');
                } else {
                    Log::info('MAIN CART ' . $cart->cartId . ' NOT deleted, still has ' . $remainingItemsCount . ' items.');
                }
            } else {
                Log::info('Related Cart object for CartItem ' . $cartItem->id . ' was null (maybe already deleted).');
            }
            Log::info('--- CartItem deleted event finished ---');
        });
    }
}
