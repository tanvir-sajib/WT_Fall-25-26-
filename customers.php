<?php
session_start();
require_once 'config.php';
check_login();

$page_title = 'Customers';
$active_nav = 'customers';
$success = $error = "";

// Add customer
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['add_customer'])) {
    $name    = sanitize_input($_POST['customer_name']);
    $email   = sanitize_input($_POST['customer_email']);
    $phone   = sanitize_input($_POST['customer_phone']);
    $address = sanitize_input($_POST['customer_address']);
    if(empty($name)) $error="Name required!";
    elseif(empty($email)||!filter_var($email,FILTER_VALIDATE_EMAIL)) $error="Valid email required!";
    else {
        $chk=mysqli_query($conn,"SELECT id FROM customers WHERE email='$email'");
        if(mysqli_num_rows($chk)>0) $error="Email already exists!";
        else {
            $date=date('Y-m-d');
            $q="INSERT INTO customers (name,email,phone,address,join_date) VALUES ('$name','$email','$phone','$address','$date')";
            if(mysqli_query($conn,$q)) $success="Customer added!";
            else $error="Error: ".mysqli_error($conn);
        }
    }
}

// Update status
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['update_status'])) {
    $cid=(int)$_POST['customer_id']; $ns=sanitize_input($_POST['status']);
    if(mysqli_query($conn,"UPDATE customers SET status='$ns' WHERE id=$cid")) $success="Status updated!";
    else $error="Error: ".mysqli_error($conn);
}

// Delete
if(isset($_GET['action'])&&$_GET['action']=='delete'&&isset($_GET['id'])){
    $id=(int)$_GET['id'];
    if(mysqli_query($conn,"DELETE FROM customers WHERE id=$id")) $success="Customer deleted!";
    else $error="Error: ".mysqli_error($conn);
}

$customers = mysqli_query($conn,"SELECT * FROM customers ORDER BY id DESC");
$total = mysqli_num_rows($customers);
$r=mysqli_query($conn,"SELECT COUNT(*) as c FROM customers WHERE status='Active'"); $active=mysqli_fetch_assoc($r)['c']??0;
$r=mysqli_query($conn,"SELECT COUNT(*) as c FROM customers WHERE status='Suspended'"); $suspended=mysqli_fetch_assoc($r)['c']??0;
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Customers - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?php echo $success;?></div><?php endif;?>
<?php if($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo $error;?></div><?php endif;?>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-users"></i></div><div><div class="stat-val"><?php echo $total;?></div><div class="stat-label">Total Customers</div></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-user-check"></i></div><div><div class="stat-val"><?php echo $active;?></div><div class="stat-label">Active</div></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-user-slash"></i></div><div><div class="stat-val"><?php echo $suspended;?></div><div class="stat-label">Suspended</div></div></div>
</div>

<!-- Add Customer -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-user-plus"></i> Add New Customer</div></div>
    <div class="panel-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="customer_name" class="form-input" placeholder="Full name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="customer_email" class="form-input" placeholder="email@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="customer_phone" class="form-input" placeholder="+880...">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="customer_address" class="form-input" placeholder="City, Country">
                </div>
            </div>
            <div style="margin-top:16px;"><button type="submit" name="add_customer" class="btn btn-primary"><i class="fas fa-plus"></i> Add Customer</button></div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title"><i class="fas fa-users"></i> All Customers</div>
        <span class="badge badge-blue"><?php echo $total;?> total</span>
    </div>
    <table class="data-table">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php mysqli_data_seek($customers,0); if(mysqli_num_rows($customers)>0): while($c=mysqli_fetch_assoc($customers)): ?>
        <tr>
            <td style="color:var(--text-3);font-size:12px;">#<?php echo $c['id'];?></td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:700;flex-shrink:0;"><?php echo strtoupper(substr($c['name'],0,1));?></div>
                    <strong><?php echo htmlspecialchars($c['name']);?></strong>
                </div>
            </td>
            <td style="font-size:13px;"><?php echo htmlspecialchars($c['email']);?></td>
            <td style="font-size:13px;"><?php echo htmlspecialchars($c['phone']??'—');?></td>
            <td style="font-size:13px;"><?php echo htmlspecialchars($c['address']??'—');?></td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo $c['join_date']??'—';?></td>
            <td>
                <?php $sc=['Active'=>'badge-success','Suspended'=>'badge-danger','Inactive'=>'badge-gray'];?>
                <span class="badge <?php echo $sc[$c['status']??'Active']??'badge-gray';?>"><?php echo $c['status']??'Active';?></span>
            </td>
            <td>
                <div style="display:flex;gap:6px;align-items:center;">
                    <form method="POST" style="display:inline-flex;gap:4px;align-items:center;">
                        <input type="hidden" name="customer_id" value="<?php echo $c['id'];?>">
                        <select name="status" class="form-select" style="padding:5px 8px;font-size:12px;">
                            <?php foreach(['Active','Suspended','Inactive'] as $s): ?>
                            <option <?php echo ($c['status']??'')==$s?'selected':'';?>><?php echo $s;?></option>
                            <?php endforeach;?>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-warning btn-sm"><i class="fas fa-save"></i></button>
                    </form>
                    <a href="?action=delete&id=<?php echo $c['id'];?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete customer?')"><i class="fas fa-trash"></i></a>
                </div>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="8"><div class="empty-state"><i class="fas fa-users"></i><p>No customers yet.</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

</div></div></body></html>