<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];

    $host = 'localhost';
    $db = 'store';
    $user = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            SELECT vg.id, vg.title, vg.price, c.quantity
            FROM cart c
            JOIN videogame vg ON c.game_id = vg.id
            WHERE c.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function remove_from_session_cart($game_id) {
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $game_id) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_id'])) {
    $game_id = $_POST['game_id'];

    if ($is_logged_in) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND game_id = :game_id");
        $stmt->execute(['user_id' => $user_id, 'game_id' => $game_id]);
    } else {
        remove_from_session_cart($game_id);
    }

    header('Location: cart_page.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../styles/styles_for_cart_page.css">
</head>
<body>
<header>
    <div class="top-bar">
        <div class="logo">
            <a href="../index.php">GameQuest</a>
        </div>
        <div class="user-info">
            <?php if ($is_logged_in): ?>
                <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user_first_name']) ?></strong>!</p>
            <?php endif; ?>
            <input type="text" placeholder="Search products...">
            <button type="submit">Search</button>
            <a href="cart_page.php"> <img src="images/shopping-cart-349544_640.png"> </a>
            <?php if ($is_logged_in): ?>
                <?php if ($_SESSION['is_admin'] == 1): ?>
                    <a href="admin_page.php" class="profile-button">Admin</a>
                <?php endif; ?>
                <a href="profile_page.php" class="profile-button">My Profile</a>
                <a href="../util/logout.php">Log Out</a>
            <?php else: ?>
                <a href="login_page.php">Log In</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
    <div class="cart">
        <h1>Your Cart</h1>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($cart_items as $item): ?>
                    <li>
                        <?= htmlspecialchars($item['title']) ?> - <?= number_format($item['price'], 2) ?> PLN
                        (<?= $item['quantity'] ?> <?= $item['quantity'] > 1 ? 'copies' : 'copy' ?>)
                        <form action="cart_page.php" method="POST" style="display:inline;">
                            <input type="hidden" name="game_id" value="<?= $item['id'] ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($is_logged_in): ?>
                <form action="checkout_page.php" method="POST">
                    <button type="submit">Proceed to Checkout</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<footer>
    <div class="footer-content">
        <div class="footer-about">
            <h3>About Us</h3>
            <p>We are a leading online store offering a wide range of products at competitive prices. Check out our promotions!</p>
        </div>
        <div class="footer-links">
            <h3>Useful Links</h3>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms and Conditions</a></li>
                <li><a href="#">Return Policy</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h3>Contact</h3>
            <p>Email: contact@store.com</p>
            <p>Phone: +48 123 456 789</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 Online Store. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
