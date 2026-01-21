<?php
// orders.php - Order & Fulfillment Management
session_start();

// Initialize inventory if not exists
if (!isset($_SESSION['inventory'])) {
    $_SESSION['inventory'] = [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999.00, 'stock' => 50, 'discount' => 10, 'visibility' => 1],
        ['id' => 2, 'name' => 'Mouse', 'price' => 25.00, 'stock' => 5, 'discount' => 0, 'visibility' => 1],
        ['id' => 3, 'name' => 'Keyboard', 'price' => 45.00, 'stock' => 0, 'discount' => 15, 'visibility' => 0],
    ];
}

// Initialize orders if not exists
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [
        ["id" => 101, "customer" => "Rahim", "product_id" => 1, "product" => "Laptop", "quantity" => 1, "total_price" => 899.10, "status" => "Pending", "delivery" => "Not Assigned", "refund" => "No", "date" => date('Y-m-d')],
        ["id" => 102, "customer" => "Karim", "product_id" => 2, "product" => "Mouse", "quantity" => 2, "total_price" => 50.00, "status" => "Shipped", "delivery" => "On the way", "refund" => "No", "date" => date('Y-m-d')]
    ];
}

// Variables
$statusMsg = "";
$errorMsg = "";

// Handle Create New Order
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_order'])) {
    $customer_name = trim($_POST['customer_name']);
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Validation
    if (empty($customer_name)) {
        $errorMsg = "Customer name is required!";
    } elseif (empty($product_id)) {
        $errorMsg = "Please select a product!";
    } elseif (empty($quantity) || $quantity <= 0) {
        $errorMsg = "Please enter a valid quantity!";
    } else {
        // Find the product
        $selected_product = null;
        foreach ($_SESSION['inventory'] as $product) {
            if ($product['id'] == $product_id) {
                $selected_product = $product;
                break;
            }
        }
        
        if ($selected_product) {
            // Check stock availability
            if ($selected_product['stock'] < $quantity) {
                $errorMsg = "Insufficient stock! Available: " . $selected_product['stock'];
            } else {
                // Calculate total price with discount
                $price_per_item = $selected_product['price'];
                $discount_amount = ($price_per_item * $selected_product['discount']) / 100;
                $final_price_per_item = $price_per_item - $discount_amount;
                $total_price = $final_price_per_item * $quantity;
                
                // Create new order
                $new_order_id = count($_SESSION['orders']) > 0 ? max(array_column($_SESSION['orders'], 'id')) + 1 : 101;
                
                $new_order = [
                    'id' => $new_order_id,
                    'customer' => htmlspecialchars($customer_name),
                    'product_id' => $product_id,
                    'product' => $selected_product['name'],
                    'quantity' => $quantity,
                    'total_price' => $total_price,
                    'status' => 'Pending',
                    'delivery' => 'Not Assigned',
                    'refund' => 'No',
                    'date' => date('Y-m-d')
                ];
                
                $_SESSION['orders'][] = $new_order;
                
                // Update stock in inventory
                foreach ($_SESSION['inventory'] as &$product) {
                    if ($product['id'] == $product_id) {
                        $product['stock'] -= $quantity;
                        break;
                    }
                }
                
                $statusMsg = "Order created successfully! Order ID: #" . $new_order_id;
            }
        } else {
            $errorMsg = "Product not found!";
        }
    }
}

// Handle Update Order Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    foreach ($_SESSION['orders'] as &$order) {
        if ($order['id'] == $order_id) {
            $order['status'] = htmlspecialchars($new_status);
            $statusMsg = "Order #" . $order_id . " status updated to: " . $new_status;
            break;
        }
    }
}

// Handle Update Delivery Info
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_delivery'])) {
    $order_id = $_POST['order_id'];
    $delivery_info = trim($_POST['delivery_info']);
    
    if (!empty($delivery_info)) {
        foreach ($_SESSION['orders'] as &$order) {
            if ($order['id'] == $order_id) {
                $order['delivery'] = htmlspecialchars($delivery_info);
                $statusMsg = "Delivery information updated for Order #" . $order_id;
                break;
            }
        }
    }
}

