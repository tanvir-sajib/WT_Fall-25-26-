<?php
session_start();

$inventory = $_SESSION['inventory'] ?? [];

$totalOrders = count($inventory);
$totalSales = 0;
$totalRevenue = 0;
$productSales = [];

foreach ($inventory as $item) {

    // SAFETY CHECK (prevents warnings)
    $status = $item['status'] ?? "Pending";

    if ($status === "Delivered") {
        $qty = $item['qty'] ?? 0;
        $price = $item['price'] ?? 0;

        $totalSales += $qty;
        $totalRevenue += ($price * $qty);

        if (!isset($productSales[$item['name']])) {
            $productSales[$item['name']] = 0;
        }
        $productSales[$item['name']] += $qty;
    }
}

arsort($productSales);
$topSelling = !empty($productSales) ? array_key_first($productSales) : "N/A";
?>

<!DOCTYPE html>
<html>
<head>
<title>Sales & Analytics Dashboard</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f1f4f9;
    margin: 0;
}

.dashboard {
    width: 90%;
    margin: 30px auto;
}

h1 {
    margin-bottom: 20px;
}

/* STAT CARDS */
.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    flex: 1;
    background: #ffffff;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.card h2 {
    color: #2f80ed;
    font-size: 32px;
    margin: 0;
}

/* SECTIONS */
.section {
    background: #ffffff;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th {
    background: #2f80ed;
    color: white;
    padding: 10px;
}

td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

/* LINKS */
.links a {
    margin-right: 15px;
    color: #2f80ed;
    text-decoration: none;
    font-weight: bold;
}
</style>

</head>
<body>

<div class="dashboard">

<h1>Sales, Orders & Analytics Dashboard</h1>

<!-- SUMMARY -->
<div class="cards">
    <div class="card">
        <h2><?= $totalOrders ?></h2>
        <p>Total Orders</p>
    </div>
    <div class="card">
        <h2><?= $totalSales ?></h2>
        <p>Total Sales (Units)</p>
    </div>
    <div class="card">
        <h2>$<?= $totalRevenue ?></h2>
        <p>Total Revenue</p>
    </div>
</div>

<!-- ANALYTICS -->
<div class="section">
<h3>Daily / Weekly / Monthly Analytics</h3>
<p><b>Note:</b> Database will be used later for real-time analytics.</p>

<ul>
    <li>Daily Orders: <?= $totalOrders ?></li>
    <li>Weekly Orders: <?= $totalOrders ?></li>
    <li>Monthly Orders: <?= $totalOrders ?></li>
</ul>
</div>

<!-- TOP SELLING -->
<div class="section">
<h3>Top-Selling Products</h3>

<table>
<tr>
<th>Product Name</th>
<th>Units Sold</th>
</tr>

<?php if (!empty($productSales)): ?>
    <?php foreach ($productSales as $name => $qty): ?>
        <tr>
            <td><?= $name ?></td>
            <td><?= $qty ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="2">No sales yet</td>
    </tr>
<?php endif; ?>
</table>

<p><b>Top Selling Product:</b> <?= $topSelling ?></p>
</div>

<!-- NAVIGATION -->
<div class="section links">
    <a href="InventoryPage.php">Inventory Page</a>
    <a href="OrderManagement.php">Order Management</a>
</div>

</div>

</body>
</html>
