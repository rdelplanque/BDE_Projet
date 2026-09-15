<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Affiche la vue connect.blade.php.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('accueil');
        }

        return response()
            ->view('connect')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Traite la tentative de connexion.
     */
    public function login(Request $request)
    {
        $credentials =$request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember =$request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {$request->session()->regenerate();

            return redirect()->intended(route('accueil'));
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }

    /**
     * Déconnexion de l'utilisateur.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();$request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
