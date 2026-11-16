<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'SKU-'.strtoupper(Str::random(8)),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->randomElement(['Hitam', 'Cream', 'Burgundy', 'Putih']),
            'stock' => fake()->numberBetween(1, 30),
            'is_active' => true,
        ];
    }
}
