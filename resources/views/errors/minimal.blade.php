@extends('errors.layout')

@section('title', $exception->getMessage() ?: ($title ?? 'Terjadi Kesalahan'))
@section('code', $exception->getStatusCode() ?? 'Galat')
@section('badge', ($exception->getStatusCode() ?? 'Galat') . ' · Status Sistem')
@section('description', $exception->getMessage() ?: 'Terjadi kendala saat memproses permintaan Anda. Silakan coba muat ulang atau kembali ke beranda.')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
</svg>
@endsection

@section('actions')
<a href="/" class="btn btn-primary">Kembali ke Beranda</a>
<button type="button" onclick="window.location.reload()" class="btn btn-secondary">Segarkan Halaman</button>
@endsection
