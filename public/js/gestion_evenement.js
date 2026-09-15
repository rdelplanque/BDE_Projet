// Données globales injectées depuis Blade
const rawEvents = window.BDE_EVENTS || [];
const storeUrl = window.BDE_STORE_URL || '/evenements';

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth(); // 0-11

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

    const modal = document.getElementById('eventModal');
    if (modal) modal.classList.add('show');
}

function closeEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) modal.classList.remove('show');
}

// --- RECHERCHE FLOUE EN TEMPS RÉEL ---
function fuzzyMatch(pattern, text) {
    pattern = pattern.toLowerCase();
    text = text.toLowerCase();
    let pIdx = 0;
    let tIdx = 0;
    while (pIdx < pattern.length && tIdx < text.length) {
        if (pattern[pIdx] === text[tIdx]) {
            pIdx++;
        }
        tIdx++;
    }
    return pIdx === pattern.length;
}

function filterEventsList() {
    const input = document.getElementById('eventSearchInput');
    if (!input) return;
    const query = input.value.trim();

    if (!query) {
        renderEventsList(rawEvents);
        return;
    }

    const filtered = rawEvents.filter(ev => {
        const haystack = `${ev.nom || ''} ${ev.detail || ''} ${ev.date_evenement || ''}`;
        return fuzzyMatch(query, haystack);
    });

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

// Initialisation au chargement de la page
window.addEventListener('DOMContentLoaded', initCalendar);