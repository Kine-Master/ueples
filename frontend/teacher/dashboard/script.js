// frontend/teacher/dashboard/script.js

function formatTime(time24) {
    if (!time24) return '';
    const [h, m] = time24.split(':');
    const d = new Date(); d.setHours(h); d.setMinutes(m);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function getTodayName() {
    return new Date().toLocaleDateString('en-US', { weekday: 'long' });
}

async function loadDashboard() {
    try {
        // 1. Load Stats
        const res = await fetch('../../../backend/dashboard/stats.php');
        const json = await res.json();
        if (json.status === 'success') {
            const stats = json.data.teacher_stats;
            document.getElementById('stClasses').textContent = stats.advisory_classes;
            document.getElementById('stStudents').textContent = stats.total_students;
            document.getElementById('stSchedules').textContent = stats.weekly_schedules;
        }

        // 2. Load Today's Schedule
        const day = getTodayName(); // e.g. "Monday"
        const schedRes = await fetch('../../../backend/schedule/list.php'); // API filters internally by teacher_id from session if teacher
        const schedJs = await schedRes.json();

        const todayList = document.getElementById('todayList');
        if (schedJs.status !== 'success') {
            todayList.innerHTML = '<div style="color:#ef4444;text-align:center;padding:20px">Failed to load schedule.</div>';
            return;
        }

        const todayClasses = schedJs.data.filter(s => s.day_of_week === day).sort((a, b) => a.start_time.localeCompare(b.start_time));

        if (todayClasses.length === 0) {
            todayList.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.9rem">
        <i class="fa-regular fa-calendar-xmark" style="font-size:2rem;margin-bottom:12px;opacity:0.5;display:block"></i>
        No classes scheduled for today (${day}).
      </div>`;
        } else {
            todayList.innerHTML = todayClasses.map(s => {
                const timeStr = `${formatTime(s.start_time)} - ${formatTime(s.end_time)}`;
                const subjName = s.schedule_type === 'LES' ? s.subject_name : s.coed_subject_name;
                const details = s.schedule_type === 'LES' ? s.section_name : s.coed_course_year;
                const roomName = s.room_name || 'TBA';
                const isLes = s.schedule_type === 'LES';

                return `
          <div style="background:rgba(255,255,255,.03);border-radius:var(--radius-sm);padding:12px;margin-bottom:12px;border-left:3px solid ${isLes ? '#38bdf8' : '#fbbf24'}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
              <div>
                <strong style="color:var(--text);font-size:.95rem;display:block;margin-bottom:2px">${subjName}</strong>
                <span style="font-size:.85rem;color:var(--text-sub)">${details}</span>
              </div>
              <span class="badge ${isLes ? 'badge-info' : 'badge-warning'}">${s.schedule_type}</span>
            </div>
            <div style="margin-top:8px;font-size:.8rem;color:var(--text-muted);display:flex;gap:12px">
              <span><i class="fa-regular fa-clock" style="color:var(--accent)"></i> ${timeStr}</span>
              <span><i class="fa-solid fa-door-open" style="color:var(--text-muted)"></i> ${roomName}</span>
            </div>
          </div>
        `;
            }).join('');
        }

    } catch (e) {
        document.getElementById('todayList').innerHTML = '<div style="color:#ef4444;text-align:center;padding:20px">Error connecting to server.</div>';
    }
}

document.addEventListener('DOMContentLoaded', loadDashboard);