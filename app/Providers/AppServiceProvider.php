<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.public', function ($view) {
            $user = auth()->user();

            $cartCount = 0;
            if ($user && $user->isCustomer()) {
                $cartCount = (int) ($user->cart?->items()->sum('quantity') ?? 0);
            }

            $view->with([
                'navCategories' => Category::query()->withCount('products')->orderBy('name')->get(),
                'cartCount' => $cartCount,
            ]);
        });

        View::composer('layouts.admin', function ($view) {
            $pendingOrders = Order::query()->where('order_status', 'pending_payment')->count();

            $view->with('adminPendingOrders', $pendingOrders);
        });
    }
}
