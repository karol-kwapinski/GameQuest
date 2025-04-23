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

$categories_query = "SELECT * FROM category";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

$platforms_query = "SELECT * FROM platform";
$platforms_result = $conn->query($platforms_query);
$platforms = [];
while ($row = $platforms_result->fetch_assoc()) {
    $platforms[] = $row;
}

$games_query = "SELECT * FROM videogame ORDER BY videogame.rating DESC";
$games_result = $conn->query($games_query);
$games = [];
while ($row = $games_result->fetch_assoc()) {
    $games[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameQuest</title>
    <link rel="stylesheet" href="styles_for_index.css">
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo">
                <a href = "index.php">GameQuest</a>
            </div>
            <div class="user-info">
              <?php if (isset($_SESSION['user_id'])): ?>
                <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user_first_name']) ?></strong>!</p>
              <?php endif; ?>
                <form method="GET" action="game_catalogue.php" style="display: flex; align-items: center; gap: 10px;">
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
                        <a href="admin.php" class="profile-button">Admin</a>
                    <?php endif; ?>
                    <a href="profile.php" class="profile-button">My Profile</a>
                    <a href="logout.php">Log Out</a>
                <?php else: ?>
                    <a href="login_page.php">Log In</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
      <div class="banner-container">
          <div class="banner active">
              <img src="images/prince_of_persia_the_sands_of_time_banner.png" alt="Banner 1">
              <div class="overlay">
                  <h2>Prince of Persia: The Sands of Time</h2>
                  <a href="game.php?id=8" class="btn">Learn More</a>
              </div>
          </div>
          <div class="banner">
              <img src="images/portal_2_banner.png" alt="Banner 2">
              <div class="overlay">
                  <h2>Portal 2</h2>
                  <a href="game.php?id=33" class="btn">Learn More</a>
              </div>
          </div>
          <div class="banner">
              <img src="images/vampire_the_masquerade_bloodlines_banner.png" alt="Banner 3">
              <div class="overlay">
                  <h2>Vampire The Masquerade: Bloodlines</h2>
                  <a href="game.php?id=45" class="btn">Learn More</a>
              </div>
          </div>
      </div>
        <h2>Categories:</h2>
        <section class="categories">
            <button class="nav-button left" onclick="slideLeft('.category','.category-grid')">◄</button>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category">
                        <a href="game_catalogue.php?category=<?= $category['id'] ?>">
                            <img src="<?= $category['image_url'] ?>" alt="<?= htmlspecialchars($category['name']) ?>">
                            <h3><?= htmlspecialchars($category['name']) ?></h3>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="nav-button right" onclick="slideRight('.category-grid','.category')">►</button>
        </section>
        <section class="platforms">
            <h2>Platforms:</h2>
            <div class="platform-grid">
              <?php foreach ($platforms as $platform): ?>
                  <div class="platform">
                      <a href="game_catalogue.php?platform=<?= $platform['id'] ?>">
                          <img src="<?= $platform['image_url'] ?>" alt="<?= htmlspecialchars($platform['name']) ?>">
                      </a>
                      <h3><?= htmlspecialchars($platform['name']) ?></h3>
                  </div>
              <?php endforeach; ?>
            </div>
        </section>
        <h1>Recommended:</h1>
        <section class="product-section">
            <button class="nav-button2 left" onclick="slideLeft('.product','.product-grid')">◄</button>
            <div class="product-grid">
              <?php
              $counter = 0;
              foreach ($games as $game):
                  if ($counter >= 12) break;
              ?>
                  <div class="product">
                      <a href="game.php?id=<?= $game['id'] ?>">
                          <img src="<?= $game['image_url'] ?>" alt="<?= htmlspecialchars($game['title']) ?>">
                          <h3><?= htmlspecialchars($game['title']) ?></h3>
                      </a>
                  </div>
              <?php
                  $counter++;
              endforeach;
              ?>
            </div>
            <button class="nav-button2 right" onclick="slideRight('.product-grid','.product')">►</button>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-about">
                <h3>About Us</h3>
                <p>We are a leading online store offering a wide range of products at competitive prices.</p>
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

    <script>
      let scrollAmount = 0;
      const itemsPerGroup = 5;

      function slideLeft(item, grid) {
        const slider = document.querySelector(grid);
        const itemWidth = document.querySelector(item).offsetWidth;
        const scrollStep = itemWidth * itemsPerGroup;
        scrollAmount = Math.max(scrollAmount - scrollStep, 0);
        updateSlider(slider);
      }

      function slideRight(grid, item) {
        const slider = document.querySelector(grid);
        const itemWidth = document.querySelector(item).offsetWidth;
        const scrollStep = itemWidth * itemsPerGroup;
        const maxScroll = slider.scrollWidth - itemWidth * itemsPerGroup;
        scrollAmount = Math.min(scrollAmount + scrollStep, maxScroll);
        updateSlider(slider);
      }

      function updateSlider(slider) {
        slider.style.transform = `translateX(-${scrollAmount}px)`;
      }
      let currentIndex = 0;
      const banners = document.querySelectorAll('.banner');

      function showNextBanner() {
          banners[currentIndex].classList.remove('active');
          currentIndex = (currentIndex + 1) % banners.length;
          banners[currentIndex].classList.add('active');
      }

      setInterval(showNextBanner, 5000);


  </script>
</body>
</html>
