<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../pages/loginPage.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$game_id = $_POST['game_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];
$release_date = $_POST['release_date'];
$publisher = $_POST['publisher'];
$rating = $_POST['rating'];
$stock_quantity = $_POST['stock_quantity'];
$categories = $_POST['categories'] ?? [];
$platforms = $_POST['platforms'] ?? [];

$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image_path = 'images/' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
}

$query = "UPDATE videogame SET
    title = :title,
    description = :description,
    price = :price,
    release_date = :release_date,
    publisher = :publisher,
    rating = :rating,
    stock_quantity = :stock_quantity" . ($image_path ? ", image_url = :image_url" : "") . "
    WHERE id = :id";

$params = [
    ':title' => $title,
    ':description' => $description,
    ':price' => $price,
    ':release_date' => $release_date,
    ':publisher' => $publisher,
    ':rating' => $rating,
    ':stock_quantity' => $stock_quantity,
    ':id' => $game_id
];

if ($image_path) {
    $params[':image_url'] = $image_path;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$stmt = $pdo->prepare("DELETE FROM videogame_category WHERE video_game_id = :id");
$stmt->execute([':id' => $game_id]);

foreach ($categories as $category_id) {
    $stmt = $pdo->prepare("INSERT INTO videogame_category (video_game_id, category_id) VALUES (:game_id, :category_id)");
    $stmt->execute([':game_id' => $game_id, ':category_id' => $category_id]);
}

$stmt = $pdo->prepare("DELETE FROM videogame_platform WHERE video_game_id = :id");
$stmt->execute([':id' => $game_id]);

foreach ($platforms as $platform_id) {
    $stmt = $pdo->prepare("INSERT INTO videogame_platform (video_game_id, platform_id) VALUES (:game_id, :platform_id)");
    $stmt->execute([':game_id' => $game_id, ':platform_id' => $platform_id]);
}

header("Location: ../pages/admin_page.php");
exit();
?>
