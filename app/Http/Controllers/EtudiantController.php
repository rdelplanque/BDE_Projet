<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use App\Models\Etudiant;
use App\Models\Classe;
use App\Imports\EtudiantsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EtudiantController extends Controller
{
    public function index()
    {
        // Chargement imbriqué : etudiant -> classe -> filiere
        // withCount / withSum calculent nb_participations et points_bde en une seule requête,
        // à partir de la table participations (pas de colonnes en dur sur etudiants).
        $etudiantsBruts = Etudiant::with('classe.filiere')
            ->withCount('participations')
            ->withSum('participations', 'gain_total')
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
                'nb_participations' => $e->participations_count,
                'points_bde'        => ($e->participations_sum_gain_total ?? 0) + $e->points_bonus,
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

    /**
     * Ajoute (ou retire, si négatif) des points BDE bonus à un étudiant.
     */
    public function ajouterPoints(Request $request, Etudiant $etudiant)
    {
        $data = $request->validate([
            'points' => 'required|integer',
        ]);

        $etudiant->increment('points_bonus', $data['points']);

        $totalParticipations = $etudiant->participations()->sum('gain_total');

        return response()->json([
            'success' => true,
            'points_bonus' => $etudiant->points_bonus,
            'points_bde' => $totalParticipations + $etudiant->points_bonus,
        ]);
    }

    /**
     * Données du classement des étudiants (JSON), pour la modal "Voir le classement".
     */
    public function classement()
    {
        $etudiants = Etudiant::with('classe.filiere')
            ->withCount('participations')
            ->withSum('participations', 'gain_total')
            ->get()
            ->map(function ($e) {
                return [
                    'id'                => $e->id,
                    'nom'               => $e->nom,
                    'prenom'            => $e->prenom,
                    'classe'            => $e->classe->nom ?? 'Sans classe',
                    'filiere'           => $e->classe->filiere->nom ?? 'Sans filière',
                    'nb_participations' => $e->participations_count,
                    'points_bde'        => ($e->participations_sum_gain_total ?? 0) + $e->points_bonus,
                ];
            });

        return response()->json($etudiants);
    }

    /**
     * Exporte la liste des étudiants en CSV : prénom + nb de participations uniquement,
     * triés par filière, puis classe, puis nom.
     */
    public function exportCsv()
    {
        $etudiants = Etudiant::query()
            ->join('classes', 'etudiants.classe_id', '=', 'classes.id')
            ->join('filieres', 'classes.filiere_id', '=', 'filieres.id')
            ->with('classe.filiere')
            ->select('etudiants.*')
            ->withCount('participations')
            ->orderBy('filieres.nom')
            ->orderBy('classes.nom')
            ->orderBy('etudiants.nom')
            ->get();

        $filename = 'export_etudiants_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($etudiants) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour qu'Excel affiche correctement les accents
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Filière', 'Classe', 'Nom', 'Prénom', 'Participations'], ';');

            foreach ($etudiants as $etudiant) {
                fputcsv($handle, [
                    $etudiant->classe->filiere->nom ?? '',
                    $etudiant->classe->nom ?? '',
                    $etudiant->nom,
                    $etudiant->prenom,
                    $etudiant->participations_count,
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier_etudiants' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new EtudiantsImport();

        try {
            Excel::import($import, $request->file('fichier_etudiants'));
        } catch (\Throwable $e) {
            return redirect()->route('etudiants.index')
                ->withErrors(['error' => 'Impossible de lire ce fichier. Vérifiez qu\'il s\'agit bien d\'un fichier Excel (.xlsx) ou CSV valide, avec les colonnes Filiere, Classe, nom, prenom, email.']);
        }

        $message = "{$import->created} étudiant(s) créé(s), {$import->updated} mis à jour.";

        return redirect()->route('etudiants.index')
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }
}
