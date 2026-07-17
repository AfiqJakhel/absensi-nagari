<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle authentication request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $credentials['username'];
        $password = $credentials['password'];

        // Determine if username is email or employee number (NIP)
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_number';

        $user = User::where($loginField, $username)->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'username' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ])->onlyInput('username');
        }

        // Attempt login
        if (Auth::attempt([$loginField => $username, 'password' => $password], $request->filled('remember'))) {
            $request->session()->regenerate();

            return $this->redirectUser(Auth::user());
        }

        return back()->withErrors([
            'username' => 'Username (NIP/Email) atau password salah.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect authenticated users depending on their role.
     */
    protected function redirectUser($user)
    {
        if ($user->hasRole('admin') || $user->hasRole('operator')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
