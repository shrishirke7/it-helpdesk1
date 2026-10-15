<?php
date_default_timezone_set('Asia/Kolkata');
session_start();

$tickets_file = 'it_tickets.json';
$material_file = 'material_requests.json';
$upload_dir = 'it_uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$action = $_GET['action'] ?? '';

// --- EXPORT TO CSV ACTIONS ---
if ($action === 'export_tickets') {
    if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'it_ticketing')) {
        header("Location: it=backend.php");
        exit();
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=it_tickets_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Token', 'User', 'Department', 'Factory', 'Primary Category', 'Sub Category', 'Issue', 'Assigned To', 'Status', 'ETA', 'Note']);
    $tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
    foreach ($tickets as $t) {
        fputcsv($output, [
            $t['id'] ?? '',
            $t['user'] ?? '',
            $t['department'] ?? '',
            $t['factory'] ?? '',
            $t['primary_cat'] ?? '',
            $t['sub_cat'] ?? '',
            $t['issue'] ?? '',
            $t['assigned_to'] ?? '',
            $t['status'] ?? '',
            $t['eta'] ?? '',
            $t['note'] ?? ''
        ]);
    }
    fclose($output);
    exit();
}

if ($action === 'export_material') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['role'] === 'it_ticketing') {
        header("Location: it=backend.php");
        exit();
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=material_requests_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Date', 'Employee Name', 'Department', 'Factory', 'Items Summary', 'Total Price', 'Dept Head Status', 'IT Dept Status', 'IT HOD Status', 'Director Status']);
    $requests = file_exists($material_file) ? json_decode(file_get_contents($material_file), true) : [];
    foreach ($requests as $r) {
        $items_summary = '';
        if (isset($r['items'])) {
            foreach ($r['items'] as $i) {
                if ($i['qty'] > 0) {
                    $items_summary .= $i['name'] . ' (Qty: ' . $i['qty'] . '); ';
                }
            }
        }
        fputcsv($output, [
            $r['id'] ?? '',
            $r['date'] ?? '',
            $r['employee_name'] ?? '',
            $r['department'] ?? '',
            $r['factory'] ?? '',
            $items_summary,
            $r['total_price'] ?? '',
            $r['approvals']['department_head']['status'] ?? '',
            $r['approvals']['it_dept']['status'] ?? '',
            $r['approvals']['it_hod']['status'] ?? '',
            $r['approvals']['director']['status'] ?? ''
        ]);
    }
    fclose($output);
    exit();
}

// --- PUBLIC ACTIONS (No Login Required for Ticket & Material Request Submission) ---
if ($action === 'create_ticket') {
    $tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
    if (!is_array($tickets)) { $tickets = []; }
    
    $today_str = date('d-m-Y'); $today_count = 0;
    foreach ($tickets as $t) {
        if (isset($t['timestamp']) && date('d-m-Y', $t['timestamp']) === $today_str) { $today_count++; }
        elseif (isset($t['id']) && strpos($t['id'], $today_str) !== false) { $today_count++; }
    }
    $id = sprintf("IT%02d/%s", $today_count + 1, $today_str);

    $uploaded_files = [];
    if (!empty($_FILES['photos']['name'])) {
        foreach ($_FILES['photos']['name'] as $key => $name) {
            if (!empty($name) && $_FILES['photos']['error'][$key] == 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = "it_" . time() . "_" . $key . "." . $ext;
                if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $upload_dir . $filename)) {
                    $uploaded_files[] = $upload_dir . $filename;
                }
            }
        }
    }

    $new_ticket = [
        'id' => $id,
        'user' => trim($_POST['username'] ?? 'Unknown'),
        'department' => trim($_POST['department'] ?? ''),
        'factory' => trim($_POST['factory'] ?? ''),
        'primary_cat' => trim($_POST['primary_cat'] ?? 'ERP/Software/Manufacturing'),
        'sub_cat' => trim($_POST['sub_cat'] ?? 'Entry Issue'),
        'issue' => trim($_POST['issue'] ?? ''),
        'photos' => $uploaded_files,
        'status' => 'Open',
        'assigned_to' => 'Unassigned',
        'eta' => '',
        'note' => '',
        'timestamp' => time(),
        'resolved_time' => null
    ];
    array_unshift($tickets, $new_ticket);
    file_put_contents($tickets_file, json_encode($tickets, JSON_PRETTY_PRINT));
    header("Location: " . ($_POST['redirect'] ?? '/it/'));
    exit();
}

