<?php
session_start();
require_once 'config.php';

// If already logged in, redirect to inventory
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: inventory.php");
    exit();
}

// Initialize variables
$admin_id = $username = $full_name = $email = $password = $confirm_password = "";
$adminErr = $userErr = $nameErr = $emailErr = $passErr = $confirmErr = "";
$success = "";

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_type = $_POST["form_type"];
    $active_tab = $form_type;
    
    // Admin ID validation
    if (empty($_POST["admin_id"])) {
        $adminErr = "Admin ID is required";
    } else {
        $admin_id = sanitize_input($_POST["admin_id"]);
    }
    
    // Username validation
    if (empty($_POST["username"])) {
        $userErr = "Username is required";
    } else {
        $username = sanitize_input($_POST["username"]);
    }
    
    if ($form_type == "register") {
        // REGISTRATION PROCESS
        
        // Full Name validation
        if (empty($_POST["full_name"])) {
            $nameErr = "Full Name is required";
        } else {
            $full_name = sanitize_input($_POST["full_name"]);
        }
        
        // Email validation
        if (empty($_POST["email"])) {
            $emailErr = "Email is required";
        } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        } else {
            $email = sanitize_input($_POST["email"]);
        }
        
        // Password validation
        if (empty($_POST["password"])) {
            $passErr = "Password is required";
        } elseif (strlen($_POST["password"]) < 6) {
            $passErr = "Password must be at least 6 characters";
        } else {
            $password = $_POST["password"];
        }
        
        // Confirm Password validation
        if (empty($_POST["confirm_password"])) {
            $confirmErr = "Please confirm your password";
        } elseif ($_POST["password"] != $_POST["confirm_password"]) {
            $confirmErr = "Passwords do not match";
        }
        
        // Check if all validations passed
        if (empty($adminErr) && empty($userErr) && empty($nameErr) && empty($emailErr) && empty($passErr) && empty($confirmErr)) {
            
            // Check if admin_id, username or email already exists
            $check_query = "SELECT * FROM admins WHERE admin_id = '$admin_id' OR username = '$username' OR email = '$email'";
            $check_result = mysqli_query($conn, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                $emailErr = "Admin ID, Username or Email already exists!";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert into database
                $insert_query = "INSERT INTO admins (admin_id, username, full_name, email, password) 
                                VALUES ('$admin_id', '$username', '$full_name', '$email', '$hashed_password')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = "Registration successful! You can now login.";
                    $active_tab = 'login';
                    // Clear form
                    $admin_id = $username = $full_name = $email = "";
                } else {
                    $emailErr = "Error: " . mysqli_error($conn);
                }
            }
        }
    } else {
        // LOGIN PROCESS
        
        if (empty($_POST["password"])) {
            $passErr = "Password is required";
        } else {
            $password = $_POST["password"];
        }
        
        // Check if all validations passed
        if (empty($adminErr) && empty($userErr) && empty($passErr)) {
            
            // Check credentials in database
            $login_query = "SELECT * FROM admins WHERE admin_id = '$admin_id' AND username = '$username'";
            $login_result = mysqli_query($conn, $login_query);
            
            if (mysqli_num_rows($login_result) == 1) {
                $admin = mysqli_fetch_assoc($login_result);
                
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    // Set session variables
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['username'] = $admin['username'];
                    $_SESSION['full_name'] = $admin['full_name'];
                    
                    // Redirect to inventory page
                    header("Location: inventory.php");
                    exit();
                } else {
                    $passErr = "Invalid password!";
                }
            } else {
                $userErr = "Invalid Admin ID or Username!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .tabs {
            display: flex;
            background: #f5f5f5;
        }
        
        .tab-link {
            flex: 1;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-link:hover {
            background: #e8e8e8;
            color: #333;
        }
        
        .tab-link.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: white;
        }
        
        .form-container {
            padding: 35px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            outline: none;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #667eea;
        }
        
        .error {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Portal</h1>
            <p>E-Commerce Management System</p>
        </div>
        
        <div class="tabs">
            <a href="?tab=login" class="tab-link <?php echo $active_tab == 'login' ? 'active' : ''; ?>">
                Login
            </a>
            <a href="?tab=register" class="tab-link <?php echo $active_tab == 'register' ? 'active' : ''; ?>">
                Register
            </a>
        </div>
        
        <div class="form-container">
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($active_tab == 'login'): ?>
                <!-- Login Form -->
                <form method="POST" action="">
                    <input type="hidden" name="form_type" value="login">
                    
                    <div class="form-group">
                        <label>Admin ID</label>
                        <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_id); ?>">
                        <?php if (!empty($adminErr)): ?>
                            <span class="error"><?php echo $adminErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        <?php if (!empty($userErr)): ?>
                            <span class="error"><?php echo $userErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password">
                        <?php if (!empty($passErr)): ?>
                            <span class="error"><?php echo $passErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">Login</button>
                </form>
            <?php else: ?>
                <!-- Registration Form -->
                <form method="POST" action="">
                    <input type="hidden" name="form_type" value="register">
                    
                    <div class="form-group">
                        <label>Admin ID</label>
                        <input type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_id); ?>">
                        <?php if (!empty($adminErr)): ?>
                            <span class="error"><?php echo $adminErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
                        <?php if (!empty($nameErr)): ?>
                            <span class="error"><?php echo $nameErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <?php if (!empty($emailErr)): ?>
                            <span class="error"><?php echo $emailErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        <?php if (!empty($userErr)): ?>
                            <span class="error"><?php echo $userErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password">
                        <?php if (!empty($passErr)): ?>
                            <span class="error"><?php echo $passErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password">
                        <?php if (!empty($confirmErr)): ?>
                            <span class="error"><?php echo $confirmErr; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">Register</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>