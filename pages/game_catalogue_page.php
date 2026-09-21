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

$categories = $conn->query("SELECT * FROM category");

$platforms = $conn->query("SELECT * FROM platform");

$selected_categories = [];
if (isset($_GET['categories']) && is_array($_GET['categories'])) {
    $selected_categories = $_GET['categories'];
} elseif (isset($_GET['category'])) {
    $selected_categories = [$_GET['category']];
}

$selected_platforms = [];
if (isset($_GET['platforms']) && is_array($_GET['platforms'])) {
    $selected_platforms = $_GET['platforms'];
} elseif (isset($_GET['platform'])) {
    $selected_platforms = [$_GET['platform']];
}

$games = [];
$where_conditions = [];

if (!empty($selected_categories)) {
    $category_ids = implode(',', array_map('intval', $selected_categories));
    $where_conditions[] = "gc.category_id IN ($category_ids)";
}

if (!empty($selected_platforms)) {
    $platform_ids = implode(',', array_map('intval', $selected_platforms));
    $where_conditions[] = "vp.platform_id IN ($platform_ids)";
}
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
if (!empty($search_query)) {
    $where_conditions[] = "v.title LIKE '%$search_query%'";
}
if (!empty($where_conditions)) {
    $query = "
        SELECT DISTINCT v.id, v.title, v.image_url
        FROM videogame v
        JOIN videogame_category gc ON v.id = gc.video_game_id
        JOIN videogame_platform vp ON v.id = vp.video_game_id
        WHERE " . implode(' AND ', $where_conditions);
} else {
    $query = "SELECT id, title, image_url FROM videogame";
}

$games = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game List</title>
    <link rel="stylesheet" href="../styles/styles_for_game_catalogue.css">
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo">
                <a href = "../index.php">GameQuest</a>
            </div>
            <div class="user-info">
              <?php if (isset($_SESSION['user_id'])): ?>
                <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user_first_name']) ?></strong>!</p>
              <?php endif; ?>
                <form method="GET" action="game_catalogue_page.php" style="display: flex; align-items: center; gap: 10px;">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search games..."
                        value="<?= htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '') ?>"
                    >
                    <button type="submit">Search</button>
                </form>
                <button type="submit">Search</button>
                <a href = "cart_page.php"> <img src="images/shopping-cart-349544_640.png"> </a>
                <?php if (isset($_SESSION['user_id'])): ?>
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
        <div id="sidebarAndMain">
            <div id="sidebar">
                <h3>Filters</h3>
                <form method="GET" action="game_catalogue_page.php">
                    <h4>Categories</h4>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <label>
                            <input
                                type="checkbox"
                                name="categories[]"
                                value="<?= $category['id'] ?>"
                                <?= in_array($category['id'], $selected_categories) ? 'checked' : '' ?>
                            >
                            <?= htmlspecialchars($category['name']) ?>
                        </label><br>
                    <?php endwhile; ?>

                    <h4>Platforms</h4>
                    <?php while ($platform = $platforms->fetch_assoc()): ?>
                        <label>
                            <input
                                type="checkbox"
                                name="platforms[]"
                                value="<?= $platform['id'] ?>"
                                <?= in_array($platform['id'], $selected_platforms) ? 'checked' : '' ?>
                            >
                            <?= htmlspecialchars($platform['name']) ?>
                        </label><br>
                    <?php endwhile; ?>

                    <button type="submit">Search</button>
                </form>
            </div>
            <div id="main">
                <?php if ($games->num_rows > 0): ?>
                    <?php while ($game = $games->fetch_assoc()): ?>
                        <div class="game">
                          <a href="game_page.php?id=<?= $game['id'] ?>">
                              <img src="<?= $game['image_url'] ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                              <h3><?= htmlspecialchars($game['title']) ?></h3>
                          </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No games to display.</p>
                <?php endif; ?>
            </div>
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
                <p>Email: contact@gamequest.com</p>
                <p>Phone: +48 123 456 789</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 GameQuest. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
