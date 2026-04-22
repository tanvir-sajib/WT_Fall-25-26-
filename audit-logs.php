<?php
// ============================================================
// FILE: FT_Project/audit-logs.php  (NEW FILE)
// ============================================================
session_start();
require_once 'config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: LogPage.php"); exit(); }

$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 25;
$offset  = ($page - 1) * $per;
$search  = sanitize_input($_GET['search'] ?? '');

$where   = $search ? "WHERE action LIKE '%$search%' OR admin_username LIKE '%$search%' OR description LIKE '%$search%'" : '';
$count   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM admin_audit_logs $where"))['cnt'];
$pages   = ceil($count / $per);
$logs    = [];
$res     = mysqli_query($conn, "SELECT * FROM admin_audit_logs $where ORDER BY created_at DESC LIMIT $per OFFSET $offset");
while ($row = mysqli_fetch_assoc($res)) $logs[] = $row;
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Audit Logs — BanglaBazaar Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f8fafc;color:#0f172a}
.topbar{background:white;padding:16px 32px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 4px rgba(0,0,0,0.08)}
.topbar .brand{font-size:18px;font-weight:800;color:#6366f1}
.container{max-width:1100px;margin:32px auto;padding:0 20px}
h2{font-size:22px;font-weight:800;margin-bottom:20px}
.toolbar{display:flex;gap:12px;margin-bottom:20px;align-items:center}
.toolbar input{flex:1;max-width:320px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;font-family:inherit;font-size:14px;outline:none}
.toolbar input:focus{border-color:#6366f1}
.table-wrap{background:white;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06)}
table{width:100%;border-collapse:collapse}
th{background:#f8fafc;padding:14px 16px;text-align:left;font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.5px}
td{padding:13px 16px;border-top:1px solid #f1f5f9;font-size:13px;vertical-align:top}
tr:hover td{background:#fafafa}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.badge-create{background:#d1fae5;color:#065f46}
.badge-update{background:#dbeafe;color:#1e40af}
.badge-delete{background:#fee2e2;color:#991b1b}
.badge-login{background:#ede9fe;color:#5b21b6}
.badge-other{background:#f1f5f9;color:#475569}
.pagination{display:flex;gap:8px;margin-top:24px;justify-content:center}
.pagination a,.pagination span{padding:7px 13px;border-radius:8px;font-weight:600;font-size:13px;text-decoration:none}
.pagination a{border:1.5px solid #e2e8f0;color:#475569}
.pagination a:hover{border-color:#6366f1;color:#6366f1}
.pagination .active{background:#6366f1;color:white;border:1.5px solid #6366f1}
.back{color:#6366f1;text-decoration:none;font-size:14px;font-weight:600}
</style>
</head><body>
<div class="topbar">
  <div class="brand">🛍️ BanglaBazaar Admin</div>
  <a href="inventory.php" class="back">← Dashboard</a>
</div>
<div class="container">
  <h2><i class="fas fa-shield-halved" style="color:#6366f1"></i> Audit Logs</h2>
  <div class="toolbar">
    <form method="GET" style="display:flex;gap:10px;flex:1">
      <input type="text" name="search" placeholder="Search by action, admin, description..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" style="padding:10px 18px;background:#6366f1;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600">Search</button>
      <?php if($search): ?><a href="audit-logs.php" style="padding:10px 18px;border:1.5px solid #e2e8f0;border-radius:8px;text-decoration:none;color:#475569;font-size:14px">Clear</a><?php endif; ?>
    </form>
    <span style="font-size:13px;color:#94a3b8"><?= $count ?> total entries</span>
  </div>

  <div class="table-wrap">
    <table>
      <thead><tr>
        <th>#</th><th>Admin</th><th>Action</th><th>Description</th><th>IP</th><th>Time</th>
      </tr></thead>
      <tbody>
      <?php if (empty($logs)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:#94a3b8">No logs found.</td></tr>
      <?php else: ?>
        <?php foreach($logs as $log):
          $badge = 'badge-other';
          $act = strtolower($log['action']);
          if (str_contains($act,'create')||str_contains($act,'add')||str_contains($act,'insert')) $badge='badge-create';
          elseif (str_contains($act,'update')||str_contains($act,'edit')) $badge='badge-update';
          elseif (str_contains($act,'delete')||str_contains($act,'remove')) $badge='badge-delete';
          elseif (str_contains($act,'login')||str_contains($act,'logout')) $badge='badge-login';
        ?>
        <tr>
          <td style="color:#94a3b8"><?= $log['id'] ?></td>
          <td><strong><?= htmlspecialchars($log['admin_username']) ?></strong><br><span style="color:#94a3b8;font-size:11px"><?= htmlspecialchars($log['admin_id']) ?></span></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($log['action']) ?></span></td>
          <td style="color:#475569;max-width:280px"><?= htmlspecialchars($log['description']) ?></td>
          <td style="color:#94a3b8;font-family:monospace"><?= $log['ip_address'] ?></td>
          <td style="color:#64748b;white-space:nowrap"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php if ($page>1): ?><a href="?search=<?=urlencode($search)?>&page=<?=$page-1?>">← Prev</a><?php endif; ?>
    <?php for($i=1;$i<=$pages;$i++): ?>
      <?php if($i==$page): ?><span class="active"><?=$i?></span>
      <?php else: ?><a href="?search=<?=urlencode($search)?>&page=<?=$i?>"><?=$i?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if ($page<$pages): ?><a href="?search=<?=urlencode($search)?>&page=<?=$page+1?>">Next →</a><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
</body></html>