<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['success' => false, 'message' => '用户未登录']));
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pokemon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    exit(json_encode(['success' => false, 'message' => '连接失败: ' . $conn->connect_error]));
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $imageFilePath = '';
    $filePath = '';
    $fileName = '';

    // 上传封面图片
    if (!empty($_FILES["imageFile"]["name"])) {
        $imageFileName = basename($_FILES["imageFile"]["name"]);
        $targetImageDir = "uploads/images/";
        $imageFilePath = $targetImageDir . $imageFileName;

        if (!file_exists($targetImageDir)) {
            mkdir($targetImageDir, 0755, true);
        }

        if (!move_uploaded_file($_FILES["imageFile"]["tmp_name"], $imageFilePath)) {
            $response['message'] = '图片上传失败: ' . $_FILES["imageFile"]["error"];
            echo json_encode($response);
            exit();
        }
    }

    // 上传音频文件
    if (!empty($_FILES["musicFile"]["name"])) {
        $fileName = basename($_FILES["musicFile"]["name"]);
        $targetDir = "uploads/song/";
        $filePath = $targetDir . $fileName;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (move_uploaded_file($_FILES["musicFile"]["tmp_name"], $filePath)) {
            $sql = "INSERT INTO music_files (user_id, file_name, file_path, image_path) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("isss", $user_id, $fileName, $filePath, $imageFilePath);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = '文件上传成功';
                    $response['music'] = [
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'image_path' => $imageFilePath
                    ];
                } else {
                    $response['message'] = '数据库错误: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'SQL 错误: ' . $conn->error;
            }
        } else {
            $response['message'] = '文件上传失败: ' . $_FILES["musicFile"]["error"];
        }
    } else {
        $response['message'] = '没有音频文件被上传';
    }
}

$conn->close();
echo json_encode($response);
?>
