<?php
// PHP Backend Logic
date_default_timezone_set('Asia/Kolkata');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Helpdesk Portal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f9; }
        .ticket-thumb { width: 50px; height: 50px; object-fit: cover; margin-right: 5px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>

    <h2>IT Helpdesk Tickets</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Created At</th>
                <th>User</th>
                <th>Dept</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Issue</th>
                <th>Status</th>
                <th>ETA</th>
            </tr>
        </thead>
        <tbody id="live-it-tickets">
            <!-- Tickets render here -->
        </tbody>
    </table>

    <script>
    function renderTickets(ticketsToRender) { 
        const tbody = document.getElementById('live-it-tickets'); 
        if (!tbody) return; 
        
        if (ticketsToRender.length === 0) { 
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 25px; color: #888;">No matching tickets found.</td></tr>`; 
            return; 
        } 
        
        let rowsHtml = ""; 
        ticketsToRender.forEach(t => { 
            let photosHtml = ""; 
            if (t.photos && t.photos.length > 0) { 
                t.photos.forEach(p => { photosHtml += `<img src="/${p}" class="ticket-thumb" title="Image">`; }); 
            } else { 
                photosHtml = `<span style="font-size:11px; color:#94a3b8;">No Images</span>`; 
            } 

            const statusColor = t.status === 'Resolved' ? '#16a34a' : (t.status === 'In Progress' ? '#0284c7' : '#d97706'); 

            // 24-Hour Clock Formatting
            let formattedDateTime = '-'; 
            if (t.created_at) { 
                formattedDateTime = `<b>${t.created_at.split(' ')[0]}</b><br><small style="color:#64748b;">⏰ ${t.created_at.split(' ')[1] || ''} hrs</small>`; 
            } else if (t.timestamp) { 
                let d = new Date(t.timestamp * 1000); 
                let day = String(d.getDate()).padStart(2, '0'); 
                let month = String(d.getMonth() + 1).padStart(2, '0'); 
                let year = d.getFullYear(); 
                let hours = String(d.getHours()).padStart(2, '0'); 
                let minutes = String(d.getMinutes()).padStart(2, '0'); 
                formattedDateTime = `${day}-${month}-${year} ⏰ ${hours}:${minutes} hrs`; 
            } 

            rowsHtml += `<tr>
                <td>${t.id}</td>
                <td>${formattedDateTime}</td>
                <td>${t.user}</td>
                <td>${t.department}</td>
                <td>${t.primary_cat || 'ERP'}</td>
                <td><b>${t.sub_cat || '-'}</b></td>
                <td>${t.issue} ${photosHtml}</td>
                <td><span style="color: ${statusColor}; font-weight: bold;">${t.status}</span></td>
                <td>⏰ ${t.eta || '-'}</td>
            </tr>`; 
        }); 
        tbody.innerHTML = rowsHtml; 
    }

    // Dummy trigger test for UI verification
    document.addEventListener("DOMContentLoaded", function() {
        renderTickets([]);
    });
    </script>
</body>
</html>
