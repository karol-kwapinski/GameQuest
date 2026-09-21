<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login_page.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$game_id = $_GET['game_id'] ?? null;

if (!$game_id) {
    die("Game ID not provided!");
}

$stmt = $pdo->prepare("SELECT * FROM videogame WHERE id = :id");
$stmt->execute([':id' => $game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("Game not found!");
}

$stmt = $pdo->prepare("SELECT category_id FROM videogame_category WHERE video_game_id = :id");
$stmt->execute([':id' => $game_id]);
$assigned_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT platform_id FROM videogame_platform WHERE video_game_id = :id");
$stmt->execute([':id' => $game_id]);
$assigned_platforms = $stmt->fetchAll(PDO::FETCH_COLUMN);

$categories = $pdo->query("SELECT * FROM category")->fetchAll(PDO::FETCH_ASSOC);
$platforms = $pdo->query("SELECT * FROM platform")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Game</title>
    <link rel="stylesheet" href=../styles/styles_for_edit_game.css>
</head>
<body>
    <h1>Edit Game</h1>
    <form action="../util/update_game.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($game['title']) ?>" required><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($game['description']) ?></textarea><br>

        <label for="price">Price:</label>
        <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($game['price']) ?>" required><br>

        <label for="release_date">Release Date:</label>
        <input type="date" id="release_date" name="release_date" value="<?= htmlspecialchars($game['release_date']) ?>" required><br>

        <label for="publisher">Publisher:</label>
        <input type="text" id="publisher" name="publisher" value="<?= htmlspecialchars($game['publisher']) ?>" required><br>

        <label for="rating">Rating:</label>
        <input type="number" step="0.1" id="rating" name="rating" value="<?= htmlspecialchars($game['rating']) ?>" required><br>

        <label for="stock_quantity">Stock Quantity:</label>
        <input type="number" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($game['stock_quantity']) ?>" required><br>

        <label for="image">Change Image (optional):</label>
        <input type="file" id="image" name="image"><br>

        <label for="categories">Categories:</label>
        <select id="categories" name="categories[]" multiple>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= in_array($category['id'], $assigned_categories) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="platforms">Platforms:</label>
        <select id="platforms" name="platforms[]" multiple>
            <?php foreach ($platforms as $platform): ?>
                <option value="<?= $platform['id'] ?>" <?= in_array($platform['id'], $assigned_platforms) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($platform['name']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
