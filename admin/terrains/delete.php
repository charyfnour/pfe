<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

$terrain_id = $_GET['id'] ?? 0;

if ($terrain_id) {
    try {
        // Récupérer les informations du terrain pour supprimer l'image
        $stmt = $pdo->prepare("SELECT image FROM terrains WHERE terrain_id = ?");
        $stmt->execute([$terrain_id]);
        $terrain = $stmt->fetch();
        
        // Supprimer le terrain de la base de données
        $stmt = $pdo->prepare("DELETE FROM terrains WHERE terrain_id = ?");
        $stmt->execute([$terrain_id]);
        
        // Supprimer l'image du serveur si elle existe
        if ($terrain && $terrain['image'] && file_exists('../../' . $terrain['image'])) {
            unlink('../../' . $terrain['image']);
        }
        
        header("Location: index.php?success=1");
    } catch (PDOException $e) {
        header("Location: index.php?error=1");
    }
} else {
    header("Location: index.php");
}
exit;
?>