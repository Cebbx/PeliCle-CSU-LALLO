<?php

namespace App\Filament\Auth\MultiFactor;

use Closure;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use LogicException;
use SensitiveParameter;

class CustomEmailAuthentication extends EmailAuthentication
{
    public function isEnabled(Authenticatable $user): bool
    {
        // Allow disabling via environment variable if needed
        if (! config('auth.admin_otp_enabled', env('ADMIN_OTP_ENABLED', true))) {
            return false;
        }

        return parent::isEnabled($user);
    }

    public function sendCode(HasEmailAuthentication $user): bool
    {
        if (! ($user instanceof Model)) {
            throw new LogicException('The [' . $user::class . '] class must be an instance of [' . Model::class . '] to use email authentication.');
        }

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;
            throw new LogicException("Model [{$userClass}] does not have a [notify()] method.");
        }

        $rateLimitingKey = "filament-email-authentication:{$user->getKey()}";

        if (RateLimiter::tooManyAttempts($rateLimitingKey, maxAttempts: 5)) {
            return false;
        }

        RateLimiter::hit($rateLimitingKey, decaySeconds: 60);

        $code = $this->generateCode();
        $codeExpiryMinutes = $this->getCodeExpiryMinutes();

        session()->put($this->getCodeSessionKey($user), Hash::make($code));
        session()->put($this->getCodeExpirySessionKey($user), now()->addMinutes($codeExpiryMinutes));

        // Always log OTP for troubleshooting / audit logs
        Log::info("🔐 [PeliCle Admin OTP] Generated code for {$user->email}: {$code} (Expires in {$codeExpiryMinutes} mins)");

        try {
            $user->notify(app($this->getCodeNotification(), [
                'code' => $code,
                'codeExpiryMinutes' => $codeExpiryMinutes,
            ]));
        } catch (\Throwable $e) {
            Log::error("Failed to send Admin OTP email: " . $e->getMessage());

            if (config('app.debug')) {
                Notification::make()
                    ->title('Demo / Testing OTP Code')
                    ->body("Email sending failed or in debug mode. Login code: {$code}")
                    ->warning()
                    ->persistent()
                    ->send();
            }
        }

        return true;
    }

    /**
     * @param  Authenticatable&HasEmailAuthentication  $user
     */
    public function getChallengeFormComponents(Authenticatable $user): array
    {
        $rawEmail = $user->email ?? '';
        $maskedEmail = $this->maskEmail($rawEmail);

        return [
            OneTimeCodeInput::make('code')
                ->label('Verification Code')
                ->helperText("A 6-digit verification code has been sent to {$maskedEmail}. Please enter the code from your email.")
                ->validationAttribute('code')
                ->belowContent(
                    Action::make('resend')
                        ->label('Resend verification code')
                        ->link()
                        ->action(function () use ($user): void {
                            if (! $this->sendCode($user)) {
                                Notification::make()
                                    ->title('Please wait a moment before requesting another code.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('A new verification code has been sent to your email!')
                                ->success()
                                ->send();
                        })
                )
                ->required()
                ->rule(function () use ($user): Closure {
                    return function (string $attribute, #[SensitiveParameter] $value, Closure $fail) use ($user): void {
                        if (is_string($value) && $this->verifyCode($value, $user)) {
                            return;
                        }

                        $fail('The verification code you entered is invalid or has expired.');
                    };
                }),
        ];
    }

    protected function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', min(4, $len - 2)) . substr($name, -1);
        }

        return "{$maskedName}@{$domain}";
    }
}
