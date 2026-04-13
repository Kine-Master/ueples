// frontend/secretary/schedule/create_schedule.js
// Unified LES + COED schedule creation with live 15-min timetable grid.

'use strict';

// ── Constants ─────────────────────────────────────────────────────────────────
const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
const START_H = 6;   // 6:00 AM
const END_H = 18;    // 6:00 PM  (exclusive, last slot 17:45)
const SLOT_MIN = 15; // 15-minute rows
const SLOTS_PER_HOUR = 60 / SLOT_MIN;
const TOTAL_ROWS = (END_H - START_H) * SLOTS_PER_HOUR; // 48 rows

// ── Theme ─────────────────────────────────────────────────────────────────────
function initTheme() {
    const t = localStorage.getItem('ueples_theme') || 'dark';
    document.documentElement.dataset.theme = t;
    document.getElementById('themeBtn').textContent = t === 'dark' ? '🌙' : '☀️';
}
function toggleTheme() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('ueples_theme', next);
    document.getElementById('themeBtn').textContent = next === 'dark' ? '🌙' : '☀️';
}

// ── Accordion ─────────────────────────────────────────────────────────────────
function toggleAcc(id) {
    const el = document.getElementById(id);
    const btn = el.querySelector('.acc-trigger');
    // Do not expand if this step is still locked
    if (btn && btn.disabled) return;
    el.classList.toggle('acc-open');
}

// ── Type switch (LES ↔ COED) ──────────────────────────────────────────────────
function onTypeChange() {
    const isLES = document.getElementById('fType').value === 'LES';

    document.getElementById('acc-details').style.display = isLES ? '' : 'none';
    document.getElementById('acc-coed-fields').style.display = isLES ? 'none' : '';
    document.getElementById('specialistToggleWrap').style.display = isLES ? '' : 'none';
    document.getElementById('grp-room-sel').style.display = isLES ? '' : 'none';
    document.getElementById('grp-room-txt').style.display = isLES ? 'none' : '';

    // Reset flow sequence
    stepCompleted('type');
}

// ── Step Sequencing Logic ─────────────────────────────────────────────────────
function unlockStep(id) {
    const wrap = document.getElementById(id);
    const trigger = document.querySelector(`#${id} .acc-trigger`);
    if (!wrap || !trigger) return;
    wrap.classList.add('acc-open');
    trigger.disabled = false;
}

function stepCompleted(step) {
    const type = document.getElementById('fType').value;
    const sy = document.getElementById('fSy').value;
    const sem = document.getElementById('fSem').value;
    const room = type === 'LES' ? document.getElementById('fRoom').value : document.getElementById('fCoedRoomTxt').value.trim();
    const teacher = document.getElementById('fTeacher').value;
    const lesReady = document.getElementById('fSec').value && document.getElementById('fSubj').value;
    const coedReady = document.getElementById('fCoedCourse').value.trim() && document.getElementById('fCoedSubject').value.trim();

    if (step === 'grade') {
        const grade = document.getElementById('fGrade').value;
        document.getElementById('fSec').disabled = !grade;
        document.getElementById('fCurriculum').disabled = !grade;
        document.getElementById('fSubj').disabled = true;
    }
    if (step === 'subj' && type === 'LES') onSubjectChange();

    if (type) unlockStep('acc-sy');
    if (sy && sem) unlockStep('acc-room');
    if (sy && sem && room) unlockStep('acc-teacher');
    if (sy && sem && room && teacher) unlockStep(type === 'LES' ? 'acc-details' : 'acc-coed-fields');
    if (type === 'LES' && sy && sem && room && teacher && lesReady) unlockStep('acc-time');
    if (type === 'COED' && sy && sem && room && teacher && coedReady) unlockStep('acc-time');

    const days = Array.from(document.querySelectorAll('#dayCheckboxes input:checked'));
    const hasTime = document.getElementById('fStart').value && document.getElementById('fEnd').value;
    const canSave = (type === 'LES' ? lesReady : coedReady) && !!room && !!teacher && !!sy && !!sem && days.length > 0 && hasTime;
    document.getElementById('saveBtn').disabled = !canSave;
    onFieldChange();
}

