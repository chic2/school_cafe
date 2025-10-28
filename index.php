<?php 
session_start();
include 'db.php';

// Fetch available food items
$result = $conn->query("SELECT * FROM food_items WHERE available = 1 ORDER BY id DESC");

// Handle "Proceed to Checkout"
if (isset($_POST['proceed'])) {
    $selected_food = $_POST['food'] ?? [];
    $_SESSION['cart'] = array_filter($selected_food, fn($qty) => intval($qty) > 0);

    if (!empty($_SESSION['cart'])) {
        header("Location: checkout.php");
        exit;
    } else {
        echo "<script>alert('Please select at least one item.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Café Menu</title>
<style>
body {
    font-family: sans-serif;
    background: linear-gradient(to right, #f8ffae, #43c6ac);
    margin: 0;
    padding: 0;
}
.container {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    color: #ff6b6b;
}
.menu-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #ddd;
    flex-wrap: wrap;
}
.menu-item:last-child { border-bottom:none; }
.menu-item h3 { margin:0; color:#0984e3; }
.menu-item p { margin:5px 0 0 0; color:#636e72; }
.menu-item span.price { font-weight:bold; color:#00b894; margin-right:15px; }
.menu-item span.stock { font-size:14px; color:#666; margin-right:10px; }
.menu-item input[type="number"] { width:60px; padding:5px; }

button {
    background:#00b894;
    color:#fff;
    padding:10px 20px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    margin-top:15px;
}
button:hover { background:#019875; }

.out-of-stock {
    opacity: 0.5;
    pointer-events: none;
}
@media(max-width:600px){
    .menu-item{ flex-direction: column; align-items:flex-start;}
    .menu-item span.price, .menu-item span.stock { margin:5px 0; }
}
</style>
</head>
<body>
<div class="container">
    <h1>School Café Menu</h1>
    <form method="POST">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php $isOut = ($row['quantity'] <= 0); ?>
                <div class="menu-item <?= $isOut ? 'out-of-stock' : '' ?>">
                    <div>
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p><?= htmlspecialchars($row['description']) ?></p>
                    </div>
                    <div>
                        <span class="price">$<?= number_format($row['price'], 2) ?></span>
                        <span class="stock">
                            <?= $isOut ? 'Out of Stock' : $row['quantity'] . ' left' ?>
                        </span>
                        <?php if (!$isOut): ?>
                            <input type="number" name="food[<?= $row['id'] ?>]" min="0" max="<?= $row['quantity'] ?>" value="0">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No food items available today.</p>
        <?php endif; ?>

        <button type="submit" name="proceed">Proceed to Checkout</button>
    </form>
</div>
</body>
</html>
