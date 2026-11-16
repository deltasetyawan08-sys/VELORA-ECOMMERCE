# VELORA — Fashion E-Commerce

Website e-commerce toko pakaian **VELORA** (single seller) berbasis Laravel 13, MySQL, Blade + Tailwind CSS v4, Alpine.js, dan Midtrans Snap Sandbox.

Tagline: **Find Your Style, Wear Your Confidence.**

---

## 1. Kebutuhan Sistem

| Komponen | Versi yang dipakai |
| --- | --- |
| PHP | 8.3+ (diuji pada 8.3.35) |
| Laravel | 13.42 |
| MySQL | 8.0+ (diuji pada 8.0.30) |
| Composer | 2.x |
| Node.js / npm | Node 24 / npm 11 |
| Package pembayaran | `midtrans/midtrans-php` 2.6.2 |
| Pengujian | PHPUnit 12, database SQLite in-memory |

Ekstensi PHP yang dibutuhkan: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` (opsional untuk pembuatan gambar uji).

---

## 2. Setup Cepat

```powershell
composer install
npm install

Copy-Item .env.example .env
php artisan key:generate

# Buat database MySQL terlebih dahulu, misalnya:
# C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root -e "CREATE DATABASE db_VELORA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
php artisan storage:link
npm run build
```

Alternatif: `composer setup` (dependency, `.env`, key, migrate + seed, `npm install`, dan build). Setelah itu jalankan:

```powershell
php artisan serve
```

Buka `http://localhost:8000`.

Mode pengembangan dengan Vite + queue worker:

```powershell
composer dev
```

### Konfigurasi `.env` penting

```dotenv
APP_NAME=VELORA
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_VELORA
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Midtrans Sandbox (jangan commit kredensial asli)
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

SHOP_SHIPPING_COST=15000
SHOP_EXPIRY_MINUTES=60
SHOP_NAME=VELORA
SHOP_PHONE=081234567890
SHOP_EMAIL=hello@velora.test
SHOP_ADDRESS="Jl. Fashion Raya No. 10, Jakarta Selatan, Indonesia"
```

Jangan pernah menaruh `MIDTRANS_SERVER_KEY` di JavaScript, repository, atau tangkapan layar publik. Server Key hanya dibaca backend melalui `config/midtrans.php`.

---

## 3. Akun Demo