if ($action === 'create_material_request') {
    $requests = file_exists($material_file) ? json_decode(file_get_contents($material_file), true) : [];
    if (!is_array($requests)) { $requests = []; }

    $items = [];
    if (isset($_POST['item_name'])) {
        foreach ($_POST['item_name'] as $idx => $name) {
            $qty = intval($_POST['item_qty'][$idx] ?? 0);
            if ($qty > 0) {
                $items[] = ['name' => $name, 'desc' => trim($_POST['item_desc'][$idx] ?? ''), 'qty' => $qty];
            }
        }
    }

    $attachment = '';
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $filename = "mat_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $filename)) { $attachment = $upload_dir . $filename; }
    }

    // Generate unique ID based on max existing ID + 1 to avoid conflicts
    $max_id = 0;
    foreach ($requests as $r) {
        if (isset($r['id']) && intval($r['id']) > $max_id) {
            $max_id = intval($r['id']);
        }
    }

    $new_req = [
        'id' => $max_id + 1,
        'employee_name' => trim($_POST['employee_name'] ?? 'User'),
        'department' => trim($_POST['department'] ?? ''),
        'factory' => trim($_POST['factory'] ?? ''),
        'items' => $items,
        'total_price' => trim($_POST['total_price'] ?? 'Pending Price Update'),
        'attachment' => $attachment,
        'date' => date('d M Y, h:i A'),
        'approvals' => [
            'department_head' => ['status' => 'Pending', 'by' => '', 'remark' => ''],
            'it_dept'         => ['status' => 'Pending', 'by' => '', 'remark' => ''],
            'it_hod'          => ['status' => 'Pending', 'by' => '', 'remark' => ''],
            'director'        => ['status' => 'Pending', 'by' => '', 'remark' => '']
        ]
    ];
    $requests[] = $new_req;
    file_put_contents($material_file, json_encode($requests, JSON_PRETTY_PRINT));
    header("Location: " . ($_POST['redirect'] ?? '/it/?msg=material_submitted'));
    exit();
}

// RESTRICT RESOLVED TICKETS OLDER THAN 7 DAYS FROM PUBLIC FRONTEND
if ($action === 'get_tickets') {
    $tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
    $active_public_tickets = [];
    $current_time = time();
    $seven_days = 7 * 24 * 60 * 60;

    foreach ($tickets as $t) {
        if (isset($t['status']) && $t['status'] === 'Resolved') {
            if (!isset($t['resolved_time']) || empty($t['resolved_time'])) {
                $t['resolved_time'] = $current_time;
            }
            if (($current_time - $t['resolved_time']) > $seven_days) {
                continue; 
            }
        }
        $active_public_tickets[] = $t;
    }

    echo json_encode(['all_tickets' => is_array($active_public_tickets) ? $active_public_tickets : []]);
    exit();
}

if ($action === 'get_material_requests') {
    $requests = file_exists($material_file) ? array_reverse(json_decode(file_get_contents($material_file), true) ?: []) : [];
    echo json_encode(['requests' => $requests]);
    exit();
}

