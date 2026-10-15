if ($action === 'create_ticket') {
    $tickets = file_exists($tickets_file) ? json_decode(file_get_contents($tickets_file), true) : [];
    if (!is_array($tickets)) { $tickets = []; }
    
    $today_str = date('d-m-Y'); 
    $today_count = 0;
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
        'primary_cat' => trim($_POST['primary_cat'] ?? 'Network/Hardware'),
        'sub_cat' => trim($_POST['sub_cat'] ?? 'other'),
        'issue' => trim($_POST['issue'] ?? ''),
        'photos' => $uploaded_files,
        'status' => 'Open',
        'assigned_to' => 'Unassigned',
        'eta' => '',
        'note' => '',
        'timestamp' => time(),
        'created_at' => date('d-m-Y H:i'), // 24-Hour Format Fix
        'resolved_time' => null
    ];
    array_unshift($tickets, $new_ticket);
    file_put_contents($tickets_file, json_encode($tickets, JSON_PRETTY_PRINT));
    header("Location: " . ($_POST['redirect'] ?? '/?msg=it_submitted'));
    exit();
}
