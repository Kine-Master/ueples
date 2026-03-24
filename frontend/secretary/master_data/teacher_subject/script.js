// frontend/secretary/master_data/teacher_subject/script.js

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

async function fetchDropdowns() {
    try {
        const rt = await fetch('../../../../backend/schedule/list_teachers.php');
        const jt = await rt.json();
        const tf = document.getElementById('teacherFilter'); const ts = document.getElementById('fTeacher');
        (jt.data || []).forEach(t => {
            const opt = `<option value="${t.user_id}">${t.first_name} ${t.last_name}</option>`;
            tf.innerHTML += opt;
            ts.innerHTML += opt;
        });

        const rs = await fetch('../../../../backend/master_data/subject/list.php');
        const js = await rs.json();
        const ss = document.getElementById('fSubject');
        js.data.forEach(s => {
            if (s.is_active) ss.innerHTML += `<option value="${s.subject_id}">${s.subject_name} (${s.curriculum_name} - ${s.grade_name})</option>`;
        });

        loadSpecs();
    } catch (e) { showToast('Error fetching data', 'error'); }
}

async function loadSpecs() {
    const tId = document.getElementById('teacherFilter').value;
    const body = document.getElementById('specBody');
    body.innerHTML = '<tr class="no-data"><td colspan="5"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';

    try {
        const res = await fetch(`../../../../backend/master_data/teacher_subject/list.php${tId ? '?user_id=' + tId : ''}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="5">No specialties assigned.</td></tr>'; return; }

        body.innerHTML = data.map(d => `
      <tr>
        <td><strong>${d.first_name} ${d.last_name}</strong></td>
        <td>${d.subject_name}</td>
        <td><span style="font-size:.85rem;color:var(--text-sub)">${d.curriculum_name}</span></td>
        <td>${d.grade_name}</td>
        <td>
          <button class="btn btn-danger btn-sm btn-icon" title="Remove Specialty" onclick="removeSpec(${d.user_id}, ${d.subject_id})"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `).join('');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="5">Error loading data.</td></tr>';
        showToast(e.message, 'error');
    }
}

function openAssignModal() {
    document.getElementById('assignForm').reset();
    openModal('assignModal');
}

async function saveAssign(e) {
    e.preventDefault();
    const body = new FormData();
    body.append('user_id', document.getElementById('fTeacher').value);
    body.append('subject_id', document.getElementById('fSubject').value);

    try {
        const res = await fetch('../../../../backend/master_data/teacher_subject/assign.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('assignModal');
        showToast('Specialty assigned.', 'success');
        loadSpecs();
    } catch (e) { showToast(e.message, 'error'); }
}

async function removeSpec(userId, subjectId) {
    if (!confirm('Are you sure you want to remove this specialty from the teacher?')) return;
    const body = new FormData();
    body.append('user_id', userId);
    body.append('subject_id', subjectId);
    try {
        const res = await fetch('../../../../backend/master_data/teacher_subject/remove.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        showToast('Specialty removed.', 'success');
        loadSpecs();
    } catch (e) { showToast(e.message, 'error'); }
}

fetchDropdowns();
