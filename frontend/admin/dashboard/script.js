// frontend/admin/dashboard/script.js

// ── Toast Helper ──────────────────────────────────────────────
function showToast(msg, type = 'info') {
  const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

// ── Date Display ─────────────────────────────────────────────
function renderDate() {
  const d = new Date();
  document.getElementById('currentDate').innerHTML =
    `<div style="text-align:right;font-size:.8rem;color:var(--text-muted)">
       ${d.toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'})}
     </div>`;
}

// ── Load Dashboard Stats ─────────────────────────────────────
async function loadStats() {
  try {
    const res  = await fetch('../../../backend/dashboard/stats.php');
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    const d = json.data;

    document.getElementById('totalUsers').textContent = d.total_users ?? '—';
    document.getElementById('activeSY').textContent   = d.active_sy?.label ?? 'None';

    (d.role_counts || []).forEach(r => {
      const el = document.getElementById('count' + capitalize(r.role_name));
      if (el) el.textContent = r.total;
    });

    // Audit logs
    const body = document.getElementById('auditBody');
    if (!d.recent_logs?.length) {
      body.innerHTML = '<tr class="no-data"><td colspan="5">No recent activity.</td></tr>'; return;
    }
    body.innerHTML = d.recent_logs.map(l => `
      <tr>
        <td><strong>${l.first_name ?? 'System'} ${l.last_name ?? ''}</strong></td>
        <td>${l.role_name ? `<span class="badge badge-${l.role_name}">${l.role_name}</span>` : '—'}</td>
        <td><code style="font-size:.78rem;color:var(--info)">${l.user_action}</code></td>
        <td style="color:var(--text-sub);font-size:.82rem">${l.details ?? '—'}</td>
        <td style="color:var(--text-muted);font-size:.8rem">${formatDate(l.timestamp)}</td>
      </tr>`).join('');
  } catch (e) {
    showToast('Failed to load dashboard: ' + e.message, 'error');
  }
}

function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function formatDate(dt) {
  if (!dt) return '—';
  return new Date(dt).toLocaleString('en-PH', { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
}

// ── Init ─────────────────────────────────────────────────────
renderDate();
loadStats();
