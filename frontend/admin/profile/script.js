// frontend/admin/profile/script.js

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

function getEl(id) {
    return document.getElementById(id);
}

function setValue(id, value) {
    const el = getEl(id);
    if (!el) return;
    el.value = value ?? '';
}

function getValue(id) {
    const el = getEl(id);
    return el ? el.value.trim() : '';
}

async function loadProfile() {
    try {
        const res = await fetch('../../../backend/user/get_profile.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const u = json.data;
        const fullName = getEl('profileFullName');
        if (fullName) fullName.textContent = `${u.first_name} ${u.last_name}`;
        setValue('pLastName', u.last_name);
        setValue('pFirstName', u.first_name);
        setValue('pMiddleName', u.middle_name || '');
        setValue('pEmail', u.email || '');
        setValue('pRank', u.academic_rank || '');
        setValue('pSchool', u.school_college || '');
        setValue('pDept', u.department || '');
    } catch (e) { showToast('Error loading profile: ' + e.message, 'error'); }
}

async function saveProfile(e) {
    e.preventDefault();
    const body = new FormData();
    body.append('first_name', getValue('pFirstName'));
    body.append('middle_name', getValue('pMiddleName'));
    body.append('last_name', getValue('pLastName'));
    body.append('email', getValue('pEmail'));
    body.append('academic_rank', getValue('pRank'));
    body.append('school_college', getValue('pSchool'));
    body.append('department', getValue('pDept'));
    try {
        const res = await fetch('../../../backend/user/update_profile.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const fullName = getEl('profileFullName');
        if (fullName) fullName.textContent = `${body.get('first_name')} ${body.get('last_name')}`;
        showToast('Profile updated.', 'success');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

async function changePassword(e) {
    e.preventDefault();
    const newPass = document.getElementById('cNewPass').value;
    const confirmPass = document.getElementById('cConfirmPass').value;
    if (newPass !== confirmPass) { showToast('Passwords do not match.', 'warning'); return; }
    if (newPass.length < 6) { showToast('Password must be at least 6 characters.', 'warning'); return; }
    const body = new FormData();
    body.append('current_password', document.getElementById('cCurrentPass').value);
    body.append('new_password', newPass);
    body.append('confirm_password', confirmPass);
    try {
        const res = await fetch('../../../backend/user/change_password.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        document.getElementById('passwordForm').reset();
        showToast('Password changed successfully.', 'success');
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

loadProfile();
