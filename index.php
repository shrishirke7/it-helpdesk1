function renderTickets(ticketsToRender) {
  const tbody = document.getElementById('live-it-tickets');
  if (!tbody) return;
  if (ticketsToRender.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 25px; color: var(--text-muted);">No matching tickets found.</td></tr>`;
    return;
  }
  let rowsHtml = "";
  ticketsToRender.forEach(t => {
    let photosHtml = "";
    if (t.photos && t.photos.length > 0) {
      t.photos.forEach(p => { photosHtml += `<img src="/${p}" class="ticket-thumb" title="Image">`; });
    } else { photosHtml = `<span style="font-size:11px; color:#94a3b8;">No Images</span>`; }
    
    const statusColor = t.status === 'Resolved' ? '#16a34a' : (t.status === 'In Progress' ? '#0284c7' : '#d97706');

    // 24-Hour Clock Formatting
    let formattedDateTime = '-';
    if (t.created_at) {
      formattedDateTime = `<b>${t.created_at.split(' ')[0]}</b><br><small style="color:#64748b;">🕒 ${t.created_at.split(' ')[1] || ''} hrs</small>`;
    } else if (t.timestamp) {
      let d = new Date(t.timestamp * 1000);
      let day = String(d.getDate()).padStart(2, '0');
      let month = String(d.getMonth() + 1).padStart(2, '0');
      let year = d.getFullYear();
      let hours = String(d.getHours()).padStart(2, '0');
      let minutes = String(d.getMinutes()).padStart(2, '0');
      formattedDateTime = `<b>${day}-${month}-${year}</b><br><small style="color:#64748b;">🕒 ${hours}:${minutes} hrs</small>`;
    }

    rowsHtml += `<tr>
      <td><b style="color:var(--it-theme); font-size:12px;">${t.id}</b></td>
      <td>${formattedDateTime}</td>
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
