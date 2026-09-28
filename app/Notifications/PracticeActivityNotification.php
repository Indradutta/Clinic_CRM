<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class PracticeActivityNotification extends Notification
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(public array $data) {}

    /**
     * Send a practice activity notification to active staff with access to its destination.
     */
    public static function sendToActiveStaff(array $data, ?string $settingKey = null): void
    {
        if ($settingKey !== null && Setting::get($settingKey, '1') !== '1') {
            return;
        }

        $recipients = User::query()
            ->with('permissions')
            ->where('status', 'active')
            ->get()
            ->filter(fn (User $user) => $user->hasPermission($data['permission']));

        NotificationFacade::send($recipients, new self($data));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }
}
