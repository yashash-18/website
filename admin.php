<?php
include 'config.php';
session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}


if (isset($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del_stmt->bind_param("i", $_GET['delete']);
    $del_stmt->execute();
    $del_stmt->close();

    
    header("Location: admin.php");
    exit;
}


$users = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html>
<head>
    <title> Dashboard</title>
    <style>
        table {border-collapse: collapse; width: 75%;}
        th, td {border: 1px solid #888; padding: 8px;}
        th {background: #ddd;}
    </style>
</head>
<body>
<div style="text-align:left;">
    <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    <a href="logout.php">Logout</a>

</div>
<h2> Dashboard</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created_at</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $users->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['role']); ?></td>
        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
        <td>
        <?php if ($row['id'] != $_SESSION['user_id']): ?>
            <a href="admin.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
        <?php else: ?>
            (You)
        <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<button onclick="window.location.href='newpage.php';" style="margin-bottom:20px; font-size:18px;">&#8592; Back to Home Page</button>
</body>
</html>
