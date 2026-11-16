<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_always_creates_a_customer_account(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Nadia Putri',
            'email' => 'nadia@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('products.index'));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'nadia@example.test')->firstOrFail();
        $this->assertSame(Role::Customer, $user->role);
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Nadia Putri',
                'email' => 'nadia@example.test',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'nadia@example.test']);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'nadia@example.test']);

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'email' => 'nadia@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_is_returned_to_intended_page_after_login(): void
    {
        $customer = User::factory()->create();

        $this->get(route('cart.index'))->assertRedirect(route('login'));

        $this->post(route('login.attempt'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('cart.index'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login.attempt'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_customer_can_update_own_profile_and_password(): void
    {
        $customer = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($customer)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'nama.baru@example.test',
                'phone' => '081298765432',
                'address' => 'Jl. Baru No. 2',
            ])
            ->assertSessionHas('success');

        $customer->refresh();
        $this->assertSame('Nama Baru', $customer->name);
        $this->assertSame('nama.baru@example.test', $customer->email);
        $this->assertSame('081298765432', $customer->phone);

        $this->actingAs($customer)
            ->put(route('profile.password'), [
                'current_password' => 'salah-password',
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($customer)
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'password-baru-123',
                'password_confirmation' => 'password-baru-123',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password-baru-123', $customer->fresh()->password));
    }

    public function test_logout_ends_the_session(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
