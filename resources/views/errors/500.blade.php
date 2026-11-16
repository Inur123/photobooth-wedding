@extends('errors.layout')

@section('title', 'Gangguan Server')
@section('code', '500')
@section('badge', '500 · Gangguan Server')
@section('description', 'Ada kendala teknis di balik layar. Tim pengembang kami telah mencatat insiden ini dan sedang memperbaikinya.')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
</svg>
@endsection

@section('actions')
<button type="button" onclick="window.location.reload()" class="btn btn-primary">Muat Ulang Halaman</button>
<a href="/" class="btn btn-secondary">Kembali ke Beranda</a>
@endsection
