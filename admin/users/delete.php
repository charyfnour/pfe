<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

if ($user_id && $user_id != $_SESSION['user_id']) { // Empêcher l'admin de se supprimer lui-même
    try {
        // Supprimer d'abord les réservations de l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Puis supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        header("Location: index.php?success=1");
    } catch (PDOException $e) {
        header("Location: index.php?error=1");
    }
} else {
    header("Location: index.php");
}
exit;
?>