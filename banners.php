<?php
session_start();
require_once 'config.php';
check_login();

$page_title = 'Banner Management';
$active_nav = 'banners';
$success = $error = "";
$upload_dir = "uploads/banners/";

// Delete
if(isset($_GET['action'])&&$_GET['action']=='delete'&&isset($_GET['id'])){
    $id=(int)$_GET['id'];
    $r=mysqli_query($conn,"SELECT image FROM banners WHERE id=$id");
    if($row=mysqli_fetch_assoc($r)){
        $f=$upload_dir.$row['image'];
        if(!empty($row['image'])&&file_exists($f)) unlink($f);
    }
    if(mysqli_query($conn,"DELETE FROM banners WHERE id=$id")) $success="Banner deleted!";
    else $error="Error: ".mysqli_error($conn);
}

// Toggle
if(isset($_GET['action'])&&$_GET['action']=='toggle'&&isset($_GET['id'])){
    $id=(int)$_GET['id'];
    mysqli_query($conn,"UPDATE banners SET status=IF(status='Active','Inactive','Active') WHERE id=$id");
    $success="Banner status updated!";
}

// Add banner
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['add_banner'])) {
    $title = sanitize_input($_POST['title']);
    $desc  = sanitize_input($_POST['description']??'');
    $link  = sanitize_input($_POST['link']??'');
    $status= sanitize_input($_POST['status']??'Active');
    $image_name = "";

    if(!empty($_FILES['banner_image']['name'])){
        $allowed=['image/jpeg','image/png','image/gif','image/webp'];
        if(!in_array($_FILES['banner_image']['type'],$allowed)) $error="Only JPG/PNG/GIF/WEBP allowed.";
        elseif($_FILES['banner_image']['size']>5*1024*1024) $error="Max 5MB.";
        else {
            if(!is_dir($upload_dir)) mkdir($upload_dir,0755,true);
            $ext=pathinfo($_FILES['banner_image']['name'],PATHINFO_EXTENSION);
            $image_name='banner_'.time().'_'.rand(1000,9999).'.'.$ext;
            if(!move_uploaded_file($_FILES['banner_image']['tmp_name'],$upload_dir.$image_name)){
                $error="Upload failed."; $image_name="";
            }
        }
    }

    if(empty($error)){
        // Build query based on available columns
        $col_check=mysqli_query($conn,"SHOW COLUMNS FROM banners");
        $cols=[];while($c=mysqli_fetch_assoc($col_check))$cols[]=$c['Field'];
        $fields=['title']; $vals=["'$title'"];
        if(in_array('description',$cols)){$fields[]='description';$vals[]="'$desc'";}
        if(in_array('link',$cols)){$fields[]='link';$vals[]="'$link'";}
        if(in_array('image',$cols)&&$image_name){$fields[]='image';$vals[]="'$image_name'";}
        if(in_array('status',$cols)){$fields[]='status';$vals[]="'$status'";}
        $q="INSERT INTO banners (".implode(',',$fields).") VALUES (".implode(',',$vals).")";
        if(mysqli_query($conn,$q)) $success="Banner added!";
        else $error="DB Error: ".mysqli_error($conn);
    }
}

$banners = mysqli_query($conn,"SELECT * FROM banners ORDER BY id DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Banners - BanglaBazaar Admin</title>
<?php include 'admin-header.php'; ?>
</head><body>

<?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?php echo $success;?></div><?php endif;?>
<?php if($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo $error;?></div><?php endif;?>

<!-- Add Banner -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-plus-circle"></i> Add New Banner</div></div>
    <div class="panel-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Banner Title *</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Summer Sale" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Link URL</label>
                    <input type="text" name="link" class="form-input" placeholder="/sale or https://...">
                </div>
                <div class="form-group full">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" placeholder="Short description shown on the banner..."></textarea>
                </div>
                <div class="form-group full">
                    <label class="form-label">Banner Image (Recommended: 1200×400px · Max 5MB)</label>
                    <div class="upload-zone" onclick="document.getElementById('banner_image').click()">
                        <input type="file" id="banner_image" name="banner_image" accept="image/*" onchange="previewImg(event,'preview-img')">
                        <div class="upload-icon"><i class="fas fa-image"></i></div>
                        <div class="upload-text">Click to upload banner image</div>
                        <div class="upload-hint">JPG, PNG, GIF, WEBP · Max 5MB</div>
                        <img id="preview-img" src="" alt="">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:16px;"><button type="submit" name="add_banner" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Banner</button></div>
        </form>
    </div>
</div>

<!-- Banner List -->
<div class="panel">
    <div class="panel-header"><div class="panel-title"><i class="fas fa-images"></i> All Banners</div></div>
    <table class="data-table">
        <thead><tr><th>#</th><th>Image</th><th>Title</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if($banners&&mysqli_num_rows($banners)>0): while($b=mysqli_fetch_assoc($banners)): ?>
        <tr>
            <td style="color:var(--text-3);font-size:12px;">#<?php echo $b['id'];?></td>
            <td>
                <?php $img=$b['image']??''; $path=$upload_dir.$img;
                if(!empty($img)&&file_exists($path)): ?>
                    <img src="<?php echo $path;?>" style="width:100px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                <?php else: ?><div style="width:100px;height:50px;background:var(--surface2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;">🖼️</div><?php endif;?>
            </td>
            <td><strong><?php echo htmlspecialchars($b['title']);?></strong></td>
            <td style="font-size:13px;color:var(--text-2);"><?php echo htmlspecialchars($b['description']??'');?></td>
            <td><span class="badge <?php echo ($b['status']??'')==='Active'?'badge-success':'badge-gray';?>"><?php echo $b['status']??'Active';?></span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="?action=toggle&id=<?php echo $b['id'];?>" class="btn btn-warning btn-sm">
                        <?php echo ($b['status']??'')==='Active'?'<i class="fas fa-eye-slash"></i> Deactivate':'<i class="fas fa-eye"></i> Activate';?>
                    </a>
                    <a href="?action=delete&id=<?php echo $b['id'];?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete banner?')"><i class="fas fa-trash"></i></a>
                </div>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-images"></i><p>No banners yet. Add your first banner!</p></div></td></tr>
        <?php endif;?>
        </tbody>
    </table>
</div>

</div></div>
<script>
function previewImg(e,id){const img=document.getElementById(id);const f=e.target.files[0];if(f){img.src=URL.createObjectURL(f);img.style.display='block';}}
</script>
</body></html>