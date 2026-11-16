@php($product = $product ?? null)

@if ($errors->any())
    <div class="alert-error mb-4 text-sm">
        <p class="font-semibold">Periksa kembali data berikut:</p>
        <ul class="mt-1 list-inside list-disc text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="max-w-5xl space-y-5"
      x-data="{
          variants: {{ \Illuminate\Support\Js::from($variantRows) }},
          imagePreview: null,
          addVariant() {
              this.variants.push({ id: null, size: '', color: '', sku: '', stock: 0, is_active: true });
          },
          removeVariant(index) {
              if (this.variants.length < 2) return;
              this.variants.splice(index, 1);
          },
          previewImage(event) {
              const file = event.target.files[0];
              this.imagePreview = file ? URL.createObjectURL(file) : null;
          }
      }">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-sm font-semibold">Informasi Produk</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label">Nama produk</label>
                <input id="name" name="name" type="text" value="{{ old('name', $product?->name) }}" required class="input @error('name') border-danger @enderror" placeholder="Contoh: Amara Linen Blouse">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category_id" class="label">Kategori</label>
                <select id="category_id" name="category_id" required class="input @error('category_id') border-danger @enderror">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product?->category_id) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-end">
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line bg-cream-50 px-4 py-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-line text-burgundy-700 focus:ring-burgundy-300" @checked(old('is_active', $product?->is_active ?? true))>
                    <span class="text-sm">
                        <span class="font-medium">Tampilkan di katalog</span>
                        <span class="block text-[11px] text-muted">Produk nonaktif tidak dapat dibeli.</span>
                    </span>
                </label>
            </div>

            <div>
                <label for="price" class="label">Harga jual (Rp)</label>
                <input id="price" name="price" type="number" min="1000" step="1" value="{{ old('price', $product?->price) }}" required class="input @error('price') border-danger @enderror" placeholder="199000">
                @error('price')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="compare_price" class="label">Harga coret (opsional)</label>
                <input id="compare_price" name="compare_price" type="number" min="0" step="1" value="{{ old('compare_price', $product?->compare_price) }}" class="input @error('compare_price') border-danger @enderror" placeholder="299000">
                @error('compare_price')<p class="field-error">{{ $message }}</p>@enderror
                <p class="mt-1 text-[11px] text-muted">Harus lebih besar dari harga jual untuk tampil sebagai diskon.</p>
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">Deskripsi produk</label>
                <textarea id="description" name="description" rows="5" required class="input @error('description') border-danger @enderror" placeholder="Bahan, ukuran, detail produk...">{{ old('description', $product?->description) }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-sm font-semibold">Foto Produk</h2>
        <div class="mt-4 flex flex-wrap items-center gap-5">
            <span class="flex h-28 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-cream-100">
                <template x-if="imagePreview">
                    <img :src="imagePreview" alt="Pratinjau gambar" class="h-full w-full object-cover">
                </template>
                <template x-if="! imagePreview">
                    <span class="flex h-full w-full items-center justify-center overflow-hidden">
                        @if ($product?->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-[10px] font-semibold text-burgundy-700">VELORA</span>
                        @endif
                    </span>
                </template>
            </span>
            <div class="flex-1">
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @change="previewImage($event)" class="block w-full text-xs text-muted file:mr-3 file:rounded-full file:border-0 file:bg-cream-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-burgundy-700 hover:file:bg-cream-200">
                <p class="mt-1.5 text-[11px] text-muted">
                    JPEG, PNG, atau WebP maksimal 2 MB. Rasio potret 3:4 direkomendasikan.
                    @if ($product?->image_url)
                        Unggah file baru untuk mengganti gambar saat ini.
                    @endif
                </p>
                @error('image')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold">Varian &amp; Stok</h2>
                <p class="mt-1 text-[11px] text-muted">Kombinasi ukuran dan warna harus unik. SKU juga harus unik di seluruh produk.</p>
            </div>
            <button type="button" class="btn-soft btn-sm" @click="addVariant()">+ Tambah Varian</button>
        </div>

        <div class="mt-4 space-y-3">
            <template x-for="(variant, index) in variants" :key="index">
                <div class="rounded-xl border border-line bg-cream-50 p-3">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.1fr_6rem_7.5rem_auto] lg:items-end">
                        <input type="hidden" :name="'variants[' + index + '][id]'" :value="variant.id ?? ''">

                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-muted">Ukuran</span>
                            <input type="text" :name="'variants[' + index + '][size]'" :value="variant.size" @input="variant.size = $event.target.value" required maxlength="30" class="input input-sm" placeholder="S / M / L / All Size">
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-muted">Warna</span>
                            <input type="text" :name="'variants[' + index + '][color]'" :value="variant.color" @input="variant.color = $event.target.value" required maxlength="50" class="input input-sm" placeholder="Burgundy">
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-muted">SKU</span>
                            <input type="text" :name="'variants[' + index + '][sku]'" :value="variant.sku" @input="variant.sku = $event.target.value" required maxlength="80" class="input input-sm" placeholder="VLR-001-M-BRG">
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-muted">Stok</span>
                            <input type="number" min="0" step="1" :name="'variants[' + index + '][stock]'" :value="variant.stock" @input="variant.stock = $event.target.value" required class="input input-sm">
                        </label>

                        <label class="flex items-center gap-2 pb-1.5">
                            <input type="hidden" :name="'variants[' + index + '][is_active]'" value="0">
                            <input type="checkbox" :name="'variants[' + index + '][is_active]'" value="1" class="h-4 w-4 rounded border-line text-burgundy-700 focus:ring-burgundy-300" :checked="variant.is_active" @change="variant.is_active = $event.target.checked">
                            <span class="text-[11px] font-medium text-muted">Aktif</span>
                        </label>

                        <button type="button" class="btn-ghost btn-sm justify-center text-danger hover:bg-red-50" @click="removeVariant(index)" :disabled="variants.length < 2" :class="variants.length < 2 ? 'cursor-not-allowed opacity-40' : ''">
                            Hapus
                        </button>
                    </div>
                </div>
            </template>
        </div>

        @error('variants')<p class="field-error">{{ $message }}</p>@enderror
        @error('variants.*')<p class="field-error">{{ $message }}</p>@enderror
        <p class="mt-3 text-[11px] text-muted">Menghapus varian yang sudah pernah dipesan hanya akan menonaktifkannya agar riwayat pesanan tetap utuh.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="submit" class="btn-primary">{{ $product ? 'Simpan Perubahan' : 'Simpan Produk' }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn-soft">Batal</a>
    </div>
</form>

@if ($product)
    <div class="mt-6 max-w-5xl rounded-2xl border border-red-200 bg-red-50 p-5">
        <h2 class="text-sm font-semibold text-danger">Zona Berbahaya</h2>
        <p class="mt-1 text-xs text-red-700">Menghapus produk akan menghilangkannya dari katalog. Riwayat pesanan tetap tersimpan dan varian dinonaktifkan.</p>
        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus produk {{ $product->name }}? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger btn-sm">Hapus Produk</button>
        </form>
    </div>
@endif