// ── Dropdowns loader ──────────────────────────────────────────────────────────
async function loadDropdowns() {
    try {
        const [rSy, rRoom, rGrade] = await Promise.all([
            fetch('../../../backend/master_data/school_year/list.php').then(r => r.json()),
            fetch('../../../backend/master_data/room/list.php').then(r => r.json()),
            fetch('../../../backend/master_data/grade_level/list.php').then(r => r.json()),
        ]);

        // School Years
        const selSy = document.getElementById('fSy');
        selSy.innerHTML = '<option value="">Select school year…</option>';
        (rSy.data || []).forEach(s => {
            selSy.innerHTML += `<option value="${s.school_year_id}" ${s.is_active ? 'selected' : ''}>${s.label}${s.is_active ? ' (Active)' : ''}</option>`;
        });

        // Rooms
        const selRoom = document.getElementById('fRoom');
        selRoom.innerHTML = '<option value="">Select room…</option>';
        (rRoom.data || []).filter(r => r.is_active).forEach(r => {
            selRoom.innerHTML += `<option value="${r.room_id}">${r.room_name} (${r.building_name})</option>`;
        });

        // Grade Levels
        const selGrade = document.getElementById('fGrade');
        selGrade.innerHTML = '<option value="">Select grade level…</option>';
        (rGrade.data || []).forEach(g => {
            selGrade.innerHTML += `<option value="${g.grade_level_id}">${g.name}</option>`;
        });

        await loadTeachers();

        // Unlock the first step (SY) now that data is loaded
        stepCompleted('type');

        // Also auto-trigger SY step if an active SY is pre-selected
        if (document.getElementById('fSy').value && document.getElementById('fSem').value) {
            stepCompleted('sy');
        }

    } catch (e) {
        showToast('Error loading form data', 'error');
        console.error(e);
    }
}

// ── Teachers loader ───────────────────────────────────────────────────────────
async function loadTeachers() {
    const subj = document.getElementById('fSubj').value;
    const specialist = document.getElementById('chkSpecialist').checked;
    const dayBox = document.querySelector('#dayCheckboxes input:checked');
    const day = dayBox ? dayBox.value : '';
    const start = document.getElementById('fStart').value;
    const end = document.getElementById('fEnd').value;
    const sy = document.getElementById('fSy').value;
    const sem = document.getElementById('fSem').value;

    let url = `../../../backend/schedule/get_available_teachers.php?subject_id=${subj || 0}`;
    if (specialist && subj) url += `&specialists_only=1`;
    if (sy) url += `&school_year_id=${sy}`;
    if (sem) url += `&semester=${sem}`;
    if (day) url += `&day_of_week=${day}`;
    if (start) url += `&time_in=${start}`;
    if (end) url += `&time_out=${end}`;

    try {
        const json = await fetch(url).then(r => r.json());
        const sel = document.getElementById('fTeacher');
        const old = sel.value;
        sel.innerHTML = '<option value="">Select teacher…</option>';

        (json.data || []).forEach(t => {
            const conflict = t.is_available === false;
            const star = t.is_specialist ? ' ★' : '';
            const label = `${t.last_name}, ${t.first_name}${star}${conflict ? ' ⚠ Conflict' : ''}`;
            sel.innerHTML += `<option value="${t.user_id}" ${conflict ? 'disabled' : ''}>${label}</option>`;
        });

        if (old && sel.querySelector(`option[value="${old}"]:not([disabled])`)) sel.value = old;

    } catch (e) { console.error('Teacher load error', e); }
}

