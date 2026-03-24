// frontend/secretary/schedule/script.js

let pendingDelId = null;

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-info'}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function formatTime(time24) {
    if (!time24) return '';
    const [h, m] = time24.split(':');
    const d = new Date(); d.setHours(h); d.setMinutes(m);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function handleTypeChange() {
    const t = document.getElementById('fType').value;
    const sem = document.getElementById('fSem');
    if (t === 'COED') { sem.style.display = 'block'; }
    else { sem.style.display = 'none'; sem.value = ''; }
}

async function fetchDropdowns() {
    try {
        const rt = await fetch('../../../backend/schedule/list_teachers.php');
        const jt = await rt.json();
        const sel = document.getElementById('fTeacher');
        (jt.data || []).forEach(t => {
            sel.innerHTML += `<option value="${t.user_id}">${t.first_name} ${t.last_name}</option>`;
        });

        document.getElementById('fType').addEventListener('change', handleTypeChange);

        loadSchedules();
    } catch (e) { showToast('Error fetching filters', 'error'); }
}

async function loadSchedules() {
    const t = document.getElementById('fType').value;
    const s = document.getElementById('fSem').value;
    const d = document.getElementById('fDay').value;
    const u = document.getElementById('fTeacher').value;

    const body = document.getElementById('schedBody');
    body.innerHTML = '<tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';

    let p = new URLSearchParams();
    if (t) p.append('schedule_type', t);
    if (t === 'COED' && s) p.append('semester', s);
    if (d) p.append('day_of_week', d);
    if (u) p.append('teacher_id', u);
    const printBtn = document.getElementById('printScheduleBtn');
    printBtn.href = `report.php?${p.toString()}`;

    try {
        const res = await fetch('../../../backend/schedule/list.php?' + p);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const data = json.data;
        if (!data.length) { body.innerHTML = '<tr class="no-data"><td colspan="7">No schedules match your filters.</td></tr>'; return; }

        body.innerHTML = data.map(row => {
            const isLES = row.schedule_type === 'LES';
            const badgeClass = isLES ? 'badge-info' : 'badge-warning';

            let subjName = isLES ? row.subject_name : row.coed_subject;
            let secName = isLES ? (row.section_name ? `${row.section_name} <br><small style="color:var(--text-muted)">(${row.grade_name})</small>` : 'N/A') : row.coed_grade_level;

            let roomName;
            if (isLES) {
                roomName = row.room_name ? `${row.room_name} <small style="color:var(--text-muted)">(${row.building_name})</small>` : 'TBA';
            } else {
                roomName = row.coed_room ? `${row.coed_room} <small style="color:var(--text-muted)">(${row.coed_building || 'External'})</small>` : 'TBA';
            }

            let editLink = isLES ? `update_les.php?id=${row.schedule_id}` : `update_coed.php?id=${row.schedule_id}`;
            let timeStr = `${formatTime(row.time_in)} - ${formatTime(row.time_out)}`;

            return `
      <tr>
        <td><span class="badge ${badgeClass}">${row.schedule_type}</span></td>
        <td><strong>${subjName}</strong></td>
        <td>${secName || '—'}</td>
        <td>${row.teacher_name}</td>
        <td>
          <div style="font-weight:600;color:var(--accent)">${row.day_of_week}</div>
          <div style="font-size:.85rem;color:var(--text-sub)">${timeStr}</div>
          <div style="font-size:.85rem;margin-top:2px"><i class="fa-solid fa-door-open" style="color:var(--text-muted)"></i> ${roomName}</div>
        </td>
        <td>${row.semester ? `${row.semester}` : '—'}</td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" title="Edit" onclick="window.location.href='${editLink}'"><i class="fa-solid fa-pen"></i></button>
            <button class="btn btn-danger btn-sm btn-icon" title="Archive" onclick="openArchiveModal(${row.schedule_id})"><i class="fa-solid fa-trash"></i></button>
          </div>
        </td>
      </tr>`;
        }).join('');

        renderGrid(data);

    } catch (e) {
        body.innerHTML = '<tr><td colspan="7">Error loading data.</td></tr>';
        showToast(e.message, 'error');
    }
}

function openArchiveModal(id) {
    pendingDelId = id;
    openModal('delModal');
}

async function doArchive() {
    const body = new FormData(); body.append('schedule_id', pendingDelId);
    try {
        const res = await fetch('../../../backend/schedule/delete.php', { method: 'POST', body });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('delModal');
        showToast('Schedule archived successfully.', 'success');
        loadSchedules();
    } catch (e) { showToast(e.message, 'error'); }
}

fetchDropdowns();

// ── View Toggle ────────────────────────────────────────────────────────────
function switchView(view) {
    document.getElementById('btnViewTimetable').classList.toggle('active', view === 'timetable');
    document.getElementById('btnViewTimetable').style.background = view === 'timetable' ? 'var(--accent)' : 'transparent';
    document.getElementById('btnViewTimetable').style.color = view === 'timetable' ? '#fff' : 'var(--text-sub)';

    document.getElementById('btnViewTable').classList.toggle('active', view === 'table');
    document.getElementById('btnViewTable').style.background = view === 'table' ? 'var(--accent)' : 'transparent';
    document.getElementById('btnViewTable').style.color = view === 'table' ? '#fff' : 'var(--text-sub)';

    document.getElementById('viewTimetable').style.display = view === 'timetable' ? 'block' : 'none';
    document.getElementById('viewTable').style.display = view === 'table' ? 'block' : 'none';
}

