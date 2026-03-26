// frontend/teacher/classes/script.js

let activeSectionId = null;
let activeSectionName = '';
let sectionsMap = new Map();

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

async function loadClasses() {
    try {
        // Fetch schedule-based classes and advisory sections independently
        // Use allSettled so one failing doesn't break the other
        const [schedResult, advResult] = await Promise.allSettled([
            fetch('../../../backend/schedule/list.php').then(r => r.json()),
            fetch('../../../backend/master_data/class_section/list_advisory.php').then(r => r.json())
        ]);

        const schedJson = schedResult.status === 'fulfilled' ? schedResult.value : null;
        const advJson = advResult.status === 'fulfilled' ? advResult.value : null;

        // Reset map on each load to avoid stale data
        sectionsMap.clear();

        // Extract unique LES class sections from schedules
        const classes = [];
        if (schedJson && schedJson.status === 'success') {
            schedJson.data.forEach(s => {
                if (s.schedule_type === 'LES' && s.class_section_id) {
                    const csId = parseInt(s.class_section_id);
                    if (!sectionsMap.has(csId)) {
                        sectionsMap.set(csId, {
                            id: csId,
                            name: s.section_name,
                            grade: s.grade_name,
                            isAdvisory: false
                        });
                        classes.push(sectionsMap.get(csId));
                    }
                }
            });
        }

        // Merge advisory sections (mark them, avoid duplicates)
        if (advJson && advJson.status === 'success' && advJson.data) {
            advJson.data.forEach(a => {
                const csId = parseInt(a.class_section_id);
                if (sectionsMap.has(csId)) {
                    // Already in list from schedule — just mark as advisory
                    sectionsMap.get(csId).isAdvisory = true;
                } else {
                    const entry = {
                        id: csId,
                        name: a.section_name,
                        grade: a.grade_name,
                        isAdvisory: true
                    };
                    sectionsMap.set(csId, entry);
                    classes.push(entry);
                }
            });
        }

        const list = document.getElementById('classList');
        if (!classes.length) {
            list.innerHTML = `<div style="text-align:center;color:var(--text-muted);padding:20px;font-size:.85rem"><i class="fa-solid fa-ghost" style="font-size:2rem;margin-bottom:12px;opacity:0.3;display:block"></i>No enrolled classes.</div>`;
            return;
        }

        list.innerHTML = classes.map(c => `
      <div class="class-item" id="cls-${c.id}" onclick="selectClass(${c.id})">
        <strong style="display:block;color:var(--text);font-size:.95rem">${c.name}</strong>
        <span style="font-size:.8rem;color:var(--text-sub)">${c.grade}</span>
        ${c.isAdvisory ? '<span style="font-size:.7rem;background:var(--accent-bg);color:var(--accent);padding:2px 8px;border-radius:99px;margin-top:4px;display:inline-block"><i class="fa-solid fa-star" style="font-size:.6rem"></i> Advisory</span>' : ''}
      </div>
    `).join('');

    } catch (e) { document.getElementById('classList').innerHTML = 'Error loading classes.'; }
}

function selectClass(id) {
    // Update UI active state
    document.querySelectorAll('.class-item').forEach(el => el.classList.remove('active'));
    document.getElementById(`cls-${id}`).classList.add('active');

    activeSectionId = id;
    const c = sectionsMap.get(id);
    activeSectionName = `${c.name} (${c.grade})`;

    // Header updates
    document.getElementById('panelHeader').innerHTML = `
    <div>
       <h3 style="margin:0;font-size:1.2rem;color:var(--text)"><i class="fa-solid fa-users" style="color:var(--accent);margin-right:8px"></i> ${activeSectionName}</h3>
       <p style="margin:4px 0 0 0;font-size:.85rem;color:var(--text-sub)" id="capText">Loading capacity...</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-user-plus"></i> Add Student</button>
  `;

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('studentToolbar').style.display = 'flex';
    document.getElementById('studentTableWrap').style.display = 'block';

    loadStudents();
}

