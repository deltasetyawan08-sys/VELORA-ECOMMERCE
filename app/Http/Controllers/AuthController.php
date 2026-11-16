<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        $target = $this->safeRedirect($request->input('redirect'));

        if ($target) {
            return redirect($target)->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        return redirect()->intended(route('products.index'))
            ->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => Role::Customer,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('products.index')
            ->with('success', 'Akun berhasil dibuat. Selamat berbelanja di VELORA!');
    }

    private function safeRedirect(?string $target): ?string
    {
        if (! $target) {
            return null;
        }

        $parts = parse_url($target);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && $parts['host'] !== request()->getHost()) {
            return null;
        }

        return $target;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari akun.');
    }
}
