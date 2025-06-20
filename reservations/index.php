<?php
session_start();
include "../config/config.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/formulaireconnexion.php");
    exit;
}

$message = '';
$error = '';

// Récupérer toutes les catégories disponibles
$stmt = $pdo->query("SELECT DISTINCT category, category_id FROM terrains ORDER BY category;");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = htmlspecialchars(trim($_POST['category']));
    $terrain_id = (int)$_POST['terrain_id'];
    $reservation_date = $_POST['reservation_date'];
    $start_time = $_POST['start_time'];
    $user_id = $_SESSION['user_id'];
    

    // Trouver le creneau_id correspondant à l'heure de début
    $stmt = $pdo->prepare("SELECT creneau_id FROM creneau_horaire WHERE heure_debut = ?");
    $stmt->execute([$start_time]);
    $creneau = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$creneau) {
        $error = "Créneau horaire invalide.";
    } else {
        $creneau_id = $creneau['creneau_id'];

        // Vérifier si le terrain est disponible à cette date et créneau
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE terrain_id = ?
              AND reservation_date = ?
              AND creneau_id = ?
              AND status != 'cancelled'
        ");
        $stmt->execute([$terrain_id, $reservation_date, $creneau_id]);
        $existing = $stmt->fetchColumn();

        if ($existing > 0) {
            $error = "Ce créneau horaire est déjà réservé. Veuillez choisir une autre heure.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO reservations (user_id, terrain_id, reservation_date, creneau_id, status, created_at) 
                    VALUES (?, ?, ?, ?, 'confirmed', NOW())
                ");
                $stmt->execute([$user_id, $terrain_id, $reservation_date, $creneau_id]);

                $message = "Votre réservation a été confirmée avec succès !";
            } catch (PDOException $e) {
                $error = "Erreur lors de la réservation : " . $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un terrain - SportsField</title>
    <link rel="stylesheet" href="reservation-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-futbol"></i>
                    <span>SportsField</span>
                </div>
                <nav class="nav">
                    <a href="../index.html" class="nav-link">Accueil</a>
                    <a href="../terrains/index.php" class="nav-link">Terrains</a>
                    <a href="#" class="nav-link active">Réserver</a>
                    <a href="../auth/logout.php" class="nav-link">Déconnexion</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="reservation-container">
                <div class="reservation-header">
                    <h1>Réserver un terrain</h1>
                    <p>Choisissez votre terrain et votre créneau horaire préféré</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form class="reservation-form" method="POST" id="reservationForm">
                    <div class="form-grid">
                        <!-- Catégorie -->
                        <div class="form-group">
                            <label for="category">
                                <i class="fas fa-tags"></i>
                                Catégorie de sport
                            </label>
                            <select id="category" name="category" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $cat):?>
                                    <option value="<?php echo htmlspecialchars($cat["category_id"]); ?>">
                                        <?php echo htmlspecialchars($cat["category"]); ?>
                                    </option>
                                <?php 
                            endforeach; ?>
                            </select>
                        </div>

                        <!-- Terrain -->
                        <div class="form-group">
                            <label for="terrain_id">
                                <i class="fas fa-map-marked-alt"></i>
                                Terrain
                            </label>
                            <select id="terrain_id" name="terrain_id" required disabled>
                                <option value="">Sélectionnez d'abord une catégorie</option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="form-group">
                            <label for="reservation_date">
                                <i class="fas fa-calendar-alt"></i>
                                Date de réservation
                            </label>
                            <input type="date" id="reservation_date" name="reservation_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Heure -->
                        <div class="form-group">
                            <label for="start_time">
                                <i class="fas fa-clock"></i>
                                Heure de début
                            </label>
                            <input type="time" id="start_time" name="start_time" required>
                            <small class="form-help">La réservation dure 1 heure</small>
                        </div>
                    </div>

                    <!-- Terrain Info -->
                    <div class="terrain-info" id="terrainInfo" style="display: none;">
                        <div class="terrain-card">
                            <img id="terrainImage" src="" alt="Terrain" class="terrain-image">
                            <div class="terrain-details">
                                <h3 id="terrainName"></h3>
                                <p id="terrainLocation"></p>
                                <p id="terrainDescription"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i>
                            Confirmer la réservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="logo">
                        <i class="fas fa-futbol"></i>
                        <span>SportsField</span>
                    </div>
                    <p>© 2025 SportsField, Inc. Tous droits réservés.</p>
                </div>
                <div class="footer-column">
                    <h4>Liens utiles</h4>
                    <ul>
                        <li><a href="../index.html">Accueil</a></li>
                        <li><a href="../terrains/index.php">Terrains</a></li>
                        <li><a href="#">Mes réservations</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Nous contacter</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Force time input to whole hours only
        document.getElementById("start_time").addEventListener("input", function() {
            let timeValue = this.value;
            // Ensure that the time is set to the whole hour (MM = 00)
            if (timeValue && timeValue.split(":")[1] !== "00") {
                this.value = timeValue.split(":")[0] + ":00";  // Force minutes to 00
            }
        });

        // Load terrains based on selected category
        document.getElementById('category').addEventListener('change', function() {
            const category = this.value;
            const terrainSelect = document.getElementById('terrain_id');
            const terrainInfo = document.getElementById('terrainInfo');
            console.log(category)
            if (category) {
                // Fetch terrains for selected category
                fetch(`get_terrains.php?category_id=${encodeURIComponent(category)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log(data)
                        terrainSelect.innerHTML = '<option value="">Sélectionnez un terrain</option>';
                        data.forEach(terrain => {
                            const option = document.createElement('option');
                            option.value = terrain.terrain_id;
                            option.textContent = terrain.terrain_name;
                            option.dataset.location = terrain.location;
                            option.dataset.description = terrain.description;
                            option.dataset.image = terrain.image;
                            terrainSelect.appendChild(option);
                        });
                        terrainSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        terrainSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });
            } else {
                terrainSelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie</option>';
                terrainSelect.disabled = true;
                terrainInfo.style.display = 'none';
            }
        });

        // Show terrain info when terrain is selected
        document.getElementById('terrain_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const terrainInfo = document.getElementById('terrainInfo');
            
            if (this.value) {
                document.getElementById('terrainName').textContent = selectedOption.textContent;
                document.getElementById('terrainLocation').textContent = selectedOption.dataset.location;
                document.getElementById('terrainDescription').textContent = selectedOption.dataset.description;
                document.getElementById('terrainImage').src = '../' + selectedOption.dataset.image;
                terrainInfo.style.display = 'block';
            } else {
                terrainInfo.style.display = 'none';
            }
        });

        // Set minimum date to today
        document.getElementById('reservation_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>