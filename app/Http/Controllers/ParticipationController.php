<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Evenement;
use App\Models\Participation;
use Illuminate\Http\Request;

class ParticipationController extends Controller
{
    /**
     * Recherche d'étudiants pour la modal de validation.
     * Filtres combinables : filiere_id, classe_id, nom (nom OU prénom).
     * Si evenement_id est fourni, indique pour chaque étudiant s'il est déjà validé sur cet événement.
     */
    public function rechercherEtudiants(Request $request)
    {
        $request->validate([
            'filiere_id' => 'nullable|integer|exists:filieres,id',
            'classe_id' => 'nullable|integer|exists:classes,id',
            'nom' => 'nullable|string|max:80',
            'evenement_id' => 'nullable|integer|exists:evenements,id',
        ]);

        $query = Etudiant::with('classe.filiere');

        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->integer('classe_id'));
        } elseif ($request->filled('filiere_id')) {
            $query->whereHas('classe', function ($q) use ($request) {
                $q->where('filiere_id', $request->integer('filiere_id'));
            });
        }

        if ($request->filled('nom')) {
            $terme = $request->string('nom');
            $query->where(function ($q) use ($terme) {
                $q->where('nom', 'ilike', "%{$terme}%")
                  ->orWhere('prenom', 'ilike', "%{$terme}%");
            });
        }

        $etudiants = $query->orderBy('nom')->limit(30)->get();

        // Étudiants déjà validés pour cet événement (pour affichage "déjà validé" côté front)
        $dejaValides = [];
        if ($request->filled('evenement_id')) {
            $dejaValides = Participation::where('evenement_id', $request->integer('evenement_id'))
                ->whereIn('etudiant_id', $etudiants->pluck('id'))
                ->pluck('etudiant_id')
                ->all();
        }

        return response()->json(
            $etudiants->map(function (Etudiant $e) use ($dejaValides) {
                return [
                    'id' => $e->id,
                    'nom' => $e->nom,
                    'prenom' => $e->prenom,
                    'classe' => $e->classe->nom ?? null,
                    'filiere' => $e->classe->filiere->nom ?? null,
                    'deja_valide' => in_array($e->id, $dejaValides),
                ];
            })
        );
    }

    /**
     * Valide (ou met à jour) la participation d'un étudiant à un événement.
     * Crée la ligne participation si elle n'existe pas, sinon met à jour le gain_total.
     */
    public function valider(Request $request, Evenement $evenement)
    {
        $data = $request->validate([
            'etudiant_id' => 'required|integer|exists:etudiants,id',
        ]);

        $participation = Participation::updateOrCreate(
            [
                'evenement_id' => $evenement->id,
                'etudiant_id' => $data['etudiant_id'],
            ],
            [
                'gain_total' => $evenement->gain_base,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Participation validée.',
            'etudiant_id' => $participation->etudiant_id,
            'gain_total' => $participation->gain_total,
            'nb_inscrits' => Participation::where('evenement_id', $evenement->id)->count(),
        ]);
    }

    /**
     * Annule (supprime) la participation d'un étudiant à un événement.
     */
    public function annuler(Request $request, Evenement $evenement)
    {
        $data = $request->validate([
            'etudiant_id' => 'required|integer|exists:etudiants,id',
        ]);

        Participation::where('evenement_id', $evenement->id)
            ->where('etudiant_id', $data['etudiant_id'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Participation annulée.',
            'etudiant_id' => (int) $data['etudiant_id'],
            'nb_inscrits' => Participation::where('evenement_id', $evenement->id)->count(),
        ]);
    }
}
