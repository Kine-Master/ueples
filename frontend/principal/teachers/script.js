// frontend/principal/teachers/script.js

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const START_H = 6;
const END_H = 18;
const SLOT_MIN = 15;
const TOTAL_ROWS = (END_H - START_H) * (60 / SLOT_MIN);

function formatTime(time24) {
    if (!time24) return '';
    const [h, m] = time24.split(':');
    const d = new Date(); d.setHours(h); d.setMinutes(m);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

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

// ── View Toggle ────────────────────────────────────────────────────────────
function switchView(view) {
    // Buttons
    document.getElementById('btnViewTimetable').classList.toggle('active', view === 'timetable');
    document.getElementById('btnViewTimetable').style.background = view === 'timetable' ? 'var(--accent)' : 'transparent';
    document.getElementById('btnViewTimetable').style.color = view === 'timetable' ? '#fff' : 'var(--text-sub)';

    document.getElementById('btnViewTable').classList.toggle('active', view === 'table');
    document.getElementById('btnViewTable').style.background = view === 'table' ? 'var(--accent)' : 'transparent';
    document.getElementById('btnViewTable').style.color = view === 'table' ? '#fff' : 'var(--text-sub)';

    // Wrappers
    document.getElementById('viewTimetable').style.display = view === 'timetable' ? 'block' : 'none';
    document.getElementById('viewTable').style.display = view === 'table' ? 'block' : 'none';
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
        let actSec = isLES ? `${s.grade_name} - ${s.section_name}` : (s.coed_grade_level || 'COED');
        let actRoom = isLES ? (s.room_name ? `${s.room_name} (${s.building_name})` : 'TBA') : (s.coed_room ? `${s.coed_room} (${s.coed_building || 'External'})` : 'TBA');
        let actTeacher = s.teacher_name || 'Teacher';
        let labelHtml = `<strong>${escHtml(actSubj)}</strong><br><small>${escHtml(actTeacher)}</small><br><small>${escHtml(actSec)}</small><br><small style="opacity:0.8"><i class="fa-solid fa-door-open"></i> ${escHtml(actRoom)}</small>`;

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
        grid.innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:var(--text-muted)"><i class="fa-regular fa-calendar" style="font-size:2rem;margin-bottom:12px;opacity:0.3"></i><br>No schedules found for this teacher and semester.</div>';
        return;
    }

    const { blocks, covered } = buildBlockMap(schedules);

    const DAY_COL = {};
    DAYS.forEach((d, i) => { DAY_COL[d] = i + 2; });

    let html = '';

    html += '<div class="g-day-hdr" style="grid-column:1;grid-row:1;border-right:1px solid var(--accent)"></div>';
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

            if (!isBlock && isCovered) return;

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

// ── Data Fetching ──────────────────────────────────────────────────────────

async function loadDropdowns() {
    try {
        const [syRes, tRes] = await Promise.all([
            fetch('../../../backend/master_data/school_year/list.php'),
            fetch('../../../backend/schedule/list_teachers.php')
        ]);
        const syJson = await syRes.json();
        const tJson = await tRes.json();

        const sySel = document.getElementById('fSchoolYear');
        (syJson.data || []).forEach(sy => {
            const isAct = Number(sy.is_active) === 1 ? 'selected' : '';
            sySel.innerHTML += `<option value="${sy.school_year_id}" ${isAct}>${sy.label}</option>`;
        });

        const tSel = document.getElementById('fTeacher');
        (tJson.data || []).forEach(t => {
            tSel.innerHTML += `<option value="${t.user_id}">${t.last_name}, ${t.first_name}</option>`;
        });
    } catch (e) { console.error(e); }
}

async function loadSchedule() {
    const syId = document.getElementById('fSchoolYear').value;
    const tId = document.getElementById('fTeacher').value;
    const sem = document.getElementById('fSem').value;

    const loading = document.getElementById('loadingWrap');
    const empty = document.getElementById('emptyState');
    const viewTable = document.getElementById('viewTable');
    const viewTimetable = document.getElementById('viewTimetable');
    const body = document.getElementById('schedBody');
    const printBtn = document.getElementById('printScheduleBtn');
    const printParams = new URLSearchParams();
    if (syId) printParams.set('school_year_id', syId);
    if (tId) printParams.set('teacher_id', tId);
    if (sem) printParams.set('semester', sem);
    printBtn.href = `report.php?${printParams.toString()}`;
    printBtn.style.pointerEvents = tId ? '' : 'none';
    printBtn.style.opacity = tId ? '1' : '.6';

    if (!tId) {
        empty.style.display = 'block';
        loading.style.display = 'none';
        viewTable.style.display = 'none';
        viewTimetable.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    loading.style.display = 'block';
    // Maintain current display mode via classes but hide content wrappers during load
    const isTimetableActive = document.getElementById('btnViewTimetable').classList.contains('active');
    viewTable.style.display = 'none';
    viewTimetable.style.display = 'none';

    body.innerHTML = '<tr><td colspan="5" class="loading-text"><i class="fa-solid fa-spinner fa-spin"></i> Fetching records...</td></tr>';
    document.getElementById('plotGrid').innerHTML = '';

    try {
        const res = await fetch(`../../../backend/schedule/list.php?school_year_id=${syId}&teacher_id=${tId}&semester=${sem}`);
        const json = await res.json();

        if (json.status !== 'success') throw new Error(json.message);

        const data = json.data;

        if (!data || data.length === 0) {
            body.innerHTML = '<tr class="no-data"><td colspan="5">No active schedules found for this teacher and semester.</td></tr>';
            renderGrid([]);
        } else {
            body.innerHTML = data.map(s => {
                const isLES = s.schedule_type === 'LES';
                const badge = isLES ? 'les-badge' : 'coed-badge';
                const subj = isLES ? s.subject_name : s.coed_subject;
                const sec = isLES ? `${s.grade_name} - ${s.section_name}` : s.coed_grade_level;
                const roomLbl = isLES ? (s.room_name ? `${s.room_name} (${s.building_name})` : 'TBA') : (s.coed_room ? `${s.coed_room} (${s.coed_building || 'External'})` : 'TBA');
                const dTr = `${s.day_of_week} | ${formatTime(s.time_in)} - ${formatTime(s.time_out)}<br><small style="color:var(--text-muted)"><i class="fa-solid fa-door-open"></i> ${roomLbl}</small>`;
                const semLbl = s.semester == 1 ? '1st Sem' : '2nd Sem';

                return `<tr>
            <td><span class="custom-badge ${badge}">${s.schedule_type}</span></td>
            <td><strong>${escHtml(subj)}</strong></td>
            <td>${escHtml(sec)}</td>
            <td>${dTr}</td>
            <td>${semLbl}</td>
          </tr>`;
            }).join('');

            renderGrid(data);
        }

        loading.style.display = 'none';
        // Restore currently selected view
        if (isTimetableActive) {
            viewTimetable.style.display = 'block';
        } else {
            viewTable.style.display = 'block';
        }

    } catch (e) {
        loading.style.display = 'none';
        body.innerHTML = `<tr><td colspan="5" style="color:red">Error: ${e.message}</td></tr>`;
        document.getElementById('plotGrid').innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:red">Failed to load timetable.</div>';
        if (isTimetableActive) {
            viewTimetable.style.display = 'block';
        } else {
            viewTable.style.display = 'block';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDropdowns();
    loadSchedule();
    // Default mode setup
    document.getElementById('viewTable').style.display = 'none';
    document.getElementById('viewTimetable').style.display = 'none';
});
