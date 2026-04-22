<?php
session_start();
require_once 'audit_log.php';
require_once 'config.php';
check_login();

$page_title = 'Inventory';
$active_nav = 'inventory';
$success = $error = "";

// Delete product
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    verify_admin_csrf($_GET['csrf_token'] ?? '');
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $success = "Product deleted successfully!";
        log_action($conn, 'delete_product', "Deleted product ID: $id");
    } else {
        $error = "Delete failed.";
    }
}

// Add product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    verify_admin_csrf($_POST['csrf_token'] ?? '');

    $name  = sanitize_input($_POST["product_name"] ?? '');
    $price = (float)($_POST["product_price"] ?? 0);
    $stock = (int)($_POST["stock_quantity"] ?? 0);
    $disc  = max(0, min(100, (int)($_POST["discount"] ?? 0)));
    $vis   = isset($_POST["visibility"]) && $_POST["visibility"] == '1' ? 1 : 0;
    $cat   = (int)($_POST["category_id"] ?? 0);
    $desc  = sanitize_input($_POST["description"] ?? '');

    if (empty($name))    { $error = "Product name is required!"; }
    elseif ($price <= 0) { $error = "Valid price required!"; }
    elseif ($stock < 0)  { $error = "Stock cannot be negative."; }
    else {
        // Secure image upload
        $image_name = "";
        $upload_dir = "uploads/products/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        if (!empty($_FILES['product_image']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $ftype = $_FILES['product_image']['type'];
            $fsize = $_FILES['product_image']['size'];

            // Also verify via getimagesize (prevent MIME spoofing)
            $img_info = @getimagesize($_FILES['product_image']['tmp_name']);

            if (!in_array($ftype, $allowed_types) || !$img_info) {
                $error = "Only JPG, PNG, GIF, WEBP images are allowed.";
            } elseif ($fsize > 5 * 1024 * 1024) {
                $error = "Image must be under 5MB.";
            } else {
                $ext        = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                $image_name = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $image_name)) {
                    $error      = "Image upload failed.";
                    $image_name = "";
                }
            }
        }

        if (empty($error)) {
            // Dynamic column check (graceful)
            $col_check = $conn->query("SHOW COLUMNS FROM products");
            $cols = [];
            while ($c = $col_check->fetch_assoc()) $cols[] = $c['Field'];

            $fields = []; $vals = []; $bind_types = ''; $bind_vals = [];

            $fields[] = 'name';        $vals[] = '?'; $bind_types .= 's'; $bind_vals[] = $name;
            $fields[] = 'price';       $vals[] = '?'; $bind_types .= 'd'; $bind_vals[] = $price;
            if (in_array('stock', $cols))       { $fields[] = 'stock';       $vals[] = '?'; $bind_types .= 'i'; $bind_vals[] = $stock; }
            if (in_array('quantity', $cols))    { $fields[] = 'quantity';    $vals[] = '?'; $bind_types .= 'i'; $bind_vals[] = $stock; }
            if (in_array('discount', $cols))    { $fields[] = 'discount';    $vals[] = '?'; $bind_types .= 'i'; $bind_vals[] = $disc; }
            if (in_array('visibility', $cols))  { $fields[] = 'visibility';  $vals[] = '?'; $bind_types .= 'i'; $bind_vals[] = $vis; }
            if (in_array('category_id', $cols) && $cat) { $fields[] = 'category_id'; $vals[] = '?'; $bind_types .= 'i'; $bind_vals[] = $cat; }
            if (in_array('description', $cols) && $desc) { $fields[] = 'description'; $vals[] = '?'; $bind_types .= 's'; $bind_vals[] = $desc; }
            if (in_array('image', $cols) && $image_name) { $fields[] = 'image'; $vals[] = '?'; $bind_types .= 's'; $bind_vals[] = $image_name; }

            $sql  = "INSERT INTO products (" . implode(',', $fields) . ") VALUES (" . implode(',', $vals) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($bind_types, ...$bind_vals);

            if ($stmt->execute()) {
                $success = "Product '$name' added successfully!";
                log_action($conn, 'add_product', "Added: $name | Price: $price | Stock: $stock");
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}

$products   = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$total_products = $products->num_rows;
$r = $conn->query("SELECT COUNT(*) as c FROM products WHERE stock < 5"); $low_stock = $r->fetch_assoc()['c'] ?? 0;
$csrf = $_SESSION['admin_csrf'];
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Inventory - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?php echo htmlspecialchars($success);?></div><?php endif;?>
<?php if($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error);?></div><?php endif;?>

<div class="stat-grid">
    <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-boxes"></i></div><div><div class="stat-val"><?php echo $total_products;?></div><div class="stat-label">Total Products</div></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div><div><div class="stat-val"><?php echo $low_stock;?></div><div class="stat-label">Low Stock (&lt;5)</div></div></div>
</div>

<!-- Add Product -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-plus-circle"></i> Add New Product</div></div>
    <div class="panel-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf;?>">
            <div class="form-grid">
                <div class="form-group"><label class="form-label">Product Name *</label><input type="text" name="product_name" class="form-input" placeholder="e.g. Samsung Galaxy S24" required></div>
                <div class="form-group"><label class="form-label">Price (৳) *</label><input type="number" step="0.01" min="0.01" name="product_price" class="form-input" placeholder="0.00" required></div>
                <div class="form-group"><label class="form-label">Stock Quantity *</label><input type="number" min="0" name="stock_quantity" class="form-input" placeholder="0" required></div>
                <div class="form-group"><label class="form-label">Discount (%)</label><input type="number" name="discount" class="form-input" value="0" min="0" max="100"></div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Select Category --</option>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id'];?>"><?php echo htmlspecialchars($cat['name']);?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Visibility</label>
                    <select name="visibility" class="form-select">
                        <option value="1">Visible</option>
                        <option value="0">Hidden</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Product Image (JPG/PNG/WEBP · Max 5MB)</label>
                    <div class="upload-zone" onclick="document.getElementById('product_image').click()">
                        <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImg(event,'product-preview')">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Click to upload image</div>
                        <div class="upload-hint">JPG, PNG, WEBP · Max 5MB · Recommended 500×500px</div>
                        <img id="product-preview" src="" alt="" style="display:none;max-width:120px;margin-top:10px;border-radius:8px;">
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;"><button type="submit" name="add_product" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</button></div>
        </form>
    </div>
</div>

<!-- Product List -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-list"></i> Product List</div><span class="badge badge-purple"><?php echo $total_products;?> products</span></div>
    <table class="data-table">
        <thead><tr><th>#</th><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Discount</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if($products->num_rows > 0): ?>
        <?php mysqli_data_seek($products, 0); while($p = $products->fetch_assoc()): ?>
        <tr>
            <td><span style="color:var(--text-3);font-size:12px;">#<?php echo $p['id'];?></span></td>
            <td>
                <?php $img=$p['image']??''; $path='uploads/products/'.$img;
                if(!empty($img) && file_exists($path)): ?>
                    <img src="<?php echo htmlspecialchars($path);?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                <?php else: ?><div style="width:44px;height:44px;background:var(--surface2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;">📦</div><?php endif;?>
            </td>
            <td><strong><?php echo htmlspecialchars($p['name']);?></strong></td>
            <td><span style="font-size:12px;color:var(--text-3);"><?php echo htmlspecialchars($p['cat_name']??'—');?></span></td>
            <td><strong>৳<?php echo number_format($p['price'],2);?></strong></td>
            <td>
                <?php $s=$p['stock']??$p['quantity']??0;
                $sc = $s>10?'badge-success':($s>0?'badge-warning':'badge-danger'); ?>
                <span class="badge <?php echo $sc;?>"><?php echo $s;?></span>
            </td>
            <td><?php echo $p['discount']??0;?>%</td>
            <td><span class="badge <?php echo ($p['visibility']??0)?'badge-success':'badge-gray';?>"><?php echo ($p['visibility']??0)?'Visible':'Hidden';?></span></td>
            <td>
                <a href="?action=delete&id=<?php echo $p['id'];?>&csrf_token=<?php echo $csrf;?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete \'<?php echo addslashes($p['name']);?>\'?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="9"><div class="empty-state"><i class="fas fa-boxes"></i><p>No products yet. Add your first product!</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div></div>
<script>
function previewImg(e,id){const img=document.getElementById(id);const f=e.target.files[0];if(f){img.src=URL.createObjectURL(f);img.style.display='block';}}
</script>
</body></html>