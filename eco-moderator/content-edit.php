<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'eco-moderator') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$message = '';

// Fetch existing data
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $stmt = $conn->prepare("SELECT * FROM educational_content WHERE content_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $content = $stmt->get_result()->fetch_assoc();
    if (!$content) die("Content not found.");
}

// Update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $body = $_POST['content_body'];
    $tags = $_POST['category']; // Updating primary tag
    
    $stmt = $conn->prepare("UPDATE educational_content SET title=?, content_body=?, tags=? WHERE content_id=?");
    $stmt->bind_param("sssi", $title, $body, $tags, $id);
    
    if ($stmt->execute()) {
        header("Location: educational_content.php");
        exit();
    } else {
        $message = "Error updating content.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Content - APRecycle</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <a href="educational_content.php" class="btn btn-secondary mb-4">&larr; Cancel</a>
        <div class="card" style="padding: 2rem;">
            <h2>Edit Content</h2>
            <form method="POST">
                <label>Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($content['title']); ?>" style="width:100%; padding: 10px; margin-bottom: 15px;" required>
                
                <label>Category/Tags</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($content['tags']); ?>" style="width:100%; padding: 10px; margin-bottom: 15px;" required>
                
                <label>Body</label>
                <textarea name="content_body" style="width:100%; height: 200px; padding: 10px;" required><?php echo htmlspecialchars($content['content_body']); ?></textarea>
                
                <button type="submit" class="btn btn-primary mt-4">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>