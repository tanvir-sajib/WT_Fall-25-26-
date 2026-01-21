<?php
// categories.php - Category & Storefront Content Management
session_start();

// Initialize categories if not exists
if (!isset($_SESSION['categories'])) {
    $_SESSION['categories'] = [
        ['id' => 1, 'name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets', 'status' => 'Active', 'product_count' => 15],
        ['id' => 2, 'name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel', 'status' => 'Active', 'product_count' => 25],
        ['id' => 3, 'name' => 'Books', 'slug' => 'books', 'description' => 'Books and literature', 'status' => 'Inactive', 'product_count' => 8]
    ];
}

// Initialize homepage content if not exists
if (!isset($_SESSION['homepage'])) {
    $_SESSION['homepage'] = [
        'hero_title' => 'Welcome to Our Store',
        'hero_subtitle' => 'Find the best products at amazing prices',
        'hero_button_text' => 'Shop Now',
        'featured_title' => 'Featured Products',
        'promo_text' => 'Get 20% OFF on all products this week!'
    ];
}

// Initialize promotional banners if not exists
if (!isset($_SESSION['banners'])) {
    $_SESSION['banners'] = [
        ['id' => 1, 'title' => 'Summer Sale', 'description' => 'Up to 50% off on selected items', 'status' => 'Active', 'link' => '/sale'],
        ['id' => 2, 'title' => 'New Arrivals', 'description' => 'Check out our latest products', 'status' => 'Active', 'link' => '/new']
    ];
}

// Variables
$statusMsg = "";
$errorMsg = "";

// Handle Add Category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $cat_name = trim($_POST['category_name']);
    $cat_desc = trim($_POST['category_description']);
    
    if (empty($cat_name)) {
        $errorMsg = "Category name is required!";
    } else {
        // Create slug from name
        $slug = strtolower(str_replace(' ', '-', $cat_name));
        
        $new_id = count($_SESSION['categories']) > 0 ? max(array_column($_SESSION['categories'], 'id')) + 1 : 1;
        
        $new_category = [
            'id' => $new_id,
            'name' => htmlspecialchars($cat_name),
            'slug' => htmlspecialchars($slug),
            'description' => htmlspecialchars($cat_desc),
            'status' => 'Active',
            'product_count' => 0
        ];
        
        $_SESSION['categories'][] = $new_category;
        $statusMsg = "Category added successfully!";
    }
}

// Handle Update Category Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category_status'])) {
    $cat_id = $_POST['category_id'];
    $new_status = $_POST['category_status'];
    
    foreach ($_SESSION['categories'] as &$category) {
        if ($category['id'] == $cat_id) {
            $category['status'] = htmlspecialchars($new_status);
            $statusMsg = "Category status updated!";
            break;
        }
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] == 'delete_category' && isset($_GET['id'])) {
    $delete_id = $_GET['id'];
    foreach ($_SESSION['categories'] as $key => $category) {
        if ($category['id'] == $delete_id) {
            unset($_SESSION['categories'][$key]);
            $_SESSION['categories'] = array_values($_SESSION['categories']);
            $statusMsg = "Category deleted successfully!";
            break;
        }
    }
}

// Handle Update Homepage Content
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_homepage'])) {
    $_SESSION['homepage']['hero_title'] = htmlspecialchars(trim($_POST['hero_title']));
    $_SESSION['homepage']['hero_subtitle'] = htmlspecialchars(trim($_POST['hero_subtitle']));
    $_SESSION['homepage']['hero_button_text'] = htmlspecialchars(trim($_POST['hero_button_text']));
    $_SESSION['homepage']['featured_title'] = htmlspecialchars(trim($_POST['featured_title']));
    $_SESSION['homepage']['promo_text'] = htmlspecialchars(trim($_POST['promo_text']));
    
    $statusMsg = "Homepage content updated successfully!";
}

// Handle Add Promotional Banner
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_banner'])) {
    $banner_title = trim($_POST['banner_title']);
    $banner_desc = trim($_POST['banner_description']);
    $banner_link = trim($_POST['banner_link']);
    
    if (empty($banner_title)) {
        $errorMsg = "Banner title is required!";
    } else {
        $new_id = count($_SESSION['banners']) > 0 ? max(array_column($_SESSION['banners'], 'id')) + 1 : 1;
        
        $new_banner = [
            'id' => $new_id,
            'title' => htmlspecialchars($banner_title),
            'description' => htmlspecialchars($banner_desc),
            'status' => 'Active',
            'link' => htmlspecialchars($banner_link)
        ];
        
        $_SESSION['banners'][] = $new_banner;
        $statusMsg = "Promotional banner added successfully!";
    }
}

