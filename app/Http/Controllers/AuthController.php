<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'pin'  => 'required|string|min:4|max:20|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'password' => $data['pin'],
        ]);

        Auth::login($user);

        return redirect()->route('tracker.index');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'pin'  => 'required|string',
        ]);

        $user = User::where('name', $data['name'])->first();

        if (! $user || ! Hash::check($data['pin'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => 'Неверное имя или пин-код.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('tracker.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
