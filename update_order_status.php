<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Access denied. User is not logged in.']));
}

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection error: ' . $conn->connect_error]));
}

$user_id = $_SESSION['user_id'];

$order_query = "SELECT * FROM `order` WHERE `customer_id` = $user_id AND `order_status` != 'delivered' ORDER BY `order_date` ASC";
$order_result = $conn->query($order_query);

if ($order_result->num_rows > 0) {
    while ($order = $order_result->fetch_assoc()) {
        $order_id = $order['id'];
        $current_status = $order['order_status'];

        $new_status = '';
        switch ($current_status) {
            case 'order placed':
                $new_status = 'in progress';
                break;
            case 'in progress':
                $new_status = 'sent';
                break;
            case 'sent':
                $new_status = 'delivered';
                break;
            default:
                continue 2;
        }

        $update_query = "UPDATE `order` SET `order_status` = '$new_status' WHERE `id` = $order_id";
        if (!$conn->query($update_query)) {
            die(json_encode(['status' => 'error', 'message' => 'Error updating status: ' . $conn->error]));
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No orders to update']);
}

$conn->close();
?>
