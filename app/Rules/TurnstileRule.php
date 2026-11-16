<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class TurnstileRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = (string) config('services.turnstile.secret_key');

        // Jika Turnstile tidak dikonfigurasi, lewati verifikasi
        if ($secretKey === '') {
            return;
        }

        if (empty($value) || ! is_string($value)) {
            $fail('Verifikasi keamanan Cloudflare Turnstile wajib dilengkapi.');

            return;
        }

        // Penanganan ramah offline saat menjalankan unit/feature test
        if (app()->environment('testing')) {
            if ($value === 'invalid-token' || $value === 'fail') {
                $fail('Verifikasi keamanan Cloudflare Turnstile gagal. Silakan coba lagi.');
            }

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->successful() || $response->json('success') !== true) {
                $fail('Verifikasi keamanan Cloudflare Turnstile gagal. Silakan coba lagi.');
            }
        } catch (\Throwable) {
            $fail('Gagal menghubungi server verifikasi Cloudflare. Silakan coba lagi.');
        }
    }
}
