<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$contact_id = isset($_POST['contact_id']) ? $_POST['contact_id'] : null;

$host = 'localhost';
$db = 'store';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT email FROM customer WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }

    $cart_stmt = $pdo->prepare("
        SELECT vg.id AS videogame_id, vg.title, vg.price, vg.stock_quantity, c.quantity
        FROM cart c
        JOIN videogame vg ON c.game_id = vg.id
        WHERE c.user_id = :user_id
    ");
    $cart_stmt->execute(['user_id' => $user_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cart_items) {
        echo json_encode(['status' => 'error', 'message' => 'Your cart is empty. You cannot place an order.']);
        exit;
    }

    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    if ($contact_id) {
        $address_stmt = $pdo->prepare("SELECT id FROM contact_address WHERE id = :contact_id AND customer_id = :user_id LIMIT 1");
        $address_stmt->execute(['contact_id' => $contact_id, 'user_id' => $user_id]);
        $address = $address_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$address) {
            echo json_encode(['status' => 'error', 'message' => 'Contact address not found.']);
            exit;
        }
        $contact_address_id = $address['id'];
    } else {
        $address_stmt = $pdo->prepare("SELECT id FROM contact_address WHERE id = :contact_id AND customer_id = :user_id LIMIT 1");
        $address_stmt->execute(['contact_id' => $contact_id, 'user_id' => $user_id]);
        $address = $address_stmt->fetch(PDO::FETCH_ASSOC);

        if ($address) {
            $contact_address_id = $address['id'];
        } else {
            $address_stmt = $pdo->prepare("
                INSERT INTO contact_address (customer_id, first_name, last_name, email, phone_number, address, city, state, postal_code, country, is_default)
                VALUES (:user_id, :first_name, :last_name, :email, :phone_number, :address, :city, :state, :postal_code, :country, :is_default)
            ");
            $address_stmt->execute([
                'user_id' => $user_id,
                'first_name' => "John",
                'last_name' => "Doe",
                'email' => $customer['email'],
                'phone_number' => "123-456-789",
                'address' => "Example Street, Apartment 1",
                'city' => "Warsaw",
                'state' => "Mazowieckie",
                'postal_code' => "00-000",
                'country' => "Poland",
                'is_default' => 1
            ]);

            $contact_address_id = $pdo->lastInsertId();
        }
    }

    $pdo->beginTransaction();

    $order_stmt = $pdo->prepare("
        INSERT INTO `order` (customer_id, order_date, total_amount, contact_address_id, order_status)
        VALUES (:customer_id, NOW(), :total_amount, :contact_address_id, :order_status)
    ");
    $order_stmt->execute([
        'customer_id' => $user_id,
        'total_amount' => $total_amount,
        'contact_address_id' => $contact_address_id,
        'order_status' => 'pending'
    ]);

    $order_id = $pdo->lastInsertId();

    $order_item_stmt = $pdo->prepare("
        INSERT INTO order_item (order_id, videogame_id, game_price, quantity)
        VALUES (:order_id, :videogame_id, :game_price, :quantity)
    ");
    $update_stock_stmt = $pdo->prepare("
        UPDATE videogame
        SET stock_quantity = stock_quantity - :quantity
        WHERE id = :videogame_id AND stock_quantity >= :quantity
    ");

    foreach ($cart_items as $item) {
        if ($item['stock_quantity'] < $item['quantity']) {
            throw new Exception("The game '{$item['title']}' is not available in the required quantity.");
        }

        $order_item_stmt->execute([
            'order_id' => $order_id,
            'videogame_id' => $item['videogame_id'],
            'game_price' => $item['price'],
            'quantity' => $item['quantity'],
        ]);

        $update_stock_stmt->execute([
            'quantity' => $item['quantity'],
            'videogame_id' => $item['videogame_id'],
        ]);
    }

    $clear_cart_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $clear_cart_stmt->execute(['user_id' => $user_id]);

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Your order has been successfully placed. Thank you for shopping!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error placing the order: ' . $e->getMessage()]);
}
?>
