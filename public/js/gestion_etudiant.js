const rawStudents = window.BDE_STUDENTS || [];
const storeStudentUrl = window.BDE_STUDENT_STORE_URL || '/etudiants';
const isAdmin = window.BDE_IS_ADMIN || false;

let currentEditingStudentId = null; // étudiant actuellement ouvert dans la modal (null = création)

// Construction de l'arborescence Filière -> Classe -> Étudiants
function buildHierarchy(studentsList) {
    const hierarchy = {};

    studentsList.forEach(st => {
        const fil = st.filiere || 'Non renseignée';
        const cls = st.classe || 'Sans classe';

        if (!hierarchy[fil]) {
            hierarchy[fil] = {};
        }
        if (!hierarchy[fil][cls]) {
            hierarchy[fil][cls] = [];
        }
        hierarchy[fil][cls].push(st);
    });

    return hierarchy;
}

function renderStudents(studentsList, forceExpand = false) {
    const container = document.getElementById('filieresContainer');
    if (!container) return;
    container.innerHTML = '';

    if (studentsList.length === 0) {
        container.innerHTML = `
            <div class="gestion-card" style="text-align:center; color:#c9bee6;">
                Aucun étudiant trouvé.
            </div>
        `;
        return;
    }

    const hierarchy = buildHierarchy(studentsList);

    Object.keys(hierarchy).sort().forEach(filiereName => {
        const classes = hierarchy[filiereName];
        let totalFiliereCount = 0;
        Object.values(classes).forEach(c => totalFiliereCount += c.length);

        const filiereBlock = document.createElement('div');
        filiereBlock.className = 'filiere-block';

        const fHeader = document.createElement('div');
        fHeader.className = 'filiere-header';
        fHeader.innerHTML = `
            <div class="filiere-title-wrap">
                <h2>${filiereName}</h2>
                <span class="badge-count">${totalFiliereCount} étudiant(s)</span>
            </div>
            <span class="filiere-toggle-icon">${forceExpand ? '▲' : '▼'}</span>
        `;

        const fBody = document.createElement('div');
        fBody.className = 'filiere-body';
        fBody.style.display = forceExpand ? 'flex' : 'none';

        fHeader.addEventListener('click', () => {
            const isVisible = fBody.style.display === 'flex';
            fBody.style.display = isVisible ? 'none' : 'flex';
            fHeader.querySelector('.filiere-toggle-icon').innerHTML = isVisible ? '▼' : '▲';
        });

        // Génération des classes
        Object.keys(classes).sort().forEach(classeName => {
            const studentsInClass = classes[classeName];

            const clsBlock = document.createElement('div');
            clsBlock.className = 'classe-block';

            const clsHeader = document.createElement('div');
            clsHeader.className = 'classe-header';
            clsHeader.innerHTML = `
                <div class="classe-title-wrap">
                    <h3>Classe : ${classeName}</h3>
                    <span class="badge-classe-count">${studentsInClass.length}</span>
                </div>
                <span style="color:#c9bee6;">${forceExpand ? '▲' : '▼'}</span>
            `;

            const clsTableWrap = document.createElement('div');
            clsTableWrap.className = 'classe-table-wrap';
            clsTableWrap.style.display = forceExpand ? 'block' : 'none';

            clsHeader.addEventListener('click', () => {
                const isTableVisible = clsTableWrap.style.display === 'block';
                clsTableWrap.style.display = isTableVisible ? 'none' : 'block';
                clsHeader.querySelector('span:last-child').innerHTML = isTableVisible ? '▼' : '▲';
            });

            // Construction de la table
            let rowsHtml = '';
            studentsInClass.forEach(st => {
                rowsHtml += `
                    <tr>
                        <td><strong>${st.nom || ''}</strong></td>
                        <td>${st.prenom || ''}</td>
                        <td>${st.email || ''}</td>
                        <td>${st.nb_participations ?? 0}</td>
                        <td><span class="pts-badge">${st.points_bde ?? 0} pts</span></td>
                        <td style="text-align: right;">
                            <button type="button" class="btn-table-edit" onclick='openEditStudent(${JSON.stringify(st)})'>
                                Modifier
                            </button>
                        </td>
                    </tr>
                `;
            });

            clsTableWrap.innerHTML = `
                <table class="table-students">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Participations</th>
                            <th>Points</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            `;

            clsBlock.appendChild(clsHeader);
            clsBlock.appendChild(clsTableWrap);
            fBody.appendChild(clsBlock);
        });

        filiereBlock.appendChild(fHeader);
        filiereBlock.appendChild(fBody);
        container.appendChild(filiereBlock);
    });
}

