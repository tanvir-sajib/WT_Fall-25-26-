<?php
session_start();
require_once 'config.php';
check_login();

$page_title = 'Orders';
$active_nav = 'orders';
$success = $error = "";

// Update status - prepared statement + CSRF
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    verify_admin_csrf($_POST['csrf_token'] ?? '');
    $oid = (int)$_POST['order_id'];
    $allowed_statuses = ['Pending','Processing','Shipped','Delivered','Cancelled','Refunded'];
    $ns = $_POST['status'] ?? '';
    if (!in_array($ns, $allowed_statuses)) {
        $error = "Invalid status.";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $ns, $oid);
        $stmt->execute() ? $success = "Order #$oid updated to '$ns'" : $error = "Update failed.";
    }
}

// Delete - CSRF via GET token
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    verify_admin_csrf($_GET['csrf_token'] ?? '');
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute() ? $success = "Order #$id deleted." : $error = "Delete failed.";
}

// Stats
$r = $conn->query("SELECT COUNT(*) as c FROM orders"); $total_orders = $r->fetch_assoc()['c'] ?? 0;
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending' OR status='processing'"); $pending = $r->fetch_assoc()['c'] ?? 0;
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='delivered'"); $delivered = $r->fetch_assoc()['c'] ?? 0;
$r = $conn->query("SELECT SUM(total) as t FROM orders WHERE status='Delivered'"); $revenue = $r->fetch_assoc()['t'] ?? 0;

// Filters
$status_filter = sanitize_input($_GET['status'] ?? '');
$search        = sanitize_input($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = []; $types = '';
if ($status_filter) { $where .= " AND o.status = ?"; $params[] = $status_filter; $types .= 's'; }
if ($search)        { $where .= " AND (u.name LIKE ? OR o.id = ?)"; $params[] = "%$search%"; $params[] = (int)$search; $types .= 'si'; }

$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        $where ORDER BY o.id DESC";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$orders = $stmt->get_result();
$csrf = $_SESSION['admin_csrf'];
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Orders - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?php echo htmlspecialchars($success);?></div><?php endif;?>
<?php if($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error);?></div><?php endif;?>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div><div><div class="stat-val"><?php echo $total_orders;?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div><div class="stat-val"><?php echo $pending;?></div><div class="stat-label">Pending / Processing</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-double"></i></div><div><div class="stat-val"><?php echo $delivered;?></div><div class="stat-label">Delivered</div></div></div>
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-taka-sign"></i></div><div><div class="stat-val">৳<?php echo number_format($revenue,0);?></div><div class="stat-label">Revenue</div></div></div>
</div>

<!-- Filter Bar -->
<div class="panel">
    <div class="panel-body">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" class="form-input" placeholder="Search order ID or customer..." value="<?php echo htmlspecialchars($search);?>" style="flex:1;min-width:200px;">
            <select name="status" class="form-select" style="width:180px;">
                <option value="">All Statuses</option>
                <?php foreach(['Pending','Processing','Shipped','Delivered','Cancelled','Refunded'] as $s): ?>
                <option <?php echo $status_filter==$s?'selected':'';?>><?php echo $s;?></option>
                <?php endforeach;?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <?php if($status_filter||$search): ?><a href="orders.php" class="btn btn-secondary">Clear</a><?php endif;?>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title"><i class="fas fa-list"></i> Orders</div>
        <span class="badge badge-blue"><?php echo $orders->num_rows;?> results</span>
    </div>
    <table class="data-table">
        <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if($orders->num_rows > 0): while($o = $orders->fetch_assoc()): ?>
        <tr>
            <td><strong>#<?php echo $o['id'];?></strong></td>
            <td>
                <div><?php echo htmlspecialchars($o['customer_name'] ?? 'Guest');?></div>
                <div style="font-size:11px;color:#94a3b8"><?php echo htmlspecialchars($o['customer_email'] ?? '');?></div>
            </td>
            <td style="font-size:12px;max-width:150px;"><?php echo htmlspecialchars($o['shipping_address'] ?? '');?></td>
            <td><span class="badge badge-blue"><?php echo htmlspecialchars($o['payment_method'] ?? 'cod');?></span></td>
            <td><strong>৳<?php echo number_format($o['total'] ?? 0,2);?></strong></td>
            <td>
                <?php $sc=['Pending'=>'badge-warning','Processing'=>'badge-info','Shipped'=>'badge-info','Delivered'=>'badge-success','Cancelled'=>'badge-danger','Refunded'=>'badge-gray'];?>
                <span class="badge <?php echo $sc[$o['status']]??'badge-gray';?>"><?php echo $o['status'];?></span>
            </td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo date('d M Y', strtotime($o['order_date'] ?? $o['created_at'] ?? 'now'));?></td>
            <td>
                <form method="POST" style="display:inline-flex;gap:6px;align-items:center;">
                    <input type="hidden" name="order_id" value="<?php echo $o['id'];?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf;?>">
                    <select name="status" class="form-select" style="padding:5px 8px;font-size:12px;">
                        <?php foreach(['Pending','Processing','Shipped','Delivered','Cancelled','Refunded'] as $s): ?>
                        <option <?php echo $o['status']==$s?'selected':'';?>><?php echo $s;?></option>
                        <?php endforeach;?>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-warning btn-sm" title="Save"><i class="fas fa-save"></i></button>
                </form>
                <a href="?action=delete&id=<?php echo $o['id'];?>&csrf_token=<?php echo $csrf;?>" class="btn btn-danger btn-sm" style="margin-left:4px;" onclick="return confirm('Delete order #<?php echo $o['id'];?>?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8"><div class="empty-state"><i class="fas fa-shopping-bag"></i><p>No orders found.</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>
</div></div></body></html>