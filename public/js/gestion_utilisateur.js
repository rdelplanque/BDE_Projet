const rawUsers = window.BDE_USERS || [];
const storeUserUrl = window.BDE_USER_STORE_URL || '/utilisateurs';
const currentUserId = window.BDE_CURRENT_USER_ID || null;

// Retire les accents (é, è, ê, à...)
function normalizeText(str) {
    return (str || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Chaque mot tapé doit apparaître dans le nom.
function fuzzyMatch(pattern, text) {
    const query = normalizeText(pattern).toLowerCase().trim();
    const haystack = normalizeText(text).toLowerCase();

    if (!query) return true;

    return query.split(/\s+/).every(mot => haystack.includes(mot));
}

function renderUsers(usersList) {
    const tbody = document.getElementById('usersTbody');
    if (!tbody) return;

    if (usersList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="no-result">Aucun utilisateur trouvé.</td></tr>';
        return;
    }

    tbody.innerHTML = usersList.map(u => `
        <tr>
            <td><strong>${u.name || ''}</strong></td>
            <td>${u.email || ''}</td>
            <td>${u.est_admin ? '<span class="pts-badge">Admin</span>' : 'Non'}</td>
            <td style="text-align: right;">
                <button type="button" class="btn-table-edit" onclick='openEditUser(${JSON.stringify(u)})'>
                    Modifier
                </button>
            </td>
        </tr>
    `).join('');
}

function filterUsers() {
    const input = document.getElementById('userSearchInput');
    if (!input) return;
    const query = input.value.trim();

    if (!query) {
        renderUsers(rawUsers);
        return;
    }

    const filtered = rawUsers.filter(u => fuzzyMatch(query, u.name));
    renderUsers(filtered);
}

// GESTION MODALE

function openUserModal(mode) {
    const title = document.getElementById('userModalTitle');
    const form = document.getElementById('userForm');
    const methodInput = document.getElementById('userFormMethod');
    const delForm = document.getElementById('deleteUserForm');
    const passwordInput = document.getElementById('form_password');
    const passwordHelp = document.getElementById('passwordHelp');

    if (mode === 'create') {
        title.textContent = 'Ajouter un utilisateur';
        form.action = storeUserUrl;
        methodInput.value = 'POST';

        document.getElementById('form_name').value = '';
        document.getElementById('form_user_email').value = '';

        const estAdminCheckbox = document.getElementById('form_est_admin');
        estAdminCheckbox.checked = false;
        estAdminCheckbox.onchange = null; // reset si un précédent onEditUser avait posé un blocage

        const estAdminHelp = document.getElementById('estAdminHelp');
        if (estAdminHelp) estAdminHelp.style.display = 'none';

        if (passwordInput) {
            passwordInput.value = '';
            passwordInput.required = true;
        }
        if (passwordHelp) passwordHelp.textContent = 'Requis à la création.';

        if (delForm) delForm.style.display = 'none';
    }

    document.getElementById('userModal').classList.add('show');
}

function openEditUser(u) {
    const title = document.getElementById('userModalTitle');
    const form = document.getElementById('userForm');
    const methodInput = document.getElementById('userFormMethod');
    const delForm = document.getElementById('deleteUserForm');
    const passwordInput = document.getElementById('form_password');
    const passwordHelp = document.getElementById('passwordHelp');

    title.textContent = 'Modifier un utilisateur';
    form.action = `/utilisateurs/${u.id}`;
    methodInput.value = 'PUT';

    document.getElementById('form_name').value = u.name || '';
    document.getElementById('form_user_email').value = u.email || '';

    const estAdminCheckbox = document.getElementById('form_est_admin');
    const estAdminHelp = document.getElementById('estAdminHelp');
    estAdminCheckbox.checked = !!u.est_admin;

    const estSoiMeme = currentUserId && u.id === currentUserId;
    const bloquerRetraitAdmin = estSoiMeme && u.est_admin;

    // On ne peut pas se retirer soi-même les droits admin.
    // On n'utilise pas `disabled` car un champ désactivé n'est pas envoyé avec le formulaire DONC ferait croire au serveur qu'on veut retirer le statut admin. 
    estAdminCheckbox.onchange = bloquerRetraitAdmin
        ? () => { estAdminCheckbox.checked = true; }
        : null;

    if (estAdminHelp) {
        estAdminHelp.style.display = bloquerRetraitAdmin ? 'block' : 'none';
    }

    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.required = false;
    }
    if (passwordHelp) passwordHelp.textContent = 'Laisser vide pour ne pas changer le mot de passe.';

    if (delForm) {
        // On ne peut pas supprimer son propre compte (vérifié aussi côté serveur)
        if (currentUserId && u.id === currentUserId) {
            delForm.style.display = 'none';
        } else {
            delForm.action = `/utilisateurs/${u.id}`;
            delForm.style.display = 'block';
        }
    }

    document.getElementById('userModal').classList.add('show');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

window.addEventListener('DOMContentLoaded', () => {
    renderUsers(rawUsers);
});
