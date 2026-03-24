// frontend/principal/students/script.js

let activeSectionId = null;
let sectionsMap = new Map();

async function loadClasses() {
    try {
        // Principal can see all class sections. Use secretary API for class_section/list
        const res = await fetch('../../../backend/master_data/class_section/list.php');
        const json = await res.json();
        if (json.status !== 'success') throw new Error();

        const classes = json.data;

        const list = document.getElementById('classList');
        if (!classes.length) {
            list.innerHTML = `<div style="text-align:center;color:var(--text-muted);padding:20px;font-size:.85rem">No active classes found.</div>`;
            return;
        }

        list.innerHTML = classes.map(c => {
            sectionsMap.set(c.class_section_id, c);
            return `
      <div class="class-item" id="cls-${c.class_section_id}" onclick="selectClass(${c.class_section_id})">
        <strong style="display:block;color:var(--text);font-size:.95rem">${c.section_name}</strong>
        <span style="font-size:.8rem;color:var(--text-sub)">${c.grade_name}</span>
      </div>`;
        }).join('');

    } catch (e) { document.getElementById('classList').innerHTML = 'Error loading classes.'; }
}

function selectClass(id) {
    document.querySelectorAll('.class-item').forEach(el => el.classList.remove('active'));
    document.getElementById(`cls-${id}`).classList.add('active');

    activeSectionId = id;
    const c = sectionsMap.get(id);

    document.getElementById('panelHeader').innerHTML = `
    <div>
       <h3 style="margin:0;font-size:1.2rem;color:var(--text)"><i class="fa-solid fa-users" style="color:var(--accent);margin-right:8px"></i> ${c.section_name} (${c.grade_name})</h3>
       <p style="margin:4px 0 0 0;font-size:.85rem;color:var(--text-sub)" id="capText">Loading capacity...</p>
    </div>
  `;

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('studentToolbar').style.display = 'flex';
    document.getElementById('studentTableWrap').style.display = 'block';

    loadStudents();
}

async function loadStudents() {
    if (!activeSectionId) return;
    const q = document.getElementById('searchQ').value;
    const tbody = document.getElementById('studentBody');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>';

    try {
        // Principal has read access to student list
        const res = await fetch(`../../../backend/student/list.php?class_section_id=${activeSectionId}&search=${encodeURIComponent(q)}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message);

        if (json.capacity_info) {
            const en = json.capacity_info.enrolled || 0;
            const cap = json.capacity_info.capacity || 0;
            let cText = `Enrolled: <strong style="color:var(--text)">${en}</strong> / ${cap} max`;
            if (en > cap && cap > 0) cText += ` <span style="color:#ef4444;margin-left:8px"><i class="fa-solid fa-triangle-exclamation"></i> Over capacity</span>`;
            document.getElementById('capText').innerHTML = cText;
        } else {
            document.getElementById('capText').innerHTML = `Enrolled: ${json.data.length}`;
        }

        if (!json.data.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-sub)">No students found.</td></tr>';
            return;
        }

        tbody.innerHTML = json.data.map(st => {
            const name = `${st.last_name}, ${st.first_name} ${st.middle_name || ''} ${st.extension_name || ''}`.trim();
            const stBadge = st.is_active ? '<span class="badge badge-active">Active</span>' : '<span class="badge badge-inactive">Inactive</span>';
            return `
      <tr>
        <td style="font-family:monospace;color:var(--text-sub)">${st.lrn}</td>
        <td><strong>${name}</strong></td>
        <td>${st.gender}</td>
        <td>${stBadge}</td>
      </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="4" style="color:#ef4444">${e.message}</td></tr>`;
    }
}

document.addEventListener('DOMContentLoaded', loadClasses);
