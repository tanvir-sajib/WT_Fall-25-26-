<?php
// admin-header.php - Shared modern header for all admin pages
// Usage: include 'admin-header.php'; at top of each page (after session_start and check_login)
// Set $page_title and $active_nav before including

$page_title = $page_title ?? 'Dashboard';
$active_nav = $active_nav ?? '';
$admin_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin';
?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --bg: #f1f5f9;
    --surface: #ffffff;
    --surface2: #f8fafc;
    --border: #e2e8f0;
    --text: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --sidebar-w: 240px;
    --header-h: 64px;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 24px rgba(0,0,0,0.08);
    --font: 'Plus Jakarta Sans', sans-serif;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: var(--font); background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

/* ── SIDEBAR ── */
.admin-sidebar {
    width: var(--sidebar-w);
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    z-index: 100;
    transition: transform 0.3s;
}
.sidebar-logo {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}
.sidebar-logo .logo-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 16px;
}
.sidebar-logo .logo-text {
    font-size: 15px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.2;
}
.sidebar-logo .logo-sub {
    font-size: 10px;
    color: var(--text-3);
    font-weight: 500;
}

.sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
.nav-section-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-3);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 8px 12px 4px;
    margin-top: 8px;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    text-decoration: none;
    color: var(--text-2);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 2px;
    transition: all 0.15s;
}
.nav-item i { width: 18px; text-align: center; font-size: 15px; }
.nav-item:hover { background: var(--surface2); color: var(--text); }
.nav-item.active { background: var(--primary-light); color: var(--primary-dark); font-weight: 600; }
.nav-item.active i { color: var(--primary); }

.sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid var(--border);
}
.admin-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--surface2);
}
.admin-avatar {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, var(--primary), #8b5cf6);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 13px; font-weight: 700;
    flex-shrink: 0;
}
.admin-info .name { font-size: 13px; font-weight: 600; color: var(--text); }
.admin-info .role { font-size: 11px; color: var(--text-3); }
.logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    text-decoration: none;
    color: var(--danger);
    font-size: 14px;
    font-weight: 500;
    margin-top: 4px;
    transition: background 0.15s;
}
.logout-btn:hover { background: #fff1f2; }

/* ── TOP BAR ── */
.admin-topbar {
    position: fixed;
    top: 0;
    left: var(--sidebar-w);
    right: 0;
    height: var(--header-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 28px;
    gap: 16px;
    z-index: 99;
}
.topbar-title { font-size: 18px; font-weight: 700; color: var(--text); flex: 1; }
.topbar-time { font-size: 13px; color: var(--text-3); }

/* ── MAIN CONTENT ── */
.admin-main {
    margin-left: var(--sidebar-w);
    padding-top: var(--header-h);
    flex: 1;
    min-height: 100vh;
}
.main-content {
    padding: 28px;
    max-width: 1200px;
}

/* ── CARDS ── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-icon.purple { background: #ede9fe; color: var(--primary); }
.stat-icon.green  { background: #d1fae5; color: var(--success); }
.stat-icon.orange { background: #fef3c7; color: var(--warning); }
.stat-icon.red    { background: #fee2e2; color: var(--danger); }
.stat-icon.blue   { background: #dbeafe; color: var(--info); }
.stat-val { font-size: 26px; font-weight: 800; color: var(--text); line-height: 1; }
.stat-label { font-size: 13px; color: var(--text-3); margin-top: 3px; }

/* ── PANEL ── */
.panel {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 20px;
    overflow: hidden;
}
.panel-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.panel-title i { color: var(--primary); }
.panel-body { padding: 24px; }

/* ── FORM ── */
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full { grid-column: 1 / -1; }
.form-label { font-size: 13px; font-weight: 600; color: var(--text-2); }
.form-input, .form-select, .form-textarea {
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-family: var(--font);
    font-size: 14px;
    color: var(--text);
    background: var(--surface);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.form-textarea { resize: vertical; min-height: 80px; }

/* ── BUTTONS ── */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border: none;
    border-radius: 8px;
    font-family: var(--font);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s;
    white-space: nowrap;
}
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.3); }
.btn-success { background: var(--success); color: white; }
.btn-success:hover { background: #059669; }
.btn-danger  { background: var(--danger); color: white; }
.btn-danger:hover  { background: #dc2626; }
.btn-warning { background: var(--warning); color: white; }
.btn-warning:hover { background: #d97706; }
.btn-ghost { background: var(--surface2); color: var(--text-2); border: 1.5px solid var(--border); }
.btn-ghost:hover { background: var(--border); }
.btn-sm { padding: 5px 12px; font-size: 12px; }

/* ── TABLE ── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    background: var(--surface2);
    border-bottom: 1px solid var(--border);
}
.data-table td {
    padding: 14px 16px;
    font-size: 14px;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    vertical-align: middle;
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--surface2); }

/* ── BADGES ── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger  { background: #fee2e2; color: #991b1b; }
.badge-info    { background: #dbeafe; color: #1e40af; }
.badge-purple  { background: #ede9fe; color: #5b21b6; }
.badge-gray    { background: #f1f5f9; color: #475569; }

/* ── ALERTS ── */
.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
.alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }

/* ── UPLOAD AREA ── */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--surface2);
}
.upload-zone:hover { border-color: var(--primary); background: var(--primary-light); }
.upload-zone input[type="file"] { display: none; }
.upload-zone .upload-icon { font-size: 28px; color: var(--text-3); margin-bottom: 8px; }
.upload-zone .upload-text { font-size: 13px; font-weight: 600; color: var(--primary); }
.upload-zone .upload-hint { font-size: 12px; color: var(--text-3); margin-top: 4px; }
#preview-img, #product-preview {
    max-width: 100%; max-height: 160px;
    border-radius: 8px; margin-top: 12px;
    display: none; border: 1px solid var(--border);
}

