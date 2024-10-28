<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationToUserForAccountUpgrade extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $user;

    public $package;

    public function __construct($user, $subscription_info)
    {
        $this->user = $user;
        $this->subscription_info = $subscription_info;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Hello!')
            ->subject('Your account has been upgraded')
            ->line('Your account has been upgraded with the following packages')
            ->line('Package: '.$this->subscription_info->package_details['name'])
            ->line('Valid Till: '.\Carbon\Carbon::parse($this->subscription_info['end_date'])->isoFormat('D/MM/YYYY'))
            ->line('Description: '.$this->subscription_info->package_details['description']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
