<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $body = $_POST['content_body'];
    $tags = $_POST['tags'];
    
    // Combine category and user tags
    $final_tags = $category;
    if (!empty($tags)) {
        $final_tags .= ", " . $tags;
    }

    $author_id = $_SESSION['user_id'];
    $image_path = '';

    // Handle Image Upload safely
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/content/";
        // Create directory if not exists (Fix for XAMPP)
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid('edu_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Save relative path for DB
            $image_path = "uploads/content/" . $file_name;
        } else {
            $message = "Error uploading image. Check folder permissions.";
        }
    }

    if (empty($message)) {
        $sql = "INSERT INTO educational_content (title, content_body, image, tags, author_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        // Correct bind parameters
        $stmt->bind_param("ssssi", $title, $body, $image_path, $final_tags, $author_id);

        if ($stmt->execute()) {
            echo "<script>alert('Content published successfully!'); window.location.href='educational_content.php';</script>";
            exit();
        } else {
            $message = "Database Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Content - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .form-card { background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 1.5rem; }
        label { font-weight: bold; display: block; margin-bottom: 0.5rem; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; }
        textarea { height: 200px; resize: vertical; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="educational_content.php" class="btn btn-secondary mb-4">&larr; Back</a>
        
        <div class="form-card">
            <h2 class="mb-4">Create Educational Content</h2>
            <?php if($message) echo "<div style='color:red; margin-bottom:15px;'>$message</div>"; ?>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="e.g., Recycling Plastic Bottles">
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select...</option>
                        <option value="Plastic">Plastic</option>
                        <option value="Paper">Paper</option>
                        <option value="Metal">Metal</option>
                        <option value="E-Waste">E-Waste</option>
                        <option value="General Tips">General Tips</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Content Body *</label>
                    <textarea name="content_body" required placeholder="Write your article here..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Image (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Additional Tags (comma separated)</label>
                    <input type="text" name="tags" placeholder="green, bottles, tips">
                </div>
                
                <button type="submit" class="btn btn-primary">Publish</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>