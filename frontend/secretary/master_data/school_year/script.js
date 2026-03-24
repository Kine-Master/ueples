// frontend/secretary/master_data/school_year/script.js

let pendingActiveId = null;

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-info'}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

async function loadSY() {
    const body = document.getElementById('syBody');
    try {
        const res = await fetch('../../../../backend/master_data/school_year/list.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="4">No school years found.</td></tr>'; return; }
        body.innerHTML = data.map((d, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><strong>${d.label}</strong></td>
        <td><span class="badge badge-${d.is_active ? 'active' : 'inactive'}">${d.is_active ? 'Active' : 'Inactive'}</span></td>
        <td>
          ${d.is_active ? '<span style="color:var(--text-muted);font-size:.8rem;font-style:italic">Current active</span>' : `<button class="btn btn-success btn-sm" onclick="openActiveModal(${d.school_year_id}, '${d.label}')"><i class="fa-solid fa-bolt"></i> Set Active</button>`}
        </td>
      </tr>
    `).join('');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="4">Error loading data.</td></tr>';
        showToast(e.message, 'error');
    }
}

function openAddModal() {
    document.getElementById('syForm').reset();
    openModal('addModal');
}

async function saveSY(e) {
    e.preventDefault();
    const label = document.getElementById('fLabel').value.trim();
    const body = new FormData(); body.append('label', label);
    try {
        const res = await fetch('../../../../backend/master_data/school_year/create.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('addModal');
        showToast('School year created.', 'success');
        loadSY();
    } catch (e) { showToast(e.message, 'error'); }
}

function openActiveModal(id, label) {
    pendingActiveId = id;
    document.getElementById('activeMsg').textContent = `Are you sure you want to set ${label} as the active school year? This will deactivate the current one.`;
    openModal('activeModal');
}

async function doSetActive() {
    const body = new FormData(); body.append('school_year_id', pendingActiveId);
    try {
        const res = await fetch('../../../../backend/master_data/school_year/set_active.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('activeModal');
        showToast('Active school year updated.', 'success');
        loadSY();
    } catch (e) { showToast(e.message, 'error'); }
}

loadSY();
