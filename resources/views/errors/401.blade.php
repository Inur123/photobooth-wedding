@extends('errors.layout')

@section('title', 'Perlu Masuk')
@section('code', '401')
@section('badge', '401 · Perlu Masuk')
@section('description', 'Akses ke halaman ini membutuhkan verifikasi identitas. Silakan masuk terlebih dahulu untuk melanjutkan.')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
</svg>
@endsection

@section('actions')
<a href="/login" class="btn btn-primary">Masuk ke Akun</a>
<a href="/" class="btn btn-secondary">Kembali ke Beranda</a>
@endsection
