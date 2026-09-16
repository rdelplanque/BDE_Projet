<?php

namespace App\Imports;

use App\Models\Classe;
use App\Models\Etudiant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import de la liste des étudiants depuis un fichier Excel/CSV.
 * Colonnes attendues (en-tête) : Filiere, Classe, nom, prenom, email.
 *
 * - Une ligne = un étudiant. Filiere + Classe doivent correspondre à une
 *   combinaison déjà existante dans la base (créées via Gestion des étudiants).
 * - "Synchronisation" : si l'email existe déjà, l'étudiant est mis à jour
 *   (nom, prénom, classe) plutôt que dupliqué ; sinon il est créé.
 * - Une ligne invalide n'interrompt pas l'import : elle est juste ignorée
 *   et ajoutée à $errors, pour que les autres lignes valides soient importées.
 */
class EtudiantsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;

    /** @var string[] */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        // Chargé une seule fois pour éviter une requête SQL par ligne du fichier.
        $classes = Classe::with('filiere')->get();

        // Détecte les doublons d'email à l'intérieur du fichier lui-même.
        $emailsVus = [];

        foreach ($rows as $index => $row) {
            // WithHeadingRow retire déjà la ligne d'en-tête ; $index commence à 0
            // pour la première ligne de données, qui est la ligne 2 du fichier.
            $numeroLigne = $index + 2;

            $filiereNom = trim((string) ($row['filiere'] ?? ''));
            $classeNom  = trim((string) ($row['classe'] ?? ''));
            $nom        = trim((string) ($row['nom'] ?? ''));
            $prenom     = trim((string) ($row['prenom'] ?? ''));
            $email      = trim((string) ($row['email'] ?? ''));

            // Ligne totalement vide (ex : ligne blanche en fin de fichier) : ignorée silencieusement.
            if ($filiereNom === '' && $classeNom === '' && $nom === '' && $prenom === '' && $email === '') {
                continue;
            }

            if ($nom === '' || $prenom === '' || $email === '') {
                $this->errors[] = "Ligne {$numeroLigne} : nom, prénom et email sont obligatoires.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Ligne {$numeroLigne} : l'email \"{$email}\" est invalide.";
                continue;
            }

            $emailClef = mb_strtolower($email);
            if (isset($emailsVus[$emailClef])) {
                $this->errors[] = "Ligne {$numeroLigne} : email \"{$email}\" en double dans le fichier (déjà utilisé ligne {$emailsVus[$emailClef]}).";
                continue;
            }
            $emailsVus[$emailClef] = $numeroLigne;

            $classe = $classes->first(function (Classe $c) use ($filiereNom, $classeNom) {
                return $c->filiere
                    && mb_strtolower(trim($c->filiere->nom)) === mb_strtolower($filiereNom)
                    && mb_strtolower(trim($c->nom)) === mb_strtolower($classeNom);
            });

            if (!$classe) {
                $this->errors[] = "Ligne {$numeroLigne} : la combinaison filière \"{$filiereNom}\" / classe \"{$classeNom}\" n'existe pas.";
                continue;
            }

            try {
                $etudiant = Etudiant::updateOrCreate(
                    ['email' => $email],
                    [
                        'nom'       => $nom,
                        'prenom'    => $prenom,
                        'classe_id' => $classe->id,
                    ]
                );

                $etudiant->wasRecentlyCreated ? $this->created++ : $this->updated++;
            } catch (\Throwable $e) {
                $this->errors[] = "Ligne {$numeroLigne} : erreur lors de l'enregistrement.";
            }
        }
    }
}
