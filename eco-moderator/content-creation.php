<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $tags = trim($_POST['category']); // Using category as primary tag
    $body = trim($_POST['content_body']);
    $extra_tags = trim($_POST['tags']); // Additional tags
    $author_id = $_SESSION['user_id'];

    // Combine category and extra tags
    $final_tags = $tags;
    if (!empty($extra_tags)) {
        $final_tags .= ', ' . $extra_tags;
    }

    // Image Upload Handling
    $image_url = ''; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/content/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('edu_') . '.' . $ext;
        $target = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_url = 'uploads/content/' . $filename;
        }
    }

    if (empty($title) || empty($body)) {
        $error = "Title and Body are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO educational_content (title, content_body, image, tags, author_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $body, $image_url, $final_tags, $author_id);
        
        if ($stmt->execute()) {
            header("Location: educational_content.php");
            exit();
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Educational Content - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .form-card {
            background: var(--color-white);
            padding: var(--space-6);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        .upload-box {
            border: 2px dashed var(--color-gray-300);
            padding: var(--space-8);
            text-align: center;
            border-radius: var(--radius-md);
            background: var(--color-gray-50);
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .upload-box:hover {
            border-color: var(--color-primary);
        }
        textarea {
            width: 100%;
            padding: var(--space-3);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            font-family: var(--font-sans);
            min-height: 200px;
            resize: vertical;
        }
        input[type="text"], select {
            width: 100%;
            padding: var(--space-3);
            border: 1px solid var(--color-gray-300);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-4);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <a href="educational_content.php" class="btn btn-secondary mb-4">&larr; Back to Library</a>
        
        <div class="form-card">
            <h2 class="mb-4">Create Educational Content</h2>
            <p class="text-gray-500 mb-6">Add recycling guides and tips for the knowledge hub.</p>

            <?php if($error) echo "<div class='badge badge-error mb-4'>$error</div>"; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Content Title *</label>
                <input type="text" name="title" placeholder="e.g. 'How to Properly Recycle Plastic Bottles'" required>

                <label>Category *</label>
                <select name="category" required>
                    <option value="General">Select category</option>
                    <option value="Plastic">Plastic</option>
                    <option value="Paper">Paper</option>
                    <option value="Metal">Metal</option>
                    <option value="E-Waste">E-Waste</option>
                    <option value="General Tips">General Tips</option>
                </select>

                <label>Content Body *</label>
                <textarea name="content_body" placeholder="Write your educational content here..." required></textarea>

                <label class="mt-4">Upload Supporting Image (Optional)</label>
                <div class="upload-box" onclick="document.getElementById('fileInput').click()">
                    <span style="font-size: 2rem;">📷</span>
                    <p>Click to upload image</p>
                    <small>PNG, JPG up to 5MB</small>
                    <input type="file" id="fileInput" name="image" style="display: none;" accept="image/*">
                </div>

                <label class="mt-4">Tags (comma separated)</label>
                <input type="text" name="tags" placeholder="plastic, bottles, recycling, tips">

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Publish Content</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>