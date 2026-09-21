<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Security error: invalid CSRF token.');
}

$email = htmlspecialchars(trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)), ENT_QUOTES, 'UTF-8');
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'All fields are required.';
    header('Location: ../pages/login_page.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customer WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'] == 1;

    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $game_id => $game) {
            $quantity = $game['quantity'];

            $cart_check_query = "
                SELECT id FROM cart
                WHERE user_id = :user_id AND game_id = :game_id
            ";
            $stmt = $pdo->prepare($cart_check_query);
            $stmt->execute([
                ':user_id' => $user['id'],
                ':game_id' => $game_id
            ]);

            if ($stmt->rowCount() > 0) {
                $update_query = "
                    UPDATE cart
                    SET quantity = quantity + :quantity
                    WHERE user_id = :user_id AND game_id = :game_id
                ";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([
                    ':quantity' => $quantity,
                    ':user_id' => $user['id'],
                    ':game_id' => $game_id
                ]);
            } else {
                try{
                  $insert_query = "
                      INSERT INTO cart (user_id, game_id, quantity)
                      VALUES (:user_id, :game_id, :quantity)
                  ";
                  $stmt = $pdo->prepare($insert_query);
                  $stmt->execute([
                      ':user_id' => $user['id'],
                      ':game_id' => $game_id,
                      ':quantity' => $quantity
                  ]);
                }
                catch (PDOException $e) {
                    die("Error: " . $e->getMessage());
                }
            }
        }

        unset($_SESSION['cart']);
    }

    header('Location: ../index.php');
    exit;
} else {
    $_SESSION['login_error'] = 'Invalid email or password.';
    header('Location: ../pages/login_page.php');
    exit;
}
?>
