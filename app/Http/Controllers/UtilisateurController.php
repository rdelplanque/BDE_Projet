<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    /**
     * Vérifie que l'utilisateur connecté est admin, sinon redirige avec une erreur.
     * Toute la gestion des utilisateurs est réservée aux admins (pas seulement le lien dans le menu).
     */
    private function refuserSiPasAdmin()
    {
        if (!Auth::user() || !Auth::user()->est_admin) {
            return redirect()->route('accueil')
                ->withErrors(['error' => 'Action réservée aux administrateurs.']);
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->refuserSiPasAdmin()) {
            return $redirect;
        }

        $utilisateurs = User::orderBy('name', 'asc')->get();

        $utilisateursJson = $utilisateurs->map(fn ($u) => [
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'est_admin' => (bool) $u->est_admin,
        ]);

        return view('gestion_utilisateur', compact('utilisateurs', 'utilisateursJson'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->refuserSiPasAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:80',
            'email'     => 'required|email|max:191|unique:users,email',
            'password'  => 'required|string|min:8',
            'est_admin' => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['est_admin'] = $request->boolean('est_admin');

        User::create($validated);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès !');
    }

    public function update(Request $request, User $utilisateur)
    {
        if ($redirect = $this->refuserSiPasAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:80',
            'email'     => 'required|email|max:191|unique:users,email,' . $utilisateur->id,
            'password'  => 'nullable|string|min:8',
            'est_admin' => 'sometimes|boolean',
        ]);

        // Mot de passe laissé vide = on ne le change pas
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['est_admin'] = $request->boolean('est_admin');

        // On ne peut pas se retirer soi-même les droits admin (risque de se bloquer l'accès)
        if ($utilisateur->id === Auth::id() && !$validated['est_admin']) {
            return redirect()->route('utilisateurs.index')
                ->withErrors(['error' => 'Vous ne pouvez pas retirer vos propres droits administrateur.']);
        }

        $utilisateur->update($validated);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur mis à jour avec succès !');
    }

    public function destroy(User $utilisateur)
    {
        if ($redirect = $this->refuserSiPasAdmin()) {
            return $redirect;
        }

        if ($utilisateur->id === Auth::id()) {
            return redirect()->route('utilisateurs.index')
                ->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $utilisateur->delete();

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
