<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - BDE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/accueil.css') }}">
</head>
<body class="page-accueil">

    <header class="accueil-header">
        <div class="wrap header-content">
            <div class="logo-mark">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo BDE" class="header-logo-img">
                <span>Bienvenue au BDE</span>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Déconnexion</button>
            </form>
        </div>
    </header>

    <main class="accueil-main">
        <div class="accueil-card">
            <h1>Bienvenue {{ Auth::user()->name ?? 'Responsable BDE' }}, que souhaites-tu faire ?</h1>
            <p class="accueil-desc">Sélectionne un espace pour continuer.</p>

            <div class="actions-grid">
                @if(Auth::user() && Auth::user()->est_admin)
                    <a href="{{ route('utilisateurs.index') }}" class="btn-action" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                        Gestion des utilisateurs
                    </a>
                @endif

                <a href="{{ route('evenements.index') }}" class="btn-action" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                    Gestion des événements
                </a>

                <a href="{{ route('etudiants.index') }}" class="btn-action" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                    Gestion des étudiants
                </a>

                <button type="button" class="btn-action" onclick="openClassementModal()">
                    Voir le classement des étudiants
                </button>
            </div>
        </div>
    </main>

    <footer class="accueil-footer">
        <div class="wrap footer-content">
            <p>&copy; 2026 BDE — Tous droits réservés.</p>
        </div>
    </footer>

    <!-- MODAL : Classement des étudiants -->
    <div class="modal-backdrop" id="classementModal">
        <div class="modal-card classement-modal-card">
            <div class="modal-head">
                <h3>Classement des étudiants</h3>
                <button type="button" class="modal-close-btn" onclick="closeClassementModal()">×</button>
            </div>

            <div class="classement-toolbar">
                <input
                    type="text"
                    id="classementSearch"
                    placeholder="Rechercher par nom..."
                    oninput="onClassementSearchInput()"
                >
            </div>

            <div class="classement-table-wrap">
                <table class="classement-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th class="sortable" onclick="sortClassement('filiere')">
                                Filière <span class="sort-icon" id="sortIcon-filiere"></span>
                            </th>
                            <th class="sortable" onclick="sortClassement('classe')">
                                Classe <span class="sort-icon" id="sortIcon-classe"></span>
                            </th>
                            <th class="sortable" onclick="sortClassement('nb_participations')">
                                Participations <span class="sort-icon" id="sortIcon-nb_participations"></span>
                            </th>
                            <th class="sortable" onclick="sortClassement('points_bde')">
                                Points <span class="sort-icon" id="sortIcon-points_bde"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="classementTbody">
                        <tr><td colspan="7" class="no-result">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        window.BDE_CLASSEMENT_URL = "{{ route('classement.data') }}";
    </script>
    <script src="{{ asset('js/accueil.js') }}"></script>

</body>
</html>
