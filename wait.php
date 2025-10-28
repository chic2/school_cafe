<?php
session_start();
include 'db.php';

$order_name = $_GET['name'] ?? null;

if (!$order_name) {
    echo "<script>alert('No name found. Please place an order first.'); window.location='index.php';</script>";
    exit;
}

// Fetch the latest order(s) for that name
$stmt = $conn->prepare("
    SELECT o.id, o.quantity, o.total_price, o.status, f.name AS food_name
    FROM orders o
    JOIN food_items f ON o.food_item_id = f.id
    WHERE o.customer_name = ?
    ORDER BY o.id DESC
    LIMIT 20
");
$stmt->bind_param("s", $order_name);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Use the first order to get status and ID
$latest_order = $orders[0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Status - School Café</title>
<meta http-equiv="refresh" content="5"> <!-- auto refresh every 5 seconds -->
<style>
body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(to right, #f8ffae, #43c6ac);
    margin: 0;
    padding: 0;
}
.container {
    max-width: 600px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
}
.status {
    font-size: 22px;
    font-weight: bold;
    padding: 20px;
    border-radius: 10px;
}
.pending { color: #ff9f43; }
.accepted { color: #0984e3; }
.ready { color: #00b894; }
button {
    background: #00b894;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 15px;
}
button:hover { background: #019875; }
ul { text-align: left; margin: 10px auto; display: inline-block; }
</style>
</head>
<body>
<div class="container">
    <h1>Order Status</h1>

    <?php if ($latest_order): ?>
        <p><strong>Name:</strong> <?= htmlspecialchars($order_name) ?></p>
        <p><strong>Order ID:</strong> <?= $latest_order['id'] ?></p>
        
        <ul>
        <?php foreach ($orders as $item): ?>
            <li>🍴 <?= htmlspecialchars($item['food_name']) ?> (x<?= intval($item['quantity']) ?>) — $<?= number_format($item['total_price'], 2) ?></li>
        <?php endforeach; ?>
        </ul>

        <div class="status <?= $latest_order['status'] ?>">
            <?php if ($latest_order['status'] == 'pending'): ?>
                ⏳ Waiting for admin to accept your order...
            <?php elseif ($latest_order['status'] == 'accepted'): ?>
                ✅ Your order has been accepted. Please wait while it's being prepared.
            <?php elseif ($latest_order['status'] == 'ready'): ?>
                🥳 Your order is ready! You can collect it now.
                <form method="post" action="index.php">
                    <button type="submit">Order Again</button>
                </form>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <p>No order found for <strong><?= htmlspecialchars($order_name) ?></strong>.</p>
        <a href="index.php"><button>Back to Menu</button></a>
    <?php endif; ?>
</div>
</body>
</html>
