<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon; 

class OneTimePassword extends Notification
{
    use Queueable;

    protected int $otp;
    protected User $user;
    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $this->otp = rand(100000, 999999);
        $this->user->update([
            'otp' => $this->otp,
            'otp_expires_at' => Carbon::now()->addMinutes(3),
        ]);

        return (new MailMessage)
                    ->subject(__('mail.one_time_password.subject'))
                    ->greeting(__('mail.one_time_password.greeting'))
                    ->line('')
                    ->line(__('mail.one_time_password.content'))
                    ->line(__('mail.one_time_password.otp', ['otp' => $this->otp]))
                    ->line(__('mail.one_time_password.warning'))
                    ->line(__('mail.one_time_password.outro'))
                    ->line('');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
