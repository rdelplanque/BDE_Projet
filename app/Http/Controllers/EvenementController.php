<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Evenement;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvenementController extends Controller
{
    /**
     * Affiche le calendrier et la liste des événements.
     */
    public function index()
    {
        $evenements = Evenement::withCount('etudiants as inscrits_count')
            ->orderBy('date_evenement', 'asc')
            ->get();

        // Nécessaires pour les filtres de la modal "Valider une participation"
        $filieres = Filiere::orderBy('nom', 'asc')->get();
        $classes = Classe::orderBy('nom', 'asc')->get(['id', 'nom', 'filiere_id']);

        return view('gestion_evenement', compact('evenements', 'filieres', 'classes'));
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
