<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Validasi
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut berisi pesan kesalahan standar yang digunakan oleh
    | kelas validator. Diformulasikan agar mudah dipahami, sopan, dan jelas bagi pengguna.
    |
    */

    'accepted' => ':attribute harus disetujui.',
    'accepted_if' => ':attribute harus disetujui bila :other bernilai :value.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':attribute tidak valid.',
    'array' => ':attribute harus berupa himpunan data (array).',
    'array_keys' => ':attribute harus memuat entri untuk: :values.',
    'ascii' => ':attribute hanya boleh berisi karakter alfanumerik dan simbol single-byte.',
    'base64' => ':attribute harus berupa teks Base64 yang valid.',
    'before' => ':attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute harus memiliki antara :min dan :max butir.',
        'file' => 'Ukuran :attribute harus antara :min dan :max kilobita.',
        'numeric' => ':attribute harus bernilai antara :min dan :max.',
        'string' => ':attribute harus berisi antara :min dan :max karakter.',
    ],
    'boolean' => ':attribute harus bernilai benar atau salah.',
    'can' => ':attribute mengandung nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => ':attribute kehilangan nilai yang wajib disertakan.',
    'current_password' => 'Kata sandi saat ini tidak sesuai.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'date_equals' => ':attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':attribute harus sesuai format :format.',
    'decimal' => ':attribute harus memiliki :decimal tempat desimal.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak bila :other bernilai :value.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus terdiri dari :digits digit angka.',
    'digits_between' => ':attribute harus terdiri dari :min sampai :max digit.',
    'dimensions' => 'Ukuran dimensi gambar :attribute tidak valid.',
    'distinct' => ':attribute memiliki nilai duplikat.',
    'doesnt_contain' => ':attribute tidak boleh memuat salah satu dari: :values.',
    'doesnt_end_with' => ':attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => ':attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'encoding' => ':attribute harus di-encode dengan format :encoding.',
    'ends_with' => ':attribute harus diakhiri salah satu dari berikut: :values.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid atau tidak ditemukan.',
    'extensions' => ':attribute harus memiliki salah satu ekstensi: :values.',
    'file' => ':attribute harus berupa berkas (file).',
    'filled' => ':attribute wajib memiliki nilai.',
    'gt' => [
        'array' => ':attribute harus memiliki lebih dari :value butir.',
        'file' => 'Ukuran :attribute harus lebih besar dari :value kilobita.',
        'numeric' => ':attribute harus bernilai lebih dari :value.',
        'string' => ':attribute harus lebih panjang dari :value karakter.',
    ],
    'gte' => [
        'array' => ':attribute minimal harus memiliki :value butir.',
        'file' => 'Ukuran :attribute minimal harus :value kilobita.',
        'numeric' => ':attribute minimal bernilai :value.',
        'string' => ':attribute minimal terdiri dari :value karakter.',
    ],
    'hex_color' => ':attribute harus berupa kode warna heksadesimal yang valid (contoh: #E11D48).',
    'image' => ':attribute harus berupa berkas gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => ':attribute harus terdapat dalam :other.',
    'in_array_keys' => ':attribute harus memuat setidaknya salah satu kunci: :values.',
    'integer' => ':attribute harus berupa angka bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa teks JSON yang valid.',
    'list' => ':attribute harus berupa daftar (list).',
    'lowercase' => ':attribute harus menggunakan huruf kecil.',
    'lt' => [
        'array' => ':attribute harus memiliki kurang dari :value butir.',
        'file' => 'Ukuran :attribute harus lebih kecil dari :value kilobita.',
        'numeric' => ':attribute harus bernilai kurang dari :value.',
        'string' => ':attribute harus lebih pendek dari :value karakter.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :value butir.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :value kilobita.',
        'numeric' => ':attribute tidak boleh bernilai lebih dari :value.',
        'string' => ':attribute tidak boleh lebih panjang dari :value karakter.',
    ],
    'mac_address' => ':attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :max butir.',
        'file' => 'Ukuran :attribute maksimal :max kilobita.',
        'numeric' => ':attribute maksimal bernilai :max.',
        'string' => ':attribute maksimal :max karakter.',
    ],
    'max_digits' => ':attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes' => ':attribute harus berupa berkas dengan format: :values.',
    'mimetypes' => ':attribute harus berupa berkas dengan tipe: :values.',
    'min' => [
        'array' => ':attribute minimal harus memiliki :min butir.',
        'file' => 'Ukuran :attribute minimal :min kilobita.',
        'numeric' => ':attribute minimal bernilai :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'min_digits' => ':attribute minimal harus memiliki :min digit.',
    'missing' => ':attribute tidak boleh disertakan.',
    'missing_if' => ':attribute tidak boleh disertakan bila :other bernilai :value.',
    'missing_unless' => ':attribute tidak boleh disertakan kecuali jika :other bernilai :value.',
    'missing_with' => ':attribute tidak boleh disertakan ketika :values ada.',
    'missing_with_all' => ':attribute tidak boleh disertakan ketika :values ada.',
    'multiple_of' => ':attribute harus merupakan kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => [
        'letters' => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':attribute harus mengandung kombinasi huruf besar dan huruf kecil.',
        'numbers' => ':attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':attribute harus mengandung setidaknya satu simbol atau tanda baca.',
        'uncompromised' => ':attribute yang dimasukkan pernah terindikasi bocor dalam kebocoran data publik. Silakan pilih kombinasi lain demi keamanan.',
    ],
    'present' => ':attribute harus ada.',
    'present_if' => ':attribute harus ada bila :other bernilai :value.',
    'present_unless' => ':attribute harus ada kecuali jika :other bernilai :value.',
    'present_with' => ':attribute harus ada ketika :values ada.',
    'present_with_all' => ':attribute harus ada ketika :values ada.',
    'prohibited' => ':attribute dilarang disertakan.',
    'prohibited_if' => ':attribute dilarang disertakan bila :other bernilai :value.',
    'prohibited_if_accepted' => ':attribute dilarang disertakan bila :other disetujui.',
    'prohibited_if_declined' => ':attribute dilarang disertakan bila :other ditolak.',
    'prohibited_unless' => ':attribute dilarang disertakan kecuali jika :other ada dalam :values.',
    'prohibits' => ':attribute melarang :other untuk disertakan.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'required_array_keys' => ':attribute harus memuat entri untuk: :values.',
    'required_if' => ':attribute wajib diisi bila :other bernilai :value.',
    'required_if_accepted' => ':attribute wajib diisi bila :other disetujui.',
    'required_if_declined' => ':attribute wajib diisi bila :other ditolak.',
    'required_unless' => ':attribute wajib diisi kecuali jika :other ada dalam :values.',
    'required_with' => ':attribute wajib diisi ketika :values diisi.',
    'required_with_all' => ':attribute wajib diisi ketika :values diisi.',
    'required_without' => ':attribute wajib diisi ketika :values tidak diisi.',
    'required_without_all' => ':attribute wajib diisi ketika tidak ada satu pun dari :values yang diisi.',
    'same' => ':attribute dan :other harus sama.',
    'size' => [
        'array' => ':attribute harus memuat :size butir.',
        'file' => 'Ukuran :attribute harus sebesar :size kilobita.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus berukuran :size karakter.',
    ],
    'starts_with' => ':attribute harus diawali dengan salah satu dari: :values.',
    'string' => ':attribute harus berupa teks.',
    'timezone' => ':attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute ini sudah terdaftar. Silakan gunakan yang lain.',
    'uploaded' => ':attribute gagal diunggah.',
    'uppercase' => ':attribute harus menggunakan huruf kapital.',
    'url' => 'Format tautan :attribute tidak valid.',
    'ulid' => ':attribute harus berupa ULID yang valid.',
    'uuid' => ':attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Kustom Pesan Validasi
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'current_password' => [
            'required' => 'Kata sandi saat ini wajib diisi.',
            'current_password' => 'Kata sandi saat ini tidak sesuai.',
        ],
        'password' => [
            'required' => 'Kata sandi wajib diisi.',
            'confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'min' => 'Kata sandi minimal harus :min karakter.',
        ],
        'password_confirmation' => [
            'required' => 'Konfirmasi kata sandi wajib diisi.',
        ],
        'email' => [
            'required' => 'Email wajib diisi.',
            'email' => 'Format email tidak valid.',
            'unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
        ],
        'name' => [
            'required' => 'Nama wajib diisi.',
            'max' => 'Nama maksimal :max karakter.',
        ],
        'party_one_name' => [
            'required' => 'Nama mempelai pertama wajib diisi.',
        ],
        'package_id' => [
            'required' => 'Silakan pilih paket acara.',
        ],
        'category' => [
            'required' => 'Silakan pilih kategori acara.',
        ],
        'guest_name' => [
            'required' => 'Nama tamu wajib diisi.',
        ],
        'message' => [
            'required' => 'Pesan ucapan wajib diisi.',
        ],
        'file' => [
            'required' => 'Silakan pilih atau unggah berkas terlebih dahulu.',
            'max' => 'Ukuran berkas melebihi batas yang diizinkan.',
            'mimetypes' => 'Format berkas belum didukung.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kustom Nama Atribut
    |--------------------------------------------------------------------------
    |
    | Baris berikut digunakan untuk menukar placeholder atribut dengan teks yang
    | lebih ramah dibaca pengguna, misalnya "kata sandi saat ini" daripada "current_password".
    |
    */

    'attributes' => [
        'current_password' => 'kata sandi saat ini',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'email' => 'email',
        'name' => 'nama',
        'party_one_name' => 'nama mempelai pertama',
        'party_two_name' => 'nama mempelai kedua',
        'event_date' => 'tanggal acara',
        'venue' => 'lokasi acara',
        'notes' => 'catatan / pesan acara',
        'gallery_visibility' => 'visibilitas galeri',
        'status' => 'status acara',
        'category' => 'kategori acara',
        'package_id' => 'paket acara',
        'theme_id' => 'tema acara',
        'custom_colors' => 'warna kustom',
        'custom_colors.primary' => 'warna utama',
        'custom_colors.accent' => 'warna aksen',
        'custom_colors.neutral' => 'warna netral',
        'cover' => 'foto sampul',
        'user_id' => 'pengguna',
        'guest_name' => 'nama tamu',
        'message' => 'pesan ucapan',
        'type' => 'jenis kiriman',
        'file' => 'berkas',
        'duration_seconds' => 'durasi rekaman',
        'layout' => 'layout foto',
        'visibility' => 'akses visibilitas',
        'frame_id' => 'bingkai foto',
        'price' => 'harga',
        'tagline' => 'tagline paket',
    ],

];
