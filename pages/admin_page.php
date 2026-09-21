<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../pages/login_page.php");
    exit();
}

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['add_category'])) {
    $category_name = $conn->real_escape_string($_POST['category_name']);
    $category_image = $conn->real_escape_string($_POST['category_image']);
    $conn->query("INSERT INTO category (name, image_url) VALUES ('$category_name', '$category_image')");
    echo "<p>Category has been added.</p>";
}

if (isset($_POST['add_platform'])) {
    $platform_name = $conn->real_escape_string($_POST['platform_name']);
    $platform_image = $conn->real_escape_string($_POST['platform_image']);
    $conn->query("INSERT INTO platform (name, image_url) VALUES ('$platform_name', '$platform_image')");
    echo "<p>Platform has been added.</p>";
}

if (isset($_POST['add_game'])) {
    $game_title = $conn->real_escape_string($_POST['game_title']);
    $game_description = $conn->real_escape_string($_POST['game_description']);
    $game_price = $conn->real_escape_string($_POST['game_price']);
    $release_date = $conn->real_escape_string($_POST['release_date']);
    $publisher = $conn->real_escape_string($_POST['publisher']);
    $game_rating = $conn->real_escape_string($_POST['game_rating']);
    $stock_quantity = (int)$_POST['stock_quantity'];
    $image_url = $conn->real_escape_string($_POST['image_url']);
    $category_ids = $_POST['category_ids'] ?? [];
    $platform_ids = $_POST['platform_ids'] ?? [];

    $conn->query("INSERT INTO videogame (title, description, price, release_date, publisher, rating, stock_quantity, image_url)
                  VALUES ('$game_title', '$game_description', '$game_price', '$release_date', '$publisher', '$game_rating', $stock_quantity, '$image_url')");
    $game_id = $conn->insert_id;

    foreach ($category_ids as $category_id) {
        $conn->query("INSERT INTO videogame_category (video_game_id, category_id) VALUES ($game_id, $category_id)");
    }

    foreach ($platform_ids as $platform_id) {
        $conn->query("INSERT INTO videogame_platform (video_game_id, platform_id) VALUES ($game_id, $platform_id)");
    }

    echo "<p>Game has been added.</p>";
}

$categories = $conn->query("SELECT * FROM category");
$platforms = $conn->query("SELECT * FROM platform");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../styles/styles_for_admin.css">
</head>
<body>
    <h1>Admin Panel</h1>

    <h2>Add New Category</h2>
    <form action="../util/add_category.php" method="POST" enctype="multipart/form-data">
        <label for="name">Category Name:</label>
        <input type="text" id="name" name="category_name" required>
        <br><br>

        <label for="image">Category Image:</label>
        <input type="file" id="image" name="category_image" accept="image/*" required>
        <br><br>

        <button type="submit" name="add_category">Add Category</button>
    </form>

    <h2>Add New Platform</h2>
    <form action="../util/add_platform.php" method="POST" enctype="multipart/form-data">
        <label for="name">Platform Name:</label>
        <input type="text" id="name" name="platform_name" required>
        <br><br>

        <label for="image">Platform Image:</label>
        <input type="file" id="image" name="platform_image" accept="image/*" required>
        <br><br>

        <button type="submit" name="add_platform">Add Platform</button>
    </form>

    <h2>Add New Game</h2>
    <form action="../util/add_game.php" method="POST" enctype="multipart/form-data">
        <label for="game_title">Game Title:</label>
        <input type="text" id="game_title" name="game_title" required>
        <br><br>

        <label for="game_description">Game Description:</label>
        <textarea id="game_description" name="game_description" required></textarea>
        <br><br>

        <label for="game_price">Price (in PLN):</label>
        <input type="number" id="game_price" name="game_price" step="0.01" required>
        <br><br>

        <label for="release_date">Release Date:</label>
        <input type="date" id="release_date" name="release_date" required>
        <br><br>

        <label for="publisher">Publisher:</label>
        <input type="text" id="publisher" name="publisher" required>
        <br><br>

        <label for="game_rating">Rating (0-10):</label>
        <input type="number" id="game_rating" name="game_rating" step="0.1" min="0" max="10" required>
        <br><br>

        <label for="stock_quantity">Stock Quantity:</label>
        <input type="number" id="stock_quantity" name="stock_quantity" required>
        <br><br>

        <label for="image">Add Game Image:</label>
        <input type="file" id="image" name="image_url" accept="image/*" required>
        <br><br>

        <label for="categories">Categories (hold Ctrl/Cmd to select multiple):</label>
        <select id="categories" name="category_ids[]" multiple size="5" required>
            <?php
            $pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');
            $categories = $pdo->query("SELECT id, name FROM category")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $category) {
                echo "<option value='{$category['id']}'>{$category['name']}</option>";
            }
            ?>
        </select>
        <br><br>

        <label for="platforms">Platforms (hold Ctrl/Cmd to select multiple):</label>
        <select id="platforms" name="platform_ids[]" multiple size="5" required>
            <?php
            $platforms = $pdo->query("SELECT id, name FROM platform")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($platforms as $platform) {
                echo "<option value='{$platform['id']}'>{$platform['name']}</option>";
            }
            ?>
        </select>
        <br><br>

        <button type="submit" name="add_game">Add Game</button>
    </form>

    <table>
        <tr>
            <th>Game Title</th>
            <th>Action</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT id, title FROM videogame");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>
                <form action='../util/delete_game.php' method='POST'>
                    <input type='hidden' name='game_id' value='" . $row['id'] . "'>
                    <button type='submit' onclick='return confirm(\"Are you sure you want to delete this game?\")'>Delete</button>
                </form>
            </td>";
            echo "<td>
                <form action='../util/edit_game.php' method='GET' style='display: inline-block;'>
                    <input type='hidden' name='game_id' value='" . $row['id'] . "'>
                    <button type='submit'>Edit</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <table>
        <tr>
            <th>Category Name</th>
            <th>Action</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT id, name FROM category");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>
                <form action='../util/delete_category.php' method='POST'>
                    <input type='hidden' name='category_id' value='" . $row['id'] . "'>
                    <button type='submit' onclick='return confirm(\"Are you sure you want to delete this category?\")'>Delete</button>
                </form>
            </td>";
            echo "<td>
                <form action='../pages/edit_category_page.php' method='GET' style='display: inline-block;'>
                    <input type='hidden' name='category_id' value='" . $row['id'] . "'>
                    <button type='submit'>Edit</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <table>
        <tr>
            <th>Platform Name</th>
            <th>Action</th>
        </tr>
        <?php
        $stmt = $pdo->query("SELECT id, name FROM platform");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>
                <form action='../util/delete_platform.php' method='POST'>
                    <input type='hidden' name='platform_id' value='" . $row['id'] . "'>
                    <button type='submit' onclick='return confirm(\"Are you sure you want to delete this platform?\")'>Delete</button>
                </form>
            </td>";
            echo "<td>
                <form action='../pages/edit_platform_page.php' method='GET' style='display: inline-block;'>
                    <input type='hidden' name='platform_id' value='" . $row['id'] . "'>
                    <button type='submit'>Edit</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>
