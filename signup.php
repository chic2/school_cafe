<?php
session_start();
include 'db.php';

$message = "";

if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password

    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        $message = "Signup successful! You can now <a href='login.php'>login</a>.";
    } else {
        $message = "Error: Username might already exist.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Signup - School Café Admin</title>
<style>
body { font-family: Arial, sans-serif; background: #f7f7f7; }
.container { max-width: 400px; margin: 100px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);}
input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc;}
button { width: 100%; padding: 10px; background: #00b894; color: white; border: none; border-radius: 5px; cursor: pointer;}
button:hover { background: #019875; }
.message { color: red; margin-bottom: 10px; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <h2>Signup</h2>
    <div class="message"><?= $message ?></div>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="signup">Sign Up</button>
    </form>
    <p style="text-align:center;">Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
