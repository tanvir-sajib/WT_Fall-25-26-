<?php
// categories.php - Category & Storefront Content Management
session_start();
require_once 'config.php';

// Check if user is logged in
check_login();

// Variables
$statusMsg = "";
$errorMsg = "";

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'categories';

// Handle Add Category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $cat_name = sanitize_input($_POST['category_name']);
    $cat_desc = sanitize_input($_POST['category_description']);
    
    if (empty($cat_name)) {
        $errorMsg = "Category name is required!";
    } else {
        // Create slug from name
        $slug = strtolower(str_replace(' ', '-', $cat_name));
        
        $insert_query = "INSERT INTO categories (name, slug, description) VALUES ('$cat_name', '$slug', '$cat_desc')";
        
        if (mysqli_query($conn, $insert_query)) {
            $statusMsg = "Category added successfully!";
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}

// Handle Update Category Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category_status'])) {
    $cat_id = (int)$_POST['category_id'];
    $new_status = sanitize_input($_POST['category_status']);
    
    $update_query = "UPDATE categories SET status = '$new_status' WHERE id = $cat_id";
    
    if (mysqli_query($conn, $update_query)) {
        $statusMsg = "Category status updated!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] == 'delete_category' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM categories WHERE id = $delete_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $statusMsg = "Category deleted successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Handle Update Homepage Content
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_homepage'])) {
    $hero_title = sanitize_input($_POST['hero_title']);
    $hero_subtitle = sanitize_input($_POST['hero_subtitle']);
    $hero_button_text = sanitize_input($_POST['hero_button_text']);
    $featured_title = sanitize_input($_POST['featured_title']);
    $promo_text = sanitize_input($_POST['promo_text']);
    
    $update_query = "UPDATE homepage_content SET 
                    hero_title = '$hero_title',
                    hero_subtitle = '$hero_subtitle',
                    hero_button_text = '$hero_button_text',
                    featured_title = '$featured_title',
                    promo_text = '$promo_text'
                    WHERE id = 1";
    
    if (mysqli_query($conn, $update_query)) {
        $statusMsg = "Homepage content updated successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Handle Add Promotional Banner
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_banner'])) {
    $banner_title = sanitize_input($_POST['banner_title']);
    $banner_desc = sanitize_input($_POST['banner_description']);
    $banner_link = sanitize_input($_POST['banner_link']);
    
    if (empty($banner_title)) {
        $errorMsg = "Banner title is required!";
    } else {
        $insert_query = "INSERT INTO banners (title, description, link) VALUES ('$banner_title', '$banner_desc', '$banner_link')";
        
        if (mysqli_query($conn, $insert_query)) {
            $statusMsg = "Promotional banner added successfully!";
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}

// Handle Update Banner Status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_banner_status'])) {
    $banner_id = (int)$_POST['banner_id'];
    $new_status = sanitize_input($_POST['banner_status']);
    
    $update_query = "UPDATE banners SET status = '$new_status' WHERE id = $banner_id";
    
    if (mysqli_query($conn, $update_query)) {
        $statusMsg = "Banner status updated!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Handle Delete Banner
if (isset($_GET['action']) && $_GET['action'] == 'delete_banner' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    $delete_query = "DELETE FROM banners WHERE id = $delete_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $statusMsg = "Banner deleted successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Fetch data
$categories_query = "SELECT * FROM categories ORDER BY id DESC";
$categories_result = mysqli_query($conn, $categories_query);

$homepage_query = "SELECT * FROM homepage_content WHERE id = 1";
$homepage_result = mysqli_query($conn, $homepage_query);
$homepage = mysqli_fetch_assoc($homepage_result);

$banners_query = "SELECT * FROM banners ORDER BY id DESC";
$banners_result = mysqli_query($conn, $banners_query);
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
        .nav-links a.active { background-color: #2980b9; }
        .nav-links a.logout { background-color: #e74c3c; }
        .nav-links a:hover { opacity: 0.8; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tab-button { padding: 12px 25px; border: none; background: #ecf0f1; color: #2c3e50; cursor: pointer; border-radius: 5px; font-size: 15px; font-weight: bold; transition: 0.3s; text-decoration: none; display: inline-block; }
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
</head>
<body>

<div class="header">
    <div class="nav-links">
        <a href="inventory.php">Inventory</a>
        <a href="orders.php">Orders</a>
        <a href="customers.php">Customers</a>
        <a href="categories.php" class="active">Categories</a>
        <a href="analytics.php">Analytics</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
    <h1>Category & Storefront Management</h1>
    <p>Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></p>
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
        <a href="?tab=categories" class="tab-button <?php echo $active_tab == 'categories' ? 'active' : ''; ?>">📁 Categories</a>
        <a href="?tab=homepage" class="tab-button <?php echo $active_tab == 'homepage' ? 'active' : ''; ?>">🏠 Homepage Content</a>
        <a href="?tab=promotions" class="tab-button <?php echo $active_tab == 'promotions' ? 'active' : ''; ?>">📢 Promotional Banners</a>
    </div>
    
    <!-- Categories Tab -->
    <div id="categories" class="tab-content <?php echo $active_tab == 'categories' ? 'active' : ''; ?>">
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
                    <?php if (mysqli_num_rows($categories_result) > 0): ?>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
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
                                <button onclick="if(confirm('Delete this category?')) window.location.href='categories.php?action=delete_category&id=<?php echo $category['id']; ?>'" class="small danger">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center;">No categories found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Homepage Content Tab -->
    <div id="homepage" class="tab-content <?php echo $active_tab == 'homepage' ? 'active' : ''; ?>">
        <div class="card">
            <h2 class="card-title">Update Homepage Content</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Hero Section Title *</label>
                    <input type="text" name="hero_title" value="<?php echo htmlspecialchars($homepage['hero_title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Hero Section Subtitle *</label>
                    <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($homepage['hero_subtitle']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Hero Button Text *</label>
                    <input type="text" name="hero_button_text" value="<?php echo htmlspecialchars($homepage['hero_button_text']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Featured Products Section Title *</label>
                    <input type="text" name="featured_title" value="<?php echo htmlspecialchars($homepage['featured_title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Promotional Text</label>
                    <textarea name="promo_text" rows="3"><?php echo htmlspecialchars($homepage['promo_text']); ?></textarea>
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
                    <h1><?php echo htmlspecialchars($homepage['hero_title']); ?></h1>
                    <p><?php echo htmlspecialchars($homepage['hero_subtitle']); ?></p>
                    <button><?php echo htmlspecialchars($homepage['hero_button_text']); ?></button>
                </div>
                
                <div class="preview-promo">
                    <?php echo htmlspecialchars($homepage['promo_text']); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Promotional Banners Tab -->
    <div id="promotions" class="tab-content <?php echo $active_tab == 'promotions' ? 'active' : ''; ?>">
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
                    <?php if (mysqli_num_rows($banners_result) > 0): ?>
                        <?php while ($banner = mysqli_fetch_assoc($banners_result)): ?>
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
                                <button onclick="if(confirm('Delete this banner?')) window.location.href='categories.php?action=delete_banner&id=<?php echo $banner['id']; ?>'" class="small danger">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
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