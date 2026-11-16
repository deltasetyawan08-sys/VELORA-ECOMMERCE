@extends('layouts.admin')

@section('title', 'Tambah Kategori — VELORA Admin')
@section('header', 'Tambah Kategori')

@section('content')
    @include('admin.categories._form', [
        'action' => route('admin.categories.store'),
        'method' => 'POST',
        'category' => null,
    ])
@endsection
