<?php
session_start();
include "../../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/formulaireconnexion.php");
    exit;
}

$terrain_id = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Récupérer les informations du terrain
$stmt = $pdo->prepare("SELECT * FROM terrains WHERE terrain_id = ?");
$stmt->execute([$terrain_id]);
$terrain = $stmt->fetch();

if (!$terrain) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $terrain_name = htmlspecialchars(trim($_POST['terrain_name']));
    $category = htmlspecialchars(trim($_POST['category']));
    $location = htmlspecialchars(trim($_POST['location']));
    $description = htmlspecialchars(trim($_POST['description']));
  
    
    $image_path = $terrain['image']; // Garder l'ancienne image par défaut
    
    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../../images/terrains/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Supprimer l'ancienne image si elle existe
                if ($terrain['image'] && file_exists('../../' . $terrain['image'])) {
                    unlink('../../' . $terrain['image']);
                }
                $image_path = 'images/terrains/' . $new_filename;
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $error = "Format d'image non autorisé. Utilisez JPG, JPEG, PNG ou GIF.";
        }
    }
    
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE terrains SET terrain_name = ?, category = ?, location = ?, description = ?,  image = ? WHERE terrain_id = ?");
            $stmt->execute([$terrain_name, $category, $location, $description,  $image_path, $terrain_id]);
            
            $message = "Terrain modifié avec succès !";
            
            // Recharger les données du terrain
            $stmt = $pdo->prepare("SELECT * FROM terrains WHERE terrain_id = ?");
            $stmt->execute([$terrain_id]);
            $terrain = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification du terrain : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier un terrain - SportsField</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="../index.php" class="brand-link">
            <i class="fas fa-futbol brand-image"></i>
            <span class="brand-text font-weight-light">SportsField Admin</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="../index.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">
                            <i class="nav-icon fas fa-map-marked-alt"></i>
                            <p>Gestion des terrains</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../users/index.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Gestion des utilisateurs</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../reservations/index.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Gestion des réservations</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Modifier un terrain</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Terrains</a></li>
                            <li class="breadcrumb-item active">Modifier</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Informations du terrain</h3>
                            </div>
                            
                            <?php if ($message): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="terrain_name">Nom du terrain</label>
                                        <input type="text" class="form-control" id="terrain_name" name="terrain_name" 
                                               value="<?php echo htmlspecialchars($terrain['terrain_name']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="category">Catégorie</label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">Sélectionner une catégorie</option>
                                            <option value="Football" <?php echo $terrain['category'] === 'Football' ? 'selected' : ''; ?>>Football</option>
                                            <option value="Tennis" <?php echo $terrain['category'] === 'Tennis' ? 'selected' : ''; ?>>Tennis</option>
                                            <option value="Basket" <?php echo $terrain['category'] === 'Basket' ? 'selected' : ''; ?>>Basket</option>
                                            <option value="Volleyball" <?php echo $terrain['category'] === 'Volleyball' ? 'selected' : ''; ?>>Volleyball</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="location">Localisation</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?php echo htmlspecialchars($terrain['location']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($terrain['description']); ?></textarea>
                                    </div>
                                    
                                  
                                    
                                    <div class="form-group">
                                        <label for="image">Image du terrain</label>
                                        <?php if ($terrain['image']): ?>
                                            <div class="mb-2">
                                                <img src="../../<?php echo htmlspecialchars($terrain['image']); ?>" 
                                                     alt="Image actuelle" class="img-thumbnail" style="max-width: 200px;">
                                                <p class="text-muted">Image actuelle</p>
                                            </div>
                                        <?php endif; ?>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                                <label class="custom-file-label" for="image">Choisir un nouveau fichier (optionnel)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Modifier le terrain</button>
                                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2025 <a href="#">SportsField</a>.</strong>
        Tous droits réservés.
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
</body>
</html>