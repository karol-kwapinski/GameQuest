<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login_page.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

$platform_id = $_GET['platform_id'] ?? null;

if (!$platform_id) {
    die("Platform ID not provided!");
}

$stmt = $pdo->prepare("SELECT * FROM platform WHERE id = :id");
$stmt->execute([':id' => $platform_id]);
$platform = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$platform) {
    die("Platform not found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Platform</title>
    <link rel="stylesheet" href="styles_for_edit_platform.css">
</head>
<body>
    <h1>Edit Platform</h1>
    <form action="update_platform.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="platform_id" value="<?= htmlspecialchars($platform['id']) ?>">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($platform['name']) ?>" required><br>

        <label for="image">Change Image:</label>
        <input type="file" id="image" name="image"><br>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
