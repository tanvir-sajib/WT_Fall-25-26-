<?php
// analytics.php - Sales, Orders & Analytics Dashboard
session_start();
require_once 'config.php';

// Check if user is logged in
check_login();

// Calculate Total Orders
$total_orders_query = "SELECT COUNT(*) as count FROM orders";
$total_orders_result = mysqli_query($conn, $total_orders_query);
$totalOrders = mysqli_fetch_assoc($total_orders_result)['count'];

// Calculate Total Sales (Units)
$total_sales_query = "SELECT SUM(quantity) as total FROM orders WHERE status = 'Delivered'";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$totalSales = mysqli_fetch_assoc($total_sales_result)['total'] ?? 0;

// Calculate Total Revenue
$total_revenue_query = "SELECT SUM(total_price) as total FROM orders WHERE status = 'Delivered'";
$total_revenue_result = mysqli_query($conn, $total_revenue_query);
$totalRevenue = mysqli_fetch_assoc($total_revenue_result)['total'] ?? 0;

// Get Pending Orders
$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);
$pendingOrders = mysqli_fetch_assoc($pending_orders_result)['count'];

// Get Top Selling Products
$top_products_query = "SELECT product_name, SUM(quantity) as total_sold, SUM(total_price) as revenue 
                       FROM orders 
                       WHERE status = 'Delivered' 
                       GROUP BY product_name 
                       ORDER BY total_sold DESC 
                       LIMIT 10";
$top_products_result = mysqli_query($conn, $top_products_query);

// Daily orders (today)
$today = date('Y-m-d');
$daily_query = "SELECT COUNT(*) as count FROM orders WHERE order_date = '$today'";
$daily_result = mysqli_query($conn, $daily_query);
$dailyOrders = mysqli_fetch_assoc($daily_result)['count'];

// Weekly orders (last 7 days)
$week_ago = date('Y-m-d', strtotime('-7 days'));
$weekly_query = "SELECT COUNT(*) as count FROM orders WHERE order_date >= '$week_ago'";
$weekly_result = mysqli_query($conn, $weekly_query);
$weeklyOrders = mysqli_fetch_assoc($weekly_result)['count'];

// Monthly orders (last 30 days)
$month_ago = date('Y-m-d', strtotime('-30 days'));
$monthly_query = "SELECT COUNT(*) as count FROM orders WHERE order_date >= '$month_ago'";
$monthly_result = mysqli_query($conn, $monthly_query);
$monthlyOrders = mysqli_fetch_assoc($monthly_result)['count'];

// Recent Orders
$recent_orders_query = "SELECT * FROM orders ORDER BY id DESC LIMIT 10";
$recent_orders_result = mysqli_query($conn, $recent_orders_query);

// Low Stock Products
$low_stock_query = "SELECT * FROM products WHERE stock < 10 AND stock > 0 ORDER BY stock ASC";
$low_stock_result = mysqli_query($conn, $low_stock_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Analytics Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f1f4f9; }
        
        .header { background-color: #2c3e50; color: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; }
        .header p { font-size: 14px; color: #bdc3c7; margin-top: 5px; }
        .nav-links { float: right; }
        .nav-links a { color: white; text-decoration: none; padding: 10px 20px; margin-left: 10px; background-color: #3498db; border-radius: 5px; display: inline-block; }
        .nav-links a.active { background-color: #2980b9; }
        .nav-links a.logout { background-color: #e74c3c; }
        .nav-links a:hover { opacity: 0.8; }
        
        .dashboard { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 25px; border-radius: 10px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .card h2 { color: #2f80ed; font-size: 36px; margin: 0; }
        .card p { color: #666; margin-top: 10px; font-size: 16px; }
        
        .section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .section h3 { color: #2c3e50; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #3498db; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f8f9fa; }
        
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-pending { background-color: #ffc107; color: #333; }
        .badge-shipped { background-color: #17a2b8; color: white; }
        .badge-delivered { background-color: #28a745; color: white; }
        
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
    </style>
</head>
<body>

<div class="header">
    <div class="nav-links">
        <a href="inventory.php">Inventory</a>
        <a href="orders.php">Orders</a>
        <a href="customers.php">Customers</a>
        <a href="categories.php">Categories</a>
        <a href="analytics.php" class="active">Analytics</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
    <h1>Sales, Orders & Analytics Dashboard</h1>
    <p>Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></p>
</div>

<div class="dashboard">

    <!-- Summary Cards -->
    <div class="cards">
        <div class="card">
            <h2><?php echo $totalOrders; ?></h2>
            <p>Total Orders</p>
        </div>
        <div class="card">
            <h2><?php echo $totalSales; ?></h2>
            <p>Total Sales (Units)</p>
        </div>
        <div class="card">
            <h2>$<?php echo number_format($totalRevenue, 2); ?></h2>
            <p>Total Revenue</p>
        </div>
        <div class="card">
            <h2><?php echo $pendingOrders; ?></h2>
            <p>Pending Orders</p>
        </div>
    </div>

    <!-- Analytics Grid -->
    <div class="analytics-grid">
        <!-- Daily/Weekly/Monthly Analytics -->
        <div class="section">
            <h3>Time-Based Analytics</h3>
            <table>
                <tr>
                    <td><strong>Daily Orders (Today)</strong></td>
                    <td><span class="badge badge-success"><?php echo $dailyOrders; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Weekly Orders (Last 7 days)</strong></td>
                    <td><span class="badge badge-success"><?php echo $weeklyOrders; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Monthly Orders (Last 30 days)</strong></td>
                    <td><span class="badge badge-success"><?php echo $monthlyOrders; ?></span></td>
                </tr>
            </table>
        </div>

        <!-- Low Stock Alert -->
        <div class="section">
            <h3>⚠️ Low Stock Alert</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($low_stock_result) > 0): ?>
                        <?php while ($product = mysqli_fetch_assoc($low_stock_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>
                                <span class="badge <?php echo $product['stock'] < 5 ? 'badge-danger' : 'badge-warning'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="text-align: center; color: #28a745;">All products have sufficient stock!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="section">
        <h3>🏆 Top-Selling Products</h3>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product Name</th>
                    <th>Units Sold</th>
                    <th>Revenue Generated</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($top_products_result) > 0): ?>
                    <?php 
                    $rank = 1;
                    while ($product = mysqli_fetch_assoc($top_products_result)): 
                    ?>
                    <tr>
                        <td><strong>#<?php echo $rank++; ?></strong></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><span class="badge badge-success"><?php echo $product['total_sold']; ?></span></td>
                        <td><strong>$<?php echo number_format($product['revenue'], 2); ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center;">No sales data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Orders -->
    <div class="section">
        <h3>📦 Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($recent_orders_result) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                    <tr>
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <?php 
                                $badgeClass = 'badge-pending';
                                if ($order['status'] == 'Shipped') $badgeClass = 'badge-shipped';
                                elseif ($order['status'] == 'Delivered') $badgeClass = 'badge-delivered';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $order['status']; ?></span>
                        </td>
                        <td><?php echo $order['order_date']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center;">No orders yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>