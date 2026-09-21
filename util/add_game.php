<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../pages/login_page.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$game_title = htmlspecialchars(trim($_POST['game_title']), ENT_QUOTES, 'UTF-8');
$game_description = htmlspecialchars(trim($_POST['game_description']), ENT_QUOTES, 'UTF-8');
$game_price = trim($_POST['game_price']);
$release_date = trim($_POST['release_date']);
$publisher = htmlspecialchars(trim($_POST['publisher']), ENT_QUOTES, 'UTF-8');
$game_rating = trim($_POST['game_rating']);
$stock_quantity = trim($_POST['stock_quantity']);
$categories = $_POST['category_ids'];
$platforms = $_POST['platform_ids'];

$image = $_FILES['image_url'];
$image_path = '';
if ($image['error'] === UPLOAD_ERR_OK) {
    $image_path = 'images/' . basename($image['name']);
    if (!move_uploaded_file($image['tmp_name'], $image_path)) {
        die('Failed to save the image!');
    }
} else {
    die('Error uploading the image!');
}

$stmt = $pdo->prepare("INSERT INTO videogame (title, description, price, release_date, publisher, rating, stock_quantity, image_url)
    VALUES (:title, :description, :price, :release_date, :publisher, :rating, :stock_quantity, :image_url)");
$stmt->execute([
    ':title' => $game_title,
    ':description' => $game_description,
    ':price' => $game_price,
    ':release_date' => $release_date,
    ':publisher' => $publisher,
    ':rating' => $game_rating,
    ':stock_quantity' => $stock_quantity,
    ':image_url' => $image_path,
]);

$video_game_id = $pdo->lastInsertId();

foreach ($categories as $category_id) {
    $stmt = $pdo->prepare("INSERT INTO videogame_category (video_game_id, category_id) VALUES (:video_game_id, :category_id)");
    $stmt->execute([
        ':video_game_id' => $video_game_id,
        ':category_id' => $category_id,
    ]);
}

foreach ($platforms as $platform_id) {
    $stmt = $pdo->prepare("INSERT INTO videogame_platform (video_game_id, platform_id) VALUES (:video_game_id, :platform_id)");
    $stmt->execute([
        ':video_game_id' => $video_game_id,
        ':platform_id' => $platform_id,
    ]);
}

echo "The game has been successfully added!";
header('Location: ../pages/admin_page.php');
exit;
?>
