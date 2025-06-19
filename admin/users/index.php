<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

// Récupérer tous les utilisateurs avec leurs statistiques
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(r.reservation_id) as total_reservations,
           MAX(r.created_at) as last_reservation
    FROM users u 
    LEFT JOIN reservations r ON u.user_id = r.user_id 
    GROUP BY u.user_id 
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - SportsField</title>
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
                    <li class="nav-item active">
                        <a href="index.php" class="nav-link">
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
                    <h1>Gestion des utilisateurs</h1>
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
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Liste des utilisateurs (<?php echo count($users); ?>)</h3>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Rechercher un utilisateur..." id="searchInput">
                        </div>
                    </div>
                    <div class="card-content">
                        <?php if (count($users) > 0): ?>
                            <div class="users-grid">
                                <?php foreach ($users as $user): ?>
                                <div class="user-card" data-user="<?php echo strtolower($user['first_name'] . ' ' . $user['last_name'] . ' ' . $user['email']); ?>">
                                    <div class="user-avatar-large">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-info-detailed">
                                        <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <p class="user-phone">
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($user['phone_number']); ?>
                                        </p>
                                    </div>
                                    <div class="user-stats">
                                        <div class="stat-item">
                                            <span class="stat-number"><?php echo $user['total_reservations']; ?></span>
                                            <span class="stat-label">Réservations</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-date">
                                                <?php 
                                                if ($user['last_reservation']) {
                                                    echo date('d/m/Y', strtotime($user['last_reservation']));
                                                } else {
                                                    echo 'Aucune';
                                                }
                                                ?>
                                            </span>
                                            <span class="stat-label">Dernière réservation</span>
                                        </div>
                                    </div>
                                    <div class="user-actions">
                                        <span class="join-date">
                                        </span>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <button class="delete-btn" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3>Aucun utilisateur trouvé</h3>
                                <p>Les utilisateurs apparaîtront ici une fois qu'ils se seront inscrits</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .search-box {
            position: relative;
            max-width: 300px;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .user-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .user-avatar-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }

        .user-info-detailed {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .user-info-detailed h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .user-email {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .user-phone {
            color: #64748b;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .user-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-date {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .join-date {
            font-size: 0.875rem;
            color: #64748b;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: #fecaca;
            transform: scale(1.1);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: #64748b;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
        }

        @media (max-width: 768px) {
            .users-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .search-box {
                max-width: none;
            }
        }
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

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const userData = card.getAttribute('data-user');
                if (userData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Delete user function
        function deleteUser(userId, userName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${userName}" ?\n\nCette action supprimera également toutes ses réservations et ne peut pas être annulée.`)) {
                window.location.href = `delete.php?id=${userId}`;
            }
        }
    </script>
</body>
</html>