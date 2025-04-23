<?php
session_start();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="stylesForCart.css">
</head>
<body>
    <div class="container">
        <h1>Your Cart</h1>
        <?php if (!empty($cart)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cart as $game_id => $game): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($game['image_url']) ?>" alt="<?= htmlspecialchars($game['title']) ?>" width="50"></td>
                            <td><?= htmlspecialchars($game['title']) ?></td>
                            <td><?= number_format($game['price'], 2) ?> PLN</td>
                            <td><?= $game['quantity'] ?></td>
                            <td><?= number_format($game['price'] * $game['quantity'], 2) ?> PLN</td>
                        </tr>
                        <?php $total += $game['price'] * $game['quantity']; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Total:</strong></td>
                        <td><strong><?= number_format($total, 2) ?> PLN</strong></td>
                    </tr>
                </tfoot>
            </table>
            <a href="checkout.php" class="button">Proceed to Checkout</a>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>
</html>
