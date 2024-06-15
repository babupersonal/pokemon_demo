<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'User not logged in']));
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pokemon";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    exit(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 20;

$pokemonSql = "SELECT * FROM pokemon LIMIT $limit OFFSET $offset";
$pokemonResult = $conn->query($pokemonSql);
$pokemonData = [];
if ($pokemonResult) {
    if ($pokemonResult->num_rows > 0) {
        while ($row = $pokemonResult->fetch_assoc()) {
            $pokemonData[] = $row;
        }
    }
} else {
    exit(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

$conn->close();

echo json_encode($pokemonData);
?>
