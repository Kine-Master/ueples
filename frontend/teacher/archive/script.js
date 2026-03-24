/* frontend/teacher/archive/script.js */
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadArchives();
});

function debounceLoad() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadArchives, 300);
}

function loadArchives() {
    const search = document.getElementById('searchInput').value;
    const sort = document.getElementById('sortBy').value;
    
    // IMPORTANT: Get Teacher ID from Hidden Input
    const teacherId = document.getElementById('myUserId').value; 

    const tbody = document.getElementById('archiveTableBody');
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;">Loading...</td></tr>';

    // Pass teacher_id to backend
    let url = `../../../backend/schedule/archive_list.php?teacher_id=${teacherId}&search=${encodeURIComponent(search)}&sort_by=${sort}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderTable(data.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Error loading archives.</td></tr>';
            }
        });
}

function renderTable(rows) {
    const tbody = document.getElementById('archiveTableBody');
    if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:30px; color:#999;">No archived schedules found.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(r => {
        const isCoed = r.schedule_type === 'COED';
        const dateArchived = new Date(r.created_at).toLocaleDateString(); // Fallback if no specific archive date
        
        return `
            <tr>
                <td style="font-weight:bold;">${escapeHtml(r.subject)}</td>
                <td><span class="badge-pill ${isCoed ? 'coed' : 'les'}">${r.schedule_type}</span></td>
                <td class="details-cell">
                    ${escapeHtml(r.room)}
                    <small>${formatTime(r.time_in)} - ${formatTime(r.time_out)} • ${r.day_of_week}</small>
                </td>
                <td>${r.school_year} (${r.semester == 1 ? '1st' : (r.semester==2 ? '2nd' : 'Summer')})</td>
                <td><span class="status-archived"><i class="fa-solid fa-box-archive"></i> Inactive</span></td>
                <td style="color:#7f8c8d;">${dateArchived}</td>
            </tr>
        `;
    }).join('');
}

function formatTime(t) {
    if(!t) return '';
    const [h, m] = t.split(':');
    return `${parseInt(h)}:${m}`;
}

function escapeHtml(text) {
    return text ? text.toString().replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])) : '';
}