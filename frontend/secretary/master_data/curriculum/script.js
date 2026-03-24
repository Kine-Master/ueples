// frontend/secretary/master_data/curriculum/script.js

let pendingToggleId = null;
let pendingToggleStatus = null;

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
function esc(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

async function loadGrades() {
    try {
        const res = await fetch('../../../../backend/master_data/grade_level/list.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const filter = document.getElementById('gradeFilter');
        const sel = document.getElementById('fGrade');
        json.data.forEach(g => {
            const opt = `<option value="${g.grade_level_id}">${g.name}</option>`;
            filter.innerHTML += opt;
            sel.innerHTML += opt;
        });
    } catch (e) { }
}

async function loadCurricula() {
    const grade = document.getElementById('gradeFilter').value;
    const body = document.getElementById('currBody');
    body.innerHTML = '<tr class="no-data"><td colspan="6"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';
    try {
        const res = await fetch(`../../../../backend/master_data/curriculum/list.php${grade ? '?grade_level_id=' + grade : ''}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="6">No curricula found.</td></tr>'; return; }
        body.innerHTML = data.map((d, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><strong>${d.name}</strong></td>
        <td>${d.grade_name}</td>
        <td style="font-size:.85rem;color:var(--text-sub)">${d.description || '—'}</td>
        <td><span class="badge badge-${d.is_active ? 'active' : 'inactive'}">${d.is_active ? 'Active' : 'Inactive'}</span></td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditModal(${d.curriculum_id}, '${esc(d.name)}', ${d.grade_level_id}, '${esc(d.description || '')}')"><i class="fa-solid fa-pen"></i></button>
            <button class="btn ${d.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" title="${d.is_active ? 'Deactivate' : 'Activate'}" onclick="openToggleModal(${d.curriculum_id}, ${d.is_active}, '${esc(d.name)}')"><i class="fa-solid fa-${d.is_active ? 'ban' : 'check'}"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="6">Error loading data.</td></tr>';
        showToast(e.message, 'error');
    }
}

function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-plus"></i> Add Curriculum';
    document.getElementById('currForm').reset();
    document.getElementById('editId').value = '';
    openModal('currModal');
}

function openEditModal(id, name, grade_id, desc) {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen"></i> Edit Curriculum';
    document.getElementById('editId').value = id;
    document.getElementById('fName').value = name;
    document.getElementById('fGrade').value = grade_id;
    document.getElementById('fDesc').value = desc;
    openModal('currModal');
}

async function saveCurr(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const isEdit = !!id;
    const url = isEdit ? '../../../../backend/master_data/curriculum/update.php' : '../../../../backend/master_data/curriculum/create.php';
    const body = new FormData();
    if (isEdit) body.append('curriculum_id', id);
    else body.append('grade_level_id', document.getElementById('fGrade').value);
    body.append('name', document.getElementById('fName').value.trim());
    body.append('description', document.getElementById('fDesc').value.trim());

    try {
        const res = await fetch(url, { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('currModal');
        showToast(isEdit ? 'Curriculum updated.' : 'Curriculum created.', 'success');
        loadCurricula();
    } catch (e) { showToast(e.message, 'error'); }
}

function openToggleModal(id, isActive, name) {
    pendingToggleId = id;
    pendingToggleStatus = isActive;
    document.getElementById('toggleMsg').textContent = `Are you sure you want to ${isActive ? 'deactivate' : 'activate'} ${name}?`;
    const btn = document.getElementById('toggleConfirmBtn');
    btn.className = `btn ${isActive ? 'btn-danger' : 'btn-success'}`;
    btn.textContent = isActive ? 'Deactivate' : 'Activate';
    openModal('toggleModal');
}

async function doToggle() {
    const body = new FormData(); body.append('curriculum_id', pendingToggleId);
    try {
        const res = await fetch('../../../../backend/master_data/curriculum/toggle.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('toggleModal');
        showToast('Status updated.', 'success');
        loadCurricula();
    } catch (e) { showToast(e.message, 'error'); }
}

loadGrades().then(loadCurricula);
