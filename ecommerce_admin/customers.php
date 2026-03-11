<?php
// customers.php - Customer Account Management
session_start();
require_once 'config.php';

// Check if user is logged in
check_login();

// Variables
$statusMsg = "";
$errorMsg = "";

// Handle Add New Customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $name = sanitize_input($_POST['customer_name']);
    $email = sanitize_input($_POST['customer_email']);
    $phone = sanitize_input($_POST['customer_phone']);
    $address = sanitize_input($_POST['customer_address']);
    
    // Validation
    if (empty($name)) {
        $errorMsg = "Customer name is required!";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Valid email is required!";
    } else {
        // Check if email already exists
        $check_query = "SELECT * FROM customers WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $errorMsg = "Email already exists!";
        } else {
            $join_date = date('Y-m-d');
            $insert_query = "INSERT INTO customers (name, email, phone, address, join_date) 
                            VALUES ('$name', '$email', '$phone', '$address', '$join_date')";
            
            if (mysqli_query($conn, $insert_query)) {
                $statusMsg = "Customer added successfully!";
            } else {
                $errorMsg = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Handle Update Account Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $customer_id = (int)$_POST['customer_id'];
    $new_status = sanitize_input($_POST['status']);
    
    $update_query = "UPDATE customers SET status = '$new_status' WHERE id = $customer_id";
    
    if (mysqli_query($conn, $update_query)) {
        $statusMsg = "Account status updated successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Handle Verify Account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_account'])) {
    $customer_id = (int)$_POST['customer_id'];
    
    $update_query = "UPDATE customers SET verified = 'Yes' WHERE id = $customer_id";
    
    if (mysqli_query($conn, $update_query)) {
        $statusMsg = "Customer account verified successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Handle Fix Login Issues (Reset Password)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $customer_id = (int)$_POST['customer_id'];
    
    $customer_query = "SELECT email FROM customers WHERE id = $customer_id";
    $customer_result = mysqli_query($conn, $customer_query);
    
    if (mysqli_num_rows($customer_result) == 1) {
        $customer = mysqli_fetch_assoc($customer_result);
        $statusMsg = "Password reset link sent to " . htmlspecialchars($customer['email']);
    }
}

// Handle Delete Customer
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM customers WHERE id = $delete_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $statusMsg = "Customer deleted successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Fetch all customers
$customers_query = "SELECT * FROM customers ORDER BY id DESC";
$customers_result = mysqli_query($conn, $customers_query);

// Calculate stats
$total_customers = mysqli_num_rows($customers_result);
$active_query = "SELECT COUNT(*) as count FROM customers WHERE status = 'Active'";
$active_result = mysqli_query($conn, $active_query);
$active_count = mysqli_fetch_assoc($active_result)['count'];

$verified_query = "SELECT COUNT(*) as count FROM customers WHERE verified = 'Yes'";
$verified_result = mysqli_query($conn, $verified_query);
$verified_count = mysqli_fetch_assoc($verified_result)['count'];

$revenue_query = "SELECT SUM(total_spent) as total FROM customers";
$revenue_result = mysqli_query($conn, $revenue_query);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Account Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        
        .header { background-color: #2c3e50; color: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; }
        .header p { font-size: 14px; color: #bdc3c7; margin-top: 5px; }
        .nav-links { float: right; }
        .nav-links a { color: white; text-decoration: none; padding: 10px 20px; margin-left: 10px; background-color: #3498db; border-radius: 5px; display: inline-block; }
        .nav-links a.active { background-color: #2980b9; }
        .nav-links a.logout { background-color: #e74c3c; }
        .nav-links a:hover { opacity: 0.8; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-title { font-size: 20px; color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        
        .success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { font-size: 14px; margin-bottom: 10px; opacity: 0.9; }
        .stat-card .number { font-size: 32px; font-weight: bold; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 14px; }
        input[type="text"], input[type="email"], input[type="tel"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { resize: vertical; min-height: 80px; font-family: Arial, sans-serif; }
        
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s; }
        .btn-primary { background-color: #3498db; color: white; }
        .btn-primary:hover { background-color: #2980b9; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #34495e; color: white; font-weight: bold; }
        tr:hover { background-color: #f8f9fa; }
        
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-active { background-color: #28a745; color: white; }
        .badge-suspended { background-color: #dc3545; color: white; }
        .badge-inactive { background-color: #6c757d; color: white; }
        .badge-verified { background-color: #17a2b8; color: white; }
        .badge-unverified { background-color: #ffc107; color: #333; }
        
        select.inline { padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
        button.small { padding: 6px 12px; font-size: 13px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; margin: 2px; }
        button.small:hover { background: #0056b3; }
        button.success { background: #28a745; }
        button.success:hover { background: #218838; }
        button.warning { background: #ffc107; color: #333; }
        button.warning:hover { background: #e0a800; }
        button.danger { background: #dc3545; }
        button.danger:hover { background: #c82333; }
        
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
    </style>
    
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this customer? This action cannot be undone.");
        }
    </script>
</head>
<body>

<div class="header">
    <div class="nav-links">
        <a href="inventory.php">Inventory</a>
        <a href="orders.php">Orders</a>
        <a href="customers.php" class="active">Customers</a>
        <a href="categories.php">Categories</a>
        <a href="analytics.php">Analytics</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
    <h1>Customer Account Management</h1>
    <p>Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></p>
</div>

<div class="container">
    
    <?php if (!empty($statusMsg)): ?>
        <div class="success"><?php echo $statusMsg; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errorMsg)): ?>
        <div class="error"><?php echo $errorMsg; ?></div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>TOTAL CUSTOMERS</h3>
            <div class="number"><?php echo $total_customers; ?></div>
        </div>
        <div class="stat-card">
            <h3>ACTIVE ACCOUNTS</h3>
            <div class="number"><?php echo $active_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>VERIFIED ACCOUNTS</h3>
            <div class="number"><?php echo $verified_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>TOTAL REVENUE</h3>
            <div class="number">$<?php echo number_format($total_revenue, 2); ?></div>
        </div>
    </div>
    
    <!-- Add New Customer -->
    <div class="card">
        <h2 class="card-title">Add New Customer</h2>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Customer Name *</label>
                    <input type="text" name="customer_name" placeholder="Enter customer name" required>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="customer_email" placeholder="customer@example.com" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="customer_phone" placeholder="+880 1712-345678">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="customer_address" placeholder="Enter customer address"></textarea>
                </div>
            </div>
            <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
        </form>
    </div>
    
    <!-- Customer List -->
    <div class="card">
        <h2 class="card-title">Customer Database</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($customers_result, 0);
                if (mysqli_num_rows($customers_result) > 0): 
                ?>
                    <?php while ($customer = mysqli_fetch_assoc($customers_result)): ?>
                    <tr>
                        <td><strong>#<?php echo $customer['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo $customer['join_date']; ?></td>
                        
                        <!-- Account Status -->
                        <td>
                            <?php 
                                $statusBadge = 'badge-active';
                                if ($customer['status'] == 'Suspended') $statusBadge = 'badge-suspended';
                                elseif ($customer['status'] == 'Inactive') $statusBadge = 'badge-inactive';
                            ?>
                            <span class="badge <?php echo $statusBadge; ?>"><?php echo $customer['status']; ?></span>
                            <form method="post" style="margin-top: 5px;">
                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                <select name="status" class="inline">
                                    <option value="Active" <?php echo $customer['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Suspended" <?php echo $customer['status'] == 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    <option value="Inactive" <?php echo $customer['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <button name="update_status" class="small">Update</button>
                            </form>
                        </td>
                        
                        <!-- Verification Status -->
                        <td>
                            <span class="badge <?php echo $customer['verified'] == 'Yes' ? 'badge-verified' : 'badge-unverified'; ?>">
                                <?php echo $customer['verified']; ?>
                            </span>
                            <?php if ($customer['verified'] == 'No'): ?>
                                <form method="post" style="margin-top: 5px;">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                    <button name="verify_account" class="small success">Verify</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        
                        <td><?php echo $customer['total_orders']; ?></td>
                        <td>$<?php echo number_format($customer['total_spent'], 2); ?></td>
                        
                        <!-- Actions -->
                        <td>
                            <div class="action-buttons">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                    <button name="reset_password" class="small warning" title="Reset Password">Reset Password</button>
                                </form>
                                <button onclick="if(confirmDelete()) window.location.href='customers.php?action=delete&id=<?php echo $customer['id']; ?>'" class="small danger">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" style="text-align: center;">No customers found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>