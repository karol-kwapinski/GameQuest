<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: loginPage.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

if (isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];

    $stmt = $pdo->prepare("DELETE FROM category WHERE id = :id");
    $stmt->execute([':id' => $category_id]);

    $stmt = $pdo->prepare("DELETE FROM videogame_category WHERE category_id = :id");
    $stmt->execute([':id' => $category_id]);

    echo "The category has been successfully deleted!";
    header("Location: admin.php");
    exit();
} else {
    echo "No category ID provided for deletion!";
}
?>
