<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit();  
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pokemon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    exit();  // 不输出任何内容
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT username, money FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    exit();  // 不输出任何内容
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($userName, $userMoney);
$stmt->fetch();
$stmt->close();

if (empty($userName) || empty($userMoney)) {
    $conn->close();
    exit();  // 不输出任何内容
}

$sql = "SELECT * FROM pokemon WHERE 編號 NOT IN (SELECT pokemon_id FROM backpack WHERE user_id = ?) LIMIT 20";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    exit();  // 不输出任何内容
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pokemonResult = $stmt->get_result();
$pokemonData = [];
if ($pokemonResult->num_rows > 0) {
    while ($row = $pokemonResult->fetch_assoc()) {
        $pokemonData[] = $row;
    }
}
$stmt->close();

// 获取用户上传的音乐文件
$sql = "SELECT file_name, file_path, youtube_link, image_path FROM music_files WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    exit();  // 不输出任何内容
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$musicFilesResult = $stmt->get_result();
$musicFiles = [];
if ($musicFilesResult->num_rows > 0) {
    while ($row = $musicFilesResult->fetch_assoc()) {
        $musicFiles[] = $row;
    }
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['money'])) {
        $new_money = $_POST['money'];
        $sql = "UPDATE users SET money = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            exit();  // 不输出任何内容
        }
        $stmt->bind_param("di", $new_money, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'new_money' => $new_money]);
        } else {
            exit();  // 不输出任何内容
        }
        $stmt->close();
        exit();
    }
    if (isset($_POST['pokemon_id'])) {
        $pokemon_id = $_POST['pokemon_id'];
    
        $sql = "SELECT 總數值, 名稱, 屬性一, 屬性二 ,總數值, img_url FROM pokemon WHERE 編號 = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            exit();
        }
        $stmt->bind_param("i", $pokemon_id);
        $stmt->execute();
        $stmt->bind_result($pokemon_value, $pokemon_name, $pokemon_attr1, $pokemon_attr2, $pokemon_img_url);
        $stmt->fetch();
        $stmt->close();
    
        $sql = "SELECT money FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            exit();
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_money);
        $stmt->fetch();
        $stmt->close();
    
        if ($user_money >= $pokemon_value) {
            $new_money = $user_money - $pokemon_value;
            $sql = "UPDATE users SET money = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                exit();
            }
            $stmt->bind_param("di", $new_money, $user_id);
            $stmt->execute();
            $stmt->close();
    
            $sql = "INSERT INTO backpack (user_id, pokemon_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                exit();
            }
            $stmt->bind_param("ii", $user_id, $pokemon_id);
            $stmt->execute();
            $stmt->close();
    
            echo json_encode([
                'success' => true,
                'pokemon' => [
                    '名稱' => $pokemon_name,
                    '屬性一' => $pokemon_attr1,
                    '屬性二' => $pokemon_attr2,
                    'img_url' => $pokemon_img_url
                ],
                'new_money' => $new_money
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '金額不足!']);
        }
        exit();
    }    
}

if (isset($_GET['action']) && $_GET['action'] === 'load_backpack') {
    $sql = "SELECT pokemon.名稱, pokemon.屬性一, pokemon.屬性二, pokemon.img_url FROM backpack JOIN pokemon ON backpack.pokemon_id = pokemon.編號 WHERE backpack.user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // echo json_encode(['success' => false, 'message' => 'SQL 错误: ' . $conn->error]);
        exit();
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $backpackData = [];
    while ($row = $result->fetch_assoc()) {
        $backpackData[] = $row;
    }
    $stmt->close();
    echo json_encode($backpackData);
    exit();
}

// echo json_encode(['success' => false, 'message' => '无效的请求']);
$conn->close();
?>


<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>寶可夢走3圈</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="css/common.scss">
    <link rel="stylesheet" href="css/pokemon.scss">
    <style>
        <?php foreach ($musicFiles as $index => $music): ?>
            .bg<?php echo $index + 1; ?> {
                background: url('<?php echo htmlspecialchars($music['image_path']); ?>') no-repeat center center;
                background-size: cover;
            }
        <?php endforeach; ?>
    </style>
    <script>
        var initialMoney = <?php echo $userMoney; ?>;
        var userName = <?php echo json_encode($userName); ?>;
    </script>
