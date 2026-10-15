<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IT Help-Desk & Material Request Portal - A. R. Gold</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f4f6f9;
      --card-bg: #ffffff;
      --text: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --it-theme: #16a34a;
      --primary: #0284c7;
    }
    body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
    .header { background: #ffffff; border-bottom: 4px solid var(--it-theme); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); flex-wrap: wrap; gap: 15px; }
    .brand-title { font-size: 20px; font-weight: 700; color: var(--it-theme); display: flex; align-items: center; gap: 8px; }
    .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
    .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.04); margin-bottom: 25px; }
    .card h3 { margin-top: 0; font-size: 16px; font-weight: 600; color: var(--text); border-bottom: 1px solid var(--border-color); padding-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .input-box { width: 100%; background: #f8fafc; border: 1px solid var(--border-color); color: var(--text); padding: 10px; border-radius: 6px; font-size: 13px; box-sizing: border-box; outline: none; margin-bottom: 12px; }
    .input-box:focus { border-color: var(--it-theme); background: #ffffff; }
    .btn-submit { background: var(--it-theme); color: #fff; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; font-size: 14px; transition: background 0.2s; margin-top: 10px; }
    .btn-submit:hover { background: #15803d; }
    .compact-form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 5px; }
    
    .primary-pill-group { display: flex; gap: 15px; margin-bottom: 12px; }
    .primary-pill { background: #f8fafc; border: 2px solid var(--border-color); padding: 10px 20px; border-radius: 50px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
    
    .sub-category-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 8px; margin-bottom: 15px; display: none; }
    .sub-category-box.active { display: block; }
    .sub-radio-group { display: flex; gap: 15px; flex-wrap: wrap; }
    .sub-radio-lbl { font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 5px; cursor: pointer; background: #ffffff; padding: 6px 12px; border-radius: 6px; border: 1px solid #d1fae5; }

    .public-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; table-layout: fixed; }
    .public-table th, .public-table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
    .public-table th { background: #f0fdf4; color: var(--it-theme); font-weight: 700; }
    
    .ticket-thumb { height: 40px; width: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; margin-right: 4px; margin-bottom: 4px; }
    .config-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; font-size: 12px; }
    .config-table th, .config-table td { border: 1px solid var(--border-color); padding: 9px; text-align: left; }
    .config-table th { background: #f8fafc; color: var(--text); font-weight: 600; }
    
    .pill-btn-outline { background: #ffffff; color: var(--primary); border: 2px solid var(--primary); padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-decoration: none; transition: all 0.2s; }
    .pill-btn-outline:hover { background: var(--primary); color: #ffffff; }

    .pill-btn-dark { background: #0f172a; color: #ffffff; border: 2px solid #0f172a; padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-decoration: none; transition: all 0.2s; }
    .pill-btn-dark:hover { background: #1e293b; }
    
    .filter-bar { background: #f8fafc; padding: 12px 15px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 15px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
    .filter-checkbox { font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 5px; background: #fff; padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border-color); cursor: pointer; }

    .filter-pill-main { background: #ffffff; border: 2px solid var(--border-color); padding: 8px 18px; border-radius: 50px; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
    .filter-pill-main:hover { border-color: var(--it-theme); background: #f8fafc; }
    .filter-popup-box { position: absolute; background: #ffffff; border: 1px solid var(--border-color); box-shadow: 0 8px 20px rgba(0,0,0,0.12); padding: 12px; border-radius: 8px; display: none; z-index: 100; margin-top: 6px; min-width: 220px; }
    .filter-popup-box.active { display: block; }
  </style>
</head>
<body>

  <div class="header">
    <div class="brand-title">🛠️ IT Help-Desk Portal</div>
    <div style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
      <div style="font-size: 13px; color: var(--text); font-weight: 500;">
        🌤️ Navi Mumbai: <b style="color: var(--primary);">31°C</b> &nbsp;&nbsp;|&nbsp;&nbsp; <span id="live-clock" style="font-weight: 600; color: var(--it-theme);"></span>
      </div>
      <button onclick="openMaterialModal()" class="pill-btn-outline">📦 IT Purchase Request Form</button>
      <a href="/it=backend.php" class="pill-btn-dark">⚙️ Admin Panel</a>
    </div>
  </div>

  <div class="container">
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'it_submitted'): ?>
    <div id="success-alert" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; text-align: center; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: opacity 0.5s ease;">
      ✅ Your ticket has been submitted successfully!
    </div>
    <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'material_submitted'): ?>
    <div id="success-alert" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; text-align: center; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: opacity 0.5s ease;">
      ✅ Your material request has been submitted successfully!
    </div>
    <?php endif; ?>

    <!-- Ticket Submission Form -->
    <div class="card" style="border-top: 4px solid var(--it-theme);">
      <h3 style="color:var(--it-theme);">📝 Submit New IT Ticket / Issue</h3>
      <form action="/it=backend.php?action=create_ticket" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="redirect" value="/it/?msg=it_submitted">
        
        <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Select Primary Category:</label>
        <div class="primary-pill-group">
          <label class="primary-pill">
            <input type="radio" name="primary_cat" value="Network/Hardware" checked onclick="toggleSubCategories('hardware')"> 🖥️ Network / Hardware
          </label>
        </div>

          <div id="sub-hardware" class="sub-category-box" style="display: block !important;">
          <label style="font-size: 11px; font-weight: 600; color: #065f46; margin-bottom: 6px; display: block;">Select Sub-Category:</label>
          <div class="sub-radio-group">
            <label class="sub-radio-lbl"><input type="radio" name="sub_cat" value="network"> 🌐 Network</label>
            <label class="sub-radio-lbl"><input type="radio" name="sub_cat" value="computer"> 💻 Computer</label>
            <label class="sub-radio-lbl"><input type="radio" name="sub_cat" value="printer"> 🖨️ Printer</label>
            <label class="sub-radio-lbl"><input type="radio" name="sub_cat" value="server"> 🗄️ Server</label>
            <label class="sub-radio-lbl"><input type="radio" name="sub_cat" value="other"> 📌 Other</label>
          </div>
        </div>

        <div class="compact-form-row">
          <input type="text" name="username" class="input-box" placeholder="आपले नाव  (Your Name)" required>
          <input type="text" name="department" class="input-box" placeholder="विभाग  (Department)" required>
          <input type="text" name="factory" class="input-box" placeholder="फॅक्टरी (Factory eg: ARG/ARF/ARC/ARB)" required>
        </div>
        
        <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">📷 Attach Screenshots (up to 3):</label>
        <div class="compact-form-row">
          <input type="file" name="photos[]" class="input-box" accept="image/*" style="background:#fff;">
          <input type="file" name="photos[]" class="input-box" accept="image/*" style="background:#fff;">
          <input type="file" name="photos[]" class="input-box" accept="image/*" style="background:#fff;">
        </div>

        <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block;">📝 Describe your issue in Detail:</label>
        <textarea name="issue" class="input-box" style="height: 75px; margin-bottom:0;" placeholder="तक्रारीचे सविस्तर वर्णन करा..." required></textarea>
        
        <button type="submit" class="btn-submit">📤 Submit IT Ticket</button>
      </form>
    </div>

    <!-- Live Status & Material Request Status Section with Filters -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 15px;">
        <h3 style="margin:0; border:none; padding:0;">🌐 Live IT Tickets & Material Request Status</h3>
      </div>

      <!-- Filters Bar -->
      <div class="filter-bar">
        <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">🔍 Filter View:</span>
        <label class="filter-checkbox"><input type="radio" name="view_mode" value="helpdesk" onclick="switchView('helpdesk')" checked> Live IT Help-Desk With Status</label>
        <label class="filter-checkbox"><input type="radio" name="view_mode" value="material" onclick="switchView('material')"> List of All Material Request With Status</label>
      </div>

      <!-- Table 1: Live IT Help-Desk Status with 2 Main Filter Pills & Popups -->
      <div id="helpdesk-view">
        <div id="subcat-filter-bar" style="margin-bottom: 12px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border-color); position: relative;">
          <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">🔍 Filter Sub-Categories:</span>
          
          <div style="position: relative;">
            <button type="button" class="filter-pill-main" onclick="togglePopup('popup-hardware', event)">
              🖥️ Network / Hardware ▾
            </button>
            <div id="popup-hardware" class="filter-popup-box">
              <div style="font-size:11px; font-weight:bold; color:#0284c7; margin-bottom:6px; border-bottom:1px solid #e2e8f0; padding-bottom:4px;">Select Sub-Categories</div>
              <label class="filter-checkbox" style="margin-bottom:6px;"><input type="checkbox" class="subcat-chk" value="network" onchange="filterTableTickets()"> Network</label>
              <label class="filter-checkbox" style="margin-bottom:6px;"><input type="checkbox" class="subcat-chk" value="computer" onchange="filterTableTickets()"> Computer</label>
              <label class="filter-checkbox" style="margin-bottom:6px;"><input type="checkbox" class="subcat-chk" value="printer" onchange="filterTableTickets()"> Printer</label>
              <label class="filter-checkbox" style="margin-bottom:6px;"><input type="checkbox" class="subcat-chk" value="server" onchange="filterTableTickets()"> Server</label>
              <label class="filter-checkbox" style="margin-bottom:6px;"><input type="checkbox" class="subcat-chk" value="other" onchange="filterTableTickets()"> Other</label>
            </div>
          </div>

          <button onclick="clearTableFilters()" style="background:none; border:none; color:#ef4444; font-size:12px; font-weight:600; cursor:pointer; margin-left:auto;">Clear All</button>
        </div>

        <div style="overflow-x: auto; max-height: 500px; overflow-y: auto;">
          <table class="public-table">
            <colgroup>
              <col style="width: 10%;">
              <col style="width: 12%;">
              <col style="width: 12%;">
              <col style="width: 14%;">
              <col style="width: 24%;">
              <col style="width: 9%;">
              <col style="width: 10%;">
              <col style="width: 9%;">
              <col style="width: 12%;">
            </colgroup>
            <thead>
              <tr>
                <th>Token</th>
                <th>Created Date & Time</th>
                <th>User & Dept</th>
                <th>Category / Sub</th>
                <th>Issue Description</th>
                <th>Photos</th>
                <th>Assigned</th>
                <th>Status</th>
                <th>Note & ETA</th>
              </tr>
            </thead>
            <tbody id="live-it-tickets">
              <tr><td colspan="8" style="text-align:center; padding: 25px; color: var(--text-muted);">Loading live IT tickets...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Table 2: List of All Material Request Status (Open to All View) -->
      <div id="material-view" style="display: none; overflow-x: auto; max-height: 500px; overflow-y: auto;">
        <table class="public-table">
          <thead>
            <tr>
              <th>ID & Date</th>
              <th>Requested By & Dept</th>
              <th>Items Summary</th>
              <th>Price & Attach</th>
              <th>4-Level Approval Status</th>
            </tr>
          </thead>
          <tbody id="userStatusTableContainer">
            <tr><td colspan="5" style="text-align:center; padding: 25px; color: var(--text-muted);">Loading material requests...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- MODAL: MATERIAL REQUEST FORM -->
  <div id="materialModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center;">
    <div style="background:#ffffff; border:1px solid var(--border-color); padding:25px; border-radius:12px; width:800px; max-height:90vh; overflow-y:auto; position:relative; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
      <button onclick="closeMaterialModal()" style="position:absolute; top:15px; right:15px; background:#ef4444; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold;">✕ Close</button>
      <h3 style="font-size: 16px; font-weight: 700; color:var(--primary); border-bottom:1px solid var(--border-color); padding-bottom:10px; margin-top:0;">📦 IT New Purchase & Material Request Form</h3>
      
      <form action="/it=backend.php?action=create_material_request" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="redirect" value="/it/?msg=material_submitted">
        <div class="compact-form-row" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 12px;">
          <div><label style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Employee Name:</label><input type="text" name="employee_name" class="input-box" placeholder="Your Name" required></div>
          <div><label style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Department:</label><input type="text" name="department" class="input-box" placeholder="Department" required></div>
          <div><label style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Factory / Location:</label><input type="text" name="factory" class="input-box" placeholder="Location" required></div>
        </div>

        <table class="config-table">
          <thead><tr><th style="width: 30%;">Item Name</th><th style="width: 45%;">Description</th><th style="width: 25%;">Quantity</th></tr></thead>
          <tbody>
            <?php 
            $items = ['Desktop PC', 'Laptop', 'Printer', 'RAM', 'SSD / HDD (Storage)', 'Monitor', 'Keyboard', 'Mouse', 'OS & Software', 'Other Peripheral'];
            foreach($items as $idx => $item): 
            ?>
            <tr>
              <td><b><?php echo $item; ?></b><input type="hidden" name="item_name[<?php echo $idx; ?>]" value="<?php echo $item; ?>"></td>
              <td><input type="text" name="item_desc[<?php echo $idx; ?>]" class="input-box" placeholder="Optional..." style="margin-bottom:0;"></td>
              <td><input type="number" name="item_qty[<?php echo $idx; ?>]" class="input-box" placeholder="Qty" min="0" style="margin-bottom:0;"></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
          <div><label style="font-size: 12px; font-weight: 600;">Estimated Total Price (₹):</label><input type="text" name="total_price" class="input-box" placeholder="e.g. 25000"></div>
          <div><label style="font-size: 12px; font-weight: 600;">Attach Bill:</label><input type="file" name="attachment" class="input-box" accept="image/*,.pdf" style="background:#fff; margin-bottom:0;"></div>
        </div>
        <button type="submit" class="btn-submit" style="background:var(--primary);">🚀 Submit Material Request</button>
      </form>
    </div>
  </div>

  <script>
    setTimeout(() => {
      const alertBox = document.getElementById('success-alert');
      if (alertBox) {
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.style.display = 'none', 500);
      }
      if (window.history.replaceState) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
      }
    }, 5000);

    let globalTickets = [];

    function togglePopup(popupId, event) {
      event.stopPropagation();
      document.querySelectorAll('.filter-popup-box').forEach(p => {
        if(p.id !== popupId) p.classList.remove('active');
      });
      document.getElementById(popupId).classList.toggle('active');
    }

    window.addEventListener('click', function() {
      document.querySelectorAll('.filter-popup-box').forEach(p => p.classList.remove('active'));
    });

    function toggleSubCategories(type) {
      if (type === 'erp') {
        document.getElementById('sub-erp').classList.add('active');
        document.getElementById('sub-hardware').classList.remove('active');
      } else {
        document.getElementById('sub-hardware').classList.add('active');
        document.getElementById('sub-erp').classList.remove('active');
      }
    }

    function switchView(mode) {
      if (mode === 'helpdesk') {
        document.getElementById('helpdesk-view').style.display = 'block';
        document.getElementById('material-view').style.display = 'none';
      } else {
        document.getElementById('helpdesk-view').style.display = 'none';
        document.getElementById('material-view').style.display = 'block';
        fetchMaterialRequests();
      }
    }

    function updateClock() {
      const now = new Date();
      let hours = now.getHours();
      let minutes = now.getMinutes();
      let seconds = now.getSeconds();
      let ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12; hours = hours ? hours : 12;
      minutes = minutes < 10 ? '0' + minutes : minutes;
      seconds = seconds < 10 ? '0' + seconds : seconds;
      document.getElementById('live-clock').innerText = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    }
    setInterval(updateClock, 1000);
    updateClock();

    function openMaterialModal() { document.getElementById('materialModal').style.display = 'flex'; }
    function closeMaterialModal() { document.getElementById('materialModal').style.display = 'none'; }

    function fetchMaterialRequests() {
      fetch('/it=backend.php?action=get_material_requests')
        .then(res => res.json())
        .then(data => {
          let html = "";
          if (!data.requests || data.requests.length === 0) {
            html += `<tr><td colspan="5" style="text-align:center;">No material requests found.</td></tr>`;
          } else {
            data.requests.forEach(r => {
              let itemsText = "";
              if(r.items) { r.items.forEach(i => { if(i.qty>0) itemsText += `• ${i.name}: Qty ${i.qty}<br>`; }); }
              let attachLink = r.attachment ? `<br><a href="/${r.attachment}" target="_blank" style="font-size:11px; color:#0284c7;">📎 Attachment</a>` : '';
              
              let d_head = r.approvals?.department_head || {};
              let it_d = r.approvals?.it_dept || {};
              let it_h = r.approvals?.it_hod || {};
              let dir = r.approvals?.director || {};

              html += `<tr>
                <td><b>#${r.id}</b><br><small>${r.date}</small></td>
                <td><b>${r.employee_name}</b><br><small>${r.department} (${r.factory})</small></td>
                <td><small>${itemsText}</small></td>
                <td><b style="color:#16a34a;">${r.total_price}</b>${attachLink}</td>
                <td>
                  <div style="font-size:11px; line-height:1.4;">
                    <div>1. Dept Head: <b>${d_head.status || 'Pending'}</b> ${d_head.by ? '('+d_head.by+')' : ''}</div>
                    <div>2. IT Dept (Shri): <b>${it_d.status || 'Pending'}</b> ${it_d.by ? '('+it_d.by+')' : ''}</div>
                    <div>3. IT HOD: <b>${it_h.status || 'Pending'}</b> ${it_h.by ? '('+it_h.by+')' : ''}</div>
                    <div>4. Director: <b>${dir.status || 'Pending'}</b> ${dir.by ? '('+dir.by+')' : ''}</div>
                  </div>
                </td>
              </tr>`;
            });
          }
          document.getElementById('userStatusTableContainer').innerHTML = html;
        });
    }

    function renderTickets(ticketsToRender) {
      const tbody = document.getElementById('live-it-tickets');
      if (!tbody) return;
      if (ticketsToRender.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 25px; color: var(--text-muted);">No matching tickets found.</td></tr>`;
        return;
      }
      let rowsHtml = "";
      ticketsToRender.forEach(t => {
        let photosHtml = "";
        if (t.photos && t.photos.length > 0) {
          t.photos.forEach(p => { photosHtml += `<img src="/${p}" class="ticket-thumb" title="Image">`; });
        } else { photosHtml = `<span style="font-size:11px; color:#94a3b8;">No Images</span>`; }
        const statusColor = t.status === 'Resolved' ? '#16a34a' : (t.status === 'In Progress' ? '#0284c7' : '#d97706');
        rowsHtml += `<tr>
          <td><b style="color:var(--it-theme); font-size:12px;">${t.id}</b></td>
          <td><b>${t.user}</b><br><small>${t.department}</small><br><span style="background:#f0fdf4; color:var(--it-theme); padding:2px 4px; border-radius:4px; font-size:10px;">${t.factory || 'Navi Mumbai'}</span></td>
          <td><b>${t.primary_cat || 'ERP'}</b><br><span style="color:#0284c7; font-size:11px;">↳ ${t.sub_cat || '-'}</span></td>
          <td>${t.issue}</td>
          <td>${photosHtml}</td>
          <td><b style="color:#0284c7;">${t.assigned_to || 'Unassigned'}</b></td>
          <td><b style="color:${statusColor};">${t.status}</b></td>
          <td>${t.note ? '📝 ' + t.note : '-'} ${t.eta ? '<br>⏱️ ETA: ' + t.eta : ''}</td>
        </tr>`;
      });
      tbody.innerHTML = rowsHtml;
    }

    function filterTableTickets() {
      const checkboxes = document.querySelectorAll('.subcat-chk:checked');
      if (checkboxes.length === 0) {
        renderTickets(globalTickets);
        return;
      }
      let selectedValues = Array.from(checkboxes).map(cb => cb.value.toLowerCase());
      let filtered = globalTickets.filter(t => {
        let sc = (t.sub_cat || '').toLowerCase();
        return selectedValues.includes(sc);
      });
      renderTickets(filtered);
    }

    function clearTableFilters() {
      document.querySelectorAll('.subcat-chk').forEach(cb => cb.checked = false);
      renderTickets(globalTickets);
    }

    function updateQueueStatus() {
      fetch('/it=backend.php?action=get_tickets')
        .then(res => res.json())
        .then(data => {
          if (data.all_tickets) {
            globalTickets = data.all_tickets;
            filterTableTickets();
          }
        }).catch(err => console.log('Fetch error:', err));
    }
    setInterval(updateQueueStatus, 5000);
    updateQueueStatus();
  </script>
</body>
</html>
