<?php
session_start();

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pokemon";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account = $_POST['account'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE account = ?");
    if ($stmt === false) {
        die("准备语句失败: " . $conn->error);
    }
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // 帐号和密码匹配，登录成功
            $_SESSION['user_id'] = $row['id'];
            error_log("Login successful for user: " . $row['username']);
            // 确保没有任何输出
            ob_clean();
            header("Location: pokemon.php");
            exit();
        } else {
            // 密码不正确
            error_log("Incorrect password for account: " . $account);
            echo "帐号或密码错误";
        }
    } else {
        // 帐号不存在
        error_log("Account not found: " . $account);
        echo "帐号或密码错误";
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
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/common.scss">
    <link rel="stylesheet" href="css/login.scss">
</head>
<body>
    <div class="bg">
        <div class="login">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <label for="account">Account:</label>
                <input type="text" id="account" name="account" required><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>
                <div class="c">
                    <button class="mr-1" type="submit">Login</button>
                    <button class="ml-1"><a href="register.php" class="btn">Register</a></button>
                </div>
            </form>
            <br>
            
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
