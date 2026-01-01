<?php
session_start();
require_once '../php/config.php';
$conn = getDBConnection();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$message = '';

$stmt = $conn->prepare("SELECT * FROM educational_content WHERE content_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$content = $stmt->get_result()->fetch_assoc();
if (!$content) die("Content not found.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $body = $_POST['content_body'];
    $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
    
    // Image Handling Logic
    $final_image_path = $content['image']; 

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/content/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid('edu_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            if (!empty($content['image']) && file_exists("../" . $content['image'])) {
                unlink("../" . $content['image']);
            }
            $final_image_path = "uploads/content/" . $file_name;
        } else {
            $message = "Error uploading new image.";
        }
    } 
    elseif (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if (!empty($content['image']) && file_exists("../" . $content['image'])) {
            unlink("../" . $content['image']);
        }
        $final_image_path = '';
    }

    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE educational_content SET title=?, content_body=?, tags=?, image=? WHERE content_id=?");
        $stmt->bind_param("ssssi", $title, $body, $tags, $final_image_path, $id);
        
        if ($stmt->execute()) {
            header("Location: educational_content.php");
            exit();
        } else {
            $message = "Error updating content.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .page-hero {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 1rem;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .page-hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .page-hero p { font-size: 1.1rem; opacity: 0.95; margin: 0; }

        .form-card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; border: 1px solid #e2e8f0; }
        .form-group { margin-bottom: 1.5rem; }
        label { font-weight: 600; display: block; margin-bottom: 0.5rem; color: #4a5568; }
        input[type="text"], textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; transition: border-color 0.2s; }
        input:focus, textarea:focus { outline: none; border-color: var(--color-primary); }
        textarea { height: 200px; resize: vertical; }
        
        .image-preview-container {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .page-hero { padding: 2rem 1rem; }
            .form-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container" style="padding-top: 2rem;">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
            <a href="educational_content.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: white; color: var(--color-gray-700); text-decoration: none; border-radius: 8px; border: 1px solid var(--color-gray-200); font-weight: 500; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='var(--color-gray-50)'; this.style.borderColor='var(--color-gray-300)';" onmouseout="this.style.background='white'; this.style.borderColor='var(--color-gray-200)';">
                <i class="fas fa-arrow-left"></i> <span>Back</span>
            </a>
        </div>

        <div class="page-hero">
            <h1>Edit Content</h1>
            <p>Update article details and information.</p>
        </div>

        <div class="form-card">
            <?php if($message) echo "<div style='color:red; margin-bottom:15px; background:#fff5f5; padding:10px; border-radius:8px;'>$message</div>"; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($content['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Body</label>
                    <textarea name="content_body" required><?php echo htmlspecialchars($content['content_body']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Manage Image</label>
                    <?php if (!empty($content['image'])): ?>
                        <div class="image-preview-container">
                            <img src="../<?php echo htmlspecialchars($content['image']); ?>" style="max-height: 150px; border-radius: 6px; display: block; margin-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="delete_image" id="del_img" value="1">
                                <label for="del_img" style="margin: 0; font-weight: normal; color: #e53e3e;">Delete this image</label>
                            </div>
                        </div>
                        <label style="font-weight: 500; font-size: 0.9rem; margin-top: 10px;">Or Upload New (Replaces current):</label>
                    <?php else: ?>
                        <p style="color: #718096; font-size: 0.9rem; margin-bottom: 10px;">No image currently attached.</p>
                        <label style="font-weight: 500; font-size: 0.9rem;">Upload Image:</label>
                    <?php endif; ?>
                    
                    <input type="file" name="image" accept="image/*" style="border: none; padding-left: 0; width: 100%;">
                </div>

                <div class="form-group">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags" value="<?php echo htmlspecialchars($content['tags']); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 8px; padding: 12px; font-weight: 600;">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>