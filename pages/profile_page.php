<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.php");
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

$user_id = $_SESSION['user_id'];
$order_query = "SELECT * FROM `order` WHERE `customer_id` = $user_id";
$order_result = $conn->query($order_query);

$orders = [];
if ($order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $order_timestamp = strtotime($row['order_date']);
        $current_timestamp = time();
        $time_difference = $current_timestamp - $order_timestamp;

        $new_status = $row['order_status'];
        if ($time_difference < 60) {
            $new_status = 'Order placed';
        } elseif ($time_difference >= 60 && $time_difference < 120) {
            $new_status = 'In progress';
        } elseif ($time_difference >= 120 && $time_difference < 180) {
            $new_status = 'Sent';
        } elseif ($time_difference >= 180) {
            $new_status = 'Delivered';
        }

        if ($new_status !== $row['order_status']) {
            $update_query = "UPDATE `order` SET `order_status` = '$new_status' WHERE `id` = {$row['id']}";
            $conn->query($update_query);
            $row['order_status'] = $new_status;
        }

        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../styles/styles_for_profile.css">
</head>
<body>
    <h2>Profile tabs:</h2>
    <ul>
        <li><a href="profile_page.php">Orders</a></li>
        <li><a href="contact_and_address_page.php">Contact and Address Information</a></li>
    </ul>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_first_name']) ?>!</h1>
    <h2>Your orders:</h2>

    <?php if (empty($orders)): ?>
        <p>You have no orders.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= $order['order_status'] ?></td>
                        <td><a href="order_details_page.php?order_id=<?= $order['id'] ?>">View details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="../index.php">Back to the homepage</a>
</body>
</html>
