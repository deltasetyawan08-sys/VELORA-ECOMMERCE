<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('VELORA_ADMIN_EMAIL', 'admin@velora.test')],
            [
                'name' => 'Admin VELORA',
                'password' => Hash::make(env('VELORA_ADMIN_PASSWORD', 'password')),
                'role' => Role::Admin,
                'phone' => '081200000001',
                'address' => 'Kantor VELORA, Jakarta Selatan',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@velora.test'],
            [
                'name' => 'Nadia Putri',
                'password' => Hash::make('password'),
                'role' => Role::Customer,
                'phone' => '081200000002',
                'address' => 'Jl. Melati Indah No. 24, Bandung',
                'email_verified_at' => now(),
            ]
        );

        $categories = collect([
            ['name' => 'Atasan', 'description' => 'Blouse, kemeja, kaos, dan atasan premium untuk gaya sehari-hari.'],
            ['name' => 'Bawahan', 'description' => 'Celana, jeans, dan rok dengan potongan modern yang nyaman.'],
            ['name' => 'Dress', 'description' => 'Dress midi hingga maxi dengan detail elegan untuk berbagai kesempatan.'],
            ['name' => 'Outerwear', 'description' => 'Blazer, cardigan, dan coat untuk melengkapi tampilan Anda.'],
            ['name' => 'Pakaian Pria', 'description' => 'Koleksi pakaian pria dengan siluet rapi dan bahan berkualitas.'],
            ['name' => 'Aksesori', 'description' => 'Tas, scarf, topi, dan pelengkap gaya VELORA.'],
        ])->mapWithKeys(function (array $data, int $index) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image' => $this->placeholderImage($data['name'], $index, 'category'),
                ]
            );

            return [$data['name'] => $category];
        });

        $products = [
            ['Atasan', 'Blouse Satin Aurora', 189000, ['Cream', 'Burgundy'], ['S', 'M', 'L', 'XL']],
            ['Atasan', 'Kemeja Linen Selene', 219000, ['Putih', 'Cream'], ['S', 'M', 'L', 'XL']],
            ['Atasan', 'Kaos Premium Essentials', 99000, ['Hitam', 'Putih', 'Cream'], ['S', 'M', 'L', 'XL', 'XXL']],
            ['Atasan', 'Crop Knit Mira', 159000, ['Cream', 'Burgundy'], ['S', 'M', 'L']],
            ['Bawahan', 'Celana Kulot Kayla', 199000, ['Hitam', 'Cream'], ['S', 'M', 'L', 'XL']],
            ['Bawahan', 'Jeans Straight Luna', 259000, ['Hitam', 'Biru Muda'], ['S', 'M', 'L', 'XL']],
            ['Bawahan', 'Rok Plisket Amara', 179000, ['Burgundy', 'Hitam'], ['S', 'M', 'L']],
            ['Dress', 'Dress Midi Vienna', 329000, ['Burgundy', 'Cream'], ['S', 'M', 'L', 'XL']],
            ['Dress', 'Dress Wrap Aria', 299000, ['Hitam', 'Burgundy'], ['S', 'M', 'L']],
            ['Dress', 'Dress Puff Sleeve Elora', 289000, ['Cream', 'Putih'], ['S', 'M', 'L', 'XL']],
            ['Outerwear', 'Blazer Oversize Clara', 359000, ['Cream', 'Hitam'], ['S', 'M', 'L', 'XL']],
            ['Outerwear', 'Cardigan Knit Nova', 229000, ['Cream', 'Burgundy'], ['S', 'M', 'L']],
            ['Outerwear', 'Trench Coat Belle', 449000, ['Cream', 'Hitam'], ['M', 'L', 'XL']],
            ['Pakaian Pria', 'Kemeja Oxford Arka', 239000, ['Putih', 'Biru Muda'], ['M', 'L', 'XL', 'XXL']],
            ['Pakaian Pria', 'Polo Knit Bima', 189000, ['Hitam', 'Cream', 'Burgundy'], ['M', 'L', 'XL']],
            ['Pakaian Pria', 'Jaket Bomber Raja', 319000, ['Hitam', 'Burgundy'], ['M', 'L', 'XL', 'XXL']],
            ['Aksesori', 'Tote Bag Canvas Vela', 129000, ['Cream', 'Hitam'], ['All Size']],
            ['Aksesori', 'Scarf Silk Sora', 149000, ['Burgundy', 'Cream'], ['All Size']],
            ['Aksesori', 'Topi Bucket Nara', 89000, ['Hitam', 'Cream'], ['All Size']],
            ['Aksesori', 'Ikat Pinggang Classic', 119000, ['Hitam', 'Burgundy'], ['All Size']],
        ];

        foreach ($products as $i => [$categoryName, $name, $price, $colors, $sizes]) {
            $number = str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $categories[$categoryName]->id,
                    'name' => $name,
                    'description' => $this->description($name, $categoryName),
                    'price' => $price,
                    'image' => $this->placeholderImage($name, $i, 'product'),
                    'is_active' => true,
                ]
            );

            if ($product->variants()->exists()) {
                continue;
            }

            foreach ($sizes as $sizeIndex => $size) {
                foreach ($colors as $colorIndex => $color) {
                    $stock = match (true) {
                        $i === 3 && $sizeIndex === 0 => 0,
                        $i === 11 && $colorIndex === 1 => 0,
                        $i === 18 => ($size === 'All Size' ? 4 : 0),
                        $i % 7 === 0 && $sizeIndex === 0 && $colorIndex === 0 => 0,
                        default => (($i * 7 + $sizeIndex * 5 + $colorIndex * 3) % 18) + 3,
                    };

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => 'VLR-'.$number.'-'.strtoupper(substr(Str::slug($size, ''), 0, 3)).'-'.strtoupper(substr(Str::slug($color, ''), 0, 4)),
                        'size' => $size,
                        'color' => $color,
                        'stock' => $stock,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command?->info('Demo users: admin@velora.test / customer@velora.test (password: "password").');
    }

    private function description(string $name, string $category): string
    {
        return "{$name} dari koleksi {$category} VELORA dibuat dengan bahan pilihan yang nyaman dipakai sepanjang hari. "
            .'Potongan modern, jahitan rapi, dan warna netral yang mudah dipadukan. '
            .'Tersedia dalam beberapa ukuran dan warna. Cocok untuk aktivitas harian maupun acara semi-formal.';
    }

    private function placeholderImage(string $name, int $index, string $type): string
    {
        $palettes = [
            ['#6D2035', '#9C4A5E'],
            ['#F8F3EC', '#D9C3B0'],
            ['#242124', '#5A4650'],
            ['#7D2D45', '#C08A76'],
            ['#4A2B33', '#8E5A66'],
            ['#E8DDD0', '#B99C88'],
        ];
        [$from, $to] = $palettes[$index % count($palettes)];
        $text = htmlspecialchars($name, ENT_QUOTES);
        $safeType = htmlspecialchars(ucfirst($type), ENT_QUOTES);
        $lightText = in_array($index % count($palettes), [0, 2, 3, 4], true);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000" viewBox="0 0 800 1000">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$from}"/>
      <stop offset="100%" stop-color="{$to}"/>
    </linearGradient>
    <pattern id="dots" width="40" height="40" patternUnits="userSpaceOnUse">
      <circle cx="2" cy="2" r="1.5" fill="#FFFFFF" opacity="0.12"/>
    </pattern>
  </defs>
  <rect width="800" height="1000" fill="url(#bg)"/>
  <rect width="800" height="1000" fill="url(#dots)"/>
  <circle cx="650" cy="160" r="130" fill="#FFFFFF" opacity="0.08"/>
  <circle cx="120" cy="860" r="170" fill="#000000" opacity="0.06"/>
  <g fill="none" stroke="{$this->contrast($lightText)}" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" opacity="0.9">
    <path d="M300 300 L260 360 L200 330 L150 470 L240 520 L250 760 L550 760 L560 520 L650 470 L600 330 L540 360 L500 300 C480 340 440 355 400 355 C360 355 320 340 300 300 Z"/>
    <path d="M330 300 C345 345 375 365 400 365 C425 365 455 345 470 300"/>
  </g>
  <text x="400" y="850" text-anchor="middle" font-family="Georgia, serif" font-size="42" fill="{$this->contrast($lightText)}">{$safeType}</text>
  <text x="400" y="905" text-anchor="middle" font-family="Arial, sans-serif" font-size="27" fill="{$this->contrast($lightText)}" opacity="0.9">{$text}</text>
  <text x="400" y="955" text-anchor="middle" font-family="Arial, sans-serif" font-size="19" letter-spacing="8" fill="{$this->contrast($lightText)}" opacity="0.65">VELORA</text>
</svg>
SVG;

        $file = 'products/'.Str::slug($name).'.svg';
        Storage::disk('public')->put($file, $svg);

        return $file;
    }

    private function contrast(bool $light): string
    {
        return $light ? '#F8F3EC' : '#242124';
    }
}
