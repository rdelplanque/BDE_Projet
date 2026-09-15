<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - BDE</title>
    <link rel="preconnect" href="[https://fonts.googleapis.com](https://fonts.googleapis.com)">
    <link rel="preconnect" href="[https://fonts.gstatic.com](https://fonts.gstatic.com)" crossorigin>
    <link href="[https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap](https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap)" rel="stylesheet">
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
            <p class="accueil-desc">Sélectionne un espace de gestion pour continuer.</p>

            <div class="actions-grid">
                <a href="{{ route('evenements.index') }}" class="btn-action" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                    Gestion des événements
                </a>

                <a href="{{ route('etudiants.index') }}" class="btn-action" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                    Gestion des étudiants
                </a>

                <button type="button" class="btn-action">
                    Validation des participations
                </button>
            </div>
        </div>
    </main>

    <footer class="accueil-footer">
        <div class="wrap footer-content">
            <p>&copy; 2026 BDE — Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>