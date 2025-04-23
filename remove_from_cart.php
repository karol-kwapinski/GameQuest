<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: loginPage.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;

if ($game_id <= 0) {
    die("Invalid game ID.");
}

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND game_id = :game_id");
    $stmt->execute(['user_id' => $user_id, 'game_id' => $game_id]);

    header('Location: cartPage.php');
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
