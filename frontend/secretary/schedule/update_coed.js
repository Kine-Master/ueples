// frontend/secretary/schedule/update_coed.js

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-info'}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
function formatTime(time24) {
    if (!time24) return '';
    const [h, m] = time24.split(':');
    const d = new Date(); d.setHours(h); d.setMinutes(m);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}
function setValue(id, val) {
    const el = document.getElementById(id);
    if (el && val !== undefined && val !== null) el.value = val;
}

async function init() {
    const schedId = document.getElementById('fId').value;
    try {
        const [tRes, roomRes] = await Promise.all([
            fetch('../../../backend/schedule/list_teachers.php'),
            fetch('../../../backend/master_data/room/list.php')
        ]);
        const [tJs, roomJs] = await Promise.all([tRes.json(), roomRes.json()]);

        document.getElementById('fTeacher').innerHTML = tJs.data.map(t => `<option value="${t.user_id}">${t.first_name} ${t.last_name}</option>`).join('');
        document.getElementById('fRoom').innerHTML = roomJs.data.map(r => `<option value="${r.room_id}">${r.room_name} (${r.building_name})</option>`).join('');

        const slRes = await fetch('../../../backend/schedule/list.php');
        const slJs = await slRes.json();
        const sched = slJs.data.find(x => x.schedule_id == schedId);
        if (!sched) throw new Error("Schedule not found.");

        setValue('fSubj', sched.coed_subject_name);
        setValue('fCourse', sched.coed_course_year);
        setValue('fTeacher', sched.teacher_id);
        setValue('fSem', sched.semester);
        setValue('fRoom', sched.room_id);
        setValue('fDay', sched.day_of_week);
        setValue('fStart', sched.start_time);
        setValue('fEnd', sched.end_time);

        fetchRoomSlots();

    } catch (e) { showToast(e.message, 'error'); }
}

async function fetchRoomSlots() {
    const room = document.getElementById('fRoom').value;
    const day = document.getElementById('fDay').value;
    const sem = document.getElementById('fSem').value;
    const schedId = document.getElementById('fId').value;
    const panel = document.getElementById('roomPanel');
    const container = document.getElementById('slotsContainer');

    if (!room || !day) return;
    panel.style.display = 'block';

    try {
        let url = `../../../backend/schedule/get_room_slots.php?room_id=${room}&day_of_week=${day}&semester=${sem}&exclude_schedule_id=${schedId}`;
        const res = await fetch(url);
        const json = await res.json();

        if (!json.data.slots.length) {
            container.innerHTML = '<div style="text-align:center;padding:30px;color:var(--success);font-size:.9rem"><i class="fa-solid fa-circle-check"></i> Room is otherwise vacant.</div>';
            return;
        }

        container.innerHTML = json.data.slots.map(s => `
      <div class="slot-item">
        <strong>${formatTime(s.start_time)} - ${formatTime(s.end_time)}</strong>
        <div style="color:var(--text-sub);margin-top:2px">${s.schedule_type === 'LES' ? s.subject_name : s.coed_subject_name}</div>
      </div>
    `).join('');
    } catch (e) { container.innerHTML = 'Error loading slots.'; }
}
document.getElementById('fSem').addEventListener('change', fetchRoomSlots);

async function updateSchedule(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    const errBox = document.getElementById('errorBox');
    errBox.style.display = 'none';
    btn.disabled = true;

    const body = new FormData();
    body.append('schedule_id', document.getElementById('fId').value);
    body.append('coed_subject_name', document.getElementById('fSubj').value.trim());
    body.append('coed_course_year', document.getElementById('fCourse').value.trim());
    body.append('teacher_id', document.getElementById('fTeacher').value);
    body.append('semester', document.getElementById('fSem').value);
    body.append('room_id', document.getElementById('fRoom').value);
    body.append('day_of_week', document.getElementById('fDay').value);
    body.append('start_time', document.getElementById('fStart').value);
    body.append('end_time', document.getElementById('fEnd').value);

    try {
        const res = await fetch('../../../backend/schedule/update_coed.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') {
            errBox.innerHTML = json.message; errBox.style.display = 'block';
            btn.disabled = false; return;
        }
        showToast('Schedule updated.', 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 1000);
    } catch (e) {
        errBox.innerHTML = 'System error.'; errBox.style.display = 'block'; btn.disabled = false;
    }
}

init();
