<div id="userModal" class="modal" style="display:none;">
    <div class="modal-content medium-modal">
        <div class="modal-header">
            <h3 id="modalTitle">Add New User</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        
        <form id="userForm">
            <input type="hidden" name="user_id" id="userId">
            
            <div class="form-row">
                <div class="input-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" id="firstName" required>
                </div>
                <div class="input-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" id="middleName">
                </div>
                <div class="input-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" id="lastName" required>
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Role *</label>
                    <select name="role_id" id="roleId" required onchange="toggleTeacherFields()">
                        <option value="3">Teacher</option>
                        <option value="2">Secretary</option>
                        <option value="1">Principal</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Department</label>
                    <input type="text" name="department" id="department" placeholder="e.g. Science">
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="username" required>
                </div>
            </div>

            <div class="form-row" id="teacherFields" style="display:none;">
                 <div class="input-group">
                    <label>Academic Rank</label>
                    <input type="text" name="academic_rank" id="academicRank" placeholder="e.g. Instructor I">
                </div>
                <div class="input-group">
                    <label>School/College</label>
                    <input type="text" name="school_college" id="schoolCollege" placeholder="e.g. College of Ed">
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>