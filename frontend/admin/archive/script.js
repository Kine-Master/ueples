// frontend/admin/archive/script.js

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); });

async function loadArchive() {
    const search = document.getElementById('searchInput').value;
    const type = document.getElementById('typeFilter').value;
    const params = new URLSearchParams({ search, schedule_type: type });
    const body = document.getElementById('archiveBody');
    body.innerHTML = '<tr class="no-data"><td colspan="9"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';
    try {
        const res = await fetch('../../../backend/schedule/archive_list.php?' + params);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        document.getElementById('archiveCount').textContent = `${data.length} archived schedule(s)`;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="9">No archived schedules found.</td></tr>'; return; }
        body.innerHTML = data.map((s, i) => {
            const isLes = s.schedule_type === 'LES';
            const subject = isLes ? s.subject_name : s.coed_subject;
            const classInfo = isLes ? (s.section_name || '—') : '—';
            const room = isLes ? (s.room_name ? `${s.room_name}<br><small style="color:var(--text-muted)">${s.building_name || ''}</small>` : '—') : (s.coed_room ? `${s.coed_room}<br><small style="color:var(--text-muted)">${s.coed_building || ''}</small>` : '—');
            return `<tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><span class="badge badge-${s.schedule_type.toLowerCase()}">${s.schedule_type}</span></td>
        <td><strong>${s.teacher_name}</strong></td>
        <td style="font-size:.875rem">${subject || '—'}</td>
        <td style="font-size:.85rem">${classInfo}${s.grade_name ? `<br><small style="color:var(--text-muted)">${s.grade_name}</small>` : ''}</td>
        <td style="font-size:.85rem">${room}</td>
        <td style="font-size:.82rem;white-space:nowrap">${s.day_of_week} ${s.time_in}–${s.time_out}</td>
        <td style="font-size:.82rem">${s.school_year}<br><small style="color:var(--text-muted)">Sem ${s.semester}</small></td>
        <td style="font-size:.78rem;color:var(--text-muted)">${formatDate(s.last_modified || s.date_created)}</td>
      </tr>`;
        }).join('');
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
        body.innerHTML = '<tr class="no-data"><td colspan="9">Failed to load archives.</td></tr>';
    }
}

async function openThresholdModal() {
    try {
        const res = await fetch('../../../backend/audit_logs/settings.php');
        const json = await res.json();
        if (json.status === 'success') {
            document.getElementById('archiveThreshold').value = json.data.archive_threshold;
            document.getElementById('deletionThreshold').value = json.data.deletion_threshold;
        }
    } catch (e) { }
    openModal('thresholdModal');
}

async function saveThresholds() {
    const body = new FormData();
    body.append('archive_threshold', document.getElementById('archiveThreshold').value);
    body.append('deletion_threshold', document.getElementById('deletionThreshold').value);
    try {
        const res = await fetch('../../../backend/audit_logs/settings.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('thresholdModal');
        showToast('Thresholds saved.', 'success');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

function formatDate(dt) { return dt ? new Date(dt).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '—'; }

loadArchive();
