@extends('layouts.admin')

@section('title', 'Pelanggan — VELORA Admin')
@section('header', 'Manajemen Pelanggan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted">Daftar akun pelanggan VELORA beserta jumlah pesanan mereka.</p>
        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau email..." class="input input-sm w-56">
            <button type="submit" class="btn-soft btn-sm">Cari</button>
            @if ($search)
                <a href="{{ route('admin.customers.index') }}" class="btn-ghost btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <div class="mt-5 rounded-2xl border border-line bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Telepon</th>
                        <th>Pesanan</th>
                        <th>Terdaftar</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-burgundy-50 text-xs font-bold text-burgundy-700">{{ initials($customer->name) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $customer->name }}</p>
                                        <p class="truncate text-[11px] text-muted">{{ $customer->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-muted">{{ $customer->phone ?: '—' }}</td>
                            <td><span class="badge-neutral">{{ $customer->orders_count }} pesanan</span></td>
                            <td class="text-xs text-muted">{{ $customer->created_at->format('d M Y') }}</td>
                            <td class="text-right"><a href="{{ route('admin.customers.show', $customer) }}" class="btn-soft btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-sm text-muted">Tidak ada pelanggan ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line px-5 py-3">{{ $customers->links() }}</div>
    </div>
@endsection
