<?php
session_start();
include 'db.php';

$message = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
            header("Location: admin.php");
            exit;
        } else {
            $message = "Incorrect password!";
        }
    } else {
        $message = "Username not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - School Café Admin</title>
<style>
body { font-family: Arial, sans-serif; background: #f7f7f7; }
.container { max-width: 400px; margin: 100px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);}
input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc;}
button { width: 100%; padding: 10px; background: #0984e3; color: white; border: none; border-radius: 5px; cursor: pointer;}
button:hover { background: #0652DD; }
.message { color: red; margin-bottom: 10px; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <div class="message"><?= $message ?></div>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    <!-- <p style="text-align:center;">No account? <a href="signup.php">Sign up</a></p> -->
</div>
</body>
</html>
