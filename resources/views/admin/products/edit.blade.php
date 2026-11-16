@extends('layouts.admin')

@section('title', 'Edit Produk — VELORA Admin')
@section('header', 'Edit Produk')

@section('content')
    @include('admin.products._form', [
        'action' => route('admin.products.update', $product),
        'method' => 'PATCH',
        'product' => $product,
    ])
@endsection