// --- Active Directory Authentication Function for Admin Console ---
function authenticate_ad($username, $password) {
    $clean_user = strtolower(trim($username));
    
    $super_admins = [
        'shree' => ['pass' => 'Sysadmin@2019#', 'role' => 'super_admin', 'name' => 'Shriprasad M. Shirke'],
        'it_user1' => ['pass' => 'ticket123', 'role' => 'it_ticketing', 'name' => 'IT Ticketing Staff'],
        'nagesh' => ['pass' => 'Nagesh@2026', 'role' => 'it_ticketing', 'name' => 'Administrator'],
        'sumair' => ['pass' => 'Sumair@2026', 'role' => 'it_ticketing', 'name' => 'Administrator'],
        'shivam' => ['pass' => 'Shivam@2026', 'role' => 'it_ticketing', 'name' => 'Administrator'],
        'ajay' => ['pass' => 'Ajay@2026', 'role' => 'it_ticketing', 'name' => 'Administrator'],
        'shri' => ['pass' => 'shri123', 'role' => 'super_admin', 'name' => 'Shriprasad M. Shirke'],
        'administrator' => ['pass' => 'admin123', 'role' => 'super_admin', 'name' => 'Administrator'],
        'depthead' => ['pass' => 'dept123', 'role' => 'department_head', 'name' => 'Department Head'],
        'ithod' => ['pass' => 'hod123', 'role' => 'it_hod', 'name' => 'IT HOD'],
        'director' => ['pass' => 'dir123', 'role' => 'director', 'name' => 'Company Director']
    ];

    if (isset($super_admins[$clean_user]) && $super_admins[$clean_user]['pass'] === $password) {
        return $super_admins[$clean_user];
    }

    $ldap_host = "ldap://192.168.130.1";
    $ldap_dn = "uid=" . $clean_user . ",ou=users,dc=argold,dc=local";
    @$ldap_conn = ldap_connect($ldap_host);
    if ($ldap_conn) {
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        @$ldap_bind = ldap_bind($ldap_conn, $ldap_dn, $password);
        if ($ldap_bind) {
            return ['name' => $username, 'role' => ($clean_user == 'shree' || $clean_user == 'administrator' ? 'super_admin' : 'department_head')];
        }
    }
    return false;
}

$login_error = '';
if (isset($_POST['login_submit'])) {
    $user = trim($_POST['ad_username'] ?? '');
    $pass = trim($_POST['ad_password'] ?? '');
    $auth = authenticate_ad($user, $pass);
    if ($auth) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $auth['name'];
        $_SESSION['role'] = $auth['role'];
        header("Location: it=backend.php");
        exit();
    } else {
        $login_error = "Invalid Active Directory Credentials or Username format!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: it=backend.php");
    exit();
}