// ── Timetable Rendering ────────────────────────────────────────────────────
const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const START_H = 6;
const END_H = 18;
const SLOT_MIN = 15;
const TOTAL_ROWS = (END_H - START_H) * (60 / SLOT_MIN);

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"]/g, match =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[match]
    );
}

function timeToMins(t24) {
    if (!t24) return 0;
    const [h, m] = t24.split(':');
    return parseInt(h, 10) * 60 + parseInt(m, 10);
}

function paddedTime(h, m) { return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'); }

function paddingTimeWithSecs(h, m) {
    const d = new Date(); d.setHours(h, m, 0);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function buildBlockMap(schedules) {
    const blocks = new Map();
    const covered = new Set();

    schedules.forEach(s => {
        const mIn = timeToMins(s.time_in);
        const mOut = timeToMins(s.time_out);
        if (mIn >= mOut || mIn < START_H * 60 || mOut > END_H * 60) return;

        const totalSlots = (mOut - mIn) / SLOT_MIN;
        if (totalSlots <= 0) return;

        const h1 = Math.floor(mIn / 60); const min1 = mIn % 60;
        const startKey = `${s.day_of_week}:${paddedTime(h1, min1)}`;

        const isLES = s.schedule_type === 'LES';
        let actSubj = isLES ? s.subject_name : s.coed_subject;
        let actGrade = isLES ? s.grade_name : 'COED';
        let actSec = isLES ? s.section_name : (s.coed_grade_level || 'N/A');
        let roomLbl = isLES
            ? (s.room_name ? `${s.room_name} (${s.building_name || ''})` : 'TBA')
            : (s.coed_room ? `${s.coed_room} (${s.coed_building || 'External'})` : 'TBA');
        let labelHtml = `<strong>${escHtml(actSubj)}</strong><br><small>${escHtml(s.teacher_name || 'Teacher')}</small><br><small>${escHtml(actGrade)} - ${escHtml(actSec)}</small><br><small style="opacity:0.8">${escHtml(roomLbl)}</small>`;

        blocks.set(startKey, {
            type: s.schedule_type,
            label: labelHtml,
            span: totalSlots
        });

        for (let i = 0; i < totalSlots; i++) {
            const mCur = mIn + i * SLOT_MIN;
            const hc = Math.floor(mCur / 60); const mc = mCur % 60;
            covered.add(`${s.day_of_week}:${paddedTime(hc, mc)}`);
        }
    });

    return { blocks, covered };
}

function renderGrid(schedules) {
    const grid = document.getElementById('plotGrid');
    if (!schedules || schedules.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:var(--text-muted)">No schedules to display.</div>';
        return;
    }

    const { blocks, covered } = buildBlockMap(schedules);

    const DAY_COL = {};
    DAYS.forEach((d, i) => { DAY_COL[d] = i + 2; });

    let html = '';

    html += '<div class="g-day-hdr" style="grid-column:1;grid-row:1;border-right:1px solid #a3e635"></div>';
    DAYS.forEach(d => {
        const col = DAY_COL[d];
        html += `<div class="g-day-hdr" style="grid-column:${col};grid-row:1">${d}</div>`;
    });

    for (let row = 0; row < TOTAL_ROWS; row++) {
        const gridRow = row + 2;
        const totalMins = START_H * 60 + row * SLOT_MIN;
        const h1 = Math.floor(totalMins / 60), m1 = totalMins % 60;
        const h2 = Math.floor((totalMins + SLOT_MIN) / 60), m2 = (totalMins + SLOT_MIN) % 60;

        html += `<div class="g-time" style="grid-column:1;grid-row:${gridRow}">${paddingTimeWithSecs(h1, m1)}–${paddingTimeWithSecs(h2, m2)}</div>`;

        DAYS.forEach(d => {
            const col = DAY_COL[d];
            const key = `${d}:${paddedTime(h1, m1)}`;

            const isBlock = blocks.has(key);
            const isCovered = covered.has(key);

            if (!isBlock && isCovered) return; // Skip intermediate cells of a spanning block

            let cls = '', label = '', span = 1;

            if (isBlock) {
                const b = blocks.get(key);
                cls = b.type === 'LES' ? 'occupied' : 'coed';
                label = b.label;
                span = b.span;
            }

            const endGridRow = gridRow + span;
            const pos = `grid-column:${col};grid-row:${gridRow}/${endGridRow}`;
            const labelHtml = label ? `<div class="cl">${label}</div>` : '';
            html += `<div class="g-cell${cls ? ' ' + cls : ''}" style="${pos}">${labelHtml}</div>`;
        });
    }

    grid.innerHTML = html;
}
