<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Security error: invalid CSRF token.');
    }
}

$dsn = 'mysql:host=localhost;dbname=store;charset=utf8mb4';
$db_user = 'root';
$db_password = '';

try {
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$name_regex = "/^[a-zA-ZżźćńółęąśŻŹĆĄŚĘŁÓŃ]+$/";
if (!preg_match($name_regex, $first_name) || !preg_match($name_regex, $last_name)) {
    die('First name and last name can only contain letters.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email address.');
}

if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
    die('All fields must be filled.');
}

if (strpos($email, '@') === false || strpos($email, '.') === false) {
    die('Invalid email address.');
}

if (empty($first_name) || empty($last_name)) {
    die('First name and last name are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email address.');
}

if (strlen($password) < 8) {
    die('Password must be at least 8 characters long.');
}

if ($password !== $confirm_password) {
    die('Passwords do not match.');
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO customer (first_name, last_name, password, email) VALUES (:first_name, :last_name, :password, :email)');
    $stmt->execute([
        ':first_name' => htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'),
        ':last_name' => htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'),
        ':email' => $email,
        ':password' => $hashed_password,
    ]);
    echo 'Registration successful!';
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        die('The provided email address is already registered.');
    }
    die('An error occurred during registration: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);
header("Location: ../pages/login_page.php");
exit;
?>