Dibuat oleh `DatabaseSeeder`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@velora.test` | `password` |
| Customer | `customer@velora.test` | `password` |

Dapat diganti lewat environment sebelum seeding:

```dotenv
VELORA_ADMIN_EMAIL=admin@velora.test
VELORA_ADMIN_PASSWORD=password
VELORA_CUSTOMER_EMAIL=customer@velora.test
VELORA_CUSTOMER_PASSWORD=password
```

Registrasi publik selalu membuat role `customer`; akun admin hanya lewat seeder/provisioning.

Seeder juga membuat 6 kategori, 20 produk fashion, 134 varian (termasuk beberapa varian stok habis), dan placeholder gambar lokal di `storage/app/public`.

---

## 4. Integrasi Midtrans Sandbox

1. Daftar/masuk ke [Midtrans Sandbox](https://dashboard.sandbox.midtrans.com/), ambil **Client Key** dan **Server Key** dari menu Settings → Access Keys.
2. Isi `MIDTRANS_CLIENT_KEY` dan `MIDTRANS_SERVER_KEY` di `.env`, lalu jalankan `php artisan config:clear`.
3. Aktifkan metode pembayaran Sandbox yang diinginkan pada dashboard Midtrans (daftar metode mengikuti channel akun merchant, bukan dipaksakan aplikasi).
4. Atur **Payment Notification URL** menjadi:

   ```text
   https://domain-anda/midtrans/notification
   ```

   Untuk demo lokal, gunakan tunnel HTTPS (misalnya ngrok) dan arahkan ke route yang sama:

   ```text
   https://xxxx.ngrok-free.app/midtrans/notification
   ```

5. Endpoint webhook `POST /midtrans/notification` dikecualikan dari CSRF secara spesifik dan **hanya** menerima status setelah:

   - signature `SHA512(order_id + status_code + gross_amount + ServerKey)` valid,
   - order dikenal,
   - status dikonfirmasi ulang melalui Midtrans Get Status API server-to-server,
   - nominal dan transaction ID cocok dengan data tersimpan.

6. Status `paid` tidak pernah ditetapkan dari browser atau dashboard admin. Bila kredensial belum diisi, halaman pembayaran menampilkan peringatan dan transaksi tidak dianggap berhasil.

### Rekonsiliasi order pending

Order pending yang melewati batas bayar direkonsiliasi terhadap status gateway:

```powershell
php artisan orders:reconcile-pending --limit=50
```

Scheduler sudah terdaftar di `routes/console.php` (setiap jam). Jalankan salah satu:

```powershell
php artisan schedule:work
# atau cron: * * * * * cd /path/tokobaju && php artisan schedule:run
```

---

## 5. Pengujian

```powershell
php artisan test
# atau
composer test
```

Test memakai SQLite in-memory (lihat `phpunit.xml`) dan tidak membutuhkan kredensial Midtrans. Hasil terakhir: **48 test lulus, 233 assertion**.

Cakupan utama:

- Landing, katalog, filter/pencarian, produk nonaktif.
- Registrasi/login/logout, role, redirect, akses admin.
- Keranjang, checkout, buy now, validasi stok, idempotensi checkout.
- Snapshot order, kepemilikan order/invoice/payment.
- Status pembayaran: settlement, expire, mismatch nominal, konflik transaction ID, larangan downgrade paid.
- Webhook: signature valid/tidak valid, order tidak dikenal, gateway belum dikonfigurasi.
- CRUD kategori/produk/varian, transisi status order + kewajiban resi.

Matriks hasil pengujian TC-01 s.d. TC-29 beserta status dan blocker tersedia di `PROGRESS.md`.

---

## 6. Struktur Singkat

```text
app/
  Enums/                 Role, PaymentStatus, OrderStatus, ReservationStatus
  Http/Controllers/      publik, customer, Admin/
  Http/Requests/         validasi form termasuk Admin/
  Http/Middleware/       RoleMiddleware
  Models/                User, Category, Product, ProductVariant, Cart,
                         CartItem, Order, OrderItem, Payment
  Policies/              CartItemPolicy, OrderPolicy
  Services/              CartService, CheckoutService, MidtransService,
                         PaymentStatusService, ImageService
  Console/Commands/      ReconcilePendingOrders
config/                  midtrans.php, shop.php
database/                migrations, seeders, factories
resources/views/         layouts, components, publik, admin, profile
routes/                  web.php, console.php
tests/Feature/           pengujian fungsional
```

---

## 7. Catatan Operasional

- Uang disimpan sebagai integer rupiah (BIGINT unsigned), bukan float.
- Stok dikurangi sebagai reservasi di dalam transaksi database dengan row lock; status reservasi `held → consumed` saat paid atau `held → released` saat gagal final (tepat satu kali).
- Produk dihapus dengan soft delete; varian yang pernah dipakai transaksi dinonaktifkan agar riwayat tetap utuh.
- Kategori yang masih memiliki produk (termasuk produk soft-deleted) tidak dapat dihapus karena foreign key restrict.
- `compare_price` adalah field opsional tambahan untuk harga coret; harga jual tetap `price`.
- Placeholder gambar seeder berupa SVG lokal yang jelas; unggahan asli admin dibatasi JPEG/PNG/WebP maksimal 2 MB.

---

## 8. Status Implementasi

Seluruh fitur wajib PRD sudah diimplementasikan dan diuji otomatis. Satu hal yang belum dapat dilakukan di lingkungan ini adalah transaksi Sandbox **nyata**, karena `MIDTRANS_CLIENT_KEY`/`MIDTRANS_SERVER_KEY` belum diisi. Detail tahapan, hasil uji, dan blocker tercatat di `PROGRESS.md`.

Repositori ini belum diinisialisasi sebagai git repository; commit dibuat hanya bila diminta.
