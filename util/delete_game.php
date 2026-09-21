<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../pages/loginPage.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

if (isset($_POST['game_id'])) {
    $game_id = $_POST['game_id'];

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("DELETE FROM videogame_category WHERE video_game_id = :id");
        $stmt->execute([':id' => $game_id]);

        $stmt = $pdo->prepare("DELETE FROM videogame_platform WHERE video_game_id = :id");
        $stmt->execute([':id' => $game_id]);

        $stmt = $pdo->prepare("DELETE FROM videogame WHERE id = :id");
        $stmt->execute([':id' => $game_id]);

        $pdo->commit();

        echo "The game has been successfully deleted!";
        header("Location: ../pages/admin_page.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "An error occurred while deleting the game: " . $e->getMessage();
    }
} else {
    echo "No game ID provided for deletion!";
}
?>
