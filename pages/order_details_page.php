<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit();
}

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$order_query = "SELECT * FROM `order` WHERE `id` = $order_id AND `customer_id` = {$_SESSION['user_id']}";
$order_result = $conn->query($order_query);

if (!$order_result) {
    die("Order query error: " . $conn->error);
}

if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();

$order_timestamp = strtotime($order['order_date']);
$current_timestamp = time();
$time_difference = $current_timestamp - $order_timestamp;

$new_status = $order['order_status'];
if ($time_difference < 60) $new_status = 'Order placed';
if ($time_difference > 60) $new_status = 'In progress';
if ($time_difference > 120) $new_status = 'Sent';
if ($time_difference > 180) $new_status = 'Delivered';

if ($new_status !== $order['order_status']) {
    $update_query = "UPDATE `order` SET `order_status` = '$new_status' WHERE `id` = $order_id";
    $conn->query($update_query);
    $order['order_status'] = $new_status;
}

$order_items_query = "SELECT oi.*, vg.title FROM `order_item` oi
                      JOIN `videogame` vg ON oi.videogame_id = vg.id
                      WHERE oi.order_id = $order_id";
$order_items_result = $conn->query($order_items_query);

if (!$order_items_result) {
    die("Order items query error: " . $conn->error);
}

$order_items = $order_items_result->fetch_all(MYSQLI_ASSOC);

$current_id = $order['contact_address_id'];
$contact_query = "SELECT * FROM `contact_address` WHERE `id` = $current_id";
$contact_result = $conn->query($contact_query);

if (!$contact_result) {
    die("Contact query error: " . $conn->error);
}

if ($contact_result->num_rows > 0) {
    $contact = $contact_result->fetch_assoc();
} else {
    $contact = [
        'email' => 'No data',
        'phone_number' => 'No data',
        'address' => 'No data',
        'city' => 'No data',
        'state' => 'No data',
        'postal_code' => 'No data',
        'country' => 'No data'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="../styles/styles_for_order_details.css">
</head>
<body>
    <h1>Order Details</h1>
    <p>Order Date: <?= $order['order_date'] ?></p>
    <p>Order Status: <?= $order['order_status'] ?></p>

    <h2>Contact and Address Details:</h2>
    <p><strong>Full Name: </strong><?= htmlspecialchars($contact['first_name']) ?> <?= htmlspecialchars($contact['last_name']) ?></p>
    <p><strong>Contact Email:</strong> <?= htmlspecialchars($contact['email']) ?></p>
    <p><strong>Phone number:</strong> <?= htmlspecialchars($contact['phone_number']) ?></p>
    <p><strong>Shipping Address:</strong>
        <?= htmlspecialchars($contact['street']) ?>
        <?= htmlspecialchars($contact['building_number']) ?><?= $contact['apartment_number'] != 0 ? ('/' . $contact['apartment_number']) : '' ?>,
        <?= htmlspecialchars($contact['postal_code']) ?>
        <?= htmlspecialchars($contact['city']) ?>
    </p>

    <h2>Order Items:</h2>
    <table>
        <thead>
            <tr>
                <th>Game Title</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= $item['game_price'] ?> zł</td>
                    <td><?= $item['quantity'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>Total Order Amount: <?= htmlspecialchars($order['total_amount']) ?> zł</p>
    <a href="profile_page.php">Back to Profile</a>
</body>
</html>
