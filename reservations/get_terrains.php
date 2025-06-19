<?php
include "../config/config.php";
header('Content-Type: application/json');

if (isset($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    $stmt = $pdo->prepare("SELECT * FROM terrains WHERE category_id = ?");
    $stmt->execute([$category_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}
