<div class="rounded-xl bg-cream-50 p-3" style="margin-bottom:1rem; padding:0.75rem; border:1px solid #e8ded5; border-radius:0.75rem; background:#faf7f2;">
    @if ($item->rating !== null)
        <p class="text-sm font-semibold">Penilaian Anda</p>
        <span role="img" aria-label="{{ $item->rating }} dari 5 bintang" style="font-size:1.6rem; letter-spacing:0.15rem; line-height:1.5;">
            @for ($star = 1; $star <= 5; $star++)
                <span aria-hidden="true" style="color:{{ $star <= (int) $item->rating ? '#b77b12' : '#aaa29b' }}">{{ $star <= (int) $item->rating ? '★' : '☆' }}</span>
            @endfor
        </span>
        <span class="text-sm text-muted">{{ $item->rating }}/5 · Penilaian tersimpan</span>
    @else
        <form method="POST" action="{{ route('orders.items.rate', [$order, $item]) }}" x-data="{ rating: 0, hover: 0 }">
            @csrf
            <fieldset style="border:0; padding:0; margin:0; min-width:0;">
                <legend class="text-sm font-semibold">Beri Penilaian Produk</legend>
                <p class="text-xs text-muted" style="margin-top:0.25rem;">{{ $item->product_name }} · Pilih jumlah bintang</p>
                <div style="display:flex; align-items:center; flex-wrap:wrap; gap:0.4rem; margin:0.6rem 0;" @mouseleave="hover = 0">
                    @for ($star = 1; $star <= 5; $star++)
                        <label style="position:relative; display:inline-flex; align-items:center; justify-content:center; width:2rem; height:2rem; cursor:pointer;" @mouseenter="hover = {{ $star }}">
                            <input type="radio" name="rating" value="{{ $star }}" aria-label="{{ $star }} bintang" required x-model.number="rating" style="position:absolute; inset:0; width:100%; height:100%; opacity:0; cursor:pointer;" class="peer">
                            <span aria-hidden="true" class="peer-focus-visible:ring-2" style="font-size:1.8rem; line-height:1; pointer-events:none; color:#aaa29b;" :style="(hover || rating) >= {{ $star }} ? 'color:#b77b12' : 'color:#aaa29b'" x-text="(hover || rating) >= {{ $star }} ? '★' : '☆'">☆</span>
                        </label>
                    @endfor
                    <span class="text-xs text-muted" aria-live="polite" x-text="rating ? rating + '/5' : 'Belum dipilih'">Belum dipilih</span>
                </div>
                <button type="submit" class="btn-primary btn-sm" style="margin-top:0.2rem;">Kirim Penilaian</button>
                <p class="text-xs text-muted" style="margin-top:0.4rem;">Satu penilaian per barang yang dipesan. Penilaian tidak dapat diubah setelah dikirim.</p>
            </fieldset>
        </form>
    @endif
</div>
