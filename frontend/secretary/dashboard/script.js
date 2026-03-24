// frontend/secretary/dashboard/script.js

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

function renderDate() {
    const d = new Date();
    document.getElementById('currentDate').innerHTML =
        `<div style="text-align:right;font-size:.8rem;color:var(--text-muted)">
       ${d.toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
     </div>`;
}

async function loadStats() {
    try {
        const res = await fetch('../../../backend/dashboard/stats.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const d = json.data;

        document.getElementById('activeSY').textContent = d.active_sy?.label ?? 'None';
        document.getElementById('countLes').textContent = d.les_schedules ?? 0;
        document.getElementById('countCoed').textContent = d.coed_schedules ?? 0;
        document.getElementById('countSections').textContent = d.class_sections ?? 0;
        document.getElementById('countRooms').textContent = d.rooms ?? 0;
        document.getElementById('countSubjects').textContent = d.subjects ?? 0;
    } catch (e) {
        showToast('Failed to load dashboard: ' + e.message, 'error');
    }
}

renderDate();
loadStats();