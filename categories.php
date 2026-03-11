<?php
session_start();
require_once 'config.php';
check_login();

$page_title = 'Categories & Content';
$active_nav = 'categories';
$success = $error = "";
$active_tab = $_GET['tab'] ?? 'categories';

// Add category
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['add_category'])) {
    $name = sanitize_input($_POST['category_name']);
    $desc = sanitize_input($_POST['category_description']??'');
    if(empty($name)) $error="Category name required!";
    else {
        // Build query based on available columns
        $col_check = mysqli_query($conn,"SHOW COLUMNS FROM categories");
        $cols=[];while($c=mysqli_fetch_assoc($col_check))$cols[]=$c['Field'];
        $fields=['name']; $vals=["'$name'"];
        if(in_array('description',$cols)&&$desc){$fields[]='description';$vals[]="'$desc'";}
        if(in_array('status',$cols)){$fields[]='status';$vals[]="'Active'";}
        $q="INSERT INTO categories (".implode(',',$fields).") VALUES (".implode(',',$vals).")";
        if(mysqli_query($conn,$q)) $success="Category added!";
        else $error="Error: ".mysqli_error($conn);
    }
}

// Delete category
if(isset($_GET['action'])&&$_GET['action']=='delete_category'&&isset($_GET['id'])){
    $id=(int)$_GET['id'];
    if(mysqli_query($conn,"DELETE FROM categories WHERE id=$id")) $success="Category deleted!";
    else $error="Error: ".mysqli_error($conn);
}

// Update homepage content
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['update_homepage'])) {
    $ht = sanitize_input($_POST['hero_title']);
    $hs = sanitize_input($_POST['hero_subtitle']);
    $hb = sanitize_input($_POST['hero_button_text']);
    $ft = sanitize_input($_POST['featured_title']);
    $pt = sanitize_input($_POST['promo_text']);
    $r  = mysqli_query($conn,"SELECT id FROM homepage_content WHERE id=1");
    if(mysqli_num_rows($r)>0){
        $q="UPDATE homepage_content SET hero_title='$ht',hero_subtitle='$hs',hero_button_text='$hb',featured_title='$ft',promo_text='$pt' WHERE id=1";
    } else {
        $q="INSERT INTO homepage_content (id,hero_title,hero_subtitle,hero_button_text,featured_title,promo_text) VALUES (1,'$ht','$hs','$hb','$ft','$pt')";
    }
    if(mysqli_query($conn,$q)) $success="Homepage updated!";
    else $error="Error: ".mysqli_error($conn);
}

$categories = mysqli_query($conn,"SELECT * FROM categories ORDER BY id DESC");
$homepage = ['hero_title'=>'Welcome','hero_subtitle'=>'','hero_button_text'=>'Shop Now','featured_title'=>'Featured Products','promo_text'=>''];
$hr = mysqli_query($conn,"SELECT * FROM homepage_content WHERE id=1");
if($hr&&mysqli_num_rows($hr)>0) $homepage=mysqli_fetch_assoc($hr);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Categories - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?php echo $success;?></div><?php endif;?>
<?php if($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo $error;?></div><?php endif;?>

<div class="tab-bar">
    <a href="?tab=categories" class="tab-btn <?php echo $active_tab=='categories'?'active':'';?>">📁 Categories</a>
    <a href="?tab=homepage"   class="tab-btn <?php echo $active_tab=='homepage'?'active':'';?>">🏠 Homepage</a>
</div>

<?php if($active_tab=='categories'): ?>
<!-- Add Category -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-plus-circle"></i> Add New Category</div></div>
    <div class="panel-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="category_name" class="form-input" placeholder="e.g. Electronics" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="category_description" class="form-input" placeholder="Brief description">
                </div>
            </div>
            <div style="margin-top:16px;"><button type="submit" name="add_category" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</button></div>
        </form>
    </div>
</div>

<!-- Category List -->
<div class="panel">
    <div class="panel-header">
        <div class="panel-title"><i class="fas fa-tags"></i> All Categories</div>
        <span class="badge badge-purple"><?php echo mysqli_num_rows($categories);?> total</span>
    </div>
    <table class="data-table">
        <thead><tr><th>#</th><th>Category Name</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($categories)>0): while($cat=mysqli_fetch_assoc($categories)): ?>
        <tr>
            <td style="color:var(--text-3);font-size:12px;">#<?php echo $cat['id'];?></td>
            <td><strong><?php echo htmlspecialchars($cat['name']);?></strong></td>
            <td>
                <a href="?action=delete_category&id=<?php echo $cat['id'];?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete category?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="3"><div class="empty-state"><i class="fas fa-tags"></i><p>No categories yet.</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

<?php elseif($active_tab=='homepage'): ?>
<!-- Homepage Content -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-home"></i> Homepage Content</div></div>
    <div class="panel-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Hero Title</label>
                    <input type="text" name="hero_title" class="form-input" value="<?php echo htmlspecialchars($homepage['hero_title']);?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Hero Subtitle</label>
                    <input type="text" name="hero_subtitle" class="form-input" value="<?php echo htmlspecialchars($homepage['hero_subtitle']);?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="hero_button_text" class="form-input" value="<?php echo htmlspecialchars($homepage['hero_button_text']);?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Featured Section Title</label>
                    <input type="text" name="featured_title" class="form-input" value="<?php echo htmlspecialchars($homepage['featured_title']);?>">
                </div>
                <div class="form-group full">
                    <label class="form-label">Promo Text</label>
                    <textarea name="promo_text" class="form-textarea"><?php echo htmlspecialchars($homepage['promo_text']);?></textarea>
                </div>
            </div>
            <div style="margin-top:16px;"><button type="submit" name="update_homepage" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button></div>
        </form>
    </div>
</div>
<?php endif;?>

</div></div></body></html>