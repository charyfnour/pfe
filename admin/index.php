<?php
session_start();
include "../config/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/formulaireconnexion.php");
    exit;
}

// Récupérer les statistiques pour le dashboard
$stats = [];

// Nombre total de terrains
$stmt = $pdo->query("SELECT COUNT(*) as total FROM terrains");
$stats['terrains'] = $stmt->fetch()['total'];

// Nombre total d'utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $stmt->fetch()['total'];

// Nombre total de réservations
$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
$stats['reservations'] = $stmt->fetch()['total'];

// Réservations récentes
$stmt = $pdo->query("
    SELECT r.*, u.first_name, u.last_name, t.terrain_name 
    FROM reservations r 
    JOIN users u ON r.user_id = u.user_id 
    JOIN terrains t ON r.terrain_id = t.terrain_id 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$recent_reservations = $stmt->fetchAll();

// Terrains les plus populaires
$stmt = $pdo->query("
    SELECT t.terrain_name, t.category, COUNT(r.reservation_id) as total_reservations
    FROM terrains t
    LEFT JOIN reservations r ON t.terrain_id = r.terrain_id
    GROUP BY t.terrain_id
    ORDER BY total_reservations DESC
    LIMIT 5
");
$popular_terrains = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administration SportsField</title>
    <link rel="stylesheet" href="admin-styles.css">
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
                    <li class="nav-item active">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="terrains/index.php" class="nav-link">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Terrains</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users/index.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reservations/index.php" class="nav-link">
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
                    <h1>Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <button class="user-btn">
                            <i class="fas fa-user-circle"></i>
                            <span>Admin</span>
                        </button>
                        <div class="user-dropdown">
                            <a href="../auth/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['terrains']; ?></h3>
                            <p>Terrains</p>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['users']; ?></h3>
                            <p>Utilisateurs</p>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['reservations']; ?></h3>
                            <p>Réservations</p>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>

                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>85%</h3>
                            <p>Taux d'occupation</p>
                        </div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="dashboard-grid">
                    <!-- Recent Reservations -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Réservations récentes</h3>
                            <a href="reservations/index.php" class="btn btn-sm">Voir tout</a>
                        </div>
                        <div class="card-content">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Terrain</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_reservations as $reservation): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($reservation['terrain_name']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $reservation['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($reservation['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Terrains -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Terrains populaires</h3>
                            <a href="terrains/index.php" class="btn btn-sm">Gérer</a>
                        </div>
                        <div class="card-content">
                            <div class="popular-list">
                                <?php foreach ($popular_terrains as $terrain): ?>
                                <div class="popular-item">
                                    <div class="popular-info">
                                        <h4><?php echo htmlspecialchars($terrain['terrain_name']); ?></h4>
                                        <span class="category-tag"><?php echo htmlspecialchars($terrain['category']); ?></span>
                                    </div>
                                    <div class="popular-stats">
                                        <span class="reservation-count"><?php echo $terrain['total_reservations']; ?> réservations</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h3>Actions rapides</h3>
                    <div class="actions-grid">
                        <a href="terrains/add.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-info">
                                <h4>Ajouter un terrain</h4>
                                <p>Créer un nouveau terrain</p>
                            </div>
                        </a>

                        <a href="users/index.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-info">
                                <h4>Gérer les utilisateurs</h4>
                                <p>Voir tous les utilisateurs</p>
                            </div>
                        </a>

                        <a href="reservations/index.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="action-info">
                                <h4>Voir les réservations</h4>
                                <p>Gérer toutes les réservations</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

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