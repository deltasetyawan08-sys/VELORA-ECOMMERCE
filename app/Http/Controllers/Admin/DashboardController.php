<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'customers' => User::query()->where('role', Role::Customer->value)->count(),
            'orders' => Order::query()->count(),
            'pending_payment' => Order::query()->where('order_status', 'pending_payment')->count(),
            'paid_orders' => Order::query()->where('payment_status', PaymentStatus::Paid->value)->count(),
        ];

        $revenue = (int) Order::query()->where('payment_status', PaymentStatus::Paid->value)->sum('total');

        $recentOrders = Order::query()
            ->with(['user', 'items'])
            ->latest()
            ->take(8)
            ->get();

        $weekOrders = Order::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at', 'total', 'payment_status']);

        $chart = collect(range(6, 0))->map(function (int $daysAgo) use ($weekOrders): array {
            $date = now()->subDays($daysAgo)->startOfDay();
            $dayOrders = $weekOrders->filter(fn (Order $order) => $order->created_at->isSameDay($date));

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d/m'),
                'orders' => $dayOrders->count(),
                'revenue' => (int) $dayOrders->where('payment_status', PaymentStatus::Paid)->sum('total'),
            ];
        });

        $chartMax = max(1, (int) $chart->max('revenue'));

        $lowStock = ProductVariant::query()
            ->where('is_active', true)
            ->where('stock', '<=', 3)
            ->with('product')
            ->orderBy('stock')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'revenue', 'recentOrders', 'chart', 'chartMax', 'lowStock'));
    }
}
