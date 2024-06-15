<?php
session_start();
$servername = "localhost";
$username = "root";
// $password = "asdfg535060";
$password = "";
$dbname = "pokemon";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $account = $_POST['account'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, account, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $account, $password);

    if ($stmt->execute() === TRUE) {
        echo "註冊成功";
        header("Location: login.php");
        exit();
    } else {
        echo "註冊失败: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/common.scss">
    <link rel="stylesheet" href="css/login.scss">
</head>
<body>
    <div class="bg c">
        <div class="login c">
            <h2>註冊帳號</h2>
            <form action="register.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br>
                <label for="account">Account:</label>
                <input type="text" id="account" name="account" required><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>
                <div class="c">
                    <button type="submit">Register</button>
                </div>
                
            </form>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
