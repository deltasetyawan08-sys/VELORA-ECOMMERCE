# PROGRESS — VELORA

Dokumen ini mencatat tahapan implementasi, verifikasi lingkungan, hasil pengujian aktual, serta sisa pekerjaan. Diperbarui: **15 November 2026**.

## Ringkasan Status

- Aplikasi Laravel 13 + MySQL untuk toko fashion **VELORA** sudah diimplementasikan mengikuti `PRD.md`.
- Halaman welcome premium baru tersedia di `/`; homepage lama tetap dipertahankan di `/beranda`.
- Seluruh halaman publik, akun, katalog, keranjang, checkout, pembayaran, pesanan, invoice, dan panel admin tersedia.
- Pengujian otomatis: **48 test lulus, 233 assertion** (`php artisan test`, SQLite in-memory).
- Format kode: `vendor\bin\pint` lulus.
- Build frontend: `npm run build` lulus; `php artisan view:cache` lulus.
- **Belum diverifikasi:** transaksi Midtrans Sandbox nyata; tangkapan layar welcome desktop/mobile sudah dibuat dan metrik layout diperiksa via Chrome headless, tetapi tinjauan visual manusia dan penyimpanan screenshot ke repo belum dilakukan.

---

## 1. Verifikasi Lingkungan

| Item | Hasil |
| --- | --- |
| PHP | 8.3.35 |
| Laravel | 13.42.0 |
| MySQL | 8.0.30, database `db_VELORA`, `utf8mb4_unicode_ci` |
| Koneksi aktif | `mysql` (dikonfirmasi `php artisan about`) |
| Session/cache/queue | `database` |
| Node/npm | Node 24.13.1 / npm 11.8.0 |
| Storage publik | `public/storage` → `storage/app/public` sudah dibuat |
| Aset build | `public/build/manifest.json` ada |
| Midtrans | Paket `midtrans/midtrans-php` 2.6.2 terpasang; kredensial masih kosong |
| Git | Repositori belum diinisialisasi; belum ada commit (sesuai instruksi) |

Status migrasi: seluruh 9 migrasi `Ran`, termasuk migrasi tambahan `2026_01_01_000006_add_compare_price_to_products_table` (batch 2).

Data hasil seeder (diverifikasi langsung via MySQL):

| Data | Jumlah |
| --- | --- |
| Kategori | 6 |
| Produk | 20 (20 aktif) |
| Varian | 134 |
| Varian stok 0 | 8 |
| Pengguna | 2 (admin + customer) |

Akun demo: `admin@velora.test` dan `customer@velora.test`, password `password`.

---

## 2. Tahapan yang Sudah Diselesaikan

| Tahap | Status | Catatan |
| --- | --- | --- |
| 0. Audit awal & konfigurasi | Selesai | `.env`, MySQL, `config/midtrans.php`, `config/shop.php`, locale `id`, timezone, filesystem publik |
| 1. Database & model | Selesai | 9 migrasi, 8 model domain, 4 enum, factory, seeder 6 kategori/20 produk/134 varian, uang integer rupiah |
| 2. Autentikasi & otorisasi | Selesai | Login/registrasi/logout, registrasi selalu `customer`, middleware `role`, `CartItemPolicy`, `OrderPolicy`, profil & ganti password |
| 3. Design system & landing | Selesai | Token warna burgundy/cream, Poppins + Playfair, komponen Blade, hero, kategori dinamis, produk terbaru, terlaris/kurasi, banner, keunggulan, footer, layout responsif; landing lama sekarang di `/beranda`, welcome premium baru di `/` (tahap 11) |
| 4. Admin | Selesai | Dashboard 6 statistik + grafik + stok menipis; CRUD kategori; CRUD produk + varian + gambar; pesanan; pelanggan; profil |
| 5. Katalog & keranjang | Selesai | Pencarian, filter kategori, urutan, pagination, detail varian interaktif, keranjang dengan validasi stok |
| 6. Checkout & order | Selesai | Snapshot harga server-side, alamat tervalidasi, reservasi stok dengan row lock, kunci idempotensi checkout, buy now, riwayat & invoice |
| 7. Midtrans | Selesai (kode) | Snap token server-side, halaman pembayaran, refresh status, webhook signature + Get Status API, rekonsiliasi order pending |
| 8. Fulfillment | Selesai | Status paid-only, `processing → shipped → completed`, resi wajib saat shipped, status terlihat customer |
| 9. Pengujian & dokumentasi | Selesai | 48 test, Pint, build Vite, `view:cache`, README, dokumen ini |
| 10. Bukti Sandbox & visual | Tertunda | Butuh Client/Server Key Sandbox dan URL notifikasi publik |
| 11. Welcome page premium | Selesai | `/` memakai `WelcomeController`; `/beranda` tetap; styling terisolasi `welcome.css`; 10 foto Pexels; data katalog nyata; verifikasi layout desktop/mobile |

