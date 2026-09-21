<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../pages/loginPage.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$platform_id = $_POST['platform_id'];
$name = $_POST['name'];

$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image_path = 'images/' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
}

$query = "UPDATE platform SET name = :name" . ($image_path ? ", image_url = :image_url" : "") . " WHERE id = :id";
$params = [':name' => $name, ':id' => $platform_id];

if ($image_path) {
    $params[':image_url'] = $image_path;
}

$stmt = $pdo->prepare($query);

if ($stmt->execute($params)) {
    echo "The platform was successfully updated!";
    header("Location: ../pages/admin_page.php");
    exit();
} else {
    echo "An error occurred while updating the platform.";
}
?>
