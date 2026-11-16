@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')
@section('badge', '429 · Terlalu Banyak Permintaan')
@section('description', 'Sistem menerima terlalu banyak permintaan dari perangkat Anda dalam waktu singkat. Tunggu beberapa saat sebelum mencoba kembali.')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
</svg>
@endsection

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Coba Lagi</button>
<a href="/" class="btn btn-secondary">Kembali ke Beranda</a>
@endsection
