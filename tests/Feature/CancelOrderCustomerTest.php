<?php

use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Vendor;

class CancelOrderCustomerTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    /** @test */
    public function test_user_can_cancel_existing_order()
    {
       
        // dump($user);

        $order = Order::where('isCancelled', false)->first();
        
        $iduser = $order->userId;

        $user = User::find($iduser);
        dump($user->wellpay);
        $this->actingAs($user);

        $wellpay = $user->wellpay;
        $this->assertNotNull($order);
        dump($order->totalPrice);

        $totalPrice = $order->totalPrice;

        $corectWellpay = $wellpay + $totalPrice;
        dump($corectWellpay);


        $response = $this->put(route('order.cancel', ['id' => $order->orderId]));
        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'orderId' => $order->orderId,
            'isCancelled' => true,
        ]);

        $user->refresh();
        $this->assertEquals($corectWellpay, $user->wellpay);
    }
}
