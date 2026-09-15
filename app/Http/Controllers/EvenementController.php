<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvenementController extends Controller
{
    /**
     * Affiche le calendrier et la liste des événements.
     */
    public function index()
    {
        // withCount('inscriptions') ou withCount('etudiants') selon le nom de ta relation dans Evenement
        // Si la relation n'est pas encore créée, $evenements = Evenement::orderBy('date_evenement', 'asc')->get(); fonctionne aussi.
        $evenements = Evenement::withCount(['etudiants as inscrits_count' => function($query) {
            // si nécessaire, ou simplement withCount('etudiants')
        }])->orderBy('date_evenement', 'asc')->get();

        return view('gestion_evenement', compact('evenements'));
    }

    /**
     * Enregistre un nouvel événement.
     */
    public function store(Request $request)
    {
        $validated =$request->validate([
            'nom' => 'required|string|max:255',
            'date_evenement' => 'required|date',
            'prix' => 'required|numeric|min:0',
            'nombre_place' => 'nullable|integer|min:1',
            'gain_base' => 'required|integer|min:0',
            'detail' => 'nullable|string',
        ]);

        Evenement::create($validated);

        return redirect()->route('evenements.index')->with('success', 'Événement créé avec succès !');
    }

    /**
     * Met à jour un événement existant.
     */
    public function update(Request $request, Evenement$evenement)
    {
        $validated =$request->validate([
            'nom' => 'required|string|max:255',
            'date_evenement' => 'required|date',
            'prix' => 'required|numeric|min:0',
            'nombre_place' => 'nullable|integer|min:1',
            'gain_base' => 'required|integer|min:0',
            'detail' => 'nullable|string',
        ]);

        $evenement->update($validated);

        return redirect()->route('evenements.index')->with('success', 'Événement mis à jour avec succès !');
    }

    /**
     * Supprime un événement (réservé aux administrateurs).
     */
    public function destroy(Evenement $evenement)
    {
        // Sécurité serveur : vérifier que l'utilisateur est bien admin
        if (!Auth::user()->est_admin) {
            return redirect()->route('evenements.index')->withErrors(['error' => 'Action non autorisée : droits administrateur requis.']);
        }

        $evenement->delete();

        return redirect()->route('evenements.index')->with('success', 'Événement supprimé avec succès !');
    }
}