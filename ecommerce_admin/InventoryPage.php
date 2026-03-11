<?php
// inventory.php - Product & Inventory Management
session_start();
require_once 'config.php';

// Check if user is logged in
check_login();

// Initialize variables
$product_name = $product_price = $stock_quantity = $discount = "";
$nameErr = $priceErr = $stockErr = "";
$success = "";

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM products WHERE id = $delete_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $success = "Product deleted successfully!";
    } else {
        $success = "Error deleting product: " . mysqli_error($conn);
    }
}

// Handle form submission (Add Product)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    
    // Validate product name
    if (empty($_POST["product_name"])) {
        $nameErr = "Product name is required";
    } else {
        $product_name = sanitize_input($_POST["product_name"]);
    }
    
    // Validate price
    if (empty($_POST["product_price"])) {
        $priceErr = "Price is required";
    } elseif (!is_numeric($_POST["product_price"]) || $_POST["product_price"] < 0) {
        $priceErr = "Please enter a valid price";
    } else {
        $product_price = (float)$_POST["product_price"];
    }
    
    // Validate stock quantity
    if (empty($_POST["stock_quantity"])) {
        $stockErr = "Stock quantity is required";
    } elseif (!is_numeric($_POST["stock_quantity"]) || $_POST["stock_quantity"] < 0) {
        $stockErr = "Please enter a valid quantity";
    } else {
        $stock_quantity = (int)$_POST["stock_quantity"];
    }
    
    $discount = isset($_POST["discount"]) ? (int)$_POST["discount"] : 0;
    $visibility = isset($_POST["visibility"]) ? 1 : 0;
    
    // IF NO ERRORS, ADD TO DATABASE
    if (empty($nameErr) && empty($priceErr) && empty($stockErr)) {
        $insert_query = "INSERT INTO products (name, price, stock, discount, visibility) 
                        VALUES ('$product_name', $product_price, $stock_quantity, $discount, $visibility)";
        
        if (mysqli_query($conn, $insert_query)) {
            $success = "Product added successfully!";
            // Clear form
            $product_name = $product_price = $stock_quantity = "";
        } else {
            $nameErr = "Error: " . mysqli_error($conn);
        }
    }
}

// Fetch all products from database
$products_query = "SELECT * FROM products ORDER BY id DESC";
$products_result = mysqli_query($conn, $products_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product & Inventory Management</title>
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
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 14px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .error { color: #e74c3c; font-size: 13px; margin-top: 5px; }
        .success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn-primary { background-color: #3498db; color: white; }
        .btn-danger { background-color: #e74c3c; color: white; }
        .btn-success { background-color: #27ae60; color: white; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th { background-color: #34495e; color: white; padding: 12px; text-align: left; }
        .product-table td { padding: 12px; border-bottom: 1px solid #ddd; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .action-btns { display: flex; gap: 5px; }
        .btn-small { padding: 5px 12px; font-size: 13px; }
        .checkbox-group { margin-top: 10px; }
        .checkbox-group label { font-weight: normal; }
    </style>
</head>
<body>
    <div class="header">
        <div class="nav-links">
            <a href="inventory.php" class="active">Inventory</a>
            <a href="orders.php">Orders</a>
            <a href="customers.php">Customers</a>
            <a href="categories.php">Categories</a>
            <a href="analytics.php">Analytics</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <h1>Product & Inventory Management</h1>
        <p>Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></p>
    </div>
    
    <div class="container">
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2 class="card-title">Add New Product</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
                        <?php if (!empty($nameErr)): ?>
                            <div class="error"><?php echo $nameErr; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" step="0.01" name="product_price" value="<?php echo htmlspecialchars($product_price); ?>">
                        <?php if (!empty($priceErr)): ?>
                            <div class="error"><?php echo $priceErr; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($stock_quantity); ?>">
                        <?php if (!empty($stockErr)): ?>
                            <div class="error"><?php echo $stockErr; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Discount (%)</label>
                        <input type="number" name="discount" value="0">
                    </div>
                </div>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="visibility" checked> Make product visible</label>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Product Inventory List</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Discount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($products_result) > 0): ?>
                        <?php while ($item = mysqli_fetch_assoc($products_result)): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <?php 
                                    $stockClass = ($item['stock'] > 10) ? 'badge-success' : (($item['stock'] > 0) ? 'badge-warning' : 'badge-danger');
                                ?>
                                <span class="badge <?php echo $stockClass; ?>"><?php echo $item['stock']; ?></span>
                            </td>
                            <td><?php echo $item['discount']; ?>%</td>
                            <td>
                                <span class="badge <?php echo $item['visibility'] ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $item['visibility'] ? 'Visible' : 'Hidden'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-danger btn-small" onclick="deleteProduct(<?php echo $item['id']; ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center;">No products available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        function deleteProduct(id) {
            if (confirm("Are you sure you want to delete this product?")) {
                window.location.href = "inventory.php?action=delete&id=" + id;
            }
        }
    </script>
</body>
</html>