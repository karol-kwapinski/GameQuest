<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login_page.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$category_id = $_GET['category_id'] ?? null;

if (!$category_id) {
    die("Category ID not provided!");
}

$stmt = $pdo->prepare("SELECT * FROM category WHERE id = :id");
$stmt->execute([':id' => $category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    die("Category not found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category</title>
    <link rel="stylesheet" href="styles_for_edit_category.css">
</head>
<body>
    <h1>Edit Category</h1>
    <form action="update_category.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['id']) ?>">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required><br>

        <label for="image">Change Image (optional):</label>
        <input type="file" id="image" name="image"><br>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
