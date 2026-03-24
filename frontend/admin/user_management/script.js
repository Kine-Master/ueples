// frontend/admin/user_management/script.js

let allUsers = [];
let pendingToggleId = null;
let pendingToggleStatus = null;
let pendingResetId = null;

// ── Toast ─────────────────────────────────────────────────────
function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

// ── Modal helpers ─────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); });
document.querySelectorAll('.modal-overlay').forEach(ov => ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); }));

// ── Load Users ────────────────────────────────────────────────
async function loadUsers() {
    try {
        const res = await fetch('../../../backend/user_management/list_users.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        allUsers = json.data;
        renderUsers(allUsers);
    } catch (e) {
        showToast('Failed to load users: ' + e.message, 'error');
    }
}

function renderUsers(users) {
    const body = document.getElementById('userBody');
    if (!users.length) { body.innerHTML = '<tr class="no-data"><td colspan="8">No users found.</td></tr>'; return; }
    body.innerHTML = users.map((u, i) => {
        const name = `${u.last_name}, ${u.first_name}${u.middle_name ? ' ' + u.middle_name.charAt(0) + '.' : ''}`;
        return `<tr>
      <td style="color:var(--text-muted)">${i + 1}</td>
      <td><strong>${name}</strong><div style="font-size:.78rem;color:var(--text-muted)">${u.email || ''}</div></td>
      <td><code style="font-size:.82rem;color:var(--info)">${u.username}</code></td>
      <td><span class="badge badge-${u.role_name}">${u.role_name}</span></td>
      <td style="font-size:.85rem;color:var(--text-sub)">${u.department || '—'}</td>
      <td><span class="badge ${u.is_active ? 'badge-active' : 'badge-inactive'}">${u.is_active ? 'Active' : 'Inactive'}</span></td>
      <td style="font-size:.8rem;color:var(--text-muted)">${formatDate(u.date_created)}</td>
      <td>
        <div class="flex-center gap-2">
          <button class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditModal(${u.user_id})"><i class="fa-solid fa-pen"></i></button>
          <button class="btn btn-warning btn-sm btn-icon" title="Reset Password" onclick="openResetModal(${u.user_id},'${escHtml(name)}')"><i class="fa-solid fa-key"></i></button>
          <button class="btn ${u.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" title="${u.is_active ? 'Deactivate' : 'Activate'}" onclick="openToggleModal(${u.user_id},${u.is_active},'${escHtml(name)}')"><i class="fa-solid fa-${u.is_active ? 'ban' : 'check'}"></i></button>
        </div>
      </td>
    </tr>`;
    }).join('');
}

// ── Filter ────────────────────────────────────────────────────
function filterUsers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    const filtered = allUsers.filter(u => {
        const name = `${u.last_name} ${u.first_name} ${u.username}`.toLowerCase();
        const matchSearch = !search || name.includes(search);
        const matchRole = !role || String(u.role_id) === role;
        const matchStatus = status === '' || String(u.is_active) === status;
        return matchSearch && matchRole && matchStatus;
    });
    renderUsers(filtered);
}

// ── Add Modal ─────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus"></i> Add User';
    document.getElementById('userForm').reset();
    document.getElementById('editUserId').value = '';
    document.getElementById('fUsername').removeAttribute('readonly');
    document.getElementById('fPassword').placeholder = 'Min. 6 characters';
    document.querySelector('#passwordGroup .form-label span').textContent = '*';
    openModal('userModal');
}

