<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

$reservation_id = $_GET['id'] ?? 0;

if ($reservation_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE reservation_id = ?");
        $stmt->execute([$reservation_id]);
        
        header("Location: index.php?success=1");
    } catch (PDOException $e) {
        header("Location: index.php?error=1");
    }
} else {
    header("Location: index.php");
}
exit;
?>