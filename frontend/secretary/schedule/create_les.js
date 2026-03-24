// frontend/secretary/schedule/create_les.js

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

async function loadDropdowns() {
    try {
        // Subjects
        const rs = await fetch('../../../backend/master_data/subject/list.php');
        const js = await rs.json();
        const selSubj = document.getElementById('fSubj');
        js.data.forEach(s => {
            if (s.is_active) selSubj.innerHTML += `<option value="${s.subject_id}">${s.subject_name} (${s.curriculum_name} - ${s.grade_name})</option>`;
        });

        // Sections (active SY only since it's implied for new schedules)
        const rsec = await fetch('../../../backend/master_data/class_section/list.php');
        const jsec = await rsec.json();
        const selSec = document.getElementById('fSec');
        jsec.data.forEach(x => {
            if (x.is_active) selSec.innerHTML += `<option value="${x.class_section_id}">${x.section_name} (${x.grade_name})</option>`;
        });

        // Rooms
        const rr = await fetch('../../../backend/master_data/room/list.php');
        const jr = await rr.json();
        const selRoom = document.getElementById('fRoom');
        jr.data.forEach(r => {
            if (r.is_active) selRoom.innerHTML += `<option value="${r.room_id}">${r.room_name} (${r.building_name}) - Cap: ${r.capacity}</option>`;
        });

        // Check teacher availability initially to populate the list
        checkTeacherAvailability();

    } catch (e) { showToast('Error loading form data', 'error'); }
}

async function checkTeacherAvailability() {
    const subj = document.getElementById('fSubj').value;
    const day = document.getElementById('fDay')?.value;
    const start = document.getElementById('fStart')?.value;
    const end = document.getElementById('fEnd')?.value;

    if (!subj) { document.getElementById('fTeacher').innerHTML = '<option value="">Select subject first…</option>'; return; }

    let url = `../../../backend/schedule/get_available_teachers.php?subject_id=${subj}`;
    if (day && start && end) url += `&day_of_week=${day}&start_time=${start}&end_time=${end}`;

    try {
        const res = await fetch(url);
        const json = await res.json();
        const sel = document.getElementById('fTeacher');
        const oldVal = sel.value;
        sel.innerHTML = '<option value="">Select teacher…</option>';

        if (json.status !== 'success' || !json.data.length) {
            sel.innerHTML += '<option value="" disabled>No qualified teachers for this subject</option>';
            return;
        }

        json.data.forEach(t => {
            if (t.is_available) {
                sel.innerHTML += `<option value="${t.user_id}">${t.first_name} ${t.last_name}</option>`;
            } else {
                sel.innerHTML += `<option value="${t.user_id}" disabled>${t.first_name} ${t.last_name} (Conflict)</option>`;
            }
        });

        // Restore selected value if it's still an option and not disabled
        const opt = sel.querySelector(`option[value="${oldVal}"]`);
        if (opt && !opt.disabled) sel.value = oldVal;
        else sel.value = '';

    } catch (e) { console.error('Error fetching teachers', e); }
}

async function fetchRoomSlots() {
    const room = document.getElementById('fRoom').value;
    const day = document.getElementById('fDay').value;
    const sem = document.getElementById('fSem').value;
    const panel = document.getElementById('roomPanel');
    const container = document.getElementById('slotsContainer');
    const sub = document.getElementById('roomSubText');

    if (!room || !day) {
        if (window.innerWidth > 800) panel.style.display = 'block';
        container.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.85rem">Select a room and day to view occupied slots.</div>';
        return;
    }

    panel.style.display = 'block';
    sub.textContent = `${day} • Semester ${sem}`;
    container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-sub)"><i class="fa-solid fa-spinner fa-spin"></i> Checking slots…</div>';

    try {
        const res = await fetch(`../../../backend/schedule/get_room_slots.php?room_id=${room}&day_of_week=${day}&semester=${sem}`);
        const json = await res.json();

        if (json.status !== 'success') {
            container.innerHTML = `<div style="color:#ef4444;font-size:.85rem"><i class="fa-solid fa-triangle-exclamation"></i> ${json.message}</div>`;
            return;
        }

        const { capacity, slots } = json.data;
        sub.innerHTML = `${day} • Semester ${sem} <br><span class="badge badge-info" style="margin-top:6px;font-size:.7rem">Cap: ${capacity}</span>`;

        if (!slots.length) {
            container.innerHTML = '<div style="text-align:center;padding:30px;color:var(--success);font-size:.9rem"><i class="fa-solid fa-circle-check"></i> Room is fully vacant.</div>';
            return;
        }

        container.innerHTML = slots.map(s => {
            const typeBadge = s.schedule_type === 'LES' ? `<span style="color:#38bdf8;font-size:.7rem">LES</span>` : `<span style="color:#fbbf24;font-size:.7rem">COED</span>`;
            const title = s.schedule_type === 'LES' ? `${s.subject_name} (${s.section_name})` : s.coed_subject_name;
            return `
      <div class="slot-item">
        <strong>${formatTime(s.start_time)} - ${formatTime(s.end_time)} ${typeBadge}</strong>
        <div style="color:var(--text-sub);margin-top:2px">${title}</div>
        <div style="color:var(--text-muted);font-size:.75rem"><i class="fa-solid fa-user"></i> ${s.teacher_name}</div>
      </div>`;
        }).join('');

    } catch (e) {
        container.innerHTML = '<div style="color:#ef4444"><i class="fa-solid fa-circle-xmark"></i> Failed to verify slots.</div>';
    }
}

// Re-fetch slots if semester changes
document.getElementById('fSem').addEventListener('change', fetchRoomSlots);

async function createSchedule(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    const errBox = document.getElementById('errorBox');
    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing…';

    const body = new FormData();
    body.append('subject_id', document.getElementById('fSubj').value);
    body.append('class_section_id', document.getElementById('fSec').value);
    body.append('teacher_id', document.getElementById('fTeacher').value);
    body.append('semester', document.getElementById('fSem').value);
    body.append('room_id', document.getElementById('fRoom').value);
    body.append('day_of_week', document.getElementById('fDay').value);
    body.append('start_time', document.getElementById('fStart').value);
    body.append('end_time', document.getElementById('fEnd').value);

    try {
        const res = await fetch('../../../backend/schedule/create_les.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') {
            errBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${json.message}`;
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Create Schedule';
            return;
        }

        showToast('LES Schedule created successfully!', 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 1000);

    } catch (e) {
        errBox.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> System error during creation.`;
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Create Schedule';
    }
}

loadDropdowns();
