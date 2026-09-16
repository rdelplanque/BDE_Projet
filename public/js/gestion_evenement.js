// Données globales injectées depuis Blade
const rawEvents = window.BDE_EVENTS || [];
const storeUrl = window.BDE_STORE_URL || '/evenements';
const classesData = window.BDE_CLASSES || [];
const searchEtudiantsUrl = window.BDE_SEARCH_ETUDIANTS_URL || '/participations/recherche-etudiants';
const validerUrlTemplate = window.BDE_VALIDER_URL_TEMPLATE || '/evenements/__ID__/valider-participation';
const annulerUrlTemplate = window.BDE_ANNULER_URL_TEMPLATE || '/evenements/__ID__/annuler-participation';

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth(); // 0-11
let currentEventId = null; // événement actuellement ouvert dans la modal (null = création)
let lastEditedEvent = null; // dernier événement édité, pour le bouton "Retour" de la modal de validation

const monthNames = [
    "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", 
    "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"
];

function initCalendar() {
    renderCalendar();
    renderEventsList(rawEvents);
}

function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    } else if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar();
}

function changeYear(delta) {
    currentYear += delta;
    renderCalendar();
}

function renderCalendar() {
    const title = document.getElementById('calendar-title');
    if (title) {
        title.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    }

    const daysContainer = document.getElementById('calendar-days');
    if (!daysContainer) return;
    daysContainer.innerHTML = '';

    // Premier jour du mois (0 = Dimanche, 1 = Lundi, etc.)
    const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();
    // Décalage pour commencer le Lundi (Lundi=0 ... Dimanche=6)
    const startingDay = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    // Cases vides avant le 1er du mois
    for (let i = 0; i < startingDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'day-cell empty';
        daysContainer.appendChild(emptyCell);
    }

    // Génération des jours du mois
    for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'day-cell';

        // Format YYYY-MM-DD
        const mStr = String(currentMonth + 1).padStart(2, '0');
        const dStr = String(day).padStart(2, '0');
        const fullDateStr = `${currentYear}-${mStr}-${dStr}`;

        const dayNum = document.createElement('span');
        dayNum.className = 'day-number';
        dayNum.textContent = day;
        dayCell.appendChild(dayNum);

        // Double-clic sur une case = créer un événement à cette date
        dayCell.addEventListener('dblclick', function () {
            openCreateModal(fullDateStr);
        });

        // Filtrer les événements pour cette date
        const matchedEvents = rawEvents.filter(ev => {
            return ev.date_evenement && ev.date_evenement.startsWith(fullDateStr);
        });

        matchedEvents.forEach(ev => {
            const pill = document.createElement('div');
            pill.className = 'event-pill';
            pill.textContent = ev.nom;
            pill.title = `${ev.nom} (Cliquer pour modifier)`;
            // Simple clic sur la pastille = modifier
            pill.addEventListener('click', function (e) {
                e.stopPropagation();
                openEditModal(ev);
            });
            dayCell.appendChild(pill);
        });

        daysContainer.appendChild(dayCell);
    }
}

// --- GESTION DE LA MODALE ---
function openCreateModal(dateStr = null) {
    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('formMethod');
    const form = document.getElementById('eventForm');
    const inscritsBox = document.getElementById('inscritsBox');
    if (inscritsBox) inscritsBox.style.display = 'none';

    currentEventId = null;
    lastEditedEvent = null;
    const btnOpenValidation = document.getElementById('btnOpenValidation');
    if (btnOpenValidation) btnOpenValidation.style.display = 'none';

    if (title) title.textContent = 'Créer un événement';
    if (methodInput) methodInput.value = 'POST';
    if (form) form.action = storeUrl;

    // Réinitialisation des champs
    document.getElementById('form_nom').value = '';
    document.getElementById('form_prix').value = '0.00';
    document.getElementById('form_places').value = '';
    document.getElementById('form_gain').value = '10';
    document.getElementById('form_detail').value = '';

    if (dateStr) {
        document.getElementById('form_date').value = `${dateStr}T18:00`;
    } else {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('form_date').value = now.toISOString().slice(0, 16);
    }

    const delForm = document.getElementById('deleteEventForm');
    if (delForm) delForm.style.display = 'none';

    const modal = document.getElementById('eventModal');
    if (modal) modal.classList.add('show');
}