// ── Edit Modal ────────────────────────────────────────────────
async function openEditModal(userId) {
    try {
        const res = await fetch(`../../../backend/user_management/get_user.php?user_id=${userId}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const u = json.data;
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-pen"></i> Edit User';
        document.getElementById('editUserId').value = u.user_id;
        document.getElementById('fRole').value = u.role_id;
        document.getElementById('fLastName').value = u.last_name;
        document.getElementById('fFirstName').value = u.first_name;
        document.getElementById('fMiddleName').value = u.middle_name || '';
        document.getElementById('fUsername').value = u.username;
        document.getElementById('fUsername').setAttribute('readonly', true);
        document.getElementById('fPassword').value = '';
        document.getElementById('fPassword').placeholder = 'Leave blank to keep current';
        document.querySelector('#passwordGroup .form-label span').textContent = '';
        document.getElementById('fEmail').value = u.email || '';
        document.getElementById('fRank').value = u.academic_rank || '';
        document.getElementById('fSchool').value = u.school_college || '';
        document.getElementById('fDept').value = u.department || '';
        openModal('userModal');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

// ── Save User ─────────────────────────────────────────────────
async function saveUser(e) {
    e.preventDefault();
    const userId = document.getElementById('editUserId').value;
    const isEdit = !!userId;
    const url = isEdit ? '../../../backend/user_management/update.php' : '../../../backend/user_management/create.php';
    const body = new FormData();
    if (isEdit) body.append('user_id', userId);
    body.append('role_id', document.getElementById('fRole').value);
    body.append('first_name', document.getElementById('fFirstName').value.trim());
    body.append('middle_name', document.getElementById('fMiddleName').value.trim());
    body.append('last_name', document.getElementById('fLastName').value.trim());
    body.append('email', document.getElementById('fEmail').value.trim());
    body.append('academic_rank', document.getElementById('fRank').value.trim());
    body.append('school_college', document.getElementById('fSchool').value.trim());
    body.append('department', document.getElementById('fDept').value.trim());
    if (!isEdit) {
        body.append('username', document.getElementById('fUsername').value.trim());
        body.append('password', document.getElementById('fPassword').value);
    }
    try {
        const res = await fetch(url, { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('userModal');
        showToast(isEdit ? 'User updated.' : 'User created.', 'success');
        loadUsers();
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

// ── Reset Password ────────────────────────────────────────────
function openResetModal(userId, name) {
    pendingResetId = userId;
    document.getElementById('resetUserName').textContent = name;
    document.getElementById('resetNewPass').value = '';
    openModal('resetModal');
}
async function doResetPassword() {
    const newPass = document.getElementById('resetNewPass').value;
    if (!newPass || newPass.length < 6) { showToast('Password must be at least 6 characters.', 'warning'); return; }
    try {
        const body = new FormData();
        body.append('user_id', pendingResetId);
        body.append('new_password', newPass);
        const res = await fetch('../../../backend/user_management/reset_password.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('resetModal');
        showToast('Password reset successfully.', 'success');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

// ── Toggle Status ─────────────────────────────────────────────
function openToggleModal(userId, isActive, name) {
    pendingToggleId = userId;
    pendingToggleStatus = isActive;
    const action = isActive ? 'deactivate' : 'activate';
    document.getElementById('toggleTitle').innerHTML = `<i class="fa-solid fa-power-off"></i> ${isActive ? 'Deactivate' : 'Activate'} User`;
    document.getElementById('toggleMsg').textContent = `Are you sure you want to ${action} ${name}?`;
    const btn = document.getElementById('toggleConfirmBtn');
    btn.className = `btn ${isActive ? 'btn-danger' : 'btn-success'}`;
    btn.textContent = isActive ? 'Deactivate' : 'Activate';
    openModal('toggleModal');
}
async function doToggle() {
    try {
        const body = new FormData();
        body.append('user_id', pendingToggleId);
        const res = await fetch('../../../backend/user_management/toggle_status.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('toggleModal');
        showToast('User status updated.', 'success');
        loadUsers();
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

// ── Utilities ─────────────────────────────────────────────────
function formatDate(dt) { return dt ? new Date(dt).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'; }
function escHtml(s) { return s.replace(/['"\\]/g, '\\$&'); }

// ── Init ──────────────────────────────────────────────────────
loadUsers();
