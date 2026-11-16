<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_and_customers_are_forbidden(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        ProductVariant::factory()->for($product)->create(['stock' => 2]);
        $customer = User::factory()->create();
        $order = $this->makeOrder($customer, $product, OrderStatus::PendingPayment, PaymentStatus::Pending);

        $this->actingAs($this->admin);

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard');
        $this->get(route('admin.categories.index'))->assertOk();
        $this->get(route('admin.categories.create'))->assertOk();
        $this->get(route('admin.categories.edit', $category))->assertOk();
        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.products.create'))->assertOk();
        $this->get(route('admin.products.edit', $product))->assertOk();
        $this->get(route('admin.orders.index'))->assertOk()->assertSee($order->order_number);
        $this->get(route('admin.orders.show', $order))->assertOk();
        $this->get(route('admin.customers.index'))->assertOk();
        $this->get(route('admin.customers.show', $customer))->assertOk();
        $this->get(route('admin.profile'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_admin_can_create_category_and_slug_is_generated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Outerwear Premium',
                'description' => 'Jaket dan blazer.',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Outerwear Premium',
            'slug' => 'outerwear-premium',
        ]);
    }

    public function test_admin_can_create_and_update_product_with_variants(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'name' => 'Sena Oversized Blazer',
                'category_id' => $category->id,
                'description' => 'Blazer oversize dengan bahan premium.',
                'price' => 349000,
                'compare_price' => 499000,
                'is_active' => '1',
                'variants' => [
                    ['size' => 'M', 'color' => 'Burgundy', 'sku' => 'VLR-SENA-M-BRG', 'stock' => 4, 'is_active' => '1'],
                    ['size' => 'L', 'color' => 'Burgundy', 'sku' => 'VLR-SENA-L-BRG', 'stock' => 6, 'is_active' => '1'],
                ],
            ])
            ->assertRedirect();

        $product = Product::query()->where('name', 'Sena Oversized Blazer')->firstOrFail();

        $this->assertSame('sena-oversized-blazer', $product->slug);
        $this->assertSame(349000, $product->price);
        $this->assertSame(499000, $product->compare_price);
        $this->assertCount(2, $product->variants);

        $variant = $product->variants()->where('size', 'M')->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('admin.products.update', $product), [
                'name' => 'Sena Oversized Blazer',
                'category_id' => $category->id,
                'description' => 'Blazer oversize dengan bahan premium.',
                'price' => 329000,
                'is_active' => '1',
                'variants' => [
                    ['id' => $variant->id, 'size' => 'M', 'color' => 'Burgundy', 'sku' => 'VLR-SENA-M-BRG', 'stock' => 3, 'is_active' => '1'],
                ],
            ])
            ->assertRedirect();

        $product->refresh();
        $this->assertSame(329000, $product->price);
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertCount(1, $product->variants()->get());
    }

    public function test_product_image_is_stored_on_public_disk(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'name' => 'Aluna Pleated Dress',
                'category_id' => $category->id,
                'description' => 'Dress plissé dengan bahan ringan.',
                'price' => 289000,
                'image' => UploadedFile::fake()->image('aluna-dress.jpg', 800, 1000),
                'is_active' => '1',
                'variants' => [
                    ['size' => 'S', 'color' => 'Cream', 'sku' => 'VLR-ALU-S-CRM', 'stock' => 5, 'is_active' => '1'],
                ],
            ])
            ->assertRedirect();

        $product = Product::query()->where('name', 'Aluna Pleated Dress')->firstOrFail();

        $this->assertNotNull($product->image);
        $this->assertStringStartsWith('products/', $product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $product->delete();

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_update_category_and_soft_delete_product(): void
    {
        $category = Category::factory()->create(['name' => 'Atasan']);
        $product = Product::factory()->for($category)->create();
        $variant = ProductVariant::factory()->for($product)->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.categories.update', $category), [
                'name' => 'Atasan Premium',
                'description' => 'Koleksi atasan bahan premium.',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Atasan Premium',
            'slug' => 'atasan-premium',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertFalse($variant->fresh()->is_active);
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $existing = ProductVariant::factory()->create(['sku' => 'VLR-DUPLICATE-SKU']);
        $category = Category::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Baru',
                'category_id' => $category->id,
                'description' => 'Deskripsi produk baru.',
                'price' => 100000,
                'is_active' => '1',
                'variants' => [
                    ['size' => 'S', 'color' => 'Hitam', 'sku' => $existing->sku, 'stock' => 1, 'is_active' => '1'],
                ],
            ])
            ->assertSessionHasErrors('variants.0.sku');

        $this->assertDatabaseMissing('products', ['name' => 'Produk Baru']);
    }

    public function test_admin_can_advance_status_of_paid_order_but_requires_tracking_for_shipping(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create();
        $order = $this->makeOrder($customer, $product, OrderStatus::Processing, PaymentStatus::Paid);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['order_status' => 'shipped'])
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Processing, $order->fresh()->order_status);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), [
                'order_status' => 'shipped',
                'carrier' => 'JNE',
                'tracking_number' => 'JNE123456789',
            ])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::Shipped, $order->order_status);
        $this->assertSame('JNE', $order->carrier);
        $this->assertSame('JNE123456789', $order->tracking_number);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_dashboard_statistics_match_database_records(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $product = Product::factory()->create();
        $category = $product->category;
        ProductVariant::factory()->for($product)->create(['stock' => 2]);
        $paid = $this->makeOrder($customer, $product, OrderStatus::Processing, PaymentStatus::Paid);
        $pending = $this->makeOrder($otherCustomer, $product, OrderStatus::PendingPayment, PaymentStatus::Pending);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk()->assertViewHas('stats', function (array $stats): bool {
            return $stats['products'] === 1
                && $stats['categories'] === 1
                && $stats['customers'] === 2
                && $stats['orders'] === 2
                && $stats['pending_payment'] === 1
                && $stats['paid_orders'] === 1;
        });

        $response->assertViewHas('revenue', fn (int $revenue) => $revenue === $paid->total);
        $response->assertViewHas('recentOrders', fn ($orders) => $orders->count() === 2
            && $orders->pluck('id')->contains($pending->id));
    }

    public function test_unpaid_order_status_cannot_be_advanced_manually(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create();
        $order = $this->makeOrder($customer, $product, OrderStatus::PendingPayment, PaymentStatus::Pending);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['order_status' => 'processing'])
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::PendingPayment, $order->fresh()->order_status);
    }

    public function test_admin_cannot_force_payment_status_through_order_status_update(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create();
        $order = $this->makeOrder($customer, $product, OrderStatus::PendingPayment, PaymentStatus::Pending);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), [
                'order_status' => 'processing',
                'payment_status' => 'paid',
                'paid_at' => now()->toDateTimeString(),
            ])
            ->assertSessionHas('error');

        $order->refresh();

        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::PendingPayment, $order->order_status);
        $this->assertNull($order->paid_at);
        $this->assertSame('held', $order->reservation_status->value);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create();
        $order = $this->makeOrder($customer, $product, OrderStatus::Processing, PaymentStatus::Paid);

        $this->actingAs($this->admin)
            ->patch(route('admin.orders.status', $order), ['order_status' => 'completed'])
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Processing, $order->fresh()->order_status);
    }

    private function makeOrder(User $customer, Product $product, OrderStatus $status, PaymentStatus $paymentStatus): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'VLR-ADM-'.strtoupper(Str::random(8)),
            'checkout_key' => Str::uuid()->toString(),
            'recipient_name' => $customer->name,
            'phone' => '081200000000',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'postal_code' => '10110',
            'subtotal' => 100000,
            'shipping_cost' => 15000,
            'total' => 115000,
            'payment_status' => $paymentStatus,
            'order_status' => $status,
            'reservation_status' => $paymentStatus === PaymentStatus::Paid ? 'consumed' : 'held',
            'paid_at' => $paymentStatus === PaymentStatus::Paid ? now() : null,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->order_number,
            'gross_amount' => $order->total,
            'gateway_status' => $paymentStatus === PaymentStatus::Paid ? 'settlement' : 'pending',
            'expiry_at' => now()->addHour(),
        ]);

        return $order;
    }
}
