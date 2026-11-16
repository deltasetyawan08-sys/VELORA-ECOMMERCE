@extends('layouts.admin')

@section('title', 'Edit Kategori — VELORA Admin')
@section('header', 'Edit Kategori')

@section('content')
    @include('admin.categories._form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PATCH',
        'category' => $category,
    ])
@endsection
