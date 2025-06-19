<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

// Récupérer tous les terrains
$stmt = $pdo->query("SELECT * FROM terrains ORDER BY terrain_id DESC");
$terrains = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des terrains - SportsField</title>
    <link rel="stylesheet" href="../admin-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-futbol"></i>
                    <span>SportsField Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="../index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Terrains</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../users/index.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../reservations/index.php" class="nav-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Réservations</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Gestion des terrains</h1>
                </div>
                <div class="header-right">
                    <a href="add.php" class="btn">
                        <i class="fas fa-plus"></i>
                        Ajouter un terrain
                    </a>
                    <div class="user-menu">
                        <button class="user-btn">
                            <i class="fas fa-user-circle"></i>
                            <span>Admin</span>
                        </button>
                        <div class="user-dropdown">
                            <a href="../../auth/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="dashboard-content">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Liste des terrains (<?php echo count($terrains); ?>)</h3>
                    </div>
                    <div class="card-content">
                        <?php if (count($terrains) > 0): ?>
                            <div class="terrains-grid">
                                <?php foreach ($terrains as $terrain): ?>
                                <div class="terrain-card">
                                    <div class="terrain-image">
                                        <img src="../../<?php echo htmlspecialchars($terrain['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($terrain['terrain_name']); ?>">
                                        <div class="terrain-overlay">
                                            <div class="terrain-actions">
                                                <a href="edit.php?id=<?php echo $terrain['terrain_id']; ?>" class="action-btn edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $terrain['terrain_id']; ?>" 
                                                   class="action-btn delete"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce terrain ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="terrain-info">
                                        <div class="terrain-header">
                                            <h4><?php echo htmlspecialchars($terrain['terrain_name']); ?></h4>
                                            
                                        </div>
                                        <div class="terrain-details">
                                            <span class="category-tag"><?php echo htmlspecialchars($terrain['category']); ?></span>
                                            <span class="location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($terrain['location']); ?>
                                            </span>
                                        </div>
                                        <p class="terrain-description">
                                            <?php echo htmlspecialchars(substr($terrain['description'], 0, 100)) . (strlen($terrain['description']) > 100 ? '...' : ''); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <h3>Aucun terrain trouvé</h3>
                                <p>Commencez par ajouter votre premier terrain</p>
                                <a href="add.php" class="btn">
                                    <i class="fas fa-plus"></i>
                                    Ajouter un terrain
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
 
    </style>

    <script>
        // Sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
        });

        // User dropdown
        document.querySelector('.user-btn').addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.querySelector('.user-dropdown').classList.remove('show');
            }
        });
    </script>
</body>
</html>