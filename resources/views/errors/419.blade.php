@extends('errors.layout')

@section('title', 'Sesi Kedaluwarsa')
@section('code', '419')
@section('badge', '419 · Sesi Kedaluwarsa')
@section('description', 'Halaman berada dalam keadaan diam cukup lama demi keamanan. Silakan muat ulang halaman untuk memperbarui token keamanan.')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
</svg>
@endsection

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Segarkan Halaman</button>
<a href="/" class="btn btn-secondary">Kembali ke Beranda</a>
@endsection
