<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login_page.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$category_name = $_POST['category_name'];

$image = $_FILES['category_image'];
$image_path = '';
if ($image['error'] === UPLOAD_ERR_OK) {
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

echo "The category has been successfully added!";
?>
