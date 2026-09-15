<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Etudiant;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtudiantController extends Controller
{
    public function index()
    {
        // Chargement imbriqué : etudiant -> classe -> filiere
        $etudiantsBruts = Etudiant::with('classe.filiere')
            ->orderBy('nom', 'asc')
            ->get();

        // Aplatissement des objets pour le script JS
        $etudiants = $etudiantsBruts->map(function ($e) {
            return [
                'id'                => $e->id,
                'nom'               => $e->nom,
                'prenom'            => $e->prenom,
                'email'             => $e->email,
                'classe_id'         => $e->classe_id,
                'classe'            => $e->classe->nom ?? 'Sans classe',
                'filiere'           => $e->classe->filiere->nom ?? 'Sans filière',
                'nb_participations' => 0, // Prêt pour l'agrégation future
                'points_bde'        => 0,
            ];
        });

        // Récupération de la liste des classes pour la modale d'ajout + filiere
        $classes = Classe::with('filiere')->orderBy('nom', 'asc')->get();
        $filieres = Filiere::orderBy('nom', 'asc')->get();

        return view('gestion_etudiant', compact('etudiants', 'classes', 'filieres'));
    }

    public function store(Request $request)
    {
        $validated =$request->validate([
            'nom'            => 'required|string|max:80',
            'prenom'         => 'required|string|max:80',
            'email'          => 'required|email|max:191|unique:etudiants,email',
            'classe_id'      => 'required|exists:classes,id',
            'telephone'      => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
        ]);

        Etudiant::create($validated);

        return redirect()->route('etudiants.index')
            ->with('success', 'Étudiant ajouté avec succès !');
    }

    public function update(Request $request, Etudiant$etudiant)
    {
        $validated =$request->validate([
            'nom'            => 'required|string|max:80',
            'prenom'         => 'required|string|max:80',
            'email'          => 'required|email|max:191|unique:etudiants,email,' . $etudiant->id,
            'classe_id'      => 'required|exists:classes,id',
            'telephone'      => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
        ]);

        $etudiant->update($validated);

        return redirect()->route('etudiants.index')
            ->with('success', 'Informations mises à jour avec succès !');
    }

    public function destroy(Etudiant $etudiant)
    {
        if (!Auth::user()->est_admin) {
            return redirect()->route('etudiants.index')
                ->withErrors(['error' => 'Action refusée : seuls les administrateurs peuvent supprimer un étudiant.']);
        }

        $etudiant->delete();

        return redirect()->route('etudiants.index')
            ->with('success', 'Étudiant supprimé avec succès.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier_etudiants' => 'required|file|max:5120',
        ]);

        return redirect()->route('etudiants.index')
            ->with('success', 'Fichier reçu pour synchronisation.');
    }
}