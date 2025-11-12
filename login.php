<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($password) && !empty($row['password_hash'])) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['id'];
                header("Location: newpage.php");
                exit;
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "Password incorrect or user data incomplete.";
        }
    } else {
        echo "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

</head>
<body>
    <h2>Login</h2>
    <form method="POST">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>

<br>
<button onclick="window.location.href='newpage.php';" style="margin-bottom:20px; font-size:18px;">&#8592; Back to Home Page</button>
</body>
</html>

