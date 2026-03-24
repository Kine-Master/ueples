/* frontend/secretary/workloads/script.js */
const START_HOUR = 6;
let currentTeacherId = null;
let currentView = 'grid';

document.addEventListener('DOMContentLoaded', () => {
    loadTeachers();
    setupFormSubmission();
    setupSearch();
});

// --- 1. DATA FETCHING ---
function loadTeachers() {
    const list = document.getElementById('teacherList');
    list.innerHTML = '<div class="loading">Loading...</div>';

    fetch('../../../backend/schedule/list_teachers.php')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success' && Array.isArray(data.data)) {
                if(data.data.length === 0) return list.innerHTML = '<div style="padding:15px; text-align:center;">No teachers found.</div>';
                list.innerHTML = data.data.map(t => `
                    <div class="teacher-item" onclick="selectTeacher(${t.user_id}, '${escapeHtml(t.last_name)}', '${escapeHtml(t.first_name)}', '${escapeHtml(t.department||'')}', this)">
                        <div class="avatar">${t.first_name[0]}${t.last_name[0]}</div>
                        <div class="info"><div class="name">${escapeHtml(t.last_name)}, ${escapeHtml(t.first_name)}</div><div class="dept">${escapeHtml(t.department||'')}</div></div>
                    </div>`).join('');
            } else { list.innerHTML = '<div class="error">Failed to load.</div>'; }
        })
        .catch(() => list.innerHTML = '<div class="error">Network Error</div>');
}

function selectTeacher(id, lname, fname, dept, el) {
    currentTeacherId = id;
    document.querySelectorAll('.teacher-item').forEach(i => i.classList.remove('active'));
    if(el) el.classList.add('active');

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('scheduleWorkspace').style.display = 'block';
    document.getElementById('selectedTeacherName').innerText = `${lname}, ${fname}`;
    document.getElementById('selectedTeacherDept').innerText = dept;
    document.getElementById('form_teacher_id').value = id;

    reloadData();
}

function reloadData() {
    if(!currentTeacherId) return;
    const sem = document.getElementById('filterSemester').value;
    const sort = document.getElementById('sortOption').value;

    let url = `../../../backend/schedule/list_by_teacher.php?teacher_id=${currentTeacherId}`;
    // The backend handles 'is_active=1' automatically
    if(currentView === 'grid') url += `&semester=${sem}`; 
    else url += `&sort_by=${sort}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                const schedules = data.schedules || [];
                // Update SY from first result or calc default
                document.getElementById('displaySY').innerText = schedules.length > 0 ? schedules[0].school_year : calculateSY();
                
                if(currentView === 'grid') renderGrid(schedules);
                else renderList(schedules);
            }
        });
}

// --- 2. RENDERING ---
function switchView(view) {
    currentView = view;
    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.toggle-btn[onclick="switchView('${view}')"]`).classList.add('active');
    
    document.getElementById('viewGrid').style.display = view === 'grid' ? 'block' : 'none';
    document.getElementById('viewList').style.display = view === 'list' ? 'block' : 'none';
    document.getElementById('gridControls').style.display = view === 'grid' ? 'block' : 'none';
    document.getElementById('listControls').style.display = view === 'list' ? 'block' : 'none';
    
    reloadData();
}

function renderGrid(schedules) {
    const grid = document.getElementById('gridContent');
    grid.innerHTML = '';
    
    // Draw Headers & Grid Lines
    ['Time', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].forEach(h => {
        const d = document.createElement('div'); d.className='grid-header-cell'; d.innerText=h; grid.appendChild(d);
    });

    for(let i=0; i<26; i++) { // 13 hours * 2
        const row = i+2;
        const line = document.createElement('div'); line.className = (i%2===1)?'grid-bg-line hour-marker':'grid-bg-line';
        line.style.gridColumn='1/-1'; line.style.gridRow=row; grid.appendChild(line);

        if(i%2===0) {
            const time = document.createElement('div'); time.className='time-label';
            time.innerText = formatHour(START_HOUR + i/2);
            time.style.gridRow = row; time.style.gridColumn=1; grid.appendChild(time);
        }
    }

    schedules.forEach(s => {
        const dayMap = {'Monday':2, 'Tuesday':3, 'Wednesday':4, 'Thursday':5, 'Friday':6, 'Saturday':7};
        const col = dayMap[s.day_of_week];
        if(!col) return;
        
        const start = ((parseInt(s.time_in.split(':')[0]) - START_HOUR) * 2) + (parseInt(s.time_in.split(':')[1]) >= 30 ? 1 : 0) + 2;
        const end = ((parseInt(s.time_out.split(':')[0]) - START_HOUR) * 2) + (parseInt(s.time_out.split(':')[1]) >= 30 ? 1 : 0) + 2;
        const duration = end - start;

        if(start >= 2 && duration > 0) {
            const el = document.createElement('div');
            const isCoed = s.schedule_type === 'COED';
            el.className = `schedule-block ${isCoed ? 'block-coed' : 'block-les'}`;
            el.style.gridColumn = col; el.style.gridRow = `${start} / span ${duration}`;
            el.innerHTML = `<strong>${escapeHtml(s.subject)}</strong><br>${escapeHtml(s.room)}`;
            
            if(!isCoed) el.onclick = () => openEditModal(s);
            grid.appendChild(el);
        }
    });
}

