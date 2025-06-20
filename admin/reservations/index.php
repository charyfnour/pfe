<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

// Récupérer toutes les réservations avec les infos utilisateur, terrain et créneau horaire
$stmt = $pdo->query("
    SELECT 
        r.*, 
        u.first_name, 
        u.last_name, 
        u.email, 
        t.terrain_name, 
        t.category, 
        t.location,
        c.heure_debut,
        c.heure_fin
    FROM reservations r
    JOIN users u ON r.user_id = u.user_id 
    JOIN terrains t ON r.terrain_id = t.terrain_id 
    JOIN creneau_horaire c ON r.creneau_id = c.creneau_id
    ORDER BY r.reservation_date DESC, c.heure_debut DESC
");

$reservations = $stmt->fetchAll();

// Statistiques
$today = date('Y-m-d');
$stmt_today = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_date = ?");
$stmt_today->execute([$today]);
$reservations_today = $stmt_today->fetch()['count'];

$stmt_pending = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
$reservations_pending = $stmt_pending->fetch()['count'];

$stmt_confirmed = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'");
$reservations_confirmed = $stmt_confirmed->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des réservations - SportsField</title>
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
                    <li class="nav-item">
                        <a href="../terrains/index.php" class="nav-link">
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
                    <li class="nav-item active">
                        <a href="index.php" class="nav-link">
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
                    <h1>Gestion des réservations</h1>
                </div>
                <div class="header-right">
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
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $reservations_today; ?></h3>
                            <p>Aujourd'hui</p>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $reservations_pending; ?></h3>
                            <p>En attente</p>
                        </div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $reservations_confirmed; ?></h3>
                            <p>Confirmées</p>
                        </div>
                    </div>

                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($reservations); ?></h3>
                            <p>Total</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Toutes les réservations</h3>
                        <div class="filters">
                            <select id="statusFilter" class="filter-select">
                                <option value="">Tous les statuts</option>
                                <option value="pending">En attente</option>
                                <option value="confirmed">Confirmé</option>
                            </select>
                            <select id="categoryFilter" class="filter-select">
                                <option value="">Toutes les catégories</option>
                                <option value="Football">Football</option>
                                <option value="Tennis">Tennis</option>
                                <option value="Basket">Basket</option>
                                <option value="Volleyball">Volleyball</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php if (count($reservations) > 0): ?>
                            <div class="reservations-list">
                                <?php foreach ($reservations as $reservation): ?>
                                <div class="reservation-card" 
                                     data-status="<?php echo $reservation['status']; ?>"
                                     data-category="<?php echo $reservation['category']; ?>">
                                    <div class="reservation-header">
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($reservation['email']); ?></p>
                                            </div>
                                        </div>
                                        <div class="reservation-actions">
                                            <span class="status-badge <?php echo $reservation['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                            <button class="delete-btn" onclick="deleteReservation(<?php echo $reservation['reservation_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="reservation-details">
                                        <div class="detail-item">
                                            <i class="fas fa-map-marked-alt"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($reservation['terrain_name']); ?></strong>
                                                <span class="category-tag"><?php echo htmlspecialchars($reservation['category']); ?></span>
                                                <p><?php echo htmlspecialchars($reservation['location']); ?></p>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <div>
                                                <strong><?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></strong>
                                                <p><?php echo date('H:i', strtotime($reservation['heure_debut'])) . ' - ' . date('H:i', strtotime($reservation['heure_fin'])); ?></p>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <div>
                                                <strong>Réservé le</strong>
                                                <p><?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h3>Aucune réservation trouvée</h3>
                                <p>Les réservations apparaîtront ici une fois effectuées</p>
                            </div>
                        <?php endif; ?>
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

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterReservations);
        document.getElementById('categoryFilter').addEventListener('change', filterReservations);

        function filterReservations() {
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const reservationCards = document.querySelectorAll('.reservation-card');
            
            reservationCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const cardCategory = card.getAttribute('data-category');
                
                const statusMatch = !statusFilter || cardStatus === statusFilter;
                const categoryMatch = !categoryFilter || cardCategory === categoryFilter;
                
                if (statusMatch && categoryMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Delete reservation function
        function deleteReservation(reservationId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
                window.location.href = `delete.php?id=${reservationId}`;
            }
        }
    </script>
</body>
</html>