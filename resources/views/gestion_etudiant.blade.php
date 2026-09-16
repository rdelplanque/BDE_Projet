<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Étudiants - BDE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gestion_etudiant.css') }}">
</head>
<body class="page-gestion">

    <header class="accueil-header">
        <div class="wrap header-content">
            <div class="logo-mark">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo BDE" class="header-logo-img">
                <span>Espace BDE</span>
            </div>

            <div class="header-actions">
                <a href="{{ route('accueil') }}" class="btn-back">← Accueil</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <main class="gestion-main">
        <div class="wrap">

            @if(session('success'))
                <div class="alert-box alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-box alert-danger">{{ $errors->first() }}</div>
            @endif

            @if (session('import_errors') && count(session('import_errors')) > 0)
                <div class="alert-box alert-danger">
                    <strong>{{ count(session('import_errors')) }} ligne(s) ignorée(s) lors de l'import :</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        @foreach (session('import_errors') as $erreurLigne)
                            <li>{{ $erreurLigne }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="gestion-card">
                <div class="card-toolbar">
                    <div>
                        <h1>Gestion des Étudiants</h1>
                        <p class="toolbar-desc">Consultez, ajoutez ou mettez à jour les effectifs du campus.</p>
                    </div>

                    <div class="toolbar-btns">
                        <button type="button" class="btn-action-primary" onclick="openStudentModal('create')">
                            + Ajouter un étudiant
                        </button>
                        <button type="button" class="btn-action-import" onclick="openImportModal()">
                            Importer la liste des étudiants
                        </button>
                        <button type="button" class="btn-action-import" onclick="openExportModal()">
                            Exporter la liste des étudiants
                        </button>
                    </div>
                </div>

                <div class="search-box-wrap">
                    <label for="studentSearchInput">Recherche par nom</label>
                    <input 
                        type="text" 
                        id="studentSearchInput" 
                        placeholder="Ex : Dupont..." 
                        oninput="filterStudents()"
                    >
                </div>
            </div>

            <!-- Arborescence par Filières / Classes / Étudiants -->
            <div id="filieresContainer" class="filieres-container"></div>

        </div>
    </main>

    <!-- MODALE AJOUT / MODIFICATION D'UN ÉTUDIANT -->
    <div class="modal-backdrop" id="studentModal">
        <div class="modal-card">
            <div class="modal-head">
                <h3 id="studentModalTitle">Ajouter un étudiant</h3>
                <button type="button" class="modal-close-btn" onclick="closeStudentModal()">×</button>
            </div>

            <form id="studentForm" method="POST" action="{{ route('etudiants.store') }}">
                @csrf
                <input type="hidden" name="_method" id="studentFormMethod" value="POST">

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="form_filiere_select">Filière</label>
                        <select id="form_filiere_select" onchange="onFiliereChanged()" style="...">
                            <option value="">-- Choisir une filière --</option>
                            @foreach ($filieres as $f)
                                <option value="{{ $f->id }}">{{ $f->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-half">
                        <label for="form_classe_id">Classe</label>
                        <select id="form_classe_id" name="classe_id" required style="...">
                            <option value="">-- Choisir d'abord une filière --</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" data-filiere-id="{{ $c->filiere_id }}">{{ $c->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="form_nom">Nom</label>
                        <input type="text" id="form_nom" name="nom" required placeholder="Ex : Dupont">
                    </div>
                    <div class="form-group col-half">
                        <label for="form_prenom">Prénom</label>
                        <input type="text" id="form_prenom" name="prenom" required placeholder="Ex : Thomas">
                    </div>
                </div>

                <div class="form-group">
                    <label for="form_email">Adresse email</label>
                    <input type="email" id="form_email" name="email" required placeholder="thomas.dupont@campus.fr">
                </div>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="form_participations">Nombre de participations</label>
                        <input type="number" id="form_participations" value="0" readonly disabled
                            style="opacity: 0.7; cursor: not-allowed;">
                        <small style="color:#c9bee6;">Calculé automatiquement, non modifiable.</small>
                    </div>
                    <div class="form-group col-half">
                        <label for="form_points">Points BDE</label>
                        <input type="number" id="form_points" value="0" readonly disabled
                            style="opacity: 0.7; cursor: not-allowed;">
                        <small style="color:#c9bee6;">Somme des gains validés sur les événements.</small>
                    </div>
                </div>

                <button type="button" id="btnAjouterPoints" class="btn-manage-participations" style="display: none;" onclick="ajouterPointsBde()">
                    + Ajouter des points BDE
                </button>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeStudentModal()">Annuler</button>
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>
            </form>

            @if(Auth::user() && Auth::user()->est_admin)
                <form id="deleteStudentForm" method="POST" action="" style="display:none; margin-top: 14px; text-align: right;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete" onclick="return confirm('Confirmer la suppression définitive de cet étudiant ?')">
                        Supprimer cet étudiant
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- MODALE D'IMPORTATION DE FICHIER -->
    <div class="modal-backdrop" id="importModal">
        <div class="modal-card">
            <div class="modal-head">
                <h3>Mise à jour de la liste d'étudiants</h3>
                <button type="button" class="modal-close-btn" onclick="closeImportModal()">×</button>
            </div>

            <form action="{{ route('etudiants.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <p class="import-instructions">
                    Fichier Excel (.xlsx) ou CSV avec les colonnes <strong>Filiere, Classe, nom, prenom, email</strong>
                    (dans cet ordre, 1 ligne = 1 étudiant). La filière et la classe doivent déjà exister.
                    Si l'email existe déjà, l'étudiant est mis à jour plutôt que dupliqué.
                </p>

                <div class="file-upload-area">
                    <input type="file" id="importFile" name="fichier_etudiants" accept=".xlsx,.xls,.csv" required class="input-file">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeImportModal()">Annuler</button>
                    <button type="submit" class="btn-save">Importer et mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALE D'EXPORT CSV -->
    <div class="modal-backdrop" id="exportModal">
        <div class="modal-card">
            <div class="modal-head">
                <h3>Exporter la liste des étudiants</h3>
                <button type="button" class="modal-close-btn" onclick="closeExportModal()">×</button>
            </div>

            <p class="import-instructions">
                Le fichier CSV contient la liste des étudiants triées par filiere et classe. 
                Il indique par étudiant le nombre de participation aux évenements du BDE.
            </p>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeExportModal()">Annuler</button>
                <a
                    href="{{ route('etudiants.export') }}"
                    class="btn-save"
                    style="text-decoration: none; display: inline-block; text-align: center;"
                >
                    Télécharger le CSV
                </a>
            </div>
        </div>
    </div>

    <footer class="accueil-footer">
        <div class="wrap footer-content">
            <p>&copy; 2026 BDE — Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Variables transmises au fichier externe JS -->
    <script>
        window.BDE_CLASSES = @json($classes ?? []);
        window.BDE_STUDENTS = @json($etudiants ?? []);
        window.BDE_STUDENT_STORE_URL = "{{ route('etudiants.store') }}";
        window.BDE_IS_ADMIN = {{ (Auth::user() && Auth::user()->est_admin) ? 'true' : 'false' }};
    </script>
    <script src="{{ asset('js/gestion_etudiant.js') }}"></script>

</body>
</html>
