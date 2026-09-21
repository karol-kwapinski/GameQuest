<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../pages/loginPage.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

if (isset($_POST['platform_id'])) {
    $platform_id = $_POST['platform_id'];

    $stmt = $pdo->prepare("DELETE FROM platform WHERE id = :id");
    $stmt->execute([':id' => $platform_id]);

    $stmt = $pdo->prepare("DELETE FROM videogame_platform WHERE platform_id = :id");
    $stmt->execute([':id' => $platform_id]);

    echo "The platform has been successfully deleted!";
    header("Location: ../pages/admin_page.php");
    exit();
} else {
    echo "No platform ID provided for deletion!";
}
?>
