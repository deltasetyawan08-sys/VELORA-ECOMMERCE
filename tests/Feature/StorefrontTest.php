<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_catalog_are_accessible(): void
    {
        $product = Product::factory()->create(['name' => 'Amara Linen Blouse']);
        ProductVariant::factory()->for($product)->create(['stock' => 5]);

        $this->get('/')->assertOk()->assertSee('VELORA');
        $this->get(route('products.index'))->assertOk()->assertSee('Amara Linen Blouse');
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Amara Linen Blouse');
    }

    public function test_catalog_search_and_filters_work(): void
    {
        $category = Category::factory()->create(['name' => 'Dress']);
        $matching = Product::factory()->for($category)->create(['name' => 'Aurora Maxi Dress']);
        ProductVariant::factory()->for($matching)->create();
        $other = Product::factory()->create(['name' => 'Basic Cotton Tee']);
        ProductVariant::factory()->for($other)->create();

        $this->get(route('products.index', ['q' => 'Aurora']))
            ->assertOk()
            ->assertSee('Aurora Maxi Dress')
            ->assertDontSee('Basic Cotton Tee');

        $this->get(route('products.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee('Aurora Maxi Dress')
            ->assertDontSee('Basic Cotton Tee');
    }

    public function test_product_detail_shows_variant_options_and_sold_out_state(): void
    {
        $product = Product::factory()->create(['name' => 'Amara Linen Blouse']);
        $available = ProductVariant::factory()->for($product)->create([
            'size' => 'M',
            'color' => 'Burgundy',
            'sku' => 'VLR-AMR-M-BRG',
            'stock' => 3,
        ]);
        ProductVariant::factory()->for($product)->create([
            'size' => 'L',
            'color' => 'Hitam',
            'sku' => 'VLR-AMR-L-HTM',
            'stock' => 0,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Pilih Ukuran')
            ->assertSee('Burgundy')
            ->assertSee('Hitam')
            ->assertSee('Varian habis')
            ->assertSee($available->sku);

        $this->actingAs(User::factory()->create())
            ->post(route('cart.items.store'), [
                'product_variant_id' => $product->variants()->where('sku', 'VLR-AMR-L-HTM')->value('id'),
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_inactive_product_is_not_publicly_visible(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->get(route('products.show', $product->slug))->assertNotFound();
    }
}