function openEditModal(ev) {
    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('formMethod');
    const form = document.getElementById('eventForm');
    // Gestion de l'encart d'inscrits
    const inscritsBox = document.getElementById('inscritsBox');
    const inscritsDisplay = document.getElementById('inscritsDisplay');
    const progressBar = document.getElementById('inscritsProgressBar');

    if (inscritsBox) {
        inscritsBox.style.display = 'block';
        // Si le champ s'appelle inscrits_count ou etudiants_count ou inscrits
        const count = ev.inscrits_count ?? ev.etudiants_count ?? ev.inscrits ?? 0;
        const max = ev.nombre_place;

        if (max && max > 0) {
            inscritsDisplay.textContent = `${count} / ${max} place(s)`;
            const pct = Math.min(100, Math.round((count / max) * 100));
            if (progressBar) progressBar.style.width = `${pct}%`;
        } else {
            inscritsDisplay.textContent = `${count} inscrit(s) (Places illimitées)`;
            if (progressBar) progressBar.style.width = '100%';
        }
    }

    if (title) title.textContent = 'Modifier un événement';
    if (methodInput) methodInput.value = 'PUT';
    if (form) form.action = `/evenements/${ev.id}`;

    document.getElementById('form_nom').value = ev.nom || '';
    if (ev.date_evenement) {
        document.getElementById('form_date').value = ev.date_evenement.slice(0, 16);
    }
    document.getElementById('form_prix').value = ev.prix || 0;
    document.getElementById('form_places').value = ev.nombre_place || '';
    document.getElementById('form_gain').value = ev.gain_base || 0;
    document.getElementById('form_detail').value = ev.detail || '';

    const delForm = document.getElementById('deleteEventForm');
    if (delForm) {
        delForm.action = `/evenements/${ev.id}`;
        delForm.style.display = 'block';
    }

    // Le bouton "Gérer les participations" n'apparaît que sur un événement existant
    currentEventId = ev.id;
    lastEditedEvent = ev;
    const btnOpenValidation = document.getElementById('btnOpenValidation');
    if (btnOpenValidation) btnOpenValidation.style.display = 'inline-block';

    const modal = document.getElementById('eventModal');
    if (modal) modal.classList.add('show');
}

function closeEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) modal.classList.remove('show');
}

// --- RECHERCHE EN TEMPS RÉEL (liste des événements) ---

