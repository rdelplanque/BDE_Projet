<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestion des Événements - BDE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gestion_evenement.css') }}">
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
                        <h1>Planning des Événements</h1>
                        <p class="toolbar-desc">Double-cliquez sur une case pour ajouter un événement à cette date.</p>
                    </div>
                    <button type="button" class="btn-create-event" onclick="openCreateModal()">
                        + Créer un événement
                    </button>
                </div>

                <div class="calendar-controls">
                    <div class="nav-buttons">
                        <button type="button" class="btn-cal-nav" onclick="changeYear(-1)" title="Année précédente">«</button>
                        <button type="button" class="btn-cal-nav" onclick="changeMonth(-1)" title="Mois précédent">‹</button>
                    </div>
                    <h2 id="calendar-title">Mois Année</h2>
                    <div class="nav-buttons">
                        <button type="button" class="btn-cal-nav" onclick="changeMonth(1)" title="Mois suivant">›</button>
                        <button type="button" class="btn-cal-nav" onclick="changeYear(1)" title="Année suivante">»</button>
                    </div>
                </div>

                <div class="calendar-grid-header">
                    <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
                </div>
                <div class="calendar-days-grid" id="calendar-days"></div>
            </div>

            <div class="search-section">
                <div class="search-box-wrap">
                    <label for="eventSearchInput">Recherche d'événements</label>
                    <input 
                        type="text" 
                        id="eventSearchInput" 
                        placeholder="Tapez un nom, un mot-clé ou un détail (recherche en temps réel)..."
                        oninput="filterEventsList()"
                    >
                </div>

                <div class="events-list-results" id="eventsListResults"></div>
            </div>

        </div>
    </main>

    <!-- MODAL : Créer / modifier un événement -->
    <div class="modal-backdrop" id="eventModal">
        <div class="modal-card">
            <div class="modal-head">
                <h3 id="modalTitle">Créer un événement</h3>
                <button type="button" class="modal-close-btn" onclick="closeEventModal()">×</button>
            </div>
            <div id="inscritsBox" class="inscrits-card" style="display: none;">
                <div class="inscrits-info">
                    <span class="inscrits-label">Inscriptions actuelles</span>
                    <span class="inscrits-val" id="inscritsDisplay">0 inscrit(s)</span>
                </div>
                <div class="inscrits-progress-bg">
                    <div class="inscrits-progress-bar" id="inscritsProgressBar" style="width: 0%;"></div>
                </div>
            </div>

            <button
                type="button"
                id="btnOpenValidation"
                class="btn-manage-participations"
                style="display: none;"
                onclick="openValidationModal()"
            >
                Gérer les participations
            </button>

            <form id="eventForm" method="POST" action="{{ route('evenements.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="form-group">
                    <label for="form_nom">Nom de l'événement</label>
                    <input type="text" id="form_nom" name="nom" required placeholder="Ex : Soirée d'intégration">
                </div>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="form_date">Date et heure</label>
                        <input type="datetime-local" id="form_date" name="date_evenement" required>
                    </div>

                    <div class="form-group col-half">
                        <label for="form_prix">Prix (€)</label>
                        <input type="number" step="0.01" id="form_prix" name="prix" value="0.00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="form_places">Nombre de places</label>
                        <input type="number" id="form_places" name="nombre_place" placeholder="Illimité si vide">
                    </div>

                    <div class="form-group col-half">
                        <label for="form_gain">Gain de points BDE</label>
                        <input type="number" id="form_gain" name="gain_base" value="10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="form_detail">Description / Détails</label>
                    <textarea id="form_detail" name="detail" rows="3" placeholder="Informations complémentaires, lieu, consignes..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeEventModal()">Annuler</button>
                    <button type="submit" class="btn-save" id="btnSaveModal">Enregistrer</button>
                    @if(Auth::user() && Auth::user()->est_admin)
                        <form id="deleteEventForm" method="POST" action="" style="display:none; margin-top: 12px; text-align: right;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete" onclick="return confirm('Confirmer la suppression de cet événement ?')">
                                Supprimer cet événement
                            </button>
                        </form>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL : Valider une participation (séparée de la modal événement) -->
    <div class="modal-backdrop" id="validationModal">
        <div class="modal-card">
            <div class="modal-head">
                <h3>Valider une participation</h3>
                <button type="button" class="modal-close-btn" onclick="closeValidationModal()">×</button>
            </div>

            <div class="form-row">
                <div class="form-group col-half">
                    <label for="val_filiere">Filière</label>
                    <select id="val_filiere" onchange="onFiliereChange()">
                        <option value="">Toutes les filières</option>
                        @foreach($filieres as $filiere)
                            <option value="{{ $filiere->id }}">{{ $filiere->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-half">
                    <label for="val_classe">Classe</label>
                    <select id="val_classe" onchange="rechercherEtudiantsValidation()">
                        <option value="">Toutes les classes</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="val_recherche">Nom / prénom</label>
                <input
                    type="text"
                    id="val_recherche"
                    placeholder="Tapez un nom pour affiner la recherche..."
                    oninput="onValidationSearchInput()"
                >
            </div>

            <div id="val_resultats" class="val-resultats-list">
                <p class="no-result">Utilisez les filtres ci-dessus pour rechercher un étudiant.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="backToEventModal()">← Retour à l'événement</button>
                <button type="button" class="btn-cancel" onclick="closeValidationModal()">Fermer</button>
            </div>
        </div>
    </div>

    <footer class="accueil-footer">
        <div class="wrap footer-content">
            <p>&copy; 2026 BDE — Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Transmission propre des données Blade vers JS externe -->
    <script>
        window.BDE_EVENTS = @json($evenements ?? []);
        window.BDE_STORE_URL = "{{ route('evenements.store') }}";
        window.BDE_CLASSES = @json($classes ?? []);
        window.BDE_SEARCH_ETUDIANTS_URL = "{{ route('participations.rechercher-etudiants') }}";
        window.BDE_VALIDER_URL_TEMPLATE = "{{ route('participations.valider', ['evenement' => '__ID__']) }}";
    </script>
    <script src="{{ asset('js/gestion_evenement.js') }}"></script>

</body>
</html>
