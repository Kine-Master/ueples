/**
 * SECRETARY ARCHIVE MANAGEMENT SCRIPT
 * Handles Server-Side Searching, Sorting, and Archive Policy Settings
 */

let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadArchives();
});

// ==========================================
// 1. DATA FETCHING (SEARCH & SORT)
// ==========================================

// Debounce: Wait 300ms after user stops typing before fetching
function debounceLoad() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadArchives, 300);
}

function loadArchives() {
    const tbody = document.getElementById('archiveTableBody');
    const search = document.getElementById('searchInput').value;
    const sort = document.getElementById('sortBy').value;

    // Show loading state
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#666;">Loading archives...</td></tr>';

    // Build URL with params
    const url = `../../../backend/schedule/archive_list.php?search=${encodeURIComponent(search)}&sort_by=${sort}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // 1. Update Threshold Display (Policy Info)
                if (data.threshold_used) {
                    // Update header info text if you have an element for it
                    // Or update the modal input value
                    const input = document.getElementById('thresholdInput');
                    if(input) input.value = data.threshold_used;
                }

                // 2. Render Table
                renderTable(data.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Error loading data.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Network Error.</td></tr>';
        });
}

function renderTable(logs) {
    const tbody = document.getElementById('archiveTableBody');
    
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:30px; font-style:italic; color:#999;">No archived schedules found.</td></tr>';
        return;
    }

    tbody.innerHTML = logs.map(log => `
        <tr>
            <td>
                <strong>${escapeHtml(log.teacher_name)}</strong>
            </td>
            <td>
                ${escapeHtml(log.subject)} 
                <span class="badge ${log.schedule_type === 'LES' ? 'badge-les' : 'badge-coed'}">${log.schedule_type}</span>
            </td>
            <td>
                ${log.day_of_week}<br>
                <span style="font-size:11px; color:#666;">${formatTime(log.time_in)} - ${formatTime(log.time_out)}</span>
            </td>
            <td>
                ${log.school_year} <small>(Sem ${log.semester})</small>
            </td>
            <td>
                ${log.is_active == 0 
                    ? '<span class="status-pill deleted">Deleted</span>' 
                    : '<span class="status-pill expired">Expired</span>'}
            </td>
            <td style="font-size:12px; color:#888;">${log.date_created}</td>
        </tr>
    `).join('');
}

// ==========================================
// 2. SETTINGS / THRESHOLD LOGIC (Preserved)
// ==========================================

// Open/Close Modal
window.openSettings = () => document.getElementById('settingsModal').style.display = 'flex';
window.closeSettings = () => document.getElementById('settingsModal').style.display = 'none';

// Handle Form Submit
const settingsForm = document.getElementById('settingsForm'); // Ensure your HTML <form> has this ID
if(settingsForm) {
    settingsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../../backend/schedule/update_threshold.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert("Archive Policy updated successfully.");
                closeSettings();
                loadArchives(); // Reload to see if logic applies immediately (backend dependent)
            } else {
                alert("Error: " + data.message);
            }
        });
    });
}

// ==========================================
// 3. UTILITIES
// ==========================================

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