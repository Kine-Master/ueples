let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
});

// 1. DEBOUNCED SEARCH
function debounceLoad() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadUsers, 300);
}

// 2. FETCH DATA
function loadUsers() {
    const search = document.getElementById('searchInput').value;
    const role = document.getElementById('roleFilter').value;
    const tbody = document.getElementById('userTableBody');

    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;">Loading...</td></tr>';

    // Point to the Admin User Management Backend
    const url = `../../../backend/user_management/list_users.php?search=${encodeURIComponent(search)}&role=${role}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
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

// 3. RENDER TABLE
function renderTable(users) {
    const tbody = document.getElementById('userTableBody');
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No users found.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(u => `
        <tr>
            <td>
                <strong>${escapeHtml(u.last_name)}, ${escapeHtml(u.first_name)}</strong>
            </td>
            <td><span class="badge role-${u.role_name.toLowerCase()}">${u.role_name}</span></td>
            <td>${escapeHtml(u.username)}</td>
            <td>${escapeHtml(u.department || '-')}</td>
            <td>
                <span class="status-pill ${u.is_active == 1 ? 'active' : 'inactive'}">
                    ${u.is_active == 1 ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button onclick='openEditModal(${u.user_id})' title="Edit User" class="btn-icon edit">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    
                    <button onclick="toggleStatus(${u.user_id}, ${u.is_active})" title="${u.is_active == 1 ? 'Deactivate' : 'Activate'}" class="btn-icon toggle">
                        <i class="fa-solid ${u.is_active == 1 ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                    </button>
                    
                    <button onclick="resetPassword(${u.user_id}, '${escapeHtml(u.username)}')" title="Reset Password" class="btn-icon reset">
                        <i class="fa-solid fa-key"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// --- MODAL FUNCTIONS ---

function openAddModal() {
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = ''; // Empty ID = Create Mode
    document.getElementById('modalTitle').innerText = 'Add New User';
    document.getElementById('username').readOnly = false; 
    
    toggleTeacherFields();
    document.getElementById('userModal').style.display = 'flex';
}

function openEditModal(id) {
    // Fetch fresh data for editing
    fetch(`../../../backend/user_management/get_user.php?user_id=${id}`)
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                const u = res.data;
                document.getElementById('userId').value = u.user_id;
                document.getElementById('firstName').value = u.first_name;
                document.getElementById('middleName').value = u.middle_name || '';
                document.getElementById('lastName').value = u.last_name;
                document.getElementById('email').value = u.email;
                document.getElementById('username').value = u.username;
                document.getElementById('department').value = u.department || '';
                document.getElementById('roleId').value = u.role_id;
                document.getElementById('academicRank').value = u.academic_rank || '';
                document.getElementById('schoolCollege').value = u.school_college || '';
                
                document.getElementById('username').readOnly = true; // Lock Username on Edit
                document.getElementById('modalTitle').innerText = 'Edit User';
                
                toggleTeacherFields();
                document.getElementById('userModal').style.display = 'flex';
            }
        });
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

function toggleTeacherFields() {
    const role = document.getElementById('roleId').value;
    const fields = document.getElementById('teacherFields');
    // Show if Role is Teacher (ID 3)
    fields.style.display = (role == '3') ? 'flex' : 'none';
}

// --- FORM SUBMIT (Create/Update) ---
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('userId').value;
    
    // Choose Endpoint
    const url = id 
        ? '../../../backend/user_management/update.php' 
        : '../../../backend/user_management/create.php';
    
    const formData = new FormData(this);

    fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert(data.message);
                closeModal();
                loadUsers();
            } else {
                alert("Error: " + data.message);
            }
        });
});

// --- ACTIONS ---

function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus == 1 ? 0 : 1;
    const action = newStatus == 1 ? "activate" : "deactivate";
    
    if(!confirm(`Are you sure you want to ${action} this user?`)) return;

    const formData = new FormData();
    formData.append('user_id', id);
    formData.append('is_active', newStatus);

    fetch('../../../backend/user_management/toggle_status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') loadUsers();
            else alert("Error: " + data.message);
        });
}

function resetPassword(id, username) {
    const formData = new FormData();
    formData.append('user_id', id);

    fetch('../../../backend/user_management/reset_password.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
        });
}

// Utility: XSS Protection
function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}