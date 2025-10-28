<?php
session_start();
include 'db.php';

// --- Simple login check ---
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- Handle Add Food ---
if (isset($_POST['add_food'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO food_items (name, description, price, quantity, available) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdii", $name, $desc, $price, $quantity, $available);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// --- Handle Update Food ---
if (isset($_POST['update_food'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE food_items SET name=?, description=?, price=?, quantity=?, available=? WHERE id=?");
    $stmt->bind_param("ssdiii", $name, $desc, $price, $quantity, $available, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// --- Handle Toggle ---
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE food_items SET available = NOT available WHERE id = $id");
    header("Location: admin.php");
    exit;
}

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM food_items WHERE id = $id");
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Café Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(to right, #FFDEE9, #B5FFFC);
    margin: 0;
    padding: 0;
}
.container {
    max-width: 900px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.top-bar a {
    background: #0984e3;
    color: #fff;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 8px;
}
h1 { text-align: center; color: #FF4B2B; }
form input, form textarea {
    width: 100%;
    padding: 8px;
    margin: 5px 0 15px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
}
form input[type=checkbox] { width: auto; }
form button {
    background: #00b894;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
table th, table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
    padding-top: 2px;
}
table th { background: #FF4B2B; color: #fff; }

/* Buttons */
.btn { 
    padding: 10px 16px; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer; 
    color: #fff; 
    display: block;       /* stack vertically */
    margin: 5px 0;        /* space between buttons */
    font-size: 15px;      /* slightly bigger */
}
.btn-update { background: #6c5ce7; }
.btn-toggle { background: #0984e3; }
.btn-delete { background: #d63031; }

/* Actions cell */
.actions-cell {
    display: flex;
    flex-direction: column; /* vertical stack */
    align-items: center;    /* center horizontally */
}
.btn:hover {
    opacity: 0.85;
}
</style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>School Café Admin Panel</h1>
        <a href="order.php">View Orders</a>
    </div>

    <form method="POST">
        <h2>Add New Food Item</h2>
        <input type="text" name="name" placeholder="Food Name" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="number" name="quantity" placeholder="Quantity Available" required>
        <label><input type="checkbox" name="available" checked> Available</label>
        <button type="submit" name="add_food">Add Food</button>
    </form>

    <h2>Current Menu</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Food Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Available</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM food_items ORDER BY id DESC");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <form method="POST">
                <td><?= $row['id'] ?><input type="hidden" name="id" value="<?= $row['id'] ?>"></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>"></td>
                <td><input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>"></td>
                <td><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>"></td>
                <td><input type="number" name="quantity" value="<?= $row['quantity'] ?>"></td>
                <td><input type="checkbox" name="available" <?= $row['available'] ? "checked" : "" ?>></td>
                <td class="actions-cell">
                    <button type="submit" name="update_food" class="btn btn-update">Update</button>
                    <a class="btn btn-toggle" href="?toggle=<?= $row['id'] ?>">Toggle</a>
                    <a class="btn btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
