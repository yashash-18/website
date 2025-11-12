<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Delete file logic
if (isset($_POST['delete_file'])) {
    $fileToDelete = $_POST['delete_file'];
    $stmt = $conn->prepare("SELECT filename FROM uploads WHERE user_id=? AND filename=?");
    $stmt->bind_param("is", $user_id, $fileToDelete);
    $stmt->execute();
    $stmt->bind_result($filename);
    if ($stmt->fetch()) {
        $filepath = "uploads/" . $filename;
        if (file_exists($filepath)) unlink($filepath);
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM uploads WHERE user_id=? AND filename=?");
        $stmt->bind_param("is", $user_id, $fileToDelete);
        $stmt->execute();
        $message = "File deleted successfully!";
    } else {
        $message = "File not found!";
    }
    $stmt->close();
}

// Update (replace) file logic
if (isset($_POST['update_file']) && isset($_FILES['newfile'])) {
    $oldFilename = $_POST['update_file'];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['newfile']['type'];
    $fileSize = $_FILES['newfile']['size'];
    $filename = $_FILES['newfile']['name'];
    $tmpname = $_FILES['newfile']['tmp_name'];
    $uniqueName = uniqid('profile_', true) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
    $targetFile = "uploads/" . $uniqueName;

    if (in_array($fileType, $allowedTypes) && $fileSize <= 2000000) {
        if (move_uploaded_file($tmpname, $targetFile)) {
            // Delete the old file
            $stmt = $conn->prepare("SELECT filename FROM uploads WHERE user_id=? AND filename=?");
            $stmt->bind_param("is", $user_id, $oldFilename);
            $stmt->execute();
            $stmt->bind_result($oldExisting);
            if ($stmt->fetch()) {
                $filepath = "uploads/" . $oldExisting;
                if (file_exists($filepath)) unlink($filepath);
            }
            $stmt->close();

            // Update database
            $stmt = $conn->prepare("UPDATE uploads SET filename=? WHERE user_id=? AND filename=?");
            $stmt->bind_param("sis", $uniqueName, $user_id, $oldFilename);
            $stmt->execute();
            $stmt->close();

            $message = "File updated successfully!";
        } else {
            $message = "Failed to update file!";
        }
    } else {
        $message = "Invalid file type or size.";
    }
}

// File upload logic (new upload)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file']) && !isset($_POST['update_file'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['file']['type'];
    $fileSize = $_FILES['file']['size'];
    $filename = $_FILES['file']['name'];
    $tmpname = $_FILES['file']['tmp_name'];
    $uniqueName = uniqid('profile_', true) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
    $targetFile = "uploads/" . $uniqueName;

    if (in_array($fileType, $allowedTypes)) {
        if ($fileSize <= 2000000) { // Max size 2 MB
            if (move_uploaded_file($tmpname, $targetFile)) {
                // Insert into uploads table for this user
                $stmt = $conn->prepare("INSERT INTO uploads (user_id, filename) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $uniqueName);
                if ($stmt->execute()) {
                    $message = "File uploaded successfully!";
                } else {
                    $message = "Database upload failed.";
                    unlink($targetFile);
                }
                $stmt->close();
            } else {
                $message = "Failed to upload the file.";
            }
        } else {
            $message = "Invalid file type or size. Only JPG, PNG, and GIF are allowed.";
        }
    } else {
        $message = "Invalid file type or size. Only JPG, PNG, and GIF are allowed.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
    <style>
        .file-block {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 24px;
            width: 340px;
        }
    </style>
</head>
<body>
<p><?php echo $message; ?></p>
<h2>Upload a new file</h2>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <input type="submit" value="Upload">
</form>

<h2>My Uploaded Files</h2>
<?php
// Show all uploaded files for this user, vertically
$stmt = $conn->prepare("SELECT filename FROM uploads WHERE user_id=? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

$hasFiles = ($stmt->num_rows > 0);
$stmt->bind_result($userFile);

if ($hasFiles) {
    while ($stmt->fetch()) {
        $ext = strtolower(pathinfo($userFile, PATHINFO_EXTENSION));
        echo "<div class='file-block'>";
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<img src='uploads/" . htmlspecialchars($userFile) . "' width='100' /><br>";
        }
        echo "<a href='uploads/" . htmlspecialchars($userFile) . "' target='_blank'>view " . htmlspecialchars($userFile) . "</a><br>";
        // Update form
        echo "<form method='post' action='upload.php' enctype='multipart/form-data' style='display:inline;'>
            <input type='file' name='newfile' required>
            <button type='submit' name='update_file' value='" . htmlspecialchars($userFile) . "'>Update</button>
        </form>";
        // Delete form
        echo "<form method='post' action='upload.php' style='display:inline; margin-left:8px;'>
            <button type='submit' name='delete_file' value='" . htmlspecialchars($userFile) . "' onclick='return confirm(\"Are you sure to delete this file?\");'>Delete</button>
        </form>";
        echo "</div>";
    }
} else {
    echo "No files uploaded yet.";
}
$stmt->close();
?>
<!-- Back to Home Page option -->
<br>
<button onclick="window.location.href='newpage.php';" style="margin-bottom:20px; font-size:18px;">&#8592; Back to Home Page</button>
</body>
</html>