/* ── TABS ── */
.tab-bar {
    display: flex;
    gap: 4px;
    background: var(--surface2);
    border-radius: 10px;
    padding: 4px;
    margin-bottom: 20px;
    width: fit-content;
}
.tab-btn {
    padding: 8px 18px;
    border: none;
    border-radius: 8px;
    background: transparent;
    font-family: var(--font);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-3);
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s;
}
.tab-btn.active { background: var(--surface); color: var(--text); box-shadow: var(--shadow); }

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 48px 20px; color: var(--text-3); }
.empty-state i { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
.empty-state p { font-size: 14px; }
</style>

<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fas fa-store"></i></div>
        <div>
            <div class="logo-text">BanglaBazaar</div>
            <div class="logo-sub">Admin Panel</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="inventory.php"  class="nav-item <?php echo $active_nav=='inventory'?'active':''; ?>">
            <i class="fas fa-boxes"></i> Inventory
        </a>
        <a href="orders.php"     class="nav-item <?php echo $active_nav=='orders'?'active':''; ?>">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        <a href="customers.php"  class="nav-item <?php echo $active_nav=='customers'?'active':''; ?>">
            <i class="fas fa-users"></i> Customers
        </a>

        <div class="nav-section-label">Content</div>
        <a href="categories.php" class="nav-item <?php echo $active_nav=='categories'?'active':''; ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="banners.php"    class="nav-item <?php echo $active_nav=='banners'?'active':''; ?>">
            <i class="fas fa-image"></i> Banners
        </a>

        <div class="nav-section-label">Reports</div>
        <a href="analytics.php"  class="nav-item <?php echo $active_nav=='analytics'?'active':''; ?>">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-profile">
            <div class="admin-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
            <div class="admin-info">
                <div class="name"><?php echo $admin_name; ?></div>
                <div class="role">Administrator</div>
            </div>
        </div>
        <a href="logOut.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>

<!-- Top Bar -->
<div class="admin-topbar">
    <div class="topbar-title"><?php echo $page_title; ?></div>
    <div class="topbar-time" id="live-time"></div>
</div>

<div class="admin-main">
<div class="main-content">

<script>
function updateTime() {
    const now = new Date();
    document.getElementById('live-time').textContent = now.toLocaleString('en-US', {weekday:'short', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
}
updateTime(); setInterval(updateTime, 60000);
</script>