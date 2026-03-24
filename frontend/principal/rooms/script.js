// frontend/principal/rooms/script.js

async function loadDropdowns() {
    try {
        const res = await fetch('../../../backend/master_data/building/list.php');
        const json = await res.json();
        const sel = document.getElementById('fBldg');
        json.data.forEach(b => {
            sel.innerHTML += `<option value="${b.building_id}">${b.building_name}</option>`;
        });
    } catch (e) { console.error(e); }

    loadRooms();
}

async function loadRooms() {
    const bId = document.getElementById('fBldg').value;
    const wrap = document.getElementById('roomWrap');
    const loading = document.getElementById('loadingWrap');

    wrap.style.display = 'none';
    loading.style.display = 'block';

    try {
        const res = await fetch('../../../backend/master_data/room/list.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);

        let rooms = json.data;
        if (bId) rooms = rooms.filter(r => r.building_id == bId);

        if (!rooms.length) {
            wrap.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">No rooms found for this building.</div>`;
        } else {
            wrap.innerHTML = rooms.map(r => {
                const badge = r.is_active ? '<span class="badge badge-active" style="font-size:.7rem">Active</span>' : '<span class="badge badge-inactive" style="font-size:.7rem">Inactive</span>';
                return `
          <div class="room-card">
            <div class="room-header">
               <div class="room-title"><i class="fa-solid fa-door-open" style="color:var(--accent)"></i> ${r.room_name}</div>
               ${badge}
            </div>
            <div class="room-body">
               <div style="margin-bottom:8px"><strong>Building:</strong> <span style="color:var(--text)">${r.building_name}</span></div>
               <div><strong>Capacity:</strong> <span style="color:var(--text)">${r.capacity} seats</span></div>
               <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                 <button class="btn btn-secondary btn-sm" onclick="openRoomDetail(${r.room_id}, '${String(r.room_name).replace(/'/g, '&#39;')}')"><i class="fa-solid fa-eye"></i> View Details</button>
               </div>
            </div>
          </div>
        `;
            }).join('');
        }

        loading.style.display = 'none';
        wrap.style.display = 'grid';

    } catch (e) {
        loading.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;font-size:2rem;margin-bottom:12px"></i><p style="color:#ef4444">Failed to load rooms.</p>`;
    }
}

document.addEventListener('DOMContentLoaded', loadDropdowns);

function closeRoomDetail() {
    document.getElementById('roomDetailModal').classList.remove('open');
}

function fmtTime(t) {
    if (!t) return '';
    const [h, m] = t.split(':');
    const d = new Date();
    d.setHours(h, m, 0, 0);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function mins(t) {
    if (!t) return 0;
    const [h, m] = t.split(':').map(Number);
    return (h * 60) + (m || 0);
}

async function openRoomDetail(roomId, roomName) {
    const modal = document.getElementById('roomDetailModal');
    document.getElementById('roomDetailTitle').innerHTML = `<i class="fa-solid fa-door-open"></i> ${roomName} Details`;
    modal.classList.add('open');

    const activeSy = await fetch('../../../backend/master_data/school_year/list.php').then(r => r.json()).then(j => (j.data || []).find(x => Number(x.is_active) === 1));
    const syId = activeSy ? activeSy.school_year_id : '';

    const params = new URLSearchParams({ active: '1' });
    if (syId) params.set('school_year_id', syId);
    const schedules = await fetch(`../../../backend/schedule/list.php?${params.toString()}`).then(r => r.json()).then(j => j.data || []);

    const roomRows = schedules.filter(s => Number(s.room_id) === Number(roomId) || String(s.coed_room || '').trim().toLowerCase() === String(roomName || '').trim().toLowerCase());
    const totalMinutes = roomRows.reduce((sum, s) => sum + Math.max(0, mins(s.time_out) - mins(s.time_in)), 0);
    const totalHours = (totalMinutes / 60);
    const weeklyHours = 72; // Monday-Saturday, 6AM-6PM
    const util = Math.min(100, (totalHours / weeklyHours) * 100);

    document.getElementById('roomDetailStats').innerHTML = `
      <div class="room-stat"><small>Total Classes</small><strong>${roomRows.length}</strong></div>
      <div class="room-stat"><small>Scheduled Hours</small><strong>${totalHours.toFixed(1)}</strong></div>
      <div class="room-stat"><small>Utilization</small><strong>${util.toFixed(1)}%</strong></div>
    `;

    const body = document.getElementById('roomScheduleBody');
    if (!roomRows.length) {
        body.innerHTML = '<tr><td colspan="6">No active schedules for this room.</td></tr>';
    } else {
        body.innerHTML = roomRows.map(s => {
            const isLes = s.schedule_type === 'LES';
            const subject = isLes ? s.subject_name : s.coed_subject;
            const section = isLes ? `${s.grade_name || ''} - ${s.section_name || ''}` : (s.coed_grade_level || 'COED');
            return `<tr>
                <td>${s.schedule_type}</td>
                <td>${subject || 'N/A'}</td>
                <td>${s.teacher_name || 'N/A'}</td>
                <td>${section}</td>
                <td>${s.day_of_week}</td>
                <td>${fmtTime(s.time_in)} - ${fmtTime(s.time_out)}</td>
            </tr>`;
        }).join('');
    }

    renderMiniGrid(roomRows);
    renderAvailable(roomRows);
}

function renderMiniGrid(rows) {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const hourSlots = [];
    for (let h = 6; h < 18; h++) hourSlots.push(h);
    const busy = new Set();

    rows.forEach(s => {
        const start = mins(s.time_in);
        const end = mins(s.time_out);
        for (let t = start; t < end; t += 60) busy.add(`${s.day_of_week}:${Math.floor(t / 60)}`);
    });

    let html = '<div class="mini-grid">';
    html += '<div class="mini-h"></div>';
    days.forEach(d => html += `<div class="mini-h">${d.slice(0, 3)}</div>`);
    hourSlots.forEach(h => {
        html += `<div class="mini-time">${h}:00</div>`;
        days.forEach(d => {
            const k = `${d}:${h}`;
            const cls = busy.has(k) ? 'busy' : 'free';
            html += `<div class="mini-cell ${cls}"></div>`;
        });
    });
    html += '</div>';
    document.getElementById('roomMiniGrid').innerHTML = html;
}

function renderAvailable(rows) {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const avail = [];
    days.forEach(day => {
        const list = rows.filter(r => r.day_of_week === day).sort((a, b) => mins(a.time_in) - mins(b.time_in));
        let cursor = 6 * 60;
        list.forEach(r => {
            const s = mins(r.time_in);
            if (s > cursor) avail.push(`${day}: ${formatMin(cursor)} - ${formatMin(s)}`);
            cursor = Math.max(cursor, mins(r.time_out));
        });
        const end = 18 * 60;
        if (cursor < end) avail.push(`${day}: ${formatMin(cursor)} - ${formatMin(end)}`);
    });
    document.getElementById('roomAvailable').innerHTML = avail.length
        ? avail.map(a => `<div class="slot-item">${a}</div>`).join('')
        : '<div class="slot-item">No free slots within 6:00 AM - 6:00 PM.</div>';
}

function formatMin(m) {
    const h = Math.floor(m / 60);
    const mm = m % 60;
    const d = new Date();
    d.setHours(h, mm, 0, 0);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}