// Retire les accents (é, è, ê, à...) pour que la recherche fonctionne
// que l'utilisateur tape les accents ou non.
function normalizeText(str) {
    return (str || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Chaque mot tapé doit apparaître littéralement quelque part dans le texte
// (ordre libre entre les mots). Plus fiable qu'une correspondance caractère
// par caractère, qui devient quasiment aléatoire sur un texte long (ex : le
// champ "détail" d'un événement).
function fuzzyMatch(pattern, text) {
    const query = normalizeText(pattern).toLowerCase().trim();
    const haystack = normalizeText(text).toLowerCase();

    if (!query) return true;

    return query.split(/\s+/).every(mot => haystack.includes(mot));
}

function filterEventsList() {
    const input = document.getElementById('eventSearchInput');
    if (!input) return;
    const query = input.value.trim();

    if (!query) {
        renderEventsList(rawEvents);
        return;
    }

    const filtered = rawEvents.filter(ev => fuzzyMatch(query, ev.nom));

    renderEventsList(filtered);
}

function renderEventsList(events) {
    const container = document.getElementById('eventsListResults');
    if (!container) return;
    container.innerHTML = '';

    if (events.length === 0) {
        container.innerHTML = '<p class="no-result">Aucun événement trouvé.</p>';
        return;
    }

    events.forEach(ev => {
        const item = document.createElement('div');
        item.className = 'event-result-item';
        item.onclick = () => openEditModal(ev);

        const dateFormatted = ev.date_evenement ? ev.date_evenement.replace('T', ' à ') : '';

        item.innerHTML = `
            <div class="result-info">
                <h4>${ev.nom}</h4>
                <p>Date : ${dateFormatted} | Prix : ${ev.prix || 0} € | Points : ${ev.gain_base || 0}</p>
            </div>
            <span class="result-action">Modifier &rarr;</span>
        `;
        container.appendChild(item);
    });
}

// --- VALIDATION DE PARTICIPATION (modal séparée) ---

function openValidationModal() {
    if (!currentEventId) return;

    closeEventModal();

    resetValidationFilters();
    rechercherEtudiantsValidation();

    const modal = document.getElementById('validationModal');
    if (modal) modal.classList.add('show');
}

function closeValidationModal() {
    const modal = document.getElementById('validationModal');
    if (modal) modal.classList.remove('show');
}

function backToEventModal() {
    closeValidationModal();
    if (lastEditedEvent) {
        openEditModal(lastEditedEvent);
    }
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function resetValidationFilters() {
    const filiereSelect = document.getElementById('val_filiere');
    const classeSelect = document.getElementById('val_classe');
    const searchInput = document.getElementById('val_recherche');
    const resultats = document.getElementById('val_resultats');

    if (filiereSelect) filiereSelect.value = '';
    if (classeSelect) classeSelect.innerHTML = '<option value="">Toutes les classes</option>';
    if (searchInput) searchInput.value = '';
    if (resultats) {
        resultats.innerHTML = '<p class="no-result">Utilisez les filtres ci-dessus pour rechercher un étudiant.</p>';
    }
}

// Recalcule les options du select Classe en fonction de la filière choisie
function onFiliereChange() {
    const filiereSelect = document.getElementById('val_filiere');
    const classeSelect = document.getElementById('val_classe');
    if (!filiereSelect || !classeSelect) return;

    const filiereId = filiereSelect.value;
    classeSelect.innerHTML = '<option value="">Toutes les classes</option>';

    classesData
        .filter(c => !filiereId || String(c.filiere_id) === String(filiereId))
        .sort((a, b) => (a.nom || '').localeCompare(b.nom || ''))
        .forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nom;
            classeSelect.appendChild(opt);
        });

    rechercherEtudiantsValidation();
}

let validationSearchTimer = null;
function onValidationSearchInput() {
    clearTimeout(validationSearchTimer);
    validationSearchTimer = setTimeout(rechercherEtudiantsValidation, 300);
}

function rechercherEtudiantsValidation() {
    if (!currentEventId) return;

    const resultats = document.getElementById('val_resultats');
    if (!resultats) return;

    const filiereId = document.getElementById('val_filiere')?.value || '';
    const classeId = document.getElementById('val_classe')?.value || '';
    const nom = document.getElementById('val_recherche')?.value.trim() || '';

    const params = new URLSearchParams({
        evenement_id: currentEventId,
        filiere_id: filiereId,
        classe_id: classeId,
        nom: nom,
    });

    resultats.innerHTML = '<p class="no-result">Recherche...</p>';

    fetch(`${searchEtudiantsUrl}?${params.toString()}`, {
        headers: { 'Accept': 'application/json' },
    })
        .then(r => r.json())
        .then(list => renderResultatsValidation(list))
        .catch(() => {
            resultats.innerHTML = '<p class="no-result">Erreur lors de la recherche.</p>';
        });
}

function renderResultatsValidation(list) {
    const resultats = document.getElementById('val_resultats');
    if (!resultats) return;

    if (!list || list.length === 0) {
        resultats.innerHTML = '<p class="no-result">Aucun étudiant trouvé.</p>';
        return;
    }

    resultats.innerHTML = '';
    list.forEach(et => {
        const row = document.createElement('div');
        row.className = 'val-resultat-item';

        const info = document.createElement('div');
        info.className = 'val-resultat-info';
        const sousLigne = [et.classe, et.filiere].filter(Boolean).join(' · ');
        info.innerHTML = `
            <strong>${et.nom} ${et.prenom}</strong>
            <span>${sousLigne}</span>
        `;

        const btn = document.createElement('button');
        btn.type = 'button';
        appliquerEtatBoutonValidation(btn, et.deja_valide);
        btn.addEventListener('click', () => toggleParticipation(et.id, btn));

        row.appendChild(info);
        row.appendChild(btn);
        resultats.appendChild(row);
    });
}

// Met à jour le texte/style/état d'un bouton selon que la participation est validée ou non.
function appliquerEtatBoutonValidation(btnEl, estValide) {
    btnEl.disabled = false;
    if (estValide) {
        btnEl.textContent = '✓ Validé (cliquer pour annuler)';
        btnEl.className = 'btn-valide-done';
        btnEl.dataset.valide = '1';
    } else {
        btnEl.textContent = 'Valider';
        btnEl.className = 'btn-valider';
        btnEl.dataset.valide = '0';
    }
}

// Met à jour l'encart "Inscriptions actuelles" de la modal événement.
function updateInscritsDisplay(nbInscrits) {
    const inscritsDisplay = document.getElementById('inscritsDisplay');
    const progressBar = document.getElementById('inscritsProgressBar');
    if (!inscritsDisplay) return;

    const ev = rawEvents.find(e => e.id === currentEventId);
    const max = ev ? ev.nombre_place : null;

    if (max && max > 0) {
        inscritsDisplay.textContent = `${nbInscrits} / ${max} place(s)`;
        if (progressBar) {
            progressBar.style.width = `${Math.min(100, Math.round((nbInscrits / max) * 100))}%`;
        }
    } else {
        inscritsDisplay.textContent = `${nbInscrits} inscrit(s) (Places illimitées)`;
    }
}

// Bascule l'état d'un étudiant pour l'événement courant : valide s'il ne l'était pas, annule sinon.
function toggleParticipation(etudiantId, btnEl) {
    if (!currentEventId) return;

    const estActuellementValide = btnEl.dataset.valide === '1';
    const url = (estActuellementValide ? annulerUrlTemplate : validerUrlTemplate)
        .replace('__ID__', currentEventId);

    btnEl.disabled = true;
    btnEl.textContent = '...';

    fetch(url, {
        method: estActuellementValide ? 'DELETE' : 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ etudiant_id: etudiantId }),
    })
        .then(r => {
            if (!r.ok) throw new Error('Erreur serveur');
            return r.json();
        })
        .then(data => {
            appliquerEtatBoutonValidation(btnEl, !estActuellementValide);
            updateInscritsDisplay(data.nb_inscrits);
        })
        .catch(() => {
            appliquerEtatBoutonValidation(btnEl, estActuellementValide);
            alert(estActuellementValide
                ? "Une erreur est survenue lors de l'annulation."
                : "Une erreur est survenue lors de la validation.");
        });
}

// Initialisation au chargement de la page
window.addEventListener('DOMContentLoaded', initCalendar);