async function onGradeChange() {
    const grade = document.getElementById('fGrade').value;
    const selSec = document.getElementById('fSec');
    const selSubj = document.getElementById('fSubj');
    const selCurr = document.getElementById('fCurriculum');

    if (!grade) {
        selSec.innerHTML = '<option value="">Select grade first…</option>';
        selSubj.innerHTML = '<option value="">Select curriculum first…</option>';
        selCurr.innerHTML = '<option value="">Select grade first…</option>';
        stepCompleted('grade');
        return;
    }

    selSec.innerHTML = '<option value="">Loading classes…</option>';
    selCurr.innerHTML = '<option value="">Loading curricula…</option>';
    selSubj.innerHTML = '<option value="">Select curriculum first…</option>';
    stepCompleted('grade'); // Enable the dropdowns

    try {
        const [rSec, rCurr] = await Promise.all([
            fetch(`../../../backend/master_data/class_section/list.php?grade_level_id=${grade}`).then(r => r.json()),
            fetch(`../../../backend/master_data/curriculum/list.php?grade_level_id=${grade}`).then(r => r.json())
        ]);

        selSec.innerHTML = '<option value="">Select section…</option>';
        (rSec.data || []).filter(x => x.is_active).forEach(x => {
            selSec.innerHTML += `<option value="${x.class_section_id}">${x.section_name}</option>`;
        });

        selCurr.innerHTML = '<option value="">Select curriculum…</option>';
        (rCurr.data || []).filter(c => c.is_active).forEach(c => {
            selCurr.innerHTML += `<option value="${c.curriculum_id}">${c.name}</option>`;
        });

    } catch (e) {
        showToast('Error loading classes/curricula', 'error');
        console.error(e);
    }
}

async function onCurriculumChange() {
    const curriculum = document.getElementById('fCurriculum').value;
    const grade = document.getElementById('fGrade').value;
    const selSubj = document.getElementById('fSubj');
    selSubj.disabled = !curriculum;
    if (!curriculum) {
        selSubj.innerHTML = '<option value="">Select curriculum first…</option>';
        stepCompleted('subj');
        return;
    }

    selSubj.innerHTML = '<option value="">Loading subjects…</option>';
    try {
        const rSubj = await fetch(`../../../backend/master_data/subject/list.php?grade_level_id=${grade}&curriculum_id=${curriculum}`).then(r => r.json());
        selSubj.innerHTML = '<option value="">Select subject…</option>';
        (rSubj.data || []).filter(s => s.is_active).forEach(s => {
            selSubj.innerHTML += `<option value="${s.subject_id}">${s.name}</option>`;
        });
    } catch (e) {
        showToast('Error loading subjects', 'error');
        console.error(e);
    }
    stepCompleted('subj');
}

async function onSubjectChange() {
    await loadTeachers();
    onFieldChange();
}

// ── Main timetable refresh ────────────────────────────────────────────────────
let _refreshTimer = null;
function onFieldChange() {
    clearTimeout(_refreshTimer);
    _refreshTimer = setTimeout(refreshTimetable, 220);
}

async function refreshTimetable() {
    const sy = document.getElementById('fSy').value;
    const sem = document.getElementById('fSem').value;
    const teacher = document.getElementById('fTeacher').value;
    const type = document.getElementById('fType').value;

    // Need at least school year to load something useful
    if (!sy) {
        setStatus('Select a school year to load the timetable.');
        renderGrid([], [], []);
        return;
    }

    setStatus('<i class="fa-solid fa-spinner fa-spin"></i> Loading timetable…');

    try {
        const room = type === 'LES' ? document.getElementById('fRoom').value : document.getElementById('fCoedRoomTxt').value.trim();

        let listUrl = `../../../backend/schedule/list.php?active=1`;
        if (sy) listUrl += `&school_year_id=${sy}`;
        if (sem) listUrl += `&semester=${sem}`;

        const resp = await fetch(listUrl);
        const all = await resp.json();
        const schedules = all?.data || [];

        // Separate by teacher and room (mutually exclusive sets)
        const teacherSched = teacher ? schedules.filter(s => String(s.teacher_id) === String(teacher)) : [];
        const selectedRoomName = type === 'LES'
            ? ((document.getElementById('fRoom').selectedOptions[0]?.text || '').split(' (')[0] || '')
            : room;
        const roomNameNorm = selectedRoomName.trim().toLowerCase();
        const roomSched = room ? schedules.filter(s => {
            const lesMatch = type === 'LES' && String(s.room_id || '') === String(room);
            const coedByName = roomNameNorm && String(s.coed_room || '').trim().toLowerCase() === roomNameNorm;
            const lesByName = roomNameNorm && String(s.room_name || '').trim().toLowerCase() === roomNameNorm;
            const roomMatch = lesMatch || coedByName || lesByName;
            // Exclude selected teacher's own schedules (already in teacherSched)
            const differentTeacher = !teacher || String(s.teacher_id) !== String(teacher);
            return roomMatch && differentTeacher;
        }) : [];

        renderGrid(teacherSched, roomSched, schedules);

        // Status message
        let msgs = [];
        if (teacher && teacherSched.length) msgs.push(`${teacherSched.length} slot(s) for selected teacher`);
        if (room && roomSched.length) msgs.push(`${roomSched.length} slot(s) in room by other teachers`);
        setStatus(msgs.length ? `Showing occupied: ${msgs.join(', ')}.` : 'Timetable loaded. No conflicts found yet.');

    } catch (e) {
        setStatus('Error loading timetable.');
        console.error(e);
    }
}

