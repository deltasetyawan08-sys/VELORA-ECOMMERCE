<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $customers = User::query()
            ->where('role', Role::Customer->value)
            ->withCount('orders')
            ->when($search, function ($query, $term) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);

        $customer->load(['orders' => fn ($query) => $query->latest()->with('items')]);

        $totalSpent = (int) $customer->orders
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        return view('admin.customers.show', compact('customer', 'totalSpent'));
    }
}
