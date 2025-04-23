<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = :user_id AND game_id = :game_id");
    $stmt->execute(['user_id' => $user_id, 'game_id' => $game_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = :user_id AND game_id = :game_id");
        $stmt->execute(['user_id' => $user_id, 'game_id' => $game_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, game_id, quantity) VALUES (:user_id, :game_id, 1)");
        $stmt->execute(['user_id' => $user_id, 'game_id' => $game_id]);
    }

    header("Location: cart_page.php");
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