// ADMIN CONSOLE LOGIN CHECK
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true):
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IT Admin Login - Active Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { background: #f4f6f9; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .login-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); width: 380px; border-top: 4px solid #0284c7; }
    .input-box { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; box-sizing: border-box; margin-bottom: 12px; }
    .btn-login { background: #0284c7; color: #fff; border: none; padding: 12px; border-radius: 6px; width: 100%; font-weight: bold; cursor: pointer; }
    .btn-login:hover { background: #0369a1; }
  </style>
</head>
<body>
  <div class="login-card">
    <h3 style="margin-top:0; color:#0f172a; font-size: 18px;">🔐 IT Management Console Login</h3>
    <p style="font-size: 12px; color: #64748b; margin-bottom: 20px;">Enter your credentials to access IT Management Console.</p>
    <?php if(!empty($login_error)) echo "<p style='color:#ef4444; font-size:12px; font-weight:bold;'>$login_error</p>"; ?>
    <form method="POST">
      <label style="font-size: 12px; font-weight: 600; color: #475569;">Username:</label>
      <input type="text" name="ad_username" class="input-box" placeholder="e.g. shree" required>
      <label style="font-size: 12px; font-weight: 600; color: #475569;">Password:</label>
      <input type="password" name="ad_password" class="input-box" placeholder="Password" required>
      <button type="submit" name="login_submit" class="btn-login">Login to Console</button>
    </form>
    <div style="text-align: center; margin-top: 15px;">
      <a href="/it/" style="font-size: 12px; color: #0284c7; text-decoration: none; font-weight: 600;">← Back to IT Portal</a>
    </div>
  </div>
</body>
</html>
<?php 
exit();
endif; 

$active_tab = $_GET['tab'] ?? 'hardware';
if ($_SESSION['role'] === 'it_ticketing' && $active_tab === 'new') {
    $active_tab = 'hardware';
}

$selected_filters = $_GET['filter'] ?? [];
$filter_user = trim($_GET['f_user'] ?? '');
$filter_dept = trim($_GET['f_dept'] ?? '');
$filter_factory = trim($_GET['f_factory'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket_id'])) {
    $id = $_POST['update_ticket_id'];
    $tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
    foreach ($tickets as &$t) {
        if (($t['id'] ?? '') == $id) {
            $old_status = $t['status'] ?? 'Open';
            $new_status = $_POST['status'];
            
            $t['status'] = $new_status;
            $t['assigned_to'] = $_POST['assigned_to'];
            $t['eta'] = trim($_POST['eta']);
            $t['note'] = trim($_POST['note']);

            if ($old_status !== 'Resolved' && $new_status === 'Resolved') {
                $t['resolved_time'] = time();
            } elseif ($new_status !== 'Resolved') {
                $t['resolved_time'] = null;
            }
            break;
        }
    }
    file_put_contents($tickets_file, json_encode($tickets, JSON_PRETTY_PRINT));
    header("Location: it=backend.php?tab=$active_tab&msg=updated");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['req_id'])) {
    if ($_SESSION['role'] === 'it_ticketing') {
        header("Location: it=backend.php?tab=hardware");
        exit();
    }
    
    $req_id = intval($_POST['req_id']);
    $level = $_POST['level'] ?? 'it_dept';
    $status = $_POST['status'] ?? 'Approved';
    $remark = trim($_POST['remark'] ?? '');
    $price = trim($_POST['total_price'] ?? '');
    $approver_name = $_SESSION['username'];

    $requests = file_exists($material_file) ? json_decode(file_get_contents($material_file), true) : [];
    foreach ($requests as &$req) {
        if (intval($req['id']) == $req_id) {
            if (!empty($price)) { $req['total_price'] = $price; }
            if (isset($req['approvals'][$level])) {
                $req['approvals'][$level]['status'] = $status;
                $req['approvals'][$level]['by'] = $approver_name;
                $req['approvals'][$level]['remark'] = $remark;
            }
            break;
        }
    }
    file_put_contents($material_file, json_encode($requests, JSON_PRETTY_PRINT));
    header("Location: it=backend.php?tab=new&msg=updated");
    exit();
}

if (isset($_GET['delete'])) {
    $del = $_GET['delete'];
    $tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
    $tickets = array_filter($tickets, function($t) use ($del) { return ($t['id'] ?? '') != $del; });
    file_put_contents($tickets_file, json_encode(array_values($tickets), JSON_PRETTY_PRINT));
    header("Location: it=backend.php?tab=$active_tab&msg=deleted");
    exit();
}

// DELETE MATERIAL REQUEST ACTION
if (isset($_GET['delete_material'])) {
    $del_id = intval($_GET['delete_material']);
    $requests = file_exists($material_file) ? json_decode(file_get_contents($material_file), true) : [];
    $requests = array_filter($requests, function($r) use ($del_id) { return intval($r['id'] ?? 0) !== $del_id; });
    file_put_contents($material_file, json_encode(array_values($requests), JSON_PRETTY_PRINT));
    header("Location: it=backend.php?tab=new&msg=deleted");
    exit();
}

$all_tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
if (!is_array($all_tickets)) { $all_tickets = []; }

$filtered_tickets = [];
foreach($all_tickets as $t) {
    $p_cat = $t['primary_cat'] ?? '';
    $s_cat = $t['sub_cat'] ?? '';
    
    $is_match_tab = false;
    if ($active_tab === 'hardware' && strpos($p_cat, 'Network/Hardware') !== false) {
        if (empty($selected_filters) || in_array($s_cat, $selected_filters)) { $is_match_tab = true; }
    } elseif ($active_tab === 'software' && strpos($p_cat, 'ERP/Software') !== false) {
        if (empty($selected_filters) || in_array($s_cat, $selected_filters)) { $is_match_tab = true; }
    }

    if (!$is_match_tab) continue;

    if (!empty($filter_user) && stripos($t['user'] ?? '', $filter_user) === false) continue;
    if (!empty($filter_dept) && stripos($t['department'] ?? '', $filter_dept) === false) continue;
    if (!empty($filter_factory) && stripos($t['factory'] ?? '', $filter_factory) === false) continue;

    $filtered_tickets[] = $t;
}
usort($filtered_tickets, function($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});

$material_requests = file_exists($material_file) ? array_reverse(json_decode(file_get_contents($material_file), true) ?: []) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IT Helpdesk Management Console</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { background: #f4f6f9; font-family: 'Inter', sans-serif; padding: 20px; color: #1e293b; margin: 0; }
    .container { max-width: 1400px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .header-bar { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px; }
    .tabs-container { display: flex; gap: 10px; margin-bottom: 15px; }
    .tab-btn { background: #f1f5f9; color: #64748b; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 13px; border: 1px solid #cbd5e1; }
    .tab-btn.active { background: #0284c7; color: #fff; border-color: #0284c7; }
    .filter-bar { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .filter-checkbox { font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 5px; background: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; background: #fff; }
    th, td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; vertical-align: top; }
    th { background: #f8fafc; color: #64748b; font-weight: 600; }
    .input-sm { padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; width: 100%; box-sizing: border-box; }
    .btn-save { background: #16a34a; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .btn-del { background: #ef4444; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 11px; }
    .btn { background: #0284c7; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 11px; text-decoration: none; display: inline-block; }
    .btn:hover { background: #0369a1; }
    .btn-green { background: #16a34a; }
    .btn-green:hover { background: #15803d; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header-bar">
      <div>
        <h2 style="margin:0; color:#0f172a; font-size: 20px;">🛠️ IT Helpdesk & Material Management Console</h2>
        <p style="margin:3px 0 0 0; font-size:12px; color:#64748b;">Logged in via AD as: <b style="color:#0284c7;"><?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo strtoupper($_SESSION['role']); ?>)</b></p>
      </div>
      <div style="display:flex; gap:10px; align-items:center;">
        <?php if ($active_tab !== 'new' && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'it_ticketing')): ?>
          <a href="it=backend.php?action=export_tickets" class="btn btn-green" style="font-size:12px; padding:8px 14px;">📥 Export Tickets (CSV)</a>
        <?php elseif ($active_tab === 'new' && $_SESSION['role'] !== 'it_ticketing'): ?>
          <a href="it=backend.php?action=export_material" class="btn btn-green" style="font-size:12px; padding:8px 14px;">📥 Export Material Requests (CSV)</a>
        <?php endif; ?>
        <a href="/it/" style="background:#64748b; color:#fff; padding:8px 15px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:13px;">← Go to IT Portal</a>
        <a href="it=backend.php?logout=1" style="background:#ef4444; color:#fff; padding:8px 15px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:13px;">Logout 🚪</a>
      </div>
    </div>

    <div class="tabs-container">
        <a href="it=backend.php?tab=hardware" class="tab-btn <?php echo $active_tab=='hardware'?'active':''; ?>">🖥️ Network / Hardware</a>
        <a href="it=backend.php?tab=software" class="tab-btn <?php echo $active_tab=='software'?'active':''; ?>">📊 ERP / Software / Manufacturing</a>
        <?php if ($_SESSION['role'] !== 'it_ticketing'): ?>
        <a href="it=backend.php?tab=new" class="tab-btn <?php echo $active_tab=='new'?'active':''; ?>">📦 Material Requests</a>
        <?php endif; ?>
    </div>

    <?php if ($active_tab !== 'new'): ?>
    <form method="GET" action="it=backend.php" class="filter-bar">
      <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
      
      <div style="display: flex; gap: 10px; align-items: center; width: 100%; flex-wrap: wrap; margin-bottom: 5px;">
        <span style="font-size: 12px; font-weight: 600; color: #475569;">🔍 Search & Filter:</span>
        <input type="text" name="f_user" value="<?php echo htmlspecialchars($filter_user); ?>" placeholder="Username..." class="input-sm" style="width: 140px; background:#fff;">
        <input type="text" name="f_dept" value="<?php echo htmlspecialchars($filter_dept); ?>" placeholder="Department..." class="input-sm" style="width: 140px; background:#fff;">
        <input type="text" name="f_factory" value="<?php echo htmlspecialchars($filter_factory); ?>" placeholder="Factory/Location..." class="input-sm" style="width: 140px; background:#fff;">
      </div>

      <div style="display: flex; gap: 10px; align-items: center; width: 100%; flex-wrap: wrap;">
        <span style="font-size: 12px; font-weight: 600; color: #475569;">Sub-Categories:</span>
        <?php if ($active_tab === 'software'): ?>
          <?php foreach(['Entry Issue', 'Digital Catalog Issue', 'Reports Issue', 'Other'] as $opt): ?>
            <label class="filter-checkbox"><input type="checkbox" name="filter[]" value="<?php echo $opt; ?>" <?php if(in_array($opt, $selected_filters)) echo 'checked'; ?>> <?php echo $opt; ?></label>
          <?php endforeach; ?>
        <?php else: ?>
          <?php foreach(['network', 'computer', 'printer', 'server', 'other'] as $opt): ?>
            <label class="filter-checkbox"><input type="checkbox" name="filter[]" value="<?php echo $opt; ?>" <?php if(in_array($opt, $selected_filters)) echo 'checked'; ?>> <?php echo ucfirst($opt); ?></label>
          <?php endforeach; ?>
        <?php endif; ?>
        <button type="submit" style="background:#0284c7; color:#fff; border:none; padding:6px 14px; border-radius:6px; font-weight:bold; font-size:12px; cursor:pointer;">Apply Filter</button>
        <a href="it=backend.php?tab=<?php echo $active_tab; ?>" style="font-size:12px; color:#ef4444; text-decoration:none; font-weight:600; margin-left:auto;">Clear All Filters</a>
      </div>
    </form>
    <?php endif; ?>

    <?php if(isset($_GET['msg'])) echo "<p style='color:#16a34a; font-weight:bold; font-size:13px;'>✅ Updated successfully.</p>"; ?>

    <?php if ($active_tab === 'new' && $_SESSION['role'] !== 'it_ticketing'): ?>
      <h3 style="color:#0f172a; font-size:15px; margin-top:15px;">📋 Submitted Material & Purchase Requests (4-Level Approvals)</h3>
      <table>
        <thead>
          <tr>
            <th>ID & Date</th>
            <th>Requested By & Dept</th>
            <th>Items Summary</th>
            <th>Total Price & Attach</th>
            <th>4-Level Approval Status</th>
            <th>Action / Approval Panel</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($material_requests)): ?>
            <tr><td colspan="6" style="text-align:center;">No material requests found.</td></tr>
          <?php else: foreach($material_requests as $req): 
              $req_id = $req['id'];
              $d_head = $req['approvals']['department_head'] ?? ['status' => 'Pending', 'by' => ''];
              $it_d   = $req['approvals']['it_dept'] ?? ['status' => 'Pending', 'by' => ''];
              $it_h   = $req['approvals']['it_hod'] ?? ['status' => 'Pending', 'by' => ''];
              $dir    = $req['approvals']['director'] ?? ['status' => 'Pending', 'by' => ''];
          ?>
            <tr>
              <td><b>#<?php echo $req_id; ?></b><br><small><?php echo $req['date']; ?></small></td>
              <td><b><?php echo htmlspecialchars($req['employee_name']); ?></b><br><span style="color:#0284c7;"><?php echo htmlspecialchars($req['department']); ?></span></td>
              <td>
                <ul style="margin:0; padding-left:15px; font-size:11px;">
                  <?php foreach($req['items'] as $i): if($i['qty']>0): ?>
                    <li><b><?php echo $i['name']; ?>:</b> Qty: <?php echo $i['qty']; ?></li>
                  <?php endif; endforeach; ?>
                </ul>
              </td>
              <td>
                <b style="color:#16a34a;"><?php echo $req['total_price']; ?></b><br>
                <?php if(!empty($req['attachment'])): ?><a href="/<?php echo $req['attachment']; ?>" target="_blank" style="font-size:11px;">📎 Attachment</a><?php endif; ?>
              </td>
              <td>
                <div style="font-size:11px; line-height: 1.4;">
                  <div>1. Dept Head: <b><?php echo $d_head['status']; ?></b> <?php if(!empty($d_head['by'])) echo '<span style="color:#0284c7;">('.$d_head['by'].')</span>'; ?></div>
                  <div>2. IT Dept (Shri): <b><?php echo $it_d['status']; ?></b> <?php if(!empty($it_d['by'])) echo '<span style="color:#0284c7;">('.$it_d['by'].')</span>'; ?></div>
                  <div>3. IT HOD: <b><?php echo $it_h['status']; ?></b> <?php if(!empty($it_h['by'])) echo '<span style="color:#0284c7;">('.$it_h['by'].')</span>'; ?></div>
                  <div>4. Director: <b><?php echo $dir['status']; ?></b> <?php if(!empty($dir['by'])) echo '<span style="color:#0284c7;">('.$dir['by'].')</span>'; ?></div>
                </div>
              </td>
              <td>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                  <button onclick="openFullForm(<?php echo htmlspecialchars(json_encode($req)); ?>)" class="btn">📄 View Full Form</button>

                  <form action="it=backend.php?tab=new" method="POST" style="display: flex; flex-direction: column; gap: 4px; background: #f8fafc; padding: 6px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <input type="hidden" name="req_id" value="<?php echo $req_id; ?>">
                    <input type="text" name="total_price" placeholder="Price..." value="<?php echo htmlspecialchars($req['total_price']); ?>" class="input-sm" style="font-weight:600;">
                    <div style="display: flex; gap: 4px;">
                      <select name="level" class="input-sm" style="font-size:10px;">
                        <option value="department_head">1. Dept Head</option>
                        <option value="it_dept" selected>2. IT Dept (Shri)</option>
                        <option value="it_hod">3. IT HOD</option>
                        <option value="director">4. Director</option>
                      </select>
                      <select name="status" class="input-sm" style="font-size:10px; font-weight:bold;" required>
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Reject</option>
                      </select>
                    </div>
                    <div style="display: flex; gap: 4px;">
                      <input type="text" name="remark" placeholder="Remarks..." class="input-sm" style="flex:1;">
                      <button type="submit" class="btn btn-green" style="padding: 4px 8px;">Save</button>
                    </div>
                  </form>

                  <a href="it=backend.php?delete_material=<?php echo $req_id; ?>&tab=new" class="btn-del" onclick="return confirm('Delete this Material Request?');" style="text-align:center; padding:4px;">🗑️ Delete Request</a>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th style="width: 8%;">Token & Details</th>
            <th style="width: 6%;">User & Dept</th>
            <th style="width: 25%;">Category & Issue</th>
            <th style="width: 12%;">Photos</th>
            <th style="width: 17%;">Admin Action (Assign, Status, Note)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($filtered_tickets as $t): ?>
          <tr>
            <td>
              <b style="color:#16a34a; font-size:13px;"><?php echo htmlspecialchars($t['id']); ?></b><br>
              <small style="color:#64748b;"><?php echo date('d-m-Y H:i', $t['timestamp'] ?? time()); ?></small>
            </td>
            <td>
              <b><?php echo htmlspecialchars($t['user']); ?></b><br>
              <span style="color:#0284c7; font-size:11px;"><?php echo htmlspecialchars($t['department']); ?></span><br>
              <span style="background:#f1f5f9; padding:1px 4px; border-radius:3px; font-size:10px; color:#475569;"><?php echo htmlspecialchars($t['factory'] ?? 'Navi Mumbai'); ?></span>
            </td>
            <td>
              <span style="font-size:11px; color:#0284c7; font-weight:600;">[<?php echo htmlspecialchars($t['primary_cat']); ?>]</span><br>
              <span style="font-size:11px; background:#fef3c7; padding:2px 4px; border-radius:4px; color:#b45309;">↳ <?php echo htmlspecialchars($t['sub_cat']); ?></span><br>
              <b style="display:block; margin-top:4px; font-size:12px;"><?php echo nl2br(htmlspecialchars($t['issue'])); ?></b>
            </td>
            <td>
               <?php if(!empty($t['photos'])): ?>
                  <?php foreach($t['photos'] as$p): ?>
                     <a href="/<?php echo $p; ?>" target="_blank"><img src="/<?php echo $p; ?>" style="height:36px; width:36px; object-fit:cover; border-radius:4px; border:1px solid #ccc; margin:2px;"></a>
                  <?php endforeach; ?>
               <?php else: ?>
                  <span style="color:#94a3b8; font-size:11px;">No Images</span>
               <?php endif; ?>
            </td>
            <td>
              <form action="it=backend.php?tab=<?php echo $active_tab; ?>" method="POST" style="display:flex; flex-direction:column; gap:5px;">
                <input type="hidden" name="update_ticket_id" value="<?php echo $t['id']; ?>">
                <div style="display:flex; gap:5px; align-items:center;">
                  <select name="status" class="input-sm" style="width:110px; font-weight:bold;">
                    <option value="Open" <?php if(($t['status'] ?? '')=='Open') echo 'selected'; ?>>Open</option>
                    <option value="In Progress" <?php if(($t['status'] ?? '')=='In Progress') echo 'selected'; ?>>In Progress</option>
                    <option value="Resolved" <?php if(($t['status'] ?? '')=='Resolved') echo 'selected'; ?>>Resolved</option>
                  </select>
                  <input type="text" name="assigned_to" class="input-sm" style="flex:1;" value="<?php echo htmlspecialchars($t['assigned_to'] ?? ''); ?>" placeholder="Unassigned">
                </div>
                <div style="display:flex; gap:5px; align-items:center;">
                  <input type="text" name="note" class="input-sm" style="flex:1;" value="<?php echo htmlspecialchars($t['note'] ?? ''); ?>" placeholder="Working Note...">
                  <input type="text" name="eta" class="input-sm" style="width:75px;" value="<?php echo htmlspecialchars($t['eta'] ?? ''); ?>" placeholder="ETA">
                </div>
                <div style="display:flex; gap:5px; align-items:center; justify-content:flex-end;">
                  <button type="submit" class="btn-save" style="padding:4px 12px;">Save</button>
                  <a href="it=backend.php?delete=<?php echo $t['id']; ?>&tab=<?php echo$active_tab; ?>" class="btn-del" onclick="return confirm('Delete Ticket?');" style="padding:4px 8px;">🗑️ Delete</a>
                </div>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($filtered_tickets)) echo "<tr><td colspan='5' style='text-align:center;'>No tickets found matching the filter.</td></tr>"; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>

  <div id="formModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#ffffff; border:1px solid #cbd5e1; padding:25px; border-radius:10px; width:650px; max-height:90vh; overflow-y:auto; position:relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
      <button onclick="closeFullForm()" style="position:absolute; top:15px; right:15px; background:#ef4444; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-weight:bold;">✕ Close</button>
      <h3 style="font-size: 16px; font-weight: 700; color:#0f172a; border-bottom:1px solid #e2e8f0; padding-bottom:8px; margin-top:0;">A. R. GOLD PVT. LTD. - IT Request Form</h3>
      <div id="modalContent" style="font-size:13px; line-height:1.6;"></div>
    </div>
  </div>

  <script>
    function openFullForm(req) {
      let itemsHtml = "";
      if (req.items) {
        req.items.forEach(i => {
          itemsHtml += `<tr><td style="padding:6px; border:1px solid #e2e8f0;"><b>${i.name}</b></td><td style="padding:6px; border:1px solid #e2e8f0;">${i.desc || '-'}</td><td style="padding:6px; border:1px solid #e2e8f0;"><b>${i.qty}</b></td></tr>`;
        });
      }
      let attachHtml = req.attachment ? `<a href="/${req.attachment}" target="_blank" style="color:#0284c7;">📎 Click to View Attachment</a>` : 'No Attachment';
      let content = `
        <p><b>Request ID:</b> #${req.id} | <b>Date:</b> ${req.date}</p>
        <p><b>Employee Name:</b> ${req.employee_name} | <b>Department:</b> ${req.department} | <b>Location:</b> ${req.factory}</p>
        <hr style="border-color:#e2e8f0;">
        <h4 style="color:#0284c7; margin:10px 0 5px 0;">Requested Items:</h4>
        <table style="width:100%; border-collapse:collapse; background:#f8fafc; font-size:12px;">
          <thead><tr style="background:#e2e8f0; color:#0f172a;"><th style="padding:6px; border:1px solid #cbd5e1;">Item</th><th style="padding:6px; border:1px solid #cbd5e1;">Desc</th><th style="padding:6px; border:1px solid #cbd5e1;">Qty</th></tr></thead>
          <tbody>${itemsHtml}</tbody>
        </table>
        <p style="margin-top:15px;"><b>Total Price:</b> <span style="color:#16a34a; font-size:15px; font-weight:bold;">${req.total_price}</span></p>
        <p><b>Attachment:</b> ${attachHtml}</p>
      `;
      document.getElementById('modalContent').innerHTML = content;
      document.getElementById('formModal').style.display = 'flex';
    }
    function closeFullForm() { document.getElementById('formModal').style.display = 'none'; }
  </script>
</body>
</html>
