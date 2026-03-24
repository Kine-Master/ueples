// frontend/secretary/master_data/building_room/script.js

let pendingToggle = { type: null, id: null };

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

function escHtml(s) { return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

// --- BUILDINGS ---
async function loadBuildings() {
  const body = document.getElementById('buildBody');
  body.innerHTML = '<tr><td colspan="4" style="text-align:center">Loading…</td></tr>';
  try {
    const res = await fetch('../../../../backend/master_data/building/list.php');
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    const data = json.data;

    // Update filters and selects
    const filt = document.getElementById('bldgFilter');
    const sel = document.getElementById('frBldg');
    const currentFilt = filt.value;
    filt.innerHTML = '<option value="">All Buildings</option>';
    sel.innerHTML = '<option value="">Select building…</option>';
    data.forEach(b => {
      const opt = `<option value="${b.building_id}">${b.name}</option>`;
      filt.innerHTML += opt; sel.innerHTML += opt;
    });
    filt.value = currentFilt;

    if (!data.length) { body.innerHTML = '<tr><td colspan="4" style="text-align:center">No buildings found.</td></tr>'; return; }
    body.innerHTML = data.map(b => `
      <tr>
        <td><strong>${b.name}</strong></td>
        <td><span class="badge ${b.room_count ? 'badge-info' : 'badge-inactive'}">${b.room_count} rm</span></td>
        <td><span class="badge badge-${b.is_active ? 'active' : 'inactive'}">${b.is_active ? 'Act.' : 'Inact.'}</span></td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditBuild(${b.building_id}, '${escHtml(b.name)}', '${escHtml(b.description || '')}')"><i class="fa-solid fa-pen"></i></button>
            <button class="btn ${b.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" onclick="openToggleModal('building', ${b.building_id}, ${b.is_active}, '${escHtml(b.name)}')"><i class="fa-solid fa-${b.is_active ? 'ban' : 'check'}"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
  } catch (e) { body.innerHTML = '<tr><td colspan="4">Error</td></tr>'; }
}

function openBuildModal() {
  document.getElementById('buildTitle').innerHTML = '<i class="fa-solid fa-plus"></i> Add Building';
  document.getElementById('buildForm').reset();
  document.getElementById('ebId').value = '';
  openModal('buildModal');
}
function openEditBuild(id, name, desc) {
  document.getElementById('buildTitle').innerHTML = '<i class="fa-solid fa-pen"></i> Edit Building';
  document.getElementById('ebId').value = id;
  document.getElementById('fbName').value = name;
  document.getElementById('fbDesc').value = desc;
  openModal('buildModal');
}
async function saveBuild(e) {
  e.preventDefault();
  const id = document.getElementById('ebId').value;
  const isEdit = !!id;
  const url = isEdit ? '../../../../backend/master_data/building/update.php' : '../../../../backend/master_data/building/create.php';
  const body = new FormData();
  if (isEdit) body.append('building_id', id);
  body.append('name', document.getElementById('fbName').value.trim()); // Fixed building_name -> name
  body.append('description', document.getElementById('fbDesc').value.trim());
  try {
    const res = await fetch(url, { method: 'POST', body });
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    closeModal('buildModal');
    showToast(isEdit ? 'Building updated.' : 'Building created.', 'success');
    loadBuildings();
  } catch (e) { showToast(e.message, 'error'); }
}

// --- ROOMS ---
async function loadRooms() {
  const bldg = document.getElementById('bldgFilter').value;
  const body = document.getElementById('roomBody');
  body.innerHTML = '<tr><td colspan="7" style="text-align:center">Loading…</td></tr>';
  try {
    const res = await fetch(`../../../../backend/master_data/room/list.php${bldg ? '?building_id=' + bldg : ''}`);
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    const data = json.data;
    if (!data.length) { body.innerHTML = '<tr><td colspan="7" style="text-align:center">No rooms found.</td></tr>'; return; }
    body.innerHTML = data.map((r, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i + 1}</td>
        <td><strong>${r.room_name}</strong></td>
        <td>${r.building_name}</td>
        <td>${r.room_type}</td>
        <td>${r.capacity} <i class="fa-solid fa-user-group" style="color:var(--text-sub);font-size:.7rem"></i></td>
        <td><span class="badge badge-${r.is_active ? 'active' : 'inactive'}">${r.is_active ? 'Active' : 'Inactive'}</span></td>
        <td>
          <div class="flex-center gap-2">
            <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditRoom(${r.room_id}, '${escHtml(r.room_name)}', '${r.room_type}', ${r.capacity})"><i class="fa-solid fa-pen"></i></button>
            <button class="btn ${r.is_active ? 'btn-danger' : 'btn-success'} btn-sm btn-icon" onclick="openToggleModal('room', ${r.room_id}, ${r.is_active}, '${escHtml(r.room_name)}')"><i class="fa-solid fa-${r.is_active ? 'ban' : 'check'}"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
  } catch (e) { body.innerHTML = '<tr><td colspan="7">Error</td></tr>'; }
}

function openRoomModal() {
  document.getElementById('roomTitle').innerHTML = '<i class="fa-solid fa-plus"></i> Add Room';
  document.getElementById('roomForm').reset();
  document.getElementById('erId').value = '';
  document.getElementById('bldgGroup').style.display = 'block';
  document.getElementById('frBldg').setAttribute('required', 'true');
  openModal('roomModal');
}
function openEditRoom(id, name, type, capacity) {
  document.getElementById('roomTitle').innerHTML = '<i class="fa-solid fa-pen"></i> Edit Room';
  document.getElementById('erId').value = id;
  document.getElementById('bldgGroup').style.display = 'none';
  document.getElementById('frBldg').removeAttribute('required');
  document.getElementById('frName').value = name;
  document.getElementById('frType').value = type;
  document.getElementById('frCap').value = capacity;
  openModal('roomModal');
}
async function saveRoom(e) {
  e.preventDefault();
  const id = document.getElementById('erId').value;
  const isEdit = !!id;
  const url = isEdit ? '../../../../backend/master_data/room/update.php' : '../../../../backend/master_data/room/create.php';
  const body = new FormData();
  if (isEdit) body.append('room_id', id);
  else body.append('building_id', document.getElementById('frBldg').value);
  body.append('room_name', document.getElementById('frName').value.trim());
  body.append('room_type', document.getElementById('frType').value);
  body.append('capacity', document.getElementById('frCap').value);
  try {
    const res = await fetch(url, { method: 'POST', body });
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    closeModal('roomModal');
    showToast(isEdit ? 'Room updated.' : 'Room created.', 'success');
    loadRooms(); loadBuildings(); // Room count updates
  } catch (e) { showToast(e.message, 'error'); }
}

// --- TOGGLE ---
function openToggleModal(type, id, isActive, name) {
  pendingToggle = { type, id };
  document.getElementById('toggleMsg').textContent = `Are you sure you want to ${isActive ? 'deactivate' : 'activate'} ${name}?`;
  const btn = document.getElementById('toggleConfirmBtn');
  btn.className = `btn ${isActive ? 'btn-danger' : 'btn-success'}`;
  btn.textContent = isActive ? 'Deactivate' : 'Activate';
  openModal('toggleModal');
}
async function doToggle() {
  const isBldg = pendingToggle.type === 'building';
  const url = isBldg ? '../../../../backend/master_data/building/toggle.php' : '../../../../backend/master_data/room/toggle.php';
  const body = new FormData();
  body.append(isBldg ? 'building_id' : 'room_id', pendingToggle.id);
  try {
    const res = await fetch(url, { method: 'POST', body });
    const json = await res.json();
    if (json.status !== 'success') throw new Error(json.message);
    closeModal('toggleModal');
    showToast('Status updated.', 'success');
    if (isBldg) loadBuildings(); else loadRooms();
  } catch (e) { showToast(e.message, 'error'); }
}

loadBuildings().then(loadRooms);