// Handle Update Banner Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_banner_status'])) {
    $banner_id = $_POST['banner_id'];
    $new_status = $_POST['banner_status'];
    
    foreach ($_SESSION['banners'] as &$banner) {
        if ($banner['id'] == $banner_id) {
            $banner['status'] = htmlspecialchars($new_status);
            $statusMsg = "Banner status updated!";
            break;
        }
    }
}

// Handle Delete Banner
if (isset($_GET['action']) && $_GET['action'] == 'delete_banner' && isset($_GET['id'])) {
    $delete_id = $_GET['id'];
    foreach ($_SESSION['banners'] as $key => $banner) {
        if ($banner['id'] == $delete_id) {
            unset($_SESSION['banners'][$key]);
            $_SESSION['banners'] = array_values($_SESSION['banners']);
            $statusMsg = "Banner deleted successfully!";
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
    <title>Category & Storefront Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        
        .header { background-color: #2c3e50; color: white; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; }
        .header p { font-size: 14px; color: #bdc3c7; margin-top: 5px; }
        .nav-links { float: right; }
        .nav-links a { color: white; text-decoration: none; padding: 10px 20px; margin-left: 10px; background-color: #3498db; border-radius: 5px; display: inline-block; }
        .nav-links a.logout { background-color: #e74c3c; }
        .nav-links a:hover { opacity: 0.8; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tab-button { padding: 12px 25px; border: none; background: #ecf0f1; color: #2c3e50; cursor: pointer; border-radius: 5px; font-size: 15px; font-weight: bold; transition: 0.3s; }
        .tab-button.active { background: #3498db; color: white; }
        .tab-button:hover { opacity: 0.8; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-title { font-size: 20px; color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        
        .success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 14px; }
        input[type="text"], input[type="url"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { resize: vertical; min-height: 100px; font-family: Arial, sans-serif; }
        
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s; }
        .btn-primary { background-color: #3498db; color: white; }
        .btn-primary:hover { background-color: #2980b9; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #34495e; color: white; font-weight: bold; }
        tr:hover { background-color: #f8f9fa; }
        
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-active { background-color: #28a745; color: white; }
        .badge-inactive { background-color: #6c757d; color: white; }
        
        select.inline { padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
        button.small { padding: 6px 12px; font-size: 13px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; margin: 2px; }
        button.small:hover { background: #0056b3; }
        button.danger { background: #dc3545; }
        button.danger:hover { background: #c82333; }
        
        .preview-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #3498db; }
        .preview-section h3 { color: #2c3e50; margin-bottom: 15px; }
        .preview-hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 8px; text-align: center; }
        .preview-hero h1 { font-size: 32px; margin-bottom: 10px; }
        .preview-hero p { font-size: 18px; margin-bottom: 20px; opacity: 0.9; }
        .preview-hero button { background: white; color: #667eea; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .preview-promo { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; text-align: center; margin-top: 20px; font-weight: bold; }
    </style>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        function confirmDelete(type) {
            return confirm("Are you sure you want to delete this " + type + "?");
        }
    </script>
</head>
<body>

<div class="header">
    <div class="nav-links">
        <a href="inventory.php">Inventory</a>
        <a href="orders.php">Orders</a>
        <a href="customers.php">Customers</a>
        <a href="categories.php">Categories</a>
        <a href="login.php" class="logout">Logout</a>
    </div>
    <h1>Category & Storefront Management</h1>
    <p>Manage categories, homepage content and promotional materials</p>
</div>

<div class="container">
    
    <?php if (!empty($statusMsg)): ?>
        <div class="success"><?php echo $statusMsg; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errorMsg)): ?>
        <div class="error"><?php echo $errorMsg; ?></div>
    <?php endif; ?>
    
    <!-- Tab Navigation -->
    <div class="tabs">
        <button class="tab-button active" onclick="showTab('categories')">📁 Categories</button>
        <button class="tab-button" onclick="showTab('homepage')">🏠 Homepage Content</button>
        <button class="tab-button" onclick="showTab('promotions')">📢 Promotional Banners</button>
    </div>
    
    <!-- Categories Tab -->
    <div id="categories" class="tab-content active">
        <div class="card">
            <h2 class="card-title">Add New Category</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" name="category_name" placeholder="e.g., Electronics" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="category_description" placeholder="Brief description of this category"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Category List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($_SESSION['categories'])): ?>
                        <?php foreach ($_SESSION['categories'] as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td><?php echo $category['product_count']; ?></td>
                            <td>
                                <span class="badge <?php echo $category['status'] == 'Active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $category['status']; ?>
                                </span>
                                <form method="post" style="margin-top: 5px;">
                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                    <select name="category_status" class="inline">
                                        <option value="Active" <?php echo $category['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo $category['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                    <button name="update_category_status" class="small">Update</button>
                                </form>
                            </td>
                            <td>
                                <button onclick="if(confirmDelete('category')) window.location.href='categories.php?action=delete_category&id=<?php echo $category['id']; ?>'" class="small danger">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center;">No categories found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Homepage Content Tab -->
    <div id="homepage" class="tab-content">
        <div class="card">
            <h2 class="card-title">Update Homepage Content</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Hero Section Title *</label>
                    <input type="text" name="hero_title" value="<?php echo htmlspecialchars($_SESSION['homepage']['hero_title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Hero Section Subtitle *</label>
                    <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($_SESSION['homepage']['hero_subtitle']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Hero Button Text *</label>
                    <input type="text" name="hero_button_text" value="<?php echo htmlspecialchars($_SESSION['homepage']['hero_button_text']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Featured Products Section Title *</label>
                    <input type="text" name="featured_title" value="<?php echo htmlspecialchars($_SESSION['homepage']['featured_title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Promotional Text</label>
                    <textarea name="promo_text" rows="3"><?php echo htmlspecialchars($_SESSION['homepage']['promo_text']); ?></textarea>
                </div>
                
                <button type="submit" name="update_homepage" class="btn btn-primary">Update Homepage</button>
            </form>
        </div>
        
        <!-- Live Preview -->
        <div class="card">
            <h2 class="card-title">Live Preview</h2>
            <div class="preview-section">
                <h3>Hero Section Preview:</h3>
                <div class="preview-hero">
                    <h1><?php echo htmlspecialchars($_SESSION['homepage']['hero_title']); ?></h1>
                    <p><?php echo htmlspecialchars($_SESSION['homepage']['hero_subtitle']); ?></p>
                    <button><?php echo htmlspecialchars($_SESSION['homepage']['hero_button_text']); ?></button>
                </div>
                
                <div class="preview-promo">
                    <?php echo htmlspecialchars($_SESSION['homepage']['promo_text']); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Promotional Banners Tab -->
    <div id="promotions" class="tab-content">
        <div class="card">
            <h2 class="card-title">Add Promotional Banner</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Banner Title *</label>
                        <input type="text" name="banner_title" placeholder="e.g., Summer Sale" required>
                    </div>
                    <div class="form-group">
                        <label>Banner Link (URL)</label>
                        <input type="url" name="banner_link" placeholder="/sale or https://example.com/sale">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Banner Description</label>
                    <textarea name="banner_description" placeholder="Brief description of the promotion"></textarea>
                </div>
                
                <button type="submit" name="add_banner" class="btn btn-primary">Add Banner</button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Promotional Banners</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($_SESSION['banners'])): ?>
                        <?php foreach ($_SESSION['banners'] as $banner): ?>
                        <tr>
                            <td><?php echo $banner['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($banner['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($banner['description']); ?></td>
                            <td><?php echo htmlspecialchars($banner['link']); ?></td>
                            <td>
                                <span class="badge <?php echo $banner['status'] == 'Active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo $banner['status']; ?>
                                </span>
                                <form method="post" style="margin-top: 5px;">
                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                    <select name="banner_status" class="inline">
                                        <option value="Active" <?php echo $banner['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo $banner['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                    <button name="update_banner_status" class="small">Update</button>
                                </form>
                            </td>
                            <td>
                                <button onclick="if(confirmDelete('banner')) window.location.href='categories.php?action=delete_banner&id=<?php echo $banner['id']; ?>'" class="small danger">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">No banners found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>