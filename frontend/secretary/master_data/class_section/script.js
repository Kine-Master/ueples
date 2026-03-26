// frontend/secretary/master_data/class_section/script.js

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
function escHtml(s) { return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

async function fetchDropdowns() {
    try {
        // SY
        const rs = await fetch('../../../../backend/master_data/school_year/list.php');
        const js = await rs.json();
        const sf = document.getElementById('syFilter'); const ss = document.getElementById('fSy');
        let activeSyId = null;
        js.data.forEach(s => {
            const isAct = s.is_active ? ' (Active)' : '';
            if (s.is_active) activeSyId = s.school_year_id;
            const opt = `<option value="${s.school_year_id}">${s.label}${isAct}</option>`;
            sf.innerHTML += opt; ss.innerHTML += opt;
        });
        if (activeSyId) sf.value = activeSyId; // default filter

        // Grade Level
        const rg = await fetch('../../../../backend/master_data/grade_level/list.php');
        const jg = await rg.json();
        const gf = document.getElementById('grFilter'); const gs = document.getElementById('fGr');
        jg.data.forEach(g => {
            const opt = `<option value="${g.grade_level_id}">${g.name}</option>`;
            gf.innerHTML += opt; gs.innerHTML += opt;
        });

        // Teachers (for Adviser)
        const rt = await fetch('../../../../backend/schedule/list_teachers.php');
        const jt = await rt.json();
        const ta = document.getElementById('fAdv');
        (jt.data || []).forEach(t => {
            ta.innerHTML += `<option value="${t.user_id}">${t.first_name} ${t.last_name}</option>`;
        });

        loadSections();
    } catch (e) { showToast('Error fetching dropdown data', 'error'); }
}

async function loadSections() {
    const sy = document.getElementById('syFilter').value;
    const gr = document.getElementById('grFilter').value;
    const body = document.getElementById('secBody');
    body.innerHTML = '<tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';

    let p = new URLSearchParams();
    if (sy) p.append('school_year_id', sy);
    if (gr) p.append('grade_level_id', gr);

    try {
        const res = await fetch('../../../../backend/master_data/class_section/list.php?' + p);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="7">No sections found.</td></tr>'; return; }

        body.innerHTML = data.map((d, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><strong>${d.section_name}</strong></td>
        <td><span style="font-size:.85rem;color:var(--text-sub)">${d.sy_label}</span></td>
        <td>${d.grade_name}</td>
        <td>${d.adviser_name ? d.adviser_name : '<span style="color:var(--text-muted);font-style:italic">None</span>'}</td>
        <td><span class="badge badge-${d.is_active ? 'active' : 'inactive'}">${d.is_active ? 'Active' : 'Inactive'}</span></td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditModal(${d.class_section_id}, '${escHtml(d.section_name)}', '${d.adviser_id || ''}')"><i class="fa-solid fa-pen"></i></button>
            <button class="btn ${d.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" title="${d.is_active ? 'Deactivate' : 'Activate'}" onclick="openToggleModal(${d.class_section_id}, ${d.is_active}, '${escHtml(d.section_name)}')"><i class="fa-solid fa-${d.is_active ? 'ban' : 'check'}"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="7">Error loading data.</td></tr>';
        showToast(e.message, 'error');
    }
}

function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-plus"></i> Add Section';
    document.getElementById('secForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('creationGroup').style.display = 'flex';
    document.getElementById('fSy').required = true;
    document.getElementById('fGr').required = true;
    openModal('secModal');
}

function openEditModal(id, name, adviser_id) {
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen"></i> Edit Section Info';
    document.getElementById('editId').value = id;
    document.getElementById('fName').value = name;
    document.getElementById('fAdv').value = adviser_id;
    document.getElementById('creationGroup').style.display = 'none'; // Changing SY/Grade is dangerous once created
    document.getElementById('fSy').required = false;
    document.getElementById('fGr').required = false;
    openModal('secModal');
}

async function saveSec(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const isEdit = !!id;
    const url = isEdit ? '../../../../backend/master_data/class_section/update.php' : '../../../../backend/master_data/class_section/create.php';
    const body = new FormData();
    if (isEdit) {
        body.append('class_section_id', id);
    } else {
        body.append('school_year_id', document.getElementById('fSy').value);
        body.append('grade_level_id', document.getElementById('fGr').value);
    }
    body.append('section_name', document.getElementById('fName').value.trim());

    // Always send adviser_id — empty string means "no adviser" (backend treats as NULL)
    body.append('adviser_id', document.getElementById('fAdv').value);

    try {
        const res = await fetch(url, { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('secModal');
        showToast(isEdit ? 'Section updated.' : 'Section created.', 'success');
        loadSections();
    } catch (e) { showToast(e.message, 'error'); }
}

function openToggleModal(id, isActive, name) {
    pendingToggleId = id;
    pendingToggleStatus = isActive;
    document.getElementById('toggleMsg').textContent = `Are you sure you want to ${isActive ? 'deactivate' : 'activate'} section ${name}?`;
    const btn = document.getElementById('toggleConfirmBtn');
    btn.className = `btn ${isActive ? 'btn-danger' : 'btn-success'}`;
    btn.textContent = isActive ? 'Deactivate' : 'Activate';
    openModal('toggleModal');
}

async function doToggle() {
    const body = new FormData(); body.append('class_section_id', pendingToggleId);
    try {
        const res = await fetch('../../../../backend/master_data/class_section/toggle.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('toggleModal');
        showToast('Status updated.', 'success');
        loadSections();
    } catch (e) { showToast(e.message, 'error'); }
}

fetchDropdowns();
