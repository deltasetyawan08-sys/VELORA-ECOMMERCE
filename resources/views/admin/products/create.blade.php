@extends('layouts.admin')

@section('title', 'Tambah Produk — VELORA Admin')
@section('header', 'Tambah Produk')

@section('content')
    @include('admin.products._form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'product' => null,
    ])
@endsection