</head>
<body>
    <div class="duck c" id="duck">
        <img src="img/duck.gif" alt="">
    </div>
    <div class="hidden" id="hidden">

    <div class="black-frame">
        <div class="bg-frame">
            <div class="bg"></div>
        </div>
        <div class="cat-container">
            <img id="cat" class="cat-walk" src="img/1.png" alt="Walking Cat">
        </div>
        <footer class="c">
            ©2024 Kai | 此網站不支援RWD建議使用電腦瀏覽
            <span class="work" onclick="toggleWork()">
                <img src="img/ball.png" alt="">
            </span>
        </footer>
        <div class="work-bg" id="workBg">
            <table class="table table-bordered">
                <tr>
                    <td>名稱</td>
                    <td>業師作業</td>
                    <td>主題發想</td>
                    <td>資料搜集</td>
                    <td>網站開發</td>
                    <td>資料庫規劃</td>
                    <td>架構規劃</td>
                </tr>
                <tr>
                    <td>黃楷烜</td>
                    <td>👌</td>
                    <td>👌</td>
                    <td>👌</td>
                    <td>👌</td>
                    <td>👌</td>
                    <td>👌</td>
                </tr>
                <tr>
                    <td>莊維承</td>
                    <td>👌</td>
                    <td>👌</td>
                    <td>👌</td>
                    <td></td>
                    <td>👌</td>
                    <td>👌</td>
                </tr>
                <tr>
                    <td>洪翊翔</td>
                    <td>👌</td>
                    <td></td>
                    <td>👌</td>
                    <td></td>
                    <td>👌</td>
                    <td>👌</td>
                </tr>
                <tr>
                    <td>史玉靖</td>
                    <td></td>
                    <td></td>
                    <td>👌</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>謝博安</td>
                    <td></td>
                    <td></td>
                    <td>👌</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="backpack">
    </div>
    <div class="backpack-img c" onclick="openbackpack()">
        <img src="img/bag.png" alt="">
    </div>
    <div class="backpack-bg" id="backpack-bg">
        <div class="search w-100">
            <input class="w-100" type="text" id="searchInput" placeholder="搜尋">
        </div>
        <div id="backpack-content"></div> 
    </div>

    <div class="store">
    </div>
    <div class="store-img c" onclick="openstore()">
        <img src="img/shop.png" alt="">
    </div>
    <div class="store-bg">
        <div class="search w-100">
            <input class="w-100" type="text" id="storeSearchInput" placeholder="搜尋">
        </div>
        <div class="col-12" id="pokemon-list">
            <?php foreach ($pokemonData as $pokemon): ?>
                <?php
                $imgUrl = str_replace("file/d/", "uc?export=view&id=", $pokemon['img_url']);
                $imgUrl = str_replace("/view?usp=sharing", "", $imgUrl);
                ?>
                <div class="cards">
                    <ul>
                        <li>名稱: <?php echo $pokemon['名稱']; ?></li>
                        <li>屬性1: <?php echo $pokemon['屬性一']; ?></li>
                        <li>屬性2: <?php echo $pokemon['屬性二']; ?></li>
                        <li>價格: <?php echo $pokemon['總數值']; ?></li>
                    </ul>
                    <img src="<?php echo $imgUrl; ?>" alt="<?php echo $pokemon['名稱']; ?>">
                    <form method="post" action="" class="c buy-form">
                        <input type="hidden" name="pokemon_id" value="<?php echo $pokemon['編號']; ?>">
                        <button type="submit" class="buy">購買</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="setting c">
    </div>
    <button class="setting-btn" onclick="logout()">
        登出
    </button>

    <div class="music position-absolute c">
        <div class="info w-100 c p-3">
            <div class="username c">
                <?php echo $userName; ?>
            </div>
            <div class="img c">
                <img class="" src="img/dollar.png" alt="">
            </div>
            <div class="money c">
                <p id="money-amount"><?php echo $userMoney; ?></p>
            </div>
            <div class="zoom-out" onclick="zoomOutMusic()">
                ☜
            </div>
        </div>
        <div class="swiper muswiper p-3">
            <div class="swiper-wrapper">
                <?php foreach ($musicFiles as $index => $music): ?>
                    <div class="swiper-slide c">
                        <div id="sound-plate" class="circle bg<?php echo $index + 1; ?> c my-3">
                            <div class="center-point"></div>
                            <div class="sound-wave"></div>
                        </div>
                        <div class="name my-3">
                            <p><?php echo htmlspecialchars($music['file_name']); ?></p>
                        </div>
                        <audio controls class="my-3 song">
                            <source src="<?php echo htmlspecialchars($music['file_path']); ?>" type="audio/mpeg">
                            你的瀏覽器不支援audio
                        </audio>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>

        <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data" class=" py-2 px-4 c">
            <div class="c w-100">
                <label class="image-label" for="imageFile">選擇撥放器圖片</label>
                <input class="input" type="file" id="imageFile" name="imageFile" accept="image/*">
            </div>
            <div class="c w-100">
                <label class="music-label" for="musicFile">選擇檔案</label>
                <input class="input" type="file" id="musicFile" name="musicFile" accept="audio/*">
                <button type="submit">上傳音樂</button>
            </div>
            <div id="uploadStatus"></div>
        </form>
    </div>
    <div class="zoom-in position-absolute" onclick="zoomInMusic()">
        ☞
    </div>
    </div>            
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-beta2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>            
    <script src="js/pokemon.js"></script>
</body>
</html>



