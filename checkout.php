<?php
session_start();
include 'db.php';

$cart = $_SESSION['cart'] ?? [];

if (!$cart) {
    echo "<script>alert('Your cart is empty.'); window.location='index.php';</script>";
    exit;
}

// Handle checkout submission
if (isset($_POST['place_order'])) {
    $customer_name = trim($_POST['name'] ?? '');
    if ($customer_name === '') {
        echo "<script>alert('Please enter your name.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, food_item_id, quantity, total_price, status) VALUES (?, ?, ?, ?, 'pending')");
        foreach ($cart as $food_id => $qty) {
            // Fetch price for this item
            $res = $conn->query("SELECT price FROM food_items WHERE id = " . intval($food_id));
            $row = $res->fetch_assoc();
            $price = floatval($row['price']);
            $total = $price * intval($qty);

            $stmt->bind_param("sidd", $customer_name, $food_id, $qty, $total);
            $stmt->execute();

            // Optionally: reduce stock
            $conn->query("UPDATE food_items SET quantity = quantity - $qty WHERE id = $food_id");
        }
        $stmt->close();
        $_SESSION['cart'] = []; // clear cart
        header("Location: wait.php?name=" . urlencode($customer_name));
        exit;
    }
}

// Fetch food details for cart display
$cart_items = [];
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $res = $conn->query("SELECT * FROM food_items WHERE id IN ($ids)");
    while ($row = $res->fetch_assoc()) {
        $row['quantity_selected'] = $cart[$row['id']];
        $cart_items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>wait - School Café</title>
<style>
body { font-family: sans-serif; background: linear-gradient(to right,#f8ffae,#43c6ac); margin:0; padding:0;}
.container { max-width:600px; margin:50px auto; background:#fff; padding:30px; border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.1);}
h1 { text-align:center; color:#ff6b6b;}
.cart-item { display:flex; justify-content:space-between; margin-bottom:15px;}
.cart-item span { font-weight:bold; color:#00b894;}
input[type=text] { width:100%; padding:10px; margin:15px 0; border-radius:8px; border:1px solid #ccc;}
button { background:#00b894; color:#fff; padding:10px 20px; border:none; border-radius:8px; cursor:pointer;}
button:hover { background:#019875;}
</style>
</head>
<body>
<div class="container">
<h1>Checkout</h1>

<?php if ($cart_items): ?>
    <h3>Your Cart:</h3>
    <?php $grand_total = 0; ?>
    <?php foreach ($cart_items as $item): ?>
        <div class="cart-item">
            <div><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity_selected'] ?>)</div>
            <?php $total = $item['price'] * $item['quantity_selected']; $grand_total += $total; ?>
            <span>$<?= number_format($total,2) ?></span>
        </div>
    <?php endforeach; ?>
    <h3>Total: $<?= number_format($grand_total,2) ?></h3>
<br>
        <h5> Bank: Providos Bank</h5>
        <h5>Account number: 1234567890 </h5>

        
    <form method="POST">
        <input type="text" name="name" placeholder="Enter your name" required>
        <button type="submit" name="place_order">Place Order</button>
    </form>
<?php else: ?>
    <p>Your cart is empty.</p>
    <a href="index.php"><button>Back to Menu</button></a>
<?php endif; ?>

</div>
</body>
</html>