async function loadStudents() {
    if (!activeSectionId) return;
    const q = document.getElementById('searchQ').value;
    const tbody = document.getElementById('studentBody');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>';

    try {
        const res = await fetch(`../../../backend/student/list.php?class_section_id=${activeSectionId}&search=${encodeURIComponent(q)}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);

        // Update capacity info
        if (json.capacity_info) {
            const en = json.capacity_info.enrolled || 0;
            const cap = json.capacity_info.capacity || 0;
            let cText = `<i class="fa-solid fa-door-open"></i> Enrolled: <strong style="color:var(--text)">${en}</strong> / ${cap} max`;
            if (en > cap && cap > 0) cText += ` <span style="color:#ef4444;margin-left:8px"><i class="fa-solid fa-triangle-exclamation"></i> Over capacity</span>`;
            document.getElementById('capText').innerHTML = cText;
        } else {
            document.getElementById('capText').innerHTML = `Enrolled: ${json.data.length}`;
        }

        if (!json.data.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-sub)">No students found.</td></tr>';
            return;
        }

        let stMap = window.stMap = new Map();

        tbody.innerHTML = json.data.map(st => {
            stMap.set(st.student_id, st);
            const name = `${st.last_name}, ${st.first_name} ${st.middle_name || ''} ${st.extension_name || ''}`.trim();
            const stBadge = st.is_active ? '<span class="badge badge-active">Active</span>' : '<span class="badge badge-inactive">Inactive</span>';
            return `
      <tr>
        <td style="font-family:monospace;color:var(--text-sub)">${st.lrn}</td>
        <td><strong>${name}</strong></td>
        <td>${st.gender}</td>
        <td>${stBadge}</td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="openEditModal(${st.student_id})"><i class="fa-solid fa-pen"></i></button>
            <button class="btn ${st.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" title="${st.is_active ? 'Deactivate' : 'Activate'}" onclick="openToggleModal(${st.student_id}, ${st.is_active})"><i class="fa-solid fa-${st.is_active ? 'ban' : 'check'}"></i></button>
          </div>
        </td>
      </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="5" style="color:#ef4444">${e.message}</td></tr>`;
    }
}

function openAddModal() {
    document.getElementById('studentForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus"></i> Add Student';
    openModal('studentModal');
}

function openEditModal(id) {
    const st = window.stMap.get(id);
    if (!st) return;
    document.getElementById('editId').value = st.student_id;
    document.getElementById('fLRN').value = st.lrn;
    document.getElementById('fFirst').value = st.first_name;
    document.getElementById('fMiddle').value = st.middle_name || '';
    document.getElementById('fLast').value = st.last_name;
    document.getElementById('fExt').value = st.extension_name || '';
    document.getElementById('fGender').value = st.gender;
    document.getElementById('fDob').value = st.birth_date;

    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-pen"></i> Edit Student';
    openModal('studentModal');
}

async function saveStudent(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const isEdit = !!id;
    const url = `../../../backend/student/${isEdit ? 'update' : 'create'}.php`;

    const body = new FormData();
    if (isEdit) body.append('student_id', id);
    body.append('class_section_id', activeSectionId);
    body.append('lrn', document.getElementById('fLRN').value);
    body.append('first_name', document.getElementById('fFirst').value);
    body.append('middle_name', document.getElementById('fMiddle').value);
    body.append('last_name', document.getElementById('fLast').value);
    body.append('extension_name', document.getElementById('fExt').value);
    body.append('gender', document.getElementById('fGender').value);
    body.append('date_of_birth', document.getElementById('fDob').value);

    try {
        const res = await fetch(url, { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('studentModal');
        showToast(isEdit ? 'Student updated!' : 'Student added!', 'success');
        loadStudents();
    } catch (e) { showToast(e.message, 'error'); }
}

let pendingTogId = null;
function openToggleModal(id, isActive) {
    pendingTogId = id;
    const st = window.stMap.get(id);
    const name = `${st.first_name} ${st.last_name}`;
    document.getElementById('toggleMsg').textContent = `Are you sure you want to ${isActive ? 'deactivate' : 'activate'} student ${name}?`;
    const btn = document.getElementById('toggleBtn');
    btn.className = `btn ${isActive ? 'btn-danger' : 'btn-success'}`;
    btn.textContent = isActive ? 'Deactivate' : 'Activate';
    openModal('toggleModal');
}

async function doToggle() {
    const body = new FormData(); body.append('student_id', pendingTogId);
    try {
        const res = await fetch('../../../backend/student/toggle.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('toggleModal');
        showToast('Status updated.', 'success');
        loadStudents();
    } catch (e) { showToast(e.message, 'error'); }
}

document.addEventListener('DOMContentLoaded', loadClasses);
