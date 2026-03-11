<?php
session_start();
require_once 'config.php';

if(isset($_SESSION['admin_logged_in'])&&$_SESSION['admin_logged_in']===true){
    header("Location: inventory.php"); exit();
}

$admin_id=$username=$password=$full_name=$email=$confirm_password="";
$errors=[]; $success="";
$active_tab=$_GET['tab']??'login';

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $form=$_POST["form_type"];
    $active_tab=$form;
    $admin_id=sanitize_input($_POST["admin_id"]??"");
    $username=sanitize_input($_POST["username"]??"");
    $password=$_POST["password"]??"";

    if(empty($admin_id)) $errors[]="Admin ID required.";
    if(empty($username)) $errors[]="Username required.";
    if(empty($password)) $errors[]="Password required.";

    if($form=="register"){
        $full_name=sanitize_input($_POST["full_name"]??"");
        $email=sanitize_input($_POST["email"]??"");
        $confirm_password=$_POST["confirm_password"]??"";
        if(empty($full_name)) $errors[]="Full name required.";
        if(empty($email)||!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]="Valid email required.";
        if(strlen($password)<6) $errors[]="Password min 6 chars.";
        if($password!==$confirm_password) $errors[]="Passwords don't match.";
        if(empty($errors)){
            $chk=mysqli_query($conn,"SELECT id FROM admins WHERE admin_id='$admin_id' OR username='$username' OR email='$email'");
            if(mysqli_num_rows($chk)>0) $errors[]="Admin ID, username or email already exists.";
            else {
                $hp=password_hash($password,PASSWORD_DEFAULT);
                $q="INSERT INTO admins (admin_id,username,full_name,email,password) VALUES ('$admin_id','$username','$full_name','$email','$hp')";
                if(mysqli_query($conn,$q)){ $success="Registered! You can now login."; $active_tab='login'; }
                else $errors[]="Error: ".mysqli_error($conn);
            }
        }
    } else {
        if(empty($errors)){
            $r=mysqli_query($conn,"SELECT * FROM admins WHERE admin_id='$admin_id' AND username='$username'");
            if(mysqli_num_rows($r)==1){
                $admin=mysqli_fetch_assoc($r);
                if(password_verify($password,$admin['password'])){
                    $_SESSION['admin_logged_in']=true;
                    $_SESSION['admin_id']=$admin['admin_id'];
                    $_SESSION['username']=$admin['username'];
                    $_SESSION['full_name']=$admin['full_name'];
                    header("Location: inventory.php"); exit();
                } else $errors[]="Invalid password!";
            } else $errors[]="Invalid Admin ID or Username!";
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — BanglaBazaar</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;background:#0f172a;overflow:hidden;}
.bg-art{position:fixed;inset:0;z-index:0;overflow:hidden;}
.bg-art span{position:absolute;border-radius:50%;filter:blur(80px);opacity:0.15;}
.bg-art span:nth-child(1){width:600px;height:600px;background:#6366f1;top:-200px;left:-200px;}
.bg-art span:nth-child(2){width:500px;height:500px;background:#8b5cf6;bottom:-150px;right:-150px;}
.bg-art span:nth-child(3){width:300px;height:300px;background:#3b82f6;top:50%;left:50%;transform:translate(-50%,-50%);}
.login-wrap{position:relative;z-index:1;display:flex;width:100%;min-height:100vh;}
.login-left{flex:1;display:flex;flex-direction:column;justify-content:center;padding:60px;color:white;display:none;}
@media(min-width:900px){.login-left{display:flex;}}
.login-left .brand{font-size:32px;font-weight:800;margin-bottom:8px;}
.login-left .tagline{font-size:16px;opacity:0.7;margin-bottom:48px;}
.login-left .feature{display:flex;align-items:center;gap:14px;margin-bottom:20px;}
.login-left .feature .icon{width:40px;height:40px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.login-left .feature .text strong{display:block;font-size:14px;}
.login-left .feature .text span{font-size:13px;opacity:0.6;}
.login-right{width:100%;max-width:480px;margin:auto;padding:32px;display:flex;align-items:center;}
@media(min-width:900px){.login-right{width:480px;margin:0;}}
.login-box{background:white;border-radius:20px;padding:40px;width:100%;box-shadow:0 32px 80px rgba(0,0,0,0.4);}
.login-box .logo{display:flex;align-items:center;gap:10px;margin-bottom:28px;}
.login-box .logo .icon{width:40px;height:40px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;}
.login-box .logo .name{font-size:18px;font-weight:800;color:#0f172a;}
.login-box h2{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:6px;}
.login-box p{font-size:14px;color:#94a3b8;margin-bottom:24px;}
.tab-row{display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:24px;}
.tab-link{flex:1;text-align:center;padding:9px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;color:#64748b;transition:all .15s;}
.tab-link.active{background:white;color:#6366f1;box-shadow:0 1px 4px rgba(0,0,0,0.1);}
.fg{margin-bottom:16px;}
.fg label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
.fg .inp{width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-family:inherit;font-size:14px;color:#0f172a;outline:none;transition:border .15s,box-shadow .15s;}
.fg .inp:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);}
.submit-btn{width:100%;padding:13px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:transform .15s,box-shadow .15s;margin-top:8px;}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.4);}
.errors{background:#fee2e2;color:#991b1b;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;}
.errors li{margin-left:16px;}
.success-msg{background:#d1fae5;color:#065f46;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;}
</style>
</head>
<body>
<div class="bg-art"><span></span><span></span><span></span></div>
<div class="login-wrap">
    <div class="login-left">
        <div class="brand">🛍️ BanglaBazaar</div>
        <div class="tagline">Your complete e-commerce management hub</div>
        <div class="feature"><div class="icon">📦</div><div class="text"><strong>Inventory Management</strong><span>Track products & stock levels</span></div></div>
        <div class="feature"><div class="icon">🛒</div><div class="text"><strong>Order Processing</strong><span>Manage orders end-to-end</span></div></div>
        <div class="feature"><div class="icon">👥</div><div class="text"><strong>Customer Management</strong><span>Grow & retain your customers</span></div></div>
        <div class="feature"><div class="icon">📊</div><div class="text"><strong>Analytics Dashboard</strong><span>Data-driven decisions</span></div></div>
    </div>
    <div class="login-right">
        <div class="login-box">
            <div class="logo"><div class="icon"><i class="fas fa-store"></i></div><div class="name">Admin Portal</div></div>
            <h2><?php echo $active_tab=='login'?'Welcome back!':'Create account';?></h2>
            <p><?php echo $active_tab=='login'?'Sign in to your admin dashboard':'Register a new admin account';?></p>

            <div class="tab-row">
                <a href="?tab=login"    class="tab-link <?php echo $active_tab=='login'?'active':'';?>">Login</a>
                <a href="?tab=register" class="tab-link <?php echo $active_tab=='register'?'active':'';?>">Register</a>
            </div>

            <?php if(!empty($errors)): ?><div class="errors"><ul><?php foreach($errors as $e): ?><li><?php echo $e;?></li><?php endforeach;?></ul></div><?php endif;?>
            <?php if(!empty($success)): ?><div class="success-msg">✅ <?php echo $success;?></div><?php endif;?>

            <form method="POST">
                <input type="hidden" name="form_type" value="<?php echo $active_tab;?>">
                <div class="fg"><label>Admin ID</label><input type="text" name="admin_id" class="inp" placeholder="ADMIN001" value="<?php echo htmlspecialchars($admin_id);?>" required></div>
                <?php if($active_tab=='register'): ?>
                <div class="fg"><label>Full Name</label><input type="text" name="full_name" class="inp" placeholder="Your full name" value="<?php echo htmlspecialchars($full_name);?>"></div>
                <div class="fg"><label>Email</label><input type="email" name="email" class="inp" placeholder="you@example.com" value="<?php echo htmlspecialchars($email);?>"></div>
                <?php endif;?>
                <div class="fg"><label>Username</label><input type="text" name="username" class="inp" placeholder="admin" value="<?php echo htmlspecialchars($username);?>" required></div>
                <div class="fg"><label>Password</label><input type="password" name="password" class="inp" placeholder="••••••••" required></div>
                <?php if($active_tab=='register'): ?>
                <div class="fg"><label>Confirm Password</label><input type="password" name="confirm_password" class="inp" placeholder="••••••••"></div>
                <?php endif;?>
                <button type="submit" class="submit-btn"><?php echo $active_tab=='login'?'Sign In →':'Create Account →';?></button>
            </form>
        </div>
    </div>
</div>
</body></html>