### Detail halaman welcome baru

- Route `/` sekarang menampilkan welcome page baru (`home`); homepage lama dipindahkan ke `/beranda` (`shop.home`) tanpa mengubah tampilannya.
- Styling terisolasi di `resources/css/welcome.css` dengan prefix `vw-` dan layout sendiri (`layouts/welcome.blade.php`), sehingga tidak mengubah halaman lama.
- Konten mengikuti urutan brief: hero editorial, Shop by Category, New Arrivals (8 produk aktif terbaru), promo, Best Seller dari transaksi `paid` (fallback Featured Collection dari 4 produk terbaru), About VELORA, Our Advantages, dan footer lengkap.
- Kategori dan produk diambil dari database; gambar kategori memakai 10 foto editorial Pexels di `public/welcome/` dengan kredit di footer.
- Header responsif: menu seluler dengan scroll lock, tombol Escape, tautan anchor yang menutup menu, dan menu akun yang menyesuaikan guest/customer/admin.
- Verifikasi: HTTP smoke semua halaman utama 200, seluruh aset gambar/JS/CSS termuat, tidak ada overflow horizontal pada 1422px dan 390px, font termuat, serta interaksi menu seluler lolos uji Chrome DevTools Protocol.

### Perbaikan penting selama finishing

- `Admin\CategoryController::destroy` kini memeriksa `products()->withTrashed()->exists()`, sehingga kategori yang hanya menyisakan produk soft-deleted tidak memicu error foreign key.
- `compare_price` ditambahkan sebagai kolom opsional (migrasi, fillable, cast, validasi `gt:price`, field form admin).
- `AppServiceProvider` aman saat user tanpa cart (`?->items()->sum(...) ?? 0`).
- Route profil admin dinamai ulang menjadi `admin.profile` (sebelumnya berisiko menjadi `admin.admin.profile`).
- Komponen CSS Tailwind v4 `.btn*`/`.badge*` ditulis tanpa `@apply` ke kelas kustom agar build berhasil.
- Plugin font bawaan (Instrument Sans) yang tidak dipakai dihapus dari `vite.config.js`; antarmuka memakai Google Fonts Poppins + Playfair Display.
- Skrip `composer test` disesuaikan agar kompatibel dengan Composer 2.4.1, dan `composer setup` kini menjalankan `migrate --seed`.
- Test memakai `withoutVite()` agar tidak bergantung manifest build.

---

## 3. Hasil Pengujian Otomatis

Perintah: `php artisan test` — **48 passed, 233 assertions**.

| Berkas | Fokus |
| --- | --- |
| `StorefrontTest` | Landing, katalog, pencarian/filter, produk nonaktif, detail varian & stok habis |
| `AuthenticationTest` | Registrasi (role tidak bisa dipilih), validasi, login valid/invalid, intended redirect, redirect admin, logout, profil & password |
| `CustomerCheckoutTest` | CRUD keranjang, kepemilikan item, checkout cart/buy-now, stok, alamat, harga client diabaikan, checkout berulang |
| `PaymentStatusTest` | Settlement, expire melepas stok sekali, duplikat settlement, larangan downgrade paid, nominal salah, konflik transaction ID, refresh tanpa kredensial |
| `MidtransWebhookTest` | Signature valid/salah, order tak dikenal, gateway belum dikonfigurasi |
| `AdminPanelTest` | Guard role, render semua halaman, statistik dashboard, CRUD kategori/produk/varian, gambar, SKU duplikat, hapus kategori berelasi, transisi status & resi, larangan memaksa paid |
| `OrderAccessTest` | Kepemilikan order/invoice/payment/refresh, snapshot setelah produk dinonaktifkan |
| `ExampleTest` | Halaman `/` merespons sukses |

### Verifikasi manual halaman welcome

