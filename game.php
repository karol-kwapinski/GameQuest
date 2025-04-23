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

$game_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($game_id > 0) {
    $game_query = "
        SELECT *
        FROM videogame
        WHERE id = $game_id
    ";
    $game_result = $conn->query($game_query);

    if ($game_result->num_rows > 0) {
        $game = $game_result->fetch_assoc();

        $categories_query = "
            SELECT c.name
            FROM category c
            JOIN videogame_category vc ON c.id = vc.category_id
            WHERE vc.video_game_id = $game_id
        ";
        $categories_result = $conn->query($categories_query);
        $categories = [];
        while ($row = $categories_result->fetch_assoc()) {
            $categories[] = $row['name'];
        }

        $platforms_query = "
            SELECT p.name
            FROM platform p
            JOIN videogame_platform vp ON p.id = vp.platform_id
            WHERE vp.video_game_id = $game_id
        ";
        $platforms_result = $conn->query($platforms_query);
        $platforms = [];
        while ($row = $platforms_result->fetch_assoc()) {
            $platforms[] = $row['name'];
        }
    } else {
        $error = "The game with the given ID does not exist.";
    }
} else {
    $error = "Invalid game ID.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles_for_game.css">
    <title><?= isset($game) ? htmlspecialchars($game['title']) : "Game not found" ?></title>
</head>
<header>
    <div class="top-bar">
        <div class="logo">
            <a href = "index.php">GameQuest</a>
        </div>
        <div class="user-info">
            <?php if (isset($_SESSION['user_id'])): ?>
              <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user_first_name']) ?></strong>!</p>
            <?php endif; ?>
            <input type="text" placeholder="Search products...">
            <button type="submit">Search</button>
            <a href = "cart_page.php"> <img src="images/shopping-cart-349544_640.png"> </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['is_admin'] == 1): ?>
                    <a href="admin.php" class="profile-button">Admin</a>
                <?php endif; ?>
                <a href="profile.php" class="profile-button">My Profile</a>
                <a href="logout.php">Log Out</a>
            <?php else: ?>
                <a href="login_page.php" class="login-button">Log In</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<body>

    <div class="container">
        <?php if (isset($game)): ?>
            <h1><?= htmlspecialchars($game['title']) ?></h1>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($game['description'])) ?></p>
            <p><strong>Price:</strong> <?= number_format($game['price'], 2) ?> PLN</p>
            <p><strong>Release Date:</strong> <?= htmlspecialchars($game['release_date']) ?></p>
            <p><strong>Publisher:</strong> <?= htmlspecialchars($game['publisher']) ?></p>
            <p><strong>Rating:</strong> <?= htmlspecialchars($game['rating']) ?> / 10</p>
            <p><strong>Stock Quantity:</strong> <?= htmlspecialchars($game['stock_quantity']) ?></p>
            <p><strong>Categories:</strong> <?= !empty($categories) ? implode(', ', $categories) : 'None' ?></p>
            <p><strong>Platforms:</strong> <?= !empty($platforms) ? implode(', ', $platforms) : 'None' ?></p>
            <img src="<?= $game['image_url'] ?>" alt="<?= htmlspecialchars($game['title']) ?>">

            <div class="actions">
                <form action="cart.php" method="POST" style="display:inline;">
                    <input type="hidden" name="game_id" value="<?= $game_id ?>">
                    <button type="submit" class="button">Add to Cart</button>
                </form>

                <a href="buy.php?id=<?= $game_id ?>" class="button">Buy</a>
            </div>
        <?php elseif (isset($error)): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>

</body>
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
            <p>Email: contact@gamequest.com</p>
            <p>Phone: +48 123 456 789</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 GameQuest. All rights reserved.</p>
    </div>
</footer>
</html>
