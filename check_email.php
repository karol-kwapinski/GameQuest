<?php
$pdo = new PDO('mysql:host=localhost;dbname=store;charset=utf8mb4', 'root', '');

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customer WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $email_exists = $stmt->fetchColumn();

    echo json_encode(['exists' => $email_exists > 0]);
} else {
    echo json_encode(['exists' => false]);
}
