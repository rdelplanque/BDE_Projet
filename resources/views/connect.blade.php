<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - BDE</title>
    <link rel="preconnect" href="[https://fonts.googleapis.com](https://fonts.googleapis.com)">
    <link rel="preconnect" href="[https://fonts.gstatic.com](https://fonts.gstatic.com)" crossorigin>
    <link href="[https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap](https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap)" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/connect.css') }}">
</head>
<body class="page-login">

    <header class="login-header">
        <div class="wrap login-header-wrap">
            <div class="logo-mark">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo BDE" class="header-logo-img">
                <span>Bienvenue au BDE</span>
            </div>
        </div>
    </header>

    <main class="login-main">
        <div class="login-card">
            <h1>Connexion</h1>
            <p class="login-desc">Accès réservé aux gestionnaires du BDE.</p>

            @if ($errors->any())
                <div class="alert-box">
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" autocomplete="off">
                @csrf

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="" 
                        required 
                        autofocus
                        autocomplete="off"
                        placeholder="exemple@bde.fr"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn-submit">Se connecter</button>
            </form>
        </div>
    </main>

    <footer class="login-footer">
        <div class="wrap login-footer-content">
            <p>&copy; 2026 BDE — Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        window.addEventListener('pageshow', function () {
            var emailInput = document.getElementById('email');
            var passInput = document.getElementById('password');
            if (emailInput) emailInput.value = '';
            if (passInput) passInput.value = '';
        });
    </script>

</body>
</html>