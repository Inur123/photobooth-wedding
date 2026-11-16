<?php

namespace App\Http\Requests;

use App\Enums\ContributionType;
use App\Models\Contribution;
use App\Models\Event;
use App\Support\ContentFilter;
use App\Support\TextSanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $event->isOpen();
    }

    protected function prepareForValidation(): void
    {
        // Sanitasi teks — handle null safely
        if ($this->has('guest_name') && $this->input('guest_name') !== null) {
            $this->merge(['guest_name' => TextSanitizer::clean((string) $this->input('guest_name'))]);
        }
        if ($this->has('message') && $this->input('message') !== null) {
            $this->merge(['message' => TextSanitizer::clean((string) $this->input('message'))]);
        }
        // Honeypot: isi diam-diam dengan nilai tidak valid agar validasi gagal tanpa respons khusus
        if ($this->filled('_hp')) {
            $this->merge(['guest_name' => str_repeat('x', 999)]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Event $event */
        $event = $this->route('event');
        $event->loadMissing('package');
        $package = $event->package;

        $allowedTypes = $package ? $package->allowedContributionTypes() : ['photo', 'voice', 'wish'];
        $allowedLayouts = $package ? $package->allowedPhotoLayouts() : [1];
        $maxVideoKb = max(1, $package ? $package->resolvedVideoMaxKb() : 15360);
        $maxVideoSeconds = max(1, $package ? $package->resolvedVideoMaxSeconds() : 20);

        $rules = [
            'type' => ['required', Rule::enum(ContributionType::class), Rule::in($allowedTypes)],
            'guest_name' => ['required', 'string', 'max:60'],
            'visibility' => ['required', 'in:public,private'],
            'message' => ['nullable', 'string', 'max:500'],
            'frame_id' => ['nullable', Rule::in([])],
            '_hp' => ['sometimes', 'max:0'],
            '_ts' => ['sometimes', 'integer'],
        ];

        return array_merge($rules, match ($this->input('type')) {
            ContributionType::Photo->value => [
                'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096'],
                'layout' => ['required', 'integer', Rule::in($allowedLayouts)],
            ],
            ContributionType::Video->value => [
                'file' => ['required', 'file', 'mimetypes:video/webm,video/mp4,video/quicktime', 'max:'.$maxVideoKb],
                'duration_seconds' => ['required', 'integer', 'min:1', 'max:'.$maxVideoSeconds],
            ],
            ContributionType::Voice->value => [
                'file' => ['required', 'file', 'mimetypes:audio/webm,audio/mpeg,audio/mp4,audio/x-m4a,audio/ogg,audio/wav,audio/x-wav,video/webm', 'max:5120'],
                'duration_seconds' => ['required', 'integer', 'min:1', 'max:60'],
            ],
            ContributionType::Wish->value => [
                'message' => ['required', 'string', 'max:500'],
            ],
            default => [],
        });
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            // Timestamp check: form submitted too fast
            if ($this->has('_ts') && (time() - (int) $this->input('_ts')) < 3) {
                $v->errors()->add('guest_name', 'Isi form terlalu cepat. Silakan coba lagi.');
            }

            // Link detection in message
            if (ContentFilter::containsLink($this->input('message', ''))) {
                $v->errors()->add('message', 'Ucapan tidak boleh berisi link atau URL.');
            }

            // Bad word in guest_name
            if (ContentFilter::containsBadWord($this->input('guest_name', ''))) {
                $v->errors()->add('guest_name', 'Nama mengandung kata yang kurang pantas.');
            }

            // Bad word in message
            if (ContentFilter::containsBadWord($this->input('message', ''))) {
                $v->errors()->add('message', 'Ucapan mengandung kata yang kurang pantas. Yuk, diperhalus dulu.');
            }

            // Duplicate submission check — hanya untuk ucapan (wish) dengan pesan identik
            // Video/voice/photo tidak di-block karena tamu bisa kirim lebih dari satu
            if ($v->errors()->isEmpty() && $this->input('type') === ContributionType::Wish->value) {
                /** @var Event $event */
                $event = $this->route('event');

                $recent = Contribution::where('event_id', $event->id)
                    ->where('guest_name', $this->input('guest_name'))
                    ->where('type', ContributionType::Wish->value)
                    ->where('message', $this->input('message'))
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->first();

                if ($recent !== null) {
                    $v->errors()->add('message', 'Ucapan yang sama sudah pernah dikirim. Tunggu sebentar ya.');
                }
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'type.in' => 'Jenis kiriman ini belum tersedia pada paket acara.',
            'guest_name.required' => 'Isi namamu sebelum menitipkan momen.',
            'guest_name.max' => 'Nama maksimal 60 karakter.',
            'visibility.required' => 'Pilih siapa yang dapat melihat kirimanmu.',
            'file.required' => 'Ambil atau pilih file terlebih dahulu.',
            'file.mimetypes' => 'Format file belum didukung. Pilih format yang tercantum di bawah tombol unggah.',
            'file.max' => 'Ukuran file melebihi batas. Pilih file yang lebih kecil.',
            'duration_seconds.required' => 'Durasi rekaman wajib disertakan.',
            'layout.in' => 'Layout foto ini tidak tersedia untuk paket acara.',
            'frame_id.in' => 'Bingkai kustom tidak digunakan pada acara ini.',
            'message.required' => 'Tulis ucapanmu terlebih dahulu.',
            'message.max' => 'Ucapan atau cerita maksimal 500 karakter.',
            'duration_seconds.max' => 'Durasi rekaman melebihi batas paket acara.',
        ];
    }
}
