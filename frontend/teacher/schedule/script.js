// frontend/teacher/schedule/script.js

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
    document.getElementById('btnViewTimetable').classList.toggle('active', view === 'timetable');
    document.getElementById('btnViewTimetable').style.background = view === 'timetable' ? 'var(--accent)' : 'transparent';
    document.getElementById('btnViewTimetable').style.color = view === 'timetable' ? '#fff' : 'var(--text-sub)';

    document.getElementById('btnViewTable').classList.toggle('active', view === 'table');
    document.getElementById('btnViewTable').style.background = view === 'table' ? 'var(--accent)' : 'transparent';
    document.getElementById('btnViewTable').style.color = view === 'table' ? '#fff' : 'var(--text-sub)';

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
        grid.innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:var(--text-muted)">No schedules to display.</div>';
        return;
    }

    const { blocks, covered } = buildBlockMap(schedules);

    const DAY_COL = {};
    DAYS.forEach((d, i) => { DAY_COL[d] = i + 2; });

    let html = '';

    html += '<div class="g-day-hdr" style="grid-column:1;grid-row:1;border-right:1px solid #10b981"></div>';
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

async function loadSchoolYears() {
    const sel = document.getElementById('fSchoolYear');
    try {
        const res = await fetch('../../../backend/master_data/school_year/list.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message || 'Failed to load school years');
        const rows = json.data || [];
        sel.innerHTML = '';
        rows.forEach(sy => {
            const opt = document.createElement('option');
            opt.value = sy.school_year_id;
            opt.textContent = sy.label + (Number(sy.is_active) === 1 ? ' (Active)' : '');
            if (Number(sy.is_active) === 1) opt.selected = true;
            sel.appendChild(opt);
        });
        if (!sel.value && rows[0]) sel.value = rows[0].school_year_id;
    } catch (e) {
        sel.innerHTML = '<option value="">School year unavailable</option>';
        console.error(e);
    }
}

function updatePrintLink() {
    const sem = document.getElementById('fSem').value;
    const sy = document.getElementById('fSchoolYear').value;
    const params = new URLSearchParams({ semester: sem });
    if (sy) params.append('school_year_id', sy);
    document.getElementById('printScheduleBtn').href = `report.php?${params.toString()}`;
}

async function loadSchedule() {
    const sem = document.getElementById('fSem').value;
    const sy = document.getElementById('fSchoolYear').value;
    const body = document.getElementById('schedBody');
    const wrap = document.getElementById('viewTimetable');
    updatePrintLink();

    body.innerHTML = '<tr><td colspan="5" class="loading-text"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>';

    try {
        let url = `../../../backend/schedule/list.php?semester=${encodeURIComponent(sem)}`;
        if (sy) url += `&school_year_id=${encodeURIComponent(sy)}`;
        const res = await fetch(url);
        const json = await res.json();

        if (json.status !== 'success') throw new Error(json.message);

        const data = json.data;

        if (!data || data.length === 0) {
            body.innerHTML = '<tr class="no-data"><td colspan="5">No active schedules found.</td></tr>';
            renderGrid([]);
            return;
        }

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

    } catch (e) {
        body.innerHTML = `<tr><td colspan="5" style="color:red">Error: ${e.message}</td></tr>`;
        document.getElementById('plotGrid').innerHTML = '<div style="grid-column:1/-1;padding:40px;text-align:center;color:red">Failed to load timetable.</div>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadSchoolYears().then(() => {
        updatePrintLink();
        loadSchedule();
    });
});
