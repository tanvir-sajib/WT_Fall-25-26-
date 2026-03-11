<?php
session_start();
require_once 'config.php';
check_login();

$page_title = 'Orders';
$active_nav = 'orders';
$success = $error = "";

// Create order
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['create_order'])) {
    $cname = sanitize_input($_POST['customer_name']);
    $pid   = (int)$_POST['product_id'];
    $qty   = (int)$_POST['quantity'];
    if(empty($cname)) $error="Customer name required!";
    elseif(!$pid) $error="Select a product!";
    elseif($qty<1) $error="Valid quantity required!";
    else {
        $pr = mysqli_query($conn,"SELECT * FROM products WHERE id=$pid");
        if(mysqli_num_rows($pr)==1){
            $prod=mysqli_fetch_assoc($pr);
            $avail=$prod['stock']??999;
            if($avail<$qty) $error="Insufficient stock! Available: $avail";
            else {
                $price=$prod['price']; $disc=($price*($prod['discount']??0))/100;
                $total=($price-$disc)*$qty; $pname=$prod['name']; $date=date('Y-m-d');
                $q="INSERT INTO orders (customer_name,product_id,product_name,quantity,total_price,order_date) VALUES ('$cname',$pid,'$pname',$qty,$total,'$date')";
                if(mysqli_query($conn,$q)){
                    $new_stock=($prod['stock']??0)-$qty;
                    mysqli_query($conn,"UPDATE products SET stock=$new_stock WHERE id=$pid");
                    $success="Order created! ID: #".mysqli_insert_id($conn);
                } else $error="Error: ".mysqli_error($conn);
            }
        } else $error="Product not found!";
    }
}

// Update status
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['update_status'])) {
    $oid=(int)$_POST['order_id']; $ns=sanitize_input($_POST['status']);
    if(mysqli_query($conn,"UPDATE orders SET status='$ns' WHERE id=$oid")) $success="Order #$oid updated to $ns";
    else $error="Error: ".mysqli_error($conn);
}

// Delete
if(isset($_GET['action'])&&$_GET['action']=='delete'&&isset($_GET['id'])){
    $id=(int)$_GET['id'];
    if(mysqli_query($conn,"DELETE FROM orders WHERE id=$id")) $success="Order deleted!";
    else $error="Error: ".mysqli_error($conn);
}

$orders = mysqli_query($conn,"SELECT * FROM orders ORDER BY id DESC");
$products = mysqli_query($conn,"SELECT id,name,price,stock FROM products WHERE (visibility=1 OR visibility IS NULL) ORDER BY name ASC");
$total_orders = mysqli_num_rows($orders);
$r=mysqli_query($conn,"SELECT COUNT(*) as c FROM orders WHERE status='Pending'"); $pending=mysqli_fetch_assoc($r)['c']??0;
$r=mysqli_query($conn,"SELECT COUNT(*) as c FROM orders WHERE status='Delivered'"); $delivered=mysqli_fetch_assoc($r)['c']??0;
$r=mysqli_query($conn,"SELECT SUM(total_price) as t FROM orders WHERE status='Delivered'"); $revenue=mysqli_fetch_assoc($r)['t']??0;
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Orders - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?php echo $success;?></div><?php endif;?>
<?php if($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo $error;?></div><?php endif;?>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div><div><div class="stat-val"><?php echo $total_orders;?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div><div class="stat-val"><?php echo $pending;?></div><div class="stat-label">Pending</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-double"></i></div><div><div class="stat-val"><?php echo $delivered;?></div><div class="stat-label">Delivered</div></div></div>
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-dollar-sign"></i></div><div><div class="stat-val">$<?php echo number_format($revenue,0);?></div><div class="stat-label">Revenue</div></div></div>
</div>

<!-- Create Order -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-plus-circle"></i> Create New Order</div></div>
    <div class="panel-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-input" placeholder="Customer full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Product *</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Select Product --</option>
                        <?php while($p=mysqli_fetch_assoc($products)): ?>
                        <option value="<?php echo $p['id'];?>"><?php echo htmlspecialchars($p['name']);?> — $<?php echo number_format($p['price'],2);?> (Stock: <?php echo $p['stock']??0;?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity *</label>
                    <input type="number" name="quantity" class="form-input" value="1" min="1" required>
                </div>
            </div>
            <div style="margin-top:16px;"><button type="submit" name="create_order" class="btn btn-primary"><i class="fas fa-plus"></i> Create Order</button></div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title"><i class="fas fa-list"></i> All Orders</div>
        <span class="badge badge-blue"><?php echo $total_orders;?> orders</span>
    </div>
    <table class="data-table">
        <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php mysqli_data_seek($orders,0); if(mysqli_num_rows($orders)>0): while($o=mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td><strong>#<?php echo $o['id'];?></strong></td>
            <td><?php echo htmlspecialchars($o['customer_name']??'');?></td>
            <td><?php echo htmlspecialchars($o['product_name']??'');?></td>
            <td><?php echo $o['quantity']??0;?></td>
            <td><strong>$<?php echo number_format($o['total_price']??0,2);?></strong></td>
            <td>
                <?php $sc=['Pending'=>'badge-warning','Shipped'=>'badge-info','Delivered'=>'badge-success','Cancelled'=>'badge-danger','Refunded'=>'badge-gray'];?>
                <span class="badge <?php echo $sc[$o['status']]??'badge-gray';?>"><?php echo $o['status'];?></span>
            </td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo $o['order_date'];?></td>
            <td>
                <form method="POST" style="display:inline-flex;gap:6px;align-items:center;">
                    <input type="hidden" name="order_id" value="<?php echo $o['id'];?>">
                    <select name="status" class="form-select" style="padding:5px 8px;font-size:12px;">
                        <?php foreach(['Pending','Shipped','Delivered','Cancelled','Refunded'] as $s): ?>
                        <option <?php echo $o['status']==$s?'selected':'';?>><?php echo $s;?></option>
                        <?php endforeach;?>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-warning btn-sm"><i class="fas fa-save"></i></button>
                </form>
                <a href="?action=delete&id=<?php echo $o['id'];?>" class="btn btn-danger btn-sm" style="margin-left:4px;" onclick="return confirm('Delete order?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8"><div class="empty-state"><i class="fas fa-shopping-bag"></i><p>No orders yet.</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

</div></div></body></html>