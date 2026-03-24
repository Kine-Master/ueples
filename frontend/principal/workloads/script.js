/* frontend/principal/workloads/script.js */

const START_HOUR = 6;
let currentTeacherId = null;
let currentView = 'grid';

document.addEventListener('DOMContentLoaded', () => {
    loadTeachers();
    setupSearch();
});

// 1. DATA FETCHING
function loadTeachers() {
    const list = document.getElementById('teacherList');
    list.innerHTML = '<div class="loading">Loading...</div>';

    fetch('../../../backend/schedule/list_teachers.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.data)) {
                if (data.data.length === 0) return list.innerHTML = '<div style="padding:20px; text-align:center;">No teachers found.</div>';
                
                list.innerHTML = data.data.map(t => `
                    <div class="teacher-item" onclick="selectTeacher(${t.user_id}, '${escapeHtml(t.last_name)}', '${escapeHtml(t.first_name)}', '${escapeHtml(t.department || '')}', this)">
                        <div class="avatar">${(t.first_name?t.first_name[0]:'')}${(t.last_name?t.last_name[0]:'')}</div>
                        <div class="info">
                            <div class="name">${escapeHtml(t.last_name)}, ${escapeHtml(t.first_name)}</div>
                            <div class="dept">${escapeHtml(t.department || 'N/A')}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div class="error">Failed to load list.</div>';
            }
        })
        .catch(() => list.innerHTML = '<div class="error">Network Error</div>');
}

function selectTeacher(id, lname, fname, dept, el) {
    currentTeacherId = id;
    document.querySelectorAll('.teacher-item').forEach(i => i.classList.remove('active'));
    if(el) el.classList.add('active');

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('scheduleWorkspace').style.display = 'block'; // Flex for column layout
    document.getElementById('selectedTeacherName').innerText = `${lname}, ${fname}`;
    document.getElementById('selectedTeacherDept').innerText = dept;

    reloadData();
}

function reloadData() {
    if (!currentTeacherId) return;
    const sem = document.getElementById('filterSemester').value;
    
    // NOTE: Sorting for list view is handled by backend default or we could add sort dropdown
    let url = `../../../backend/schedule/list_by_teacher.php?teacher_id=${currentTeacherId}&semester=${sem}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const schedules = data.schedules || [];
                // Update SY
                document.getElementById('displaySY').innerText = schedules.length > 0 ? schedules[0].school_year : calculateSY();
                
                if (currentView === 'grid') renderGrid(schedules);
                else renderList(schedules);
            }
        });
}

// 2. RENDERING (READ ONLY)
function switchView(view) {
    currentView = view;
    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.toggle-btn[onclick="switchView('${view}')"]`).classList.add('active');
    
    document.getElementById('viewGrid').style.display = view === 'grid' ? 'block' : 'none';
    document.getElementById('viewList').style.display = view === 'list' ? 'block' : 'none';
    
    reloadData();
}

function renderGrid(schedules) {
    const grid = document.getElementById('gridContent');
    grid.innerHTML = '';
    
    // Headers
    ['Time', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].forEach(h => {
        const d = document.createElement('div'); d.className='grid-header-cell'; d.innerText=h; grid.appendChild(d);
    });

    // Rows
    for(let i=0; i<26; i++) {
        const row = i+2;
        const line = document.createElement('div'); line.className = (i%2===1)?'grid-bg-line hour-marker':'grid-bg-line';
        line.style.gridColumn='1/-1'; line.style.gridRow=row; grid.appendChild(line);
        if(i%2===0) {
            const time = document.createElement('div'); time.className='time-label';
            time.innerText = formatHour(START_HOUR + i/2);
            time.style.gridRow=row; time.style.gridColumn=1; grid.appendChild(time);
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
            
            // Content
            el.innerHTML = `<strong>${escapeHtml(s.subject)}</strong><br>${escapeHtml(s.room)}`;
            el.title = `${s.subject} (${s.time_in} - ${s.time_out})`; // Tooltip
            
            grid.appendChild(el);
        }
    });
}

function renderList(schedules) {
    const tbody = document.getElementById('listContent');
    if(schedules.length === 0) return tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px;">No schedules found.</td></tr>';
    
    tbody.innerHTML = schedules.map(s => {
        const isLes = s.schedule_type === 'LES';
        return `<tr>
            <td><strong>${escapeHtml(s.subject)}</strong></td>
            <td><span class="badge ${isLes?'badge-les':'badge-coed'}">${s.schedule_type}</span></td>
            <td>${s.day_of_week}<br><small>${formatTime(s.time_in)} - ${formatTime(s.time_out)}</small></td>
            <td>${escapeHtml(s.room)}</td>
            <td>${s.semester == 1 ? '1st' : (s.semester == 2 ? '2nd' : 'Summer')}</td>
        </tr>`;
    }).join('');
}

// 3. UTILS
function calculateSY(){ const y=new Date().getFullYear(); return (new Date().getMonth()<5) ? `${y-1}-${y}` : `${y}-${y+1}`; }
function formatHour(h){ return (h>12?h-12:h)+":00 "+(h>=12?"PM":"AM"); }
function formatTime(t){ const [h,m]=t.split(':'); return parseInt(h)+":"+m; }
function escapeHtml(t){ return t?t.toString().replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#039;','\"':'&quot;'}[m])): ''; }

function setupSearch(){ 
    document.getElementById('searchTeacher').addEventListener('input', e => { 
        const v=e.target.value.toLowerCase();
        document.querySelectorAll('.teacher-item').forEach(i => i.style.display = i.innerText.toLowerCase().includes(v)?'flex':'none'); 
    });
}

function printReport() {
    if(!currentTeacherId) return alert("Select a teacher first.");
    const sem = document.getElementById('filterSemester').value;
    // Uses the Secretary's report format but accessed via Principal's folder if copied, or shared.
    // Assuming we create a local report.php for Principal:
    window.open(`report.php?teacher_id=${currentTeacherId}&semester=${sem}`, '_blank');
}