- HTTP smoke pada server lokal: `/`, `/beranda`, `/products`, `/products?category=atasan`, `/login`, dan `/register` merespons 200; seluruh tautan produk/kategori internal 200; seluruh aset `public/welcome/` dan build Vite 200.
- `/cart` untuk guest menghasilkan 302 ke login (perilaku yang diharapkan).
- Chrome headless + CDP: viewport desktop 1422px dan mobile 390px; `scrollWidth` sama dengan `clientWidth` (tidak ada overflow horizontal), semua 22 gambar termuat, font `loaded`, dan semua anchor `#main/#koleksi/#tentang/#best-sellers/#keunggulan` memiliki target.
- Interaksi menu seluler: membuka menu mengunci scroll body, tautan `#koleksi` menutup menu dan menggulir ke section, tombol Escape menutup menu.
- Screenshot desktop/mobile tersimpan sementara di `C:\Users\delta\AppData\Local\Temp\opencode\velora-shots\` (belum diarsipkan ke repo).

---

## 4. Matriks Acceptance Criteria (PRD Bagian 16)

Status jujur: **Pass** = diverifikasi otomatis; **Pass (simulasi)** = logika diverifikasi dengan payload gateway tiruan; **Tertunda** = membutuhkan kredensial/lingkungan nyata; **Manual** = belum diambil bukti visual.

| TC | Status | Bukti / Catatan |
| --- | --- | --- |
| TC-01 Guest membuka welcome baru `/` | Pass | `StorefrontTest::test_homepage_and_catalog_are_accessible`; smoke memastikan `/` dan `/beranda` 200 |
| TC-02 Registrasi valid, role admin tidak bisa dipilih | Pass | `AuthenticationTest::test_registration_always_creates_a_customer_account` |
| TC-03 Login valid/invalid, logout | Pass | `AuthenticationTest` (4 test terkait) |
| TC-04 Customer akses route admin | Pass | `AdminPanelTest::test_guests_are_redirected_and_customers_are_forbidden` |
| TC-05 Enam statistik dashboard | Pass | `AdminPanelTest::test_dashboard_statistics_match_database_records` |
| TC-06 CRUD kategori & hapus berelasi ditolak | Pass | Test create/update kategori + `test_category_with_products_cannot_be_deleted` (termasuk produk soft-deleted) |
| TC-07 CRUD produk & varian, foto, input invalid | Pass | `test_admin_can_create_and_update_product_with_variants`, `test_product_image_is_stored_on_public_disk`, `test_duplicate_sku_is_rejected` |
| TC-08 Pencarian/filter, produk nonaktif | Pass | `StorefrontTest::test_catalog_search_and_filters_work`, `test_inactive_product_is_not_publicly_visible` |
| TC-09 Detail varian, stok, varian habis | Pass | `StorefrontTest::test_product_detail_shows_variant_options_and_sold_out_state` |
| TC-10 CRUD cart & kepemilikan | Pass | `CustomerCheckoutTest` cart tests + `test_customer_cannot_modify_another_customers_cart_item` |
| TC-11 Jumlah melebihi stok/manipulasi | Pass | `test_checkout_rejects_quantity_above_stock`, penolakan varian habis, validasi server-side |
| TC-12 Alamat tidak lengkap | Pass | `test_checkout_requires_complete_shipping_address` |
| TC-13 Order/items/payment/reservasi/total server | Pass | `test_checkout_from_cart_creates_order_and_holds_stock` |
| TC-14 Modifikasi harga via browser | Pass | `test_client_supplied_prices_are_ignored` |
| TC-15 Checkout paralel stok terakhir | Pass (dengan catatan) | `lockForUpdate` + validasi stok + transaksi DB; belum dilakukan stress test paralel nyata |
| TC-16 Klik checkout berulang | Pass | `test_repeated_checkout_does_not_create_duplicate_order_or_reserve_stock_twice` |
| TC-17 Snap Sandbox | Tertunda | Pembuatan token ada di server (`MidtransService`), tetapi kredensial kosong sehingga token nyata belum dibuat |
| TC-18 Pembayaran Sandbox berhasil | Tertunda | Alur status sudah diuji dengan payload settlement; transaksi Sandbox nyata belum |
| TC-19 Pending/expire/cancel/deny & stok lepas sekali | Pass (simulasi) | `PaymentStatusTest` settlement/expire; transaksi Sandbox nyata belum |
| TC-20 Signature salah/nominal beda | Pass | `MidtransWebhookTest::test_invalid_signature_is_rejected`, `PaymentStatusTest::test_amount_mismatch_is_rejected` |
| TC-21 Webhook duplikat/terlambat | Pass | `test_duplicate_settlement_notification_does_not_consume_stock_twice`, `test_paid_order_is_never_downgraded_by_late_failure_notification` |
| TC-22 Admin memaksa paid | Pass | `test_admin_cannot_force_payment_status_through_order_status_update` |
| TC-23 Admin memproses pesanan, resi wajib | Pass | `test_admin_can_advance_status_of_paid_order_but_requires_tracking_for_shipping`, `test_unpaid_order_status_cannot_be_advanced_manually`, `test_invalid_status_transition_is_rejected` |
| TC-24 Riwayat & invoice | Pass | Render order/payment/invoice di `CustomerCheckoutTest`; print CSS tersedia, bukti cetak fisik belum diambil |
| TC-25 Akses order akun lain | Pass | `OrderAccessTest::test_customer_cannot_access_another_customers_order` |
| TC-26 Produk dinonaktifkan setelah transaksi | Pass | `OrderAccessTest::test_snapshot_remains_visible_after_product_is_deactivated` |
| TC-27 Mobile & desktop | Pass (teknis) | Chrome headless + CDP pada 1422px/390px: tanpa overflow horizontal, aset/font termuat, menu seluler lolos uji; screenshot belum ditinjau manusia |
| TC-28 Midtrans gagal/timeout | Pass (sebagian) | Refresh tanpa kredensial informatif & status tidak berubah; webhook gateway belum dikonfigurasi → 503; timeout jaringan nyata belum disimulasikan |
| TC-29 Profil admin/customer | Pass | `AuthenticationTest::test_customer_can_update_own_profile_and_password`, render `admin.profile` dan `profile.edit` |

---

## 5. Kepatuhan Fungsional PRD

| Area PRD | Status |
| --- | --- |
| Landing (welcome premium di `/`: hero, kategori, terbaru, terlaris/kurasi, promo, about, keunggulan, footer) | Selesai; legacy `/beranda` dipertahankan |
| Katalog (search, filter kategori, urutan, pagination, detail varian) | Selesai |
| Akun (registrasi customer, login, logout, profil, ganti password, redirect role) | Selesai |
| Cart (tambah/ubah/hapus, subtotal, kepemilikan) | Selesai |
| Checkout (alamat, ongkir config, total server, reservasi stok, buy now, idempotensi) | Selesai |
| Pembayaran (Snap token, halaman bayar, refresh, webhook tervalidasi, rekonsiliasi) | Kode selesai; uji Sandbox nyata tertunda |
| Pesanan (riwayat, detail, invoice cetak, snapshot) | Selesai |
| Admin (dashboard, kategori, produk/varian/gambar, pesanan/resi, pelanggan, profil) | Selesai |
| Keamanan (CSRF, role middleware, policy, signature, Get Status API, tanpa status dari browser) | Selesai |
| Data (6 kategori, 20 produk, 134 varian, 8 stok habis, placeholder legal) | Selesai |
| Dokumentasi (.env.example, README, PROGRESS) | Selesai |

---

## 6. Blocker dan Sisa Pekerjaan

1. **Midtrans Sandbox nyata (TC-17, TC-18, TC-19 penuh).**
   - Isi `MIDTRANS_CLIENT_KEY` dan `MIDTRANS_SERVER_KEY` Sandbox di `.env`.
   - Jalankan `php artisan config:clear`.
   - Publikasikan webhook HTTPS, misalnya `ngrok http 8000`, lalu set Payment Notification URL ke `https://<domain>/midtrans/notification`.
   - Uji pembayaran sukses, pending, expire/cancel/deny, dan kirim notifikasi duplikat dari dashboard/API.
