<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class CustomerSubscribed extends Notification
{
    use Queueable;
    protected Order $order;
    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/manageOrder');
        return (new MailMessage)
                    ->subject(__('mail.customer_subscribed.subject', ['order_id' => $this->order->orderId]))
                    ->greeting(__('mail.customer_subscribed.greeting'))
                    ->line(__('mail.customer_subscribed.order_placed', ['order_id' => $this->order->orderId]))
                    ->line(__('mail.customer_subscribed.check_order_invitation'))
                    ->action(__('mail.customer_subscribed.view_order'), $url)
                    ->line(__('mail.customer_subscribed.outro'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->order->vendorId,
            'user_id' => $this->order->userId,
            'order_id' => $this->order->orderId
        ];
    }
}