function paddingTimeWithSecs(h, m) {
    const ampm = h < 12 ? 'am' : 'pm';
    const h12 = h === 0 ? 12 : h > 12 ? h - 12 : h;
    return `${h12}:${String(m).padStart(2, '0')}:00 ${ampm}`;
}

// ── Grid renderer ──────────────────────────────────────────────────────────
// Uses explicit grid-column + grid-row on EVERY item so CSS auto-placement
// is never relied upon — guaranteeing day columns stay aligned even when
// occupied block cells span multiple rows.
function renderGrid(teacherSched, roomSched, _all) {
    const grid = document.getElementById('plotGrid');
    const startVal = document.getElementById('fStart').value;
    const endVal = document.getElementById('fEnd').value;

    const selectedDays = Array.from(document.querySelectorAll('#dayCheckboxes input:checked')).map(cb => cb.value);

    const { blocks: tBlocks, covered: tCovered } = buildBlockMap(teacherSched, 'teacher');
    const { blocks: rBlocks, covered: rCovered } = buildBlockMap(roomSched, 'room');
    const { blocks: pBlocks, covered: pCovered } = buildPreviewBlocks(selectedDays, startVal, endVal);

    // Column map: time=1, Mon=2 … Sun=8
    const DAY_COL = {};
    DAYS.forEach((d, i) => { DAY_COL[d] = i + 2; });

    let html = '';

    // ── Header row (grid-row: 1) ──────────────────────
    html += '<div class="g-day-hdr" style="grid-column:1;grid-row:1;border-right:1px solid #a3e635"></div>';
    DAYS.forEach(d => {
        const col = DAY_COL[d];
        const isSel = selectedDays.includes(d);
        html += `<div class="g-day-hdr" style="grid-column:${col};grid-row:1;${isSel ? 'color:#15803d;border-bottom:2px solid #15803d' : ''}">${d}</div>`;
    });

    // ── Time rows (grid-row: 2 … TOTAL_ROWS+1) ────────
    for (let row = 0; row < TOTAL_ROWS; row++) {
        const gridRow = row + 2;  // row 1 is the header
        const totalMins = START_H * 60 + row * SLOT_MIN;
        const h1 = Math.floor(totalMins / 60), m1 = totalMins % 60;
        const h2 = Math.floor((totalMins + SLOT_MIN) / 60), m2 = (totalMins + SLOT_MIN) % 60;

        // Time label — always in column 1
        html += `<div class="g-time" style="grid-column:1;grid-row:${gridRow}">${paddingTimeWithSecs(h1, m1)}–${paddingTimeWithSecs(h2, m2)}</div>`;

        DAYS.forEach(d => {
            const col = DAY_COL[d];
            const key = `${d}:${paddedTime(h1, m1)}`;

            const isBT = tBlocks.has(key);
            const iBR = rBlocks.has(key);
            const iBP = pBlocks.has(key);
            const iCT = tCovered.has(key);
            const iCR = rCovered.has(key);
            const iCP = pCovered.has(key);

            // Skip intermediate rows of an already-rendered spanning block
            if (!isBT && !iBR && !iBP && (iCT || iCR || iCP)) return;

            let cls = '', label = '', span = 1;

            if (iBP && (isBT || iBR || iCT || iCR)) {
                const b = pBlocks.get(key);
                cls = 'conflict'; label = 'CONFLICT! ' + b.label; span = b.span;
            } else if (isBT) {
                const b = tBlocks.get(key);
                cls = 'occupied'; label = b.label; span = b.span;
            } else if (iBR) {
                const b = rBlocks.get(key);
                cls = 'occupied'; label = b.label; span = b.span;
            } else if (iBP) {
                const b = pBlocks.get(key);
                cls = 'preview'; label = b.label; span = b.span;
            }

            // Explicit column + spanning row placement
            const endGridRow = gridRow + span;
            const pos = `grid-column:${col};grid-row:${gridRow}/${endGridRow}`;
            const labelHtml = label ? `<div class="cl">${escHtml(label)}</div>` : '';
            html += `<div class="g-cell${cls ? ' ' + cls : ''}" style="${pos}">${labelHtml}</div>`;
        });
    }

    grid.innerHTML = html;
}

