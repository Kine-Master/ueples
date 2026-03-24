document.addEventListener('DOMContentLoaded', () => {
    loadProfile();
    setupProfileUpdate();
    setupPasswordUpdate();
});

function loadProfile() {
    fetch('../../../backend/user/get_profile.php')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const u = res.data;
                
                // Left Panel Display
                document.getElementById('displayName').innerText = `${u.first_name} ${u.last_name}`;
                // Explicitly set Principal role label
                document.getElementById('displayRole').innerText = 'PRINCIPAL'; 
                
                document.getElementById('displayDept').innerText = u.department || 'Administration';
                
                // Avatar
                const initials = (u.first_name[0] + u.last_name[0]).toUpperCase();
                document.getElementById('avatarDisplay').innerText = initials;

                // Form Fields (Populate ALL fields)
                document.getElementById('firstName').value = u.first_name;
                document.getElementById('middleName').value = u.middle_name || ''; 
                document.getElementById('lastName').value = u.last_name;
                document.getElementById('email').value = u.email;
                document.getElementById('username').value = u.username;
                
                document.getElementById('academicRank').value = u.academic_rank || '';
                document.getElementById('schoolCollege').value = u.school_college || '';
                document.getElementById('department').value = u.department || '';
            }
        });
}

function setupProfileUpdate() {
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../../backend/user/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Profile Updated Successfully!");
                loadProfile(); // Refresh UI
            } else {
                alert("Error: " + data.message);
            }
        });
    });
}

function setupPasswordUpdate() {
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../../backend/user/change_password.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Password Changed Successfully!");
                this.reset();
            } else {
                alert("Error: " + data.message);
            }
        });
    });
}