<?php
session_start();
require 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

$success = $error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);

    if (empty($new_username) || empty($new_email)) {
        $error = "Username and Email cannot be empty.";
    } else {
        
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        $stmt->execute();
        $stmt->close();

        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();
            $stmt->close();
        }
        $success = "Profile updated successfully!";
        
        $_SESSION['username'] = $new_username;
        header('Location: newpage.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>
<body>
<h2>Edit Profile</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<button onclick="window.location.href='newpage.php';" style="margin-bottom:20px; font-size:18px;">&#8592; Back to Home Page</button>
<form method="post" action="">
    <label>Username:</label>
    <input type="text" name="username" required value="<?php echo htmlspecialchars($username); ?>"><br><br>
    <label>Email:</label>
    <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"><br><br>
    <label>New Password:</label>
    <input type="password" name="password" placeholder="Leave blank to keep current"><br><br>
    <input type="submit" value="Save Changes">
</form>
</body>
</html>