function buildBlockMap(schedules, _type) {
    const blocks = new Map(); // startKey → {label, span}
    const covered = new Set(); // all keys the block occupies

    schedules.forEach(s => {
        if (!s.day_of_week || (!s.start_time && !s.time_in)) return;
        const day = s.day_of_week;
        const start = timeToMins(s.start_time || s.time_in);
        const end = timeToMins(s.end_time || s.time_out);
        if (start >= end) return;

        const spanCount = Math.ceil((end - start) / SLOT_MIN);

        const isLES = s.schedule_type === 'LES';
        const subjLabel = isLES ? (s.subject_name || 'Subject') : (s.coed_subject || s.coed_subject_name || 'COED');
        const classLabel = isLES
            ? (s.section_name ? ` · ${s.section_name}` : '')
            : (s.coed_grade_level ? ` · ${s.coed_grade_level}` : '');
        const teacherLabel = s.teacher_name ? ` | ${s.teacher_name}` : '';
        const roomLabel = s.room_name ? ` | ${s.room_name}` : (s.coed_room ? ` | ${s.coed_room}` : '');
        const label = `${subjLabel}${classLabel}${teacherLabel}${roomLabel}`;

        const h0 = Math.floor(start / 60), m0 = start % 60;
        const startKey = `${day}:${paddedTime(h0, m0)}`;
        blocks.set(startKey, { label, span: spanCount });

        for (let t = start; t < end; t += SLOT_MIN) {
            const h = Math.floor(t / 60), m = t % 60;
            covered.add(`${day}:${paddedTime(h, m)}`);
        }
    });
    return { blocks, covered };
}

