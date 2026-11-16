@php($category = $category ?? null)

<div class="max-w-2xl">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div>
            <label for="name" class="label">Nama kategori</label>
            <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required class="input @error('name') border-danger @enderror" placeholder="Contoh: Atasan">
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
            @if ($category)
                <p class="mt-1 text-xs text-muted">Slug saat ini: <span class="font-medium text-ink">{{ $category->slug }}</span></p>
            @else
                <p class="mt-1 text-xs text-muted">Slug dibuat otomatis dari nama kategori.</p>
            @endif
        </div>

        <div>
            <label for="description" class="label">Deskripsi (opsional)</label>
            <textarea id="description" name="description" rows="3" class="input @error('description') border-danger @enderror" placeholder="Deskripsi singkat kategori">{{ old('description', $category?->description) }}</textarea>
            @error('description')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="image" class="label">Gambar kategori (opsional)</label>
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-cream-100 text-sm font-semibold text-burgundy-700">
                    @if ($category?->image_url)
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                    @else
                        {{ $category?->initials ?? 'IMG' }}
                    @endif
                </span>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-muted file:mr-3 file:rounded-full file:border-0 file:bg-cream-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-burgundy-700 hover:file:bg-cream-200">
            </div>
            <p class="mt-1.5 text-[11px] text-muted">Format JPEG, PNG, atau WebP. Maksimal 2 MB.</p>
            @error('image')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-2 border-t border-line pt-4">
            <button type="submit" class="btn-primary">Simpan Kategori</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-soft">Batal</a>
        </div>
    </form>
</div>
