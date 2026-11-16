<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureVerificationEmail();

        if (! app()->isProduction()) {
            Model::preventLazyLoading();
        }
    }

    /**
     * Isi email verifikasi dan lupa kata sandi dalam Bahasa Indonesia.
     */
    protected function configureVerificationEmail(): void
    {
        VerifyEmail::toMailUsing(fn (object $notifiable, string $url) => (new MailMessage)
            ->subject('Verifikasi email akun titip.moment kamu')
            ->greeting('Halo!')
            ->line('Terima kasih sudah mendaftar di titip.moment. Klik tombol di bawah untuk memverifikasi alamat email kamu.')
            ->action('Verifikasi Email', $url)
            ->line('Tautan ini berlaku selama 60 menit.')
            ->line('Kalau kamu tidak merasa mendaftar, abaikan saja email ini.')
            ->salutation('Salam hangat, tim titip.moment'));

        ResetPassword::toMailUsing(fn (CanResetPassword $notifiable, string $token) => (new MailMessage)
            ->subject('Atur ulang kata sandi titip.moment')
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun kamu.')
            ->action('Atur Ulang Kata Sandi', url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)))
            ->line('Tautan ini berlaku selama '.config('auth.passwords.users.expire').' menit.')
            ->line('Kalau kamu tidak meminta pengaturan ulang, abaikan saja email ini. Kata sandimu tetap aman.')
            ->salutation('Salam hangat, tim titip.moment'));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
