<div id="scheduleModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color:#fefefe; margin:5% auto; padding:25px; border:1px solid #888; width:450px; border-radius:8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 id="modalTitle" style="margin:0; color:#2c3e50;">Add COED Schedule</h3>
            <span onclick="closeModal()" style="cursor:pointer; font-size:24px; color:#aaa;">&times;</span>
        </div>
        
        <form id="scheduleForm">
            <input type="hidden" name="schedule_id" id="schedule_id">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label class="form-label">Semester</label>
                    <select name="semester" id="form_semester" class="form-input">
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">School Year</label>
                    <input type="text" name="school_year" id="form_school_year" placeholder="YYYY-YYYY" required class="form-input">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Subject / Subject Code </label>
                <input type="text" name="subject" required class="form-input" placeholder="e.g. MATH 101">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label class="form-label">Units</label>
                    <input type="number" step="0.5" name="units" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Class Type</label>
                    <select name="class_type" class="form-input">
                        <option value="Lecture">Lecture</option>
                        <option value="Laboratory">Laboratory</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Days (Select multiple to repeat)</label>
                <div class="day-selector" style="display:flex; flex-wrap:wrap; gap:10px; background:#f9f9f9; padding:10px; border:1px solid #ddd; border-radius:4px;">
                    <label><input type="checkbox" name="days[]" value="Monday"> Mon</label>
                    <label><input type="checkbox" name="days[]" value="Tuesday"> Tue</label>
                    <label><input type="checkbox" name="days[]" value="Wednesday"> Wed</label>
                    <label><input type="checkbox" name="days[]" value="Thursday"> Thu</label>
                    <label><input type="checkbox" name="days[]" value="Friday"> Fri</label>
                    <label><input type="checkbox" name="days[]" value="Saturday"> Sat</label>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label class="form-label">Time In</label>
                    <input type="time" name="time_in" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Time Out</label>
                    <input type="time" name="time_out" required class="form-input">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label class="form-label">Room</label>
                <input type="text" name="room" placeholder="e.g. ACAD101" required class="form-input">
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label class="form-label">Course / Year Level / Section</label>
                <input type="text" name="course_year" placeholder="e.g. BSIT - 4A" class="form-input">
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #eee; padding-top:15px;">
                <button type="button" id="btnDelete" onclick="deleteSchedule()" style="display:none; background:#e74c3c; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
                <div style="margin-left:auto; display:flex; gap:10px;">
                    <button type="button" onclick="closeModal()" style="background:#ff0000ff; color:white; border:none; padding:8px 20px; border-radius:4px; cursor:pointer; font-weight:bold;">Cancel</button>
                    <button type="submit" id="btnSave" style="background:#00897b; color:white; border:none; padding:8px 20px; border-radius:4px; cursor:pointer; font-weight:bold;">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
.form-label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; color: #555; }
.form-input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
.day-selector label { font-size: 13px; font-weight: normal; cursor: pointer; }
</style>