<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use SensitiveParameter;

class AdminLoginOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        #[SensitiveParameter]
        public string $code,
        public int $codeExpiryMinutes = 10,
    ) {}

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminName = $notifiable->name ?? 'Admin';

        return (new MailMessage)
            ->subject('🔐 [' . config('app.name', 'PeliCle') . '] Admin Login OTP Code: ' . $this->code)
            ->greeting("Hello {$adminName},")
            ->line('You are receiving this email because a sign-in attempt was made to the **PeliCle CSU Lallo GSO Admin Portal**.')
            ->line(new HtmlString('
                <div style="text-align: center; margin: 30px 0;">
                    <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 2px; color: #64748b; margin-bottom: 8px; font-weight: 600;">Your One-Time Passcode</div>
                    <span style="display: inline-block; font-size: 34px; font-weight: 800; letter-spacing: 8px; padding: 14px 32px; background: #0f172a; color: #f59e0b; border-radius: 12px; border: 2px solid #f59e0b; font-family: monospace; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);">
                        ' . $this->code . '
                    </span>
                </div>
            '))
            ->line("This verification code is valid for **{$this->codeExpiryMinutes} minutes**. Enter this code on your device (Phone or PC) to proceed.")
            ->line('⚠️ **Security Notice:** If you did not attempt to log in to the GSO Admin portal, someone else may be trying to access your account. Please change your password immediately.')
            ->salutation("Regards,\nCSU Lallo General Services Office (GSO)\nPeliCle Fleet Management System");
    }
}
