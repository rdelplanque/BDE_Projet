<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Utilisateurs - BDE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gestion_utilisateur.css') }}">
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

            <div class="gestion-card">
                <div class="card-toolbar">
                    <div>
                        <h1>Gestion des Utilisateurs</h1>
                        <p class="toolbar-desc">Gère les comptes ayant accès à l'espace BDE.</p>
                    </div>

                    <div class="toolbar-btns">
                        <button type="button" class="btn-action-primary" onclick="openUserModal('create')">
                            + Ajouter un utilisateur
                        </button>
                    </div>
                </div>

                <div class="search-box-wrap">
                    <label for="userSearchInput">Recherche par nom</label>
                    <input
                        type="text"
                        id="userSearchInput"
                        placeholder="Ex : Dupont..."
                        oninput="filterUsers()"
                    >
                </div>
            </div>

            <div class="users-table-wrap">
                <table class="table-users">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Admin</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTbody"></tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- MODALE AJOUT / MODIFICATION D'UN UTILISATEUR -->
    <div class="modal-backdrop" id="userModal">
        <div class="modal-card">
            <div class="modal-head">
                <h3 id="userModalTitle">Ajouter un utilisateur</h3>
                <button type="button" class="modal-close-btn" onclick="closeUserModal()">×</button>
            </div>

            <form id="userForm" method="POST" action="{{ route('utilisateurs.store') }}">
                @csrf
                <input type="hidden" name="_method" id="userFormMethod" value="POST">

                <div class="form-group">
                    <label for="form_name">Nom</label>
                    <input type="text" id="form_name" name="name" required placeholder="Ex : Camille Martin">
                </div>

                <div class="form-group">
                    <label for="form_user_email">Adresse email</label>
                    <input type="email" id="form_user_email" name="email" required placeholder="camille.martin@bde.fr">
                </div>

                <div class="form-group">
                    <label for="form_password">Mot de passe</label>
                    <input type="password" id="form_password" name="password" placeholder="8 caractères minimum">
                    <small id="passwordHelp" style="color:#c9bee6;">Requis à la création.</small>
                </div>

                <div class="form-group form-checkbox-row">
                    <label for="form_est_admin" class="checkbox-label">
                        <input type="checkbox" id="form_est_admin" name="est_admin" value="1">
                        Administrateur (accès complet, y compris suppression)
                    </label>
                    <small id="estAdminHelp" style="display:none; color:#c9bee6; margin-top: 8px;">
                        Vous ne pouvez pas retirer vos propres droits administrateur.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeUserModal()">Annuler</button>
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>
            </form>

            <form id="deleteUserForm" method="POST" action="" style="display:none; margin-top: 14px; text-align: right;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete" onclick="return confirm('Confirmer la suppression définitive de cet utilisateur ?')">
                    Supprimer cet utilisateur
                </button>
            </form>
        </div>
    </div>

    <footer class="accueil-footer">
        <div class="wrap footer-content">
            <p>&copy; 2026 BDE — Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Variables transmises au fichier externe JS -->
    <script>
        window.BDE_USERS = @json($utilisateursJson);
        window.BDE_USER_STORE_URL = "{{ route('utilisateurs.store') }}";
        window.BDE_CURRENT_USER_ID = {{ Auth::id() }};
    </script>
    <script src="{{ asset('js/gestion_utilisateur.js') }}"></script>

</body>
</html>
