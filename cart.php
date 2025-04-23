<?php
session_start();

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_id'])) {
    $game_id = intval($_POST['game_id']);

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $check_cart_query = "
            SELECT * FROM cart WHERE user_id = $user_id AND game_id = $game_id
        ";
        $result = $conn->query($check_cart_query);

        if ($result->num_rows > 0) {
            $update_query = "
                UPDATE cart
                SET quantity = quantity + 1
                WHERE user_id = $user_id AND game_id = $game_id
            ";
            $conn->query($update_query);
        } else {
            $game_query = "SELECT id, title, price, image_url FROM videogame WHERE id = $game_id";
            $game_result = $conn->query($game_query);

            if ($game_result->num_rows > 0) {
                $game = $game_result->fetch_assoc();
                $insert_query = "
                    INSERT INTO cart (user_id, game_id, quantity)
                    VALUES ($user_id, $game_id, 1)
                ";
                $conn->query($insert_query);
            }
        }
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$game_id])) {
            $_SESSION['cart'][$game_id]['quantity'] += 1;
        } else {
            $game_query = "SELECT id, title, price, image_url FROM videogame WHERE id = $game_id";
            $result = $conn->query($game_query);

            if ($result->num_rows > 0) {
                $game = $result->fetch_assoc();
                $_SESSION['cart'][$game_id] = [
                    'id' => $game['id'],
                    'title' => $game['title'],
                    'price' => $game['price'],
                    'image_url' => $game['image_url'],
                    'quantity' => 1,
                ];
            }
        }
    }

    header("Location: game.php?id=$game_id");
    exit();
}

$conn->close();
?>