// Recherche floue
// Retire les accents (é, è, ê, à...) pour que la recherche fonctionne
// que l'utilisateur tape les accents ou non.
function normalizeText(str) {
    return (str || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Chaque mot tapé doit apparaître littéralement dans le nom (ordre libre entre les mots).
function fuzzyMatch(pattern, text) {
    const query = normalizeText(pattern).toLowerCase().trim();
    const haystack = normalizeText(text).toLowerCase();

    if (!query) return true;

    return query.split(/\s+/).every(mot => haystack.includes(mot));
}

function filterStudents() {
    const input = document.getElementById('studentSearchInput');
    if (!input) return;
    const query = input.value.trim();

    if (!query) {
        renderStudents(rawStudents, false);
        return;
    }

    const filtered = rawStudents.filter(st => fuzzyMatch(query, st.nom));

    // En cours de recherche, on déplie automatiquement les résultats
    renderStudents(filtered, true);
}

// GESTION MODALES

function onFiliereChanged() {
    const filiereSelect = document.getElementById('form_filiere_select');
    const classeSelect = document.getElementById('form_classe_id');
    if (!filiereSelect || !classeSelect) return;

    const filiereId = filiereSelect.value;
    classeSelect.value = '';

    Array.from(classeSelect.options).forEach(option => {
        if (!option.value) return; // on garde toujours le placeholder visible
        const appartient = filiereId !== '' && option.dataset.filiereId === filiereId;
        option.hidden = !appartient;
        option.disabled = !appartient;
    });
}

function openStudentModal(mode) {
    const title = document.getElementById('studentModalTitle');
    const form = document.getElementById('studentForm');
    const methodInput = document.getElementById('studentFormMethod');
    const delForm = document.getElementById('deleteStudentForm');
    const btnAjouterPoints = document.getElementById('btnAjouterPoints');

    if (mode === 'create') {
        title.textContent = 'Ajouter un étudiant';
        form.action = storeStudentUrl;
        methodInput.value = 'POST';

        currentEditingStudentId = null;
        if (btnAjouterPoints) btnAjouterPoints.style.display = 'none';

        // Réinitialisation des champs existants
        const selectFiliere = document.getElementById('form_filiere_select');
        if (selectFiliere) selectFiliere.value = '';
        onFiliereChanged(); // vide/cache le select classe tant qu'aucune filière n'est choisie

        document.getElementById('form_nom').value = '';
        document.getElementById('form_prenom').value = '';
        document.getElementById('form_email').value = '';
        document.getElementById('form_participations').value = '0';
        document.getElementById('form_points').value = '0';

        if (delForm) delForm.style.display = 'none';
    }

    document.getElementById('studentModal').classList.add('show');
}

function openEditStudent(st) {
    const title = document.getElementById('studentModalTitle');
    const form = document.getElementById('studentForm');
    const methodInput = document.getElementById('studentFormMethod');
    const delForm = document.getElementById('deleteStudentForm');
    const btnAjouterPoints = document.getElementById('btnAjouterPoints');

    title.textContent = 'Modifier un étudiant';
    form.action = `/etudiants/${st.id}`;
    methodInput.value = 'PUT';

    currentEditingStudentId = st.id;
    if (btnAjouterPoints) btnAjouterPoints.style.display = 'inline-block';

    const selectClasse = document.getElementById('form_classe_id');
    const selectFiliere = document.getElementById('form_filiere_select');

    if (selectClasse && selectFiliere) {
        // Retrouve la filière correspondant à la classe de cet étudiant, pour pré-filtrer le select
        const optionClasse = Array.from(selectClasse.options)
            .find(opt => opt.value == st.classe_id);

        selectFiliere.value = optionClasse ? optionClasse.dataset.filiereId : '';
        onFiliereChanged(); // filtre les classes de cette filière + reset la valeur
        selectClasse.value = st.classe_id || ''; // ré-assigne après le filtre
    }

    document.getElementById('form_nom').value = st.nom || '';
    document.getElementById('form_prenom').value = st.prenom || '';
    document.getElementById('form_email').value = st.email || '';
    document.getElementById('form_participations').value = st.nb_participations ?? 0;
    document.getElementById('form_points').value = st.points_bde ?? 0;

    if (delForm) {
        if (isAdmin) {
            delForm.action = `/etudiants/${st.id}`;
            delForm.style.display = 'block';
        } else {
            delForm.style.display = 'none';
        }
    }

    document.getElementById('studentModal').classList.add('show');
}

function openSelectStudentModal() {
    // Si l'utilisateur clique sur "Modifier un étudiant" depuis le haut, focus sur la recherche
    const searchInput = document.getElementById('studentSearchInput');
    if (searchInput) {
        searchInput.focus();
        searchInput.placeholder = "Recherchez l'étudiant à modifier ci-dessous...";
    }
}

function closeStudentModal() {
    document.getElementById('studentModal').classList.remove('show');
}

function openImportModal() {
    document.getElementById('importModal').classList.add('show');
}

function closeImportModal() {
    document.getElementById('importModal').classList.remove('show');
}

function openExportModal() {
    document.getElementById('exportModal').classList.add('show');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.remove('show');
}

// --- AJOUT DE POINTS BDE (bonus manuel) ---

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function ajouterPointsBde() {
    if (!currentEditingStudentId) return;

    const saisie = prompt('Combien de points ajouter ? (nombre négatif pour retirer des points)');
    if (saisie === null) return; // annulé

    const points = parseInt(saisie, 10);
    if (isNaN(points)) {
        alert('Merci de saisir un nombre entier valide.');
        return;
    }

    fetch(`/etudiants/${currentEditingStudentId}/ajouter-points`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ points }),
    })
        .then(r => {
            if (!r.ok) throw new Error('Erreur serveur');
            return r.json();
        })
        .then(data => {
            const pointsField = document.getElementById('form_points');
            if (pointsField) pointsField.value = data.points_bde;

            // Met à jour la donnée en mémoire pour que le tableau reflète le changement sans recharger la page
            const st = rawStudents.find(s => s.id === currentEditingStudentId);
            if (st) {
                st.points_bde = data.points_bde;
                renderStudents(rawStudents, false);
            }
        })
        .catch(() => {
            alert("Une erreur est survenue lors de l'ajout des points.");
        });
}

window.addEventListener('DOMContentLoaded', () => {
    renderStudents(rawStudents, false);
});