function buildPreviewBlocks(daysArray, startStr, endStr) {
    const blocks = new Map();
    const covered = new Set();
    if (!daysArray || !daysArray.length || !startStr || !endStr) return { blocks, covered };
    const start = timeToMins(startStr);
    const end = timeToMins(endStr);
    if (start >= end) return { blocks, covered };

    const spanCount = Math.ceil((end - start) / SLOT_MIN);
    const type = document.getElementById('fType').value;
    const subj = document.getElementById('fSubj');
    let label = 'Proposed';
    if (type === 'LES' && subj.selectedOptions[0]?.value) {
        label = subj.selectedOptions[0].text;
    } else if (type === 'COED') {
        label = document.getElementById('fCoedSubject').value || 'COED Class';
    }

    daysArray.forEach(day => {
        const h0 = Math.floor(start / 60), m0 = start % 60;
        blocks.set(`${day}:${paddedTime(h0, m0)}`, { label, span: spanCount });
        for (let t = start; t < end; t += SLOT_MIN) {
            const h = Math.floor(t / 60), m = t % 60;
            covered.add(`${day}:${paddedTime(h, m)}`);
        }
    });
    return { blocks, covered };
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function timeToMins(str) {
    if (!str) return 0;
    const [h, m] = str.split(':').map(Number);
    return h * 60 + (m || 0);
}
function paddedTime(h, m) {
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
}
function formatHour(h) {
    const ampm = h < 12 ? 'AM' : 'PM';
    const h12 = h === 0 ? 12 : h > 12 ? h - 12 : h;
    return `${h12}${ampm}`;
}
function escHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function setStatus(msg) {
    document.getElementById('plotStatus').innerHTML = msg;
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-info'}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}

// ── Submit ────────────────────────────────────────────────────────────────────
async function submitSchedule() {
    const type = document.getElementById('fType').value;
    const sy = document.getElementById('fSy').value;
    const sem = document.getElementById('fSem').value;
    const teach = document.getElementById('fTeacher').value;
    const room = type === 'LES' ? document.getElementById('fRoom').value : document.getElementById('fCoedRoomTxt').value.trim();
    const start = document.getElementById('fStart').value;
    const end = document.getElementById('fEnd').value;
    const days = Array.from(document.querySelectorAll('#dayCheckboxes input:checked')).map(cb => cb.value);

    const errBox = document.getElementById('errorBox');
    const btn = document.getElementById('saveBtn');

    errBox.style.display = 'none';

    // Validate
    const missing = [];
    if (!sy || !sem) missing.push('School Year & Sem');
    if (!teach) missing.push('Teacher');
    if (!room) missing.push('Room');
    if (days.length === 0) missing.push('Day(s) of Week');
    if (!start) missing.push('Start Time');
    if (!end) missing.push('End Time');

    if (type === 'LES') {
        if (!document.getElementById('fSec').value) missing.push('Class Section');
        if (!document.getElementById('fSubj').value) missing.push('Subject');
    } else {
        if (!document.getElementById('fCoedSubject').value.trim()) missing.push('Subject/Course Name');
        if (!document.getElementById('fCoedCourse').value.trim()) missing.push('Course/Year Level');
        if (!document.getElementById('fCoedUnits').value) missing.push('Units');
    }

    if (missing.length) {
        errBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Missing: ${missing.join(', ')}`;
        errBox.style.display = 'block';
        return;
    }
    if (start >= end) {
        errBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> End time must be after start time.';
        errBox.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';

    let endpoint = type === 'LES' ? '../../../backend/schedule/create_les.php' : '../../../backend/schedule/create_coed.php';
    let baseData = {
        school_year_id: sy,
        teacher_id: teach,
        semester: sem,
        start_time: start,
        end_time: end,
        time_in: start,
        time_out: end
    };

    if (type === 'LES') {
        baseData.room_id = room;
        baseData.subject_id = document.getElementById('fSubj').value;
        baseData.class_section_id = document.getElementById('fSec').value;
    } else {
        baseData.coed_room = room;
        baseData.coed_subject_name = document.getElementById('fCoedSubject').value.trim();
        baseData.coed_course_year = document.getElementById('fCoedCourse').value.trim();
        baseData.coed_units = document.getElementById('fCoedUnits').value;
    }

    try {
        // Iterate over days and Promise.all to map them into individual POST calls
        const promises = days.map(day => {
            const body = new FormData();
            for (let k in baseData) body.append(k, baseData[k]);
            body.append('day_of_week', day);
            return fetch(endpoint, { method: 'POST', body }).then(r => r.json());
        });

        const results = await Promise.all(promises);

        // Find if any failed
        const failed = results.find(r => r.status !== 'success');

        if (failed) {
            errBox.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> ${failed.message}`;
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Create Schedule';
            return;
        }

        showToast(`${type} schedule plotted successfully!`, 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 1100);

    } catch (e) {
        errBox.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> System error. Please try again.';
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Create Schedule';
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────
initTheme();
loadDropdowns();
