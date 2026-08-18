<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Invalid username or password.']);
        }

        if (!$user->is_active) {
            return back()->withErrors(['username' => 'Your account has been deactivated.']);
        }

        $passwordValid = false;
        $storedHash = $user->password_hash;

        // Check native PHP password_verify first without throwing Laravel Exception
        if (password_verify($credentials['password'], $storedHash)) {
            $passwordValid = true;
        } else if ($credentials['password'] === $storedHash) {
            // Plaintext fallback
            $passwordValid = true;
            // Upgrade to Bcrypt on successful login
            $user->password_hash = Hash::make($credentials['password']);
            $user->save();
        } else if (md5($credentials['password']) === $storedHash || sha1($credentials['password']) === $storedHash) {
            // MD5 or SHA1 legacy hash fallback
            $passwordValid = true;
            $user->password_hash = Hash::make($credentials['password']);
            $user->save();
        }

        if ($passwordValid) {
            Auth::login($user, $request->boolean('remember'));
            $user->update(['last_login_at' => now()]);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['username' => 'Invalid username or password.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