function renderList(schedules) {
    const tbody = document.getElementById('listContent');
    if(schedules.length === 0) return tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px;">No schedules.</td></tr>';
    
    tbody.innerHTML = schedules.map(s => {
        const isLes = s.schedule_type === 'LES';
        const json = escapeHtml(JSON.stringify(s));
        return `<tr>
            <td><strong>${escapeHtml(s.subject)}</strong></td>
            <td><span class="badge ${isLes?'badge-les':'badge-coed'}">${s.schedule_type}</span></td>
            <td>${s.day_of_week}<br><small>${formatTime(s.time_in)} - ${formatTime(s.time_out)}</small></td>
            <td>${escapeHtml(s.room)}</td>
            <td>${s.semester}</td>
            <td>${isLes ? `<button onclick="triggerEdit('${json}')" class="btn-edit-icon"><i class="fa-solid fa-pen"></i></button>` : '<i class="fa-solid fa-lock" style="color:#aaa;"></i>'}</td>
        </tr>`;
    }).join('');
}

// --- 3. FORM LOGIC ---
function openAddModal() {
    if(!currentTeacherId) return alert("Select a teacher first.");
    document.getElementById('modalTitle').innerText = "Add LES Schedule";
    document.getElementById('scheduleForm').reset();
    document.getElementById('schedule_id').value = '';
    document.getElementById('form_teacher_id').value = currentTeacherId;
    document.getElementById('form_school_year').value = calculateSY();
    
    // Uncheck and enable all days
    document.querySelectorAll('input[name="days[]"]').forEach(cb => { cb.checked=false; cb.disabled=false; });
    document.getElementById('btnDelete').style.display='none';
    document.getElementById('scheduleModal').style.display='block';
}

function openEditModal(s) {
    document.getElementById('modalTitle').innerText = "Edit Schedule";
    document.getElementById('schedule_id').value = s.schedule_id;
    document.getElementById('form_teacher_id').value = s.teacher_id;
    
    ['subject','units','class_type','time_in','time_out','room','course_year','school_year','semester'].forEach(f => {
        const el = document.querySelector(`[name="${f}"]`);
        if(el) el.value = s[f];
    });

    // Check only the specific day, disable others
    document.querySelectorAll('input[name="days[]"]').forEach(cb => {
        cb.checked = (cb.value === s.day_of_week);
        cb.disabled = !cb.checked;
    });

    document.getElementById('btnDelete').style.display='inline-block';
    document.getElementById('scheduleModal').style.display='block';
}

function setupFormSubmission() {
    const form = document.getElementById('scheduleForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('schedule_id').value;
        const formData = new FormData(this);
        const days = [];
        document.querySelectorAll('input[name="days[]"]:checked').forEach(cb => days.push(cb.value));

        if(days.length === 0) return alert("Select at least one day.");
        
        const btn = document.getElementById('btnSave');
        const txt = btn.innerText; btn.innerText="Saving..."; btn.disabled=true;

        if(id) {
            // UPDATE: Single
            formData.set('day_of_week', days[0]);
            fetch('../../../backend/schedule/update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(d => { if(d.status==='success'){ closeModal(); reloadData(); } else { alert(d.message); } })
                .finally(() => { btn.innerText=txt; btn.disabled=false; });
        } else {
            // CREATE: Loop for multiple days
            const promises = days.map(day => {
                const dData = new FormData(form);
                dData.set('day_of_week', day);
                return fetch('../../../backend/schedule/create.php', { method:'POST', body:dData }).then(r=>r.json());
            });
            Promise.all(promises).then(results => {
                const errs = results.filter(r => r.status!=='success');
                if(errs.length>0) alert("Errors:\n"+errs.map(e=>e.message).join('\n'));
                else { closeModal(); reloadData(); }
            }).finally(() => { btn.innerText=txt; btn.disabled=false; });
        }
    });
}

function deleteSchedule() {
    if(!confirm("Delete this schedule?")) return;
    const fd = new FormData();
    fd.append('schedule_id', document.getElementById('schedule_id').value);
    fetch('../../../backend/schedule/delete.php', { method:'POST', body:fd })
        .then(r=>r.json()).then(d => { if(d.status==='success'){ closeModal(); reloadData(); } else { alert(d.message); } });
}

// --- UTILS ---
function closeModal(){ document.getElementById('scheduleModal').style.display='none'; }
function triggerEdit(j){ openEditModal(JSON.parse(j)); }
function calculateSY(){ const y=new Date().getFullYear(); return (new Date().getMonth()<5) ? `${y-1}-${y}` : `${y}-${y+1}`; }
function formatHour(h){ return (h>12?h-12:h)+":00 "+(h>=12?"PM":"AM"); }
function formatTime(t){ const [h,m]=t.split(':'); return parseInt(h)+":"+m; }
function escapeHtml(t){ return t?t.toString().replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#039;','\"':'&quot;'}[m])): ''; }
function setupSearch(){ document.getElementById('searchTeacher').addEventListener('input', e => { 
    const v=e.target.value.toLowerCase();
    document.querySelectorAll('.teacher-item').forEach(i => i.style.display = i.innerText.toLowerCase().includes(v)?'flex':'none'); 
});}
function printReport() {
    if(!currentTeacherId) return alert("Select teacher.");
    const sem = document.getElementById('filterSemester').value;
    window.open(`report.php?teacher_id=${currentTeacherId}&semester=${sem}`, '_blank');
}