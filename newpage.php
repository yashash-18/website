<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome | LAMP Web</title>
    <style>
        .center-image {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 800px; /* Adjust height as needed */
        }
        .center-image img {
            max-width: 1000px; /* Example size, you can adjust */
            width: 100%;
            height: auto;
            display: block;
        }
        .options {
            text-align: center;
            margin-top: 20px;
        }
        .options a {
            margin: 0 15px;
            font-size: 20px;
        }
    </style>
    <script>
    function confirmLogout(e) {
        if (!confirm('Are you sure you want to log out?')) {
            e.preventDefault();
        }
    }
    </script>
</head>
<body>
<?php if(isset($_SESSION['username'])): ?>
    <div class="center-image">
        <img src="welcome.jpeg" alt="">
    </div>
    <p style="text-align:center;">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    <div class="options">
        <a href="upload.php">Upload File</a>
        <a href="profile.php">Edit Profile</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" onclick="confirmLogout(event)">Logout</a>
    </div>
<?php else: ?>
    <div class="options">
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </div>
<?php endif; ?>
</body>
</html>
