<?php
session_start();
require_once 'config.php';
check_login();

$page_title = 'Analytics';
$active_nav = 'analytics';

function safe_val($conn,$q,$col){$r=mysqli_query($conn,$q);if(!$r)return 0;$row=mysqli_fetch_assoc($r);return $row[$col]??0;}

$totalOrders   = safe_val($conn,"SELECT COUNT(*) as c FROM orders",'c');
$totalRevenue  = safe_val($conn,"SELECT SUM(total_price) as t FROM orders WHERE status='Delivered'",'t');
$pendingOrders = safe_val($conn,"SELECT COUNT(*) as c FROM orders WHERE status='Pending'",'c');
$totalCustomers= safe_val($conn,"SELECT COUNT(*) as c FROM customers",'c');
$totalProducts = safe_val($conn,"SELECT COUNT(*) as c FROM products",'c');
$lowStock      = safe_val($conn,"SELECT COUNT(*) as c FROM products WHERE stock < 5",'c');

$today    = date('Y-m-d');
$week_ago = date('Y-m-d',strtotime('-7 days'));
$month_ago= date('Y-m-d',strtotime('-30 days'));
$daily    = safe_val($conn,"SELECT COUNT(*) as c FROM orders WHERE order_date='$today'",'c');
$weekly   = safe_val($conn,"SELECT COUNT(*) as c FROM orders WHERE order_date>='$week_ago'",'c');
$monthly  = safe_val($conn,"SELECT COUNT(*) as c FROM orders WHERE order_date>='$month_ago'",'c');

$top_products = mysqli_query($conn,"SELECT product_name,SUM(quantity) as sold,SUM(total_price) as rev FROM orders WHERE status='Delivered' GROUP BY product_name ORDER BY sold DESC LIMIT 10");
$recent_orders= mysqli_query($conn,"SELECT * FROM orders ORDER BY id DESC LIMIT 10");
$low_products = mysqli_query($conn,"SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 10");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Analytics - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-shopping-bag"></i></div><div><div class="stat-val"><?php echo $totalOrders;?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div><div><div class="stat-val">$<?php echo number_format($totalRevenue,0);?></div><div class="stat-label">Total Revenue</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div><div class="stat-val"><?php echo $pendingOrders;?></div><div class="stat-label">Pending Orders</div></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-users"></i></div><div><div class="stat-val"><?php echo $totalCustomers;?></div><div class="stat-label">Customers</div></div></div>
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-boxes"></i></div><div><div class="stat-val"><?php echo $totalProducts;?></div><div class="stat-label">Products</div></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div><div><div class="stat-val"><?php echo $lowStock;?></div><div class="stat-label">Low Stock</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
<!-- Time Analytics -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-calendar-alt"></i> Order Timeline</div></div>
    <div class="panel-body">
        <div style="display:flex;flex-direction:column;gap:16px;">
            <?php foreach([['Today',$daily,'blue'],['Last 7 Days',$weekly,'purple'],['Last 30 Days',$monthly,'green']] as [$label,$val,$color]): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--surface2);border-radius:10px;">
                <span style="font-size:14px;font-weight:600;"><?php echo $label;?></span>
                <span class="badge badge-<?php echo $color;?>" style="font-size:14px;padding:5px 14px;"><?php echo $val;?> orders</span>
            </div>
            <?php endforeach;?>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Low Stock Alert</div></div>
    <table class="data-table">
        <thead><tr><th>Product</th><th>Stock</th></tr></thead>
        <tbody>
        <?php if($low_products&&mysqli_num_rows($low_products)>0): while($p=mysqli_fetch_assoc($low_products)): ?>
        <tr>
            <td><?php echo htmlspecialchars($p['name']);?></td>
            <td><span class="badge <?php echo ($p['stock']??0)<3?'badge-danger':'badge-warning';?>"><?php echo $p['stock']??0;?></span></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="2" style="text-align:center;color:var(--success);padding:20px;">✅ All products well stocked!</td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>
</div>

<!-- Top Products -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-trophy"></i> Top Selling Products</div></div>
    <table class="data-table">
        <thead><tr><th>Rank</th><th>Product</th><th>Units Sold</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php if($top_products&&mysqli_num_rows($top_products)>0): $rank=1; while($p=mysqli_fetch_assoc($top_products)): ?>
        <tr>
            <td><span class="badge badge-purple">#<?php echo $rank++;?></span></td>
            <td><strong><?php echo htmlspecialchars($p['product_name']);?></strong></td>
            <td><span class="badge badge-success"><?php echo $p['sold'];?></span></td>
            <td><strong>$<?php echo number_format($p['rev'],2);?></strong></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="4"><div class="empty-state"><i class="fas fa-trophy"></i><p>No sales data yet.</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

<!-- Recent Orders -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-history"></i> Recent Orders</div></div>
    <table class="data-table">
        <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php if($recent_orders&&mysqli_num_rows($recent_orders)>0): while($o=mysqli_fetch_assoc($recent_orders)): ?>
        <tr>
            <td><strong>#<?php echo $o['id'];?></strong></td>
            <td><?php echo htmlspecialchars($o['customer_name']??'');?></td>
            <td><?php echo htmlspecialchars($o['product_name']??'');?></td>
            <td><?php echo $o['quantity']??0;?></td>
            <td>$<?php echo number_format($o['total_price']??0,2);?></td>
            <td><?php $sc=['Pending'=>'badge-warning','Shipped'=>'badge-info','Delivered'=>'badge-success','Cancelled'=>'badge-danger'];?>
                <span class="badge <?php echo $sc[$o['status']]??'badge-gray';?>"><?php echo $o['status'];?></span></td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo $o['order_date'];?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="7"><div class="empty-state"><i class="fas fa-history"></i><p>No orders yet.</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

</div></div></body></html>