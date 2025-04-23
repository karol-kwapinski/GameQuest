<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login_page.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

if (!isset($_POST['category_name']) || empty($_POST['category_name'])) {
    die('Category name is required!');
}
$category_name = htmlspecialchars(trim($_POST['category_name']), ENT_QUOTES, 'UTF-8');

$image = $_FILES['category_image'];
$image_path = '';

if ($image['error'] === UPLOAD_ERR_OK) {
    $image_type = mime_content_type($image['tmp_name']);
    if (!in_array($image_type, ['image/jpeg', 'image/png', 'image/gif'])) {
        die('Invalid image type!');
    }

    $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $valid_extensions)) {
        die('Invalid file extension!');
    }
    
    $image_path = 'images/' . basename($image['name']);
    if (!move_uploaded_file($image['tmp_name'], $image_path)) {
        die('Failed to save the image!');
    }
} else {
    die('Error uploading the image!');
}

$stmt = $pdo->prepare("INSERT INTO category (name, image_url) VALUES (:name, :image_url)");
$stmt->execute([
    ':name' => $category_name,
    ':image_url' => $image_path,
]);

header('Location: admin.php');
exit;
?>
