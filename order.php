<?php
session_start();
include 'db.php';

// --- Only admin can access ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- Handle actions ---
if (isset($_GET['action']) && isset($_GET['name'])) {
    $name = $conn->real_escape_string($_GET['name']);
    $action = $_GET['action'];

    if ($action === 'accept') {
        $conn->query("UPDATE orders SET status='accepted' WHERE customer_name='$name'");
    } elseif ($action === 'ready') {
        $conn->query("UPDATE orders SET status='ready' WHERE customer_name='$name'");
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM orders WHERE customer_name='$name'");
    }

    header("Location: order.php");
    exit;
}

// --- Fetch all orders grouped by customer ---
$sql = "
    SELECT o.id, o.customer_name, f.name AS food_name, o.quantity, o.total_price, o.status
    FROM orders o
    JOIN food_items f ON o.food_item_id = f.id
    ORDER BY o.customer_name, o.id DESC
";
$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['customer_name']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Orders - School Café</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f8ffae; margin:0; padding:0; }
.container { max-width:1000px; margin:30px auto; background:#fff; padding:30px; border-radius:15px; }
h1 { text-align:center; color:#ff6b6b; }

.back-btn {
    background:#00b894;
    color:#fff;
    padding:8px 15px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    margin-bottom:20px;
    text-decoration:none;
}

.order-group {
    border: 1px solid #ddd;
    margin-bottom: 20px;
    border-radius: 10px;
    padding: 15px;
    background: #fdfdfd;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.order-header h2 { margin: 0; color: #0984e3; }

.actions a {
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    color: #fff;
    font-size: 14px;
    margin-left: 5px;
}
.accept { background: #f39c12; }
.ready { background: #3498db; }
.delete { background: #e74c3c; }
.actions a:hover { opacity: 0.8; }

.status {
    font-weight: bold;
    text-transform: capitalize;
    color: gray;
}
.status.accepted { color: orange; }
.status.ready { color: green; }

.food-list { margin-left: 20px; }
.food-item { margin-bottom: 5px; }

@media(max-width:768px){
    .container{margin:20px; padding:15px;}
    .order-header h2{font-size:16px;}
    .actions a{padding:4px 8px; font-size:12px;}
}
</style>
</head>
<body>
<div class="container">
    <h1>All Orders</h1>
    
    <!-- Back to Admin -->
    <a href="admin.php" class="back-btn">⬅ Back to Admin</a>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $customer => $items): ?>
            <?php $status = $items[0]['status']; ?>
            <div class="order-group">
                <div class="order-header">
                    <h2>👤 <?= htmlspecialchars($customer) ?></h2>
                    <div class="actions">
                        <?php if ($status == 'pending'): ?>
                            <a href="order.php?action=accept&name=<?= urlencode($customer) ?>" class="accept">Accept</a>
                        <?php elseif ($status == 'accepted'): ?>
                            <a href="order.php?action=ready&name=<?= urlencode($customer) ?>" class="ready">Finish</a>
                        <?php endif; ?>
                        <a href="order.php?action=delete&name=<?= urlencode($customer) ?>" class="delete" onclick="return confirm('Delete all orders for <?= htmlspecialchars($customer) ?>?')">Delete</a>
                    </div>
                </div>

                <div class="food-list">
                    <?php foreach ($items as $item): ?>
                        <div class="food-item">
                            🍴 <strong><?= htmlspecialchars($item['food_name']) ?></strong> 
                            (x<?= $item['quantity'] ?>) — 
                            $<?= number_format($item['total_price'], 2) ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p>Status: <span class="status <?= $status ?>"><?= $status ?></span></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No orders yet.</p>
    <?php endif; ?>

</div>
</body>
</html>