// Handle Refund/Return
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['refund'])) {
    $order_id = $_POST['order_id'];
    
    foreach ($_SESSION['orders'] as &$order) {
        if ($order['id'] == $order_id) {
            $order['refund'] = "Yes";
            $order['status'] = "Refunded";
            
            // Return stock to inventory
            foreach ($_SESSION['inventory'] as &$product) {
                if ($product['id'] == $order['product_id']) {
                    $product['stock'] += $order['quantity'];
                    break;
                }
            }
            
            $statusMsg = "Refund processed for Order #" . $order_id . ". Stock returned to inventory.";
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order & Fulfillment Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        
        .header { background-color: #343a40; color: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; }
        .header p { font-size: 14px; color: #bdc3c7; margin-top: 5px; }
        .nav-links { float: right; }
        .nav-links a { color: white; text-decoration: none; padding: 10px 20px; margin-left: 10px; background-color: #007bff; border-radius: 5px; display: inline-block; }
        .nav-links a.logout { background-color: #dc3545; }
        .nav-links a:hover { opacity: 0.8; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-title { font-size: 20px; color: #343a40; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        
        .success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 14px; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-primary:hover { background-color: #0056b3; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #f1f1f1; font-weight: bold; }
        
        select.inline, input.inline { padding: 5px; width: auto; display: inline-block; }
        button.small { padding: 6px 12px; font-size: 13px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        button.small:hover { background: #0056b3; }
        button.danger { background: red; }
        button.danger:hover { background: darkred; }
        
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-pending { background-color: #ffc107; color: #333; }
        .badge-shipped { background-color: #17a2b8; color: white; }
        .badge-delivered { background-color: #28a745; color: white; }
        .badge-cancelled { background-color: #6c757d; color: white; }
        .badge-refunded { background-color: #dc3545; color: white; }
    </style>
    
    <script>
        function confirmRefund() {
            return confirm("Are you sure you want to process refund/return? This will return the stock to inventory.");
        }
    </script>
</head>
<body>

<div class="header">
    <div class="nav-links">
        <a href="inventory.php">Inventory</a>
        <a href="orders.php">Orders</a>
        <a href="login.php" class="logout">Logout</a>
    </div>
    <h1>Order & Fulfillment Management</h1>
    <p>Welcome, Admin</p>
</div>

<div class="container">
    
    <?php if (!empty($statusMsg)): ?>
        <div class="success"><?php echo $statusMsg; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errorMsg)): ?>
        <div class="error"><?php echo $errorMsg; ?></div>
    <?php endif; ?>
    
    <!-- Create New Order Form -->
    <div class="card">
        <h2 class="card-title">Create New Order</h2>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Customer Name *</label>
                    <input type="text" name="customer_name" placeholder="Enter customer name" required>
                </div>
                <div class="form-group">
                    <label>Select Product *</label>
                    <select name="product_id" required>
                        <option value="">-- Select Product --</option>
                        <?php foreach ($_SESSION['inventory'] as $product): ?>
                            <?php if ($product['visibility'] == 1 && $product['stock'] > 0): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> 
                                    - $<?php echo number_format($product['price'], 2); ?> 
                                    (Stock: <?php echo $product['stock']; ?>) 
                                    <?php if ($product['discount'] > 0): ?>
                                        [<?php echo $product['discount']; ?>% OFF]
                                    <?php endif; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" min="1" value="1" required>
                </div>
            </div>
            <button type="submit" name="create_order" class="btn btn-primary">Create Order</button>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="card">
        <h2 class="card-title">Order List</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                    <th>Delivery Info</th>
                    <th>Refund/Return</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($_SESSION['orders'])): ?>
                    <?php foreach ($_SESSION['orders'] as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order["id"]; ?></strong></td>
                        <td><?php echo htmlspecialchars($order["customer"]); ?></td>
                        <td><?php echo htmlspecialchars($order["product"]); ?></td>
                        <td><?php echo $order["quantity"]; ?></td>
                        <td>$<?php echo number_format($order["total_price"], 2); ?></td>
                        
                        <!-- Update Order Status -->
                        <td>
                            <?php 
                                $badgeClass = 'badge-pending';
                                if ($order['status'] == 'Shipped') $badgeClass = 'badge-shipped';
                                elseif ($order['status'] == 'Delivered') $badgeClass = 'badge-delivered';
                                elseif ($order['status'] == 'Cancelled') $badgeClass = 'badge-cancelled';
                                elseif ($order['status'] == 'Refunded') $badgeClass = 'badge-refunded';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $order['status']; ?></span>
                            <form method="post" style="margin-top: 5px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="inline">
                                    <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button name="update_status" class="small">Update</button>
                            </form>
                        </td>
                        
                        <!-- Delivery Info -->
                        <td>
                            <div><?php echo htmlspecialchars($order["delivery"]); ?></div>
                            <form method="post" style="margin-top: 5px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="text" name="delivery_info" placeholder="Delivery status" class="inline">
                                <button name="update_delivery" class="small">Save</button>
                            </form>
                        </td>
                        
                        <!-- Refund / Return -->
                        <td>
                            <?php if ($order['refund'] == 'Yes'): ?>
                                <span class="badge badge-refunded">Refunded</span>
                            <?php else: ?>
                                <form method="post" onsubmit="return confirmRefund()">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button class="small danger" name="refund">Process Refund</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align: center;">No orders yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>