// frontend/secretary/master_data/subject/script.js

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

async function loadCurricula() {
    try {
        const res = await fetch('../../../../backend/master_data/curriculum/list.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const filter = document.getElementById('currFilter');
        const sel = document.getElementById('fCurr'); // RESTORED THIS LINE
        json.data.forEach(c => {
            const opt = `<option value="${c.curriculum_id}">${c.name} (${c.grade_name})</option>`;
            filter.innerHTML += opt;
            sel.innerHTML += opt;
        });
    } catch (e) { console.error(e); }
}

async function loadSubjects() {
    const curr = document.getElementById('currFilter').value;
    const body = document.getElementById('subjBody');
    body.innerHTML = '<tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';
    try {
        const res = await fetch(`../../../../backend/master_data/subject/list.php${curr ? '?curriculum_id=' + curr : ''}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="7">No subjects found.</td></tr>'; return; }
        body.innerHTML = data.map((d, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><strong>${d.name}</strong></td>
        <td>${d.curriculum_name}</td>
        <td><span style="color:var(--text-sub);font-size:.85rem">${d.grade_name}</span></td>
        <td>${d.units ?? '—'}</td>
        <td><span class="badge badge-${d.is_active ? 'active' : 'inactive'}">${d.is_active ? 'Active' : 'Inactive'}</span></td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditModal(${d.subject_id}, '${escHtml(d.name)}', '${d.units || ''}', '${escHtml(d.description || '')}')"><i class="fa-solid fa-pen"></i></button>
            <button class="btn ${d.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" title="${d.is_active ? 'Deactivate' : 'Activate'}" onclick="openToggleModal(${d.subject_id}, ${d.is_active}, '${escHtml(d.name)}')"><i class="fa-solid fa-${d.is_active ? 'ban' : 'check'}"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="7">Error loading data.</td></tr>';
        showToast(e.message, 'error');
    }
}

function escHtml(s) { return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-plus"></i> Add Subject';
    document.getElementById('subjForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('currGroup').style.display = 'block';
    document.getElementById('fCurr').setAttribute('required', 'true');
    openModal('subjModal');
}

function openEditModal(id, name, units, desc) {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen"></i> Edit Subject';
    document.getElementById('editId').value = id;
    document.getElementById('currGroup').style.display = 'none'; // Currency update backend doesn't support changing curriculum_id easily without checking dependencies
    document.getElementById('fCurr').removeAttribute('required');
    document.getElementById('fName').value = name;
    document.getElementById('fUnits').value = units;
    document.getElementById('fDesc').value = desc;
    openModal('subjModal');
}

async function saveSubj(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const isEdit = !!id;
    const url = isEdit ? '../../../../backend/master_data/subject/update.php' : '../../../../backend/master_data/subject/create.php';
    const body = new FormData();
    if (isEdit) body.append('subject_id', id);
    else body.append('curriculum_id', document.getElementById('fCurr').value);
    body.append('name', document.getElementById('fName').value.trim()); // Fixed parameter name to match backend expectation
    body.append('units', document.getElementById('fUnits').value.trim());
    body.append('description', document.getElementById('fDesc').value.trim());

    try {
        const res = await fetch(url, { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('subjModal');
        showToast(isEdit ? 'Subject updated.' : 'Subject created.', 'success');
        loadSubjects();
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
    const body = new FormData(); body.append('subject_id', pendingToggleId);
    try {
        const res = await fetch('../../../../backend/master_data/subject/toggle.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('toggleModal');
        showToast('Status updated.', 'success');
        loadSubjects();
    } catch (e) { showToast(e.message, 'error'); }
}

loadCurricula().then(loadSubjects);
