const classementUrl = window.BDE_CLASSEMENT_URL || '/classement/data';

let classementData = [];
let classementLoaded = false;
let classementSortKey = 'points_bde';
let classementSortDir = 'desc'; // 'asc' | 'desc'

// Retire les accents (é, è, ê, à...) pour que la recherche fonctionne
// que l'utilisateur tape les accents ou non.
function normalizeText(str) {
    return (str || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Chaque mot tapé doit apparaître littéralement dans le nom (ordre libre entre les mots).
function matchesNom(pattern, nom) {
    const query = normalizeText(pattern).toLowerCase().trim();
    const haystack = normalizeText(nom).toLowerCase();

    if (!query) return true;

    return query.split(/\s+/).every(mot => haystack.includes(mot));
}

const SORT_KEYS = ['filiere', 'classe', 'nb_participations', 'points_bde'];

function openClassementModal() {
    const modal = document.getElementById('classementModal');
    if (modal) modal.classList.add('show');

    if (!classementLoaded) {
        chargerClassement();
    } else {
        renderClassementTable();
    }
}

function closeClassementModal() {
    const modal = document.getElementById('classementModal');
    if (modal) modal.classList.remove('show');
}

function chargerClassement() {
    const tbody = document.getElementById('classementTbody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="no-result">Chargement...</td></tr>';
    }

    fetch(classementUrl, { headers: { 'Accept': 'application/json' } })
        .then(r => {
            if (!r.ok) throw new Error('Erreur serveur');
            return r.json();
        })
        .then(data => {
            classementData = data;
            classementLoaded = true;
            renderClassementTable();
        })
        .catch(() => {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" class="no-result">Erreur lors du chargement du classement.</td></tr>';
            }
        });
}

function sortClassement(key) {
    if (classementSortKey === key) {
        classementSortDir = classementSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        classementSortKey = key;
        // Tri décroissant par défaut pour les valeurs numériques, croissant pour le texte
        classementSortDir = (key === 'nb_participations' || key === 'points_bde') ? 'desc' : 'asc';
    }
    renderClassementTable();
}

function onClassementSearchInput() {
    renderClassementTable();
}

function renderClassementTable() {
    const tbody = document.getElementById('classementTbody');
    if (!tbody) return;

    // Filtrage par recherche libre (nom uniquement)
    const query = document.getElementById('classementSearch')?.value || '';
    let list = classementData;
    if (query.trim()) {
        list = list.filter(et => matchesNom(query, et.nom));
    }

    // Tri
    const dirMultiplier = classementSortDir === 'asc' ? 1 : -1;
    list = [...list].sort((a, b) => {
        const valA = a[classementSortKey];
        const valB = b[classementSortKey];

        if (typeof valA === 'number' && typeof valB === 'number') {
            return (valA - valB) * dirMultiplier;
        }

        return String(valA || '').localeCompare(String(valB || '')) * dirMultiplier;
    });

    // Mise à jour des flèches de tri dans l'en-tête
    SORT_KEYS.forEach(key => {
        const icon = document.getElementById(`sortIcon-${key}`);
        if (!icon) return;
        icon.textContent = key === classementSortKey ? (classementSortDir === 'asc' ? '▲' : '▼') : '';
    });

    if (list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="no-result">Aucun étudiant trouvé.</td></tr>';
        return;
    }

    tbody.innerHTML = list.map((et, index) => `
        <tr>
            <td>${index + 1}</td>
            <td><strong>${et.nom || ''}</strong></td>
            <td>${et.prenom || ''}</td>
            <td>${et.filiere || ''}</td>
            <td>${et.classe || ''}</td>
            <td>${et.nb_participations ?? 0}</td>
            <td><span class="pts-badge">${et.points_bde ?? 0} pts</span></td>
        </tr>
    `).join('');
}