2. **Tinjauan visual manusia (TC-27).** Screenshot welcome desktop/mobile sudah dibuat via Chrome headless dan metrik layout diperiksa; tinjauan visual manusia serta screenshot halaman lain (katalog, detail, cart, checkout, dashboard admin, CRUD, pesanan, invoice) masih perlu diarsipkan.
3. **Stress test paralel (TC-15).** Jalankan dua checkout bersamaan terhadap stok terakhir (misalnya dua worker/request simultan) dan catat hasilnya. Implementasi sudah memakai row lock dan validasi stok dalam transaksi.
4. **Bukti cetak invoice (TC-24).** Cetak/simpan PDF dari browser untuk memastikan stylesheet cetak.
5. **Opsional.** Tambahkan `compare_price` ke factory/seeder agar data demo menampilkan harga coret; saat ini field tersedia di admin dan model.

---

## 7. Cara Menjalankan Ulang Verifikasi

```powershell
php artisan migrate:fresh --seed
php artisan storage:link
php artisan test
vendor\bin\pint
npm run build
php artisan view:cache
php artisan serve
```

Untuk rekonsiliasi manual:

```powershell
php artisan orders:reconcile-pending --limit=50
```

---

## 8. Catatan Repository

- Belum ada `git init` dan belum ada commit; instruksi proyek melarang commit tanpa permintaan eksplisit.
- Kredensial Midtrans tidak pernah ditulis ke kode, JavaScript, test, atau dokumentasi.
- File welcome baru: `app/Http/Controllers/WelcomeController.php`, `resources/views/layouts/welcome.blade.php`, `resources/views/welcome.blade.php`, `resources/views/components/welcome-product-card.blade.php`, `resources/css/welcome.css`, dan 10 foto editorial di `public/welcome/`. Route `/` kini memakai controller tersebut; `/beranda` tetap memakai homepage lama.
- File yang ditambahkan pada tahap akhir: `README.md`, `PROGRESS.md`, `tests/Feature/AuthenticationTest.php`, `tests/Feature/OrderAccessTest.php`, serta penambahan kasus di test yang sudah ada.
