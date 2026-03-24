let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadArchives();
});

// 1. Debounce Search
function debounceLoad() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadArchives, 300);
}

// 2. Fetch Data
function loadArchives() {
    const tbody = document.getElementById('archiveTableBody');
    const search = document.getElementById('searchInput').value;
    const sort = document.getElementById('sortBy').value;

    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#666;">Loading...</td></tr>';

    // No 'teacher_id' param = Global Fetch
    const url = `../../../backend/schedule/archive_list.php?search=${encodeURIComponent(search)}&sort_by=${sort}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                renderTable(data.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Error loading archives.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Network Error.</td></tr>';
        });
}

function renderTable(logs) {
    const tbody = document.getElementById('archiveTableBody');
    
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:30px; font-style:italic; color:#999;">No archives found.</td></tr>';
        return;
    }

    tbody.innerHTML = logs.map(log => `
        <tr>
            <td>
                <strong>${escapeHtml(log.teacher_name)}</strong>
            </td>
            <td>${escapeHtml(log.subject)}</td>
            <td>
                <span class="badge ${log.schedule_type === 'LES' ? 'badge-les' : 'badge-coed'}">
                    ${log.schedule_type}
                </span>
            </td>
            <td>
                <div style="font-size:12px; color:#555;">
                    ${log.day_of_week}<br>
                    ${formatTime(log.time_in)} - ${formatTime(log.time_out)}
                </div>
            </td>
            <td>${log.school_year} <small>(Sem ${log.semester})</small></td>
            <td>
                ${log.is_active == 0 
                    ? '<span class="status-pill deleted">Deleted</span>' 
                    : '<span class="status-pill expired">Expired</span>'}
            </td>
            <td style="font-size:12px; color:#888;">${log.date_created}</td>
        </tr>
    `).join('');
}

// Utilities
function formatTime(t) {
    if(!t) return '';
    const [h, m] = t.split(':');
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${m} ${ampm}`;
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}