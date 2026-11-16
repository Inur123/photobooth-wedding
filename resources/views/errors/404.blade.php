@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('badge', '404 · Halaman Tidak Ditemukan')
@section('description', 'Momen yang Anda cari belum ada di sini. Tautan mungkin salah ketik, sudah kedaluwarsa, atau album acara telah dipindahkan.')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 15.75-2.489-2.489m0 0a3.375 3.375 0 1 0-4.773-4.773 3.375 3.375 0 0 0 4.774 4.774ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
</svg>
@endsection

@section('actions')
<a href="/" class="btn btn-primary">Kembali ke Beranda</a>
<button type="button" onclick="history.back()" class="btn btn-secondary">Kembali Sebelumnya</button>
@endsection
