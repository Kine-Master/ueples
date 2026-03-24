// frontend/admin/audit_logs/script.js

function showToast(msg, type = 'info') {
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fa-solid ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 4500);
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open')); });

// ── Load Action Types ─────────────────────────────────────────
async function loadActions() {
    try {
        const res = await fetch('../../../backend/audit_logs/get_actions.php');
        const json = await res.json();
        if (json.status !== 'success') return;
        const sel = document.getElementById('actionFilter');
        json.data.forEach(a => { const o = document.createElement('option'); o.value = a; o.textContent = a.replace(/_/g, ' '); sel.appendChild(o); });
    } catch (e) { }
}

// ── Load Logs ─────────────────────────────────────────────────
async function loadLogs() {
    const params = new URLSearchParams({
        search: document.getElementById('searchInput').value,
        action: document.getElementById('actionFilter').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
    });
    const body = document.getElementById('logBody');
    body.innerHTML = '<tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr>';
    try {
        const res = await fetch('../../../backend/audit_logs/list.php?' + params);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        const logs = json.data;
        document.getElementById('logCount').textContent = `${logs.length} record(s) found`;
        if (!logs.length) { body.innerHTML = '<tr class="no-data"><td colspan="7">No log entries found for the selected filters.</td></tr>'; return; }
        body.innerHTML = logs.map((l, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><strong>${l.first_name ?? 'System'} ${l.last_name ?? ''}</strong><div style="font-size:.78rem;color:var(--text-muted)">${l.username ?? ''}</div></td>
        <td>${l.role_name ? `<span class="badge badge-${l.role_name}">${l.role_name}</span>` : '—'}</td>
        <td><code>${l.user_action}</code></td>
        <td style="font-size:.82rem;color:var(--text-sub);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${escHtml(l.details || '')}">${l.details || '—'}</td>
        <td style="font-size:.8rem;color:var(--text-muted)">${l.ip_address || '—'}</td>
        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">${formatDate(l.timestamp)}</td>
      </tr>`).join('');
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
        body.innerHTML = '<tr class="no-data"><td colspan="7">Failed to load logs.</td></tr>';
    }
}

function openReport() {
    const params = new URLSearchParams({
        search: document.getElementById('searchInput').value,
        action: document.getElementById('actionFilter').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value
    });
    window.open(`report.php?${params.toString()}`, '_blank');
}

// ── Cleanup ───────────────────────────────────────────────────
async function openCleanupModal() {
    try {
        const res = await fetch('../../../backend/audit_logs/settings.php');
        const json = await res.json();
        if (json.status === 'success') document.getElementById('deletionThreshold').value = json.data.deletion_threshold;
    } catch (e) { }
    openModal('cleanupModal');
}
async function runCleanup() {
    const months = parseInt(document.getElementById('deletionThreshold').value);
    if (!months || months < 1) { showToast('Enter a valid number of months.', 'warning'); return; }
    try {
        const body = new FormData(); body.append('deletion_threshold', months);
        await fetch('../../../backend/audit_logs/settings.php', { method: 'POST', body });
        const res = await fetch('../../../backend/audit_logs/run_clean_up.php', { method: 'POST', body: new FormData() });
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);
        closeModal('cleanupModal');
        showToast(`Cleanup complete — ${json.deleted} records deleted (before ${json.cutoff.split(' ')[0]}).`, 'success');
        loadLogs();
    } catch (e) { showToast('Error: ' + e.message, 'error'); }
}

function formatDate(dt) { return dt ? new Date(dt).toLocaleString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'; }
function escHtml(s) { return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

// ── Init ──────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('keyup', e => { if (e.key === 'Enter') loadLogs(); });
loadActions();
loadLogs();
