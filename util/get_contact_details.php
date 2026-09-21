<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_id'])) {
    $contact_id = intval($_POST['contact_id']);

    $host = 'localhost';
    $db = 'store';
    $user = 'root';
    $password = '';
    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM contact_address WHERE id = $contact_id AND customer_id = $user_id LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $contact = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'contact' => $contact]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Contact data not found.']);
    }

    $conn->close();
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
