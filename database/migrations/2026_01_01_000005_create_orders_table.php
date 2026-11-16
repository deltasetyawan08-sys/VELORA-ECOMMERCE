<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 40)->unique();
            $table->uuid('checkout_key');
            $table->string('recipient_name', 120);
            $table->string('phone', 30);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->text('address');
            $table->string('postal_code', 10);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('shipping_cost');
            $table->unsignedBigInteger('total');
            $table->string('payment_status', 20)->default('pending')->index();
            $table->string('order_status', 30)->default('pending_payment')->index();
            $table->string('reservation_status', 20)->default('held')->index();
            $table->string('carrier', 60)->nullable();
            $table->string('tracking_number', 80)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'checkout_key']);
            $table->index('created_at');
            $table->index('recipient_name');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 150);
            $table->string('sku', 80);
            $table->string('size', 30);
            $table->string('color', 50);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('midtrans_order_id', 60)->unique();
            $table->string('midtrans_transaction_id', 100)->nullable()->unique();
            $table->string('snap_token', 255)->nullable();
            $table->string('redirect_url', 500)->nullable();
            $table->unsignedBigInteger('gross_amount');
            $table->string('gateway_status', 40)->nullable();
            $table->string('fraud_status', 40)->nullable();
            $table->string('payment_type', 40)->nullable();
            $table->timestamp('expiry_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('requires_review')->default(false);
            $table->timestamps();

            $table->index('gateway_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
