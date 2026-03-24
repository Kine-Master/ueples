document.addEventListener('DOMContentLoaded', () => {
    loadProfileName();
    loadDashboardData();
    renderDate();
});

function loadProfileName() {
    fetch('../../../backend/user/get_profile.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('principalName').innerText =
                    data.data.first_name;
            }
        })
        .catch(console.error);
}

function loadDashboardData() {
    fetch('../../../backend/dashboard/stats.php')
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                console.error(res.message);
                return;
            }

            const stats = res.data.stats;

            // STAT CARDS
            document.getElementById('totalActiveUsers').innerText = stats.total_active_users;
            document.getElementById('totalFaculty').innerText = stats.total_faculty;
            document.getElementById('totalAdmins').innerText = stats.total_admins;
            document.getElementById('activeSchedules').innerText = stats.active_schedules;

            renderRecentLogs(stats.recent_logs);
            renderRecentUsers(stats.recent_users);
        })
        .catch(err => console.error('Dashboard load failed:', err));
}

/* =======================
   TABLE RENDERERS
======================= */

function renderRecentLogs(logs) {
    const tbody = document.getElementById('recentLogsBody');

    if (!logs || logs.length === 0) {
        tbody.innerHTML = emptyRow(3, 'No recent activity found.');
        return;
    }

    tbody.innerHTML = logs.map(log => `
        <tr>
            <td>${escapeHtml(fullName(log))}</td>
            <td>${escapeHtml(log.user_action)}</td>
            <td>${formatDateTime(log.timestamp)}</td>
        </tr>
    `).join('');
}

function renderRecentUsers(users) {
    const tbody = document.getElementById('recentUsersBody');

    if (!users || users.length === 0) {
        tbody.innerHTML = emptyRow(3, 'No recent users found.');
        return;
    }

    tbody.innerHTML = users.map(u => `
        <tr>
            <td>${escapeHtml(`${u.first_name} ${u.last_name}`)}</td>
            <td>${roleLabel(u.role_id)}</td>
            <td>${formatDateTime(u.date_created)}</td>
        </tr>
    `).join('');
}

/* =======================
   UTILITIES
======================= */

function renderDate() {
    const el = document.getElementById('currentDate');
    el.innerText = new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

function roleLabel(id) {
    return { 1: 'Principal', 2: 'Secretary', 3: 'Teacher' }[id] || 'Unknown';
}

function fullName(log) {
    return log.first_name && log.last_name
        ? `${log.first_name} ${log.last_name}`
        : 'Unknown User';
}

function emptyRow(cols, msg) {
    return `<tr><td colspan="${cols}" class="loading-text">${msg}</td></tr>`;
}

function formatDateTime(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleString();
}

function escapeHtml(text) {
    return text ? text.replace(/[&<>"']/g, m =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])
    ) : '';
}

