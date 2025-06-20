<?php
session_start();
include "../config/config.php";

// Initialisation des messages d'erreur
$errors = [
    'email' => '',
    'password' => '',
    'login' => ''
];

// Initialiser les valeurs de champs
$input = [
    'email' => ''
];

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $input['email'] = $email;

    // Validation email
    if (empty($email)) {
        $errors['email'] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'adresse email invalide.";
    }

    // Validation mot de passe
    if (empty($password)) {
        $errors['password'] = "Le mot de passe est obligatoire.";
    }

    // Si pas d'erreurs, vérifier les identifiants
    if (empty($errors['email']) && empty($errors['password'])) {

        // Vérifier d'abord si c'est un admin
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin["password"])) {
            $_SESSION["user_id"] = $admin["admin_id"]; // Utiliser user_id pour la cohérence
            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["role"] = "admin";
            $_SESSION["email"] = $admin["email"];
            $_SESSION["name"] = $admin["name"] ?? "Admin";
            header("Location: ../admin/index.php"); // Redirection vers le dashboard admin
            exit;
        }

        // Sinon, vérifier si c'est un utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["role"] = "user";
            $_SESSION["email"] = $user["email"];
            $_SESSION["first_name"] = $user["first_name"];
            $_SESSION["last_name"] = $user["last_name"];
            header("Location: ../index.html"); // Redirection vers la page d'accueil utilisateur
            exit;
        }

        // Si ni admin ni user trouvé
        $errors['login'] = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="test">
        <form method="post" class="login-form">
            <h1>Se connecter</h1>

            <!-- Erreur générale de connexion -->
            <?php if (!empty($errors['login'])) : ?>
                <p style="color:red;"><?= $errors['login'] ?></p>
            <?php endif; ?>

            <label for="email">Email :</label>
            <input type="email" name="email" placeholder="Email" class="form-input" value="<?= htmlspecialchars($input['email']) ?>">
            <p style="color:red;"><?= $errors['email'] ?></p>

            <label for="password">Mot de passe :</label>
            <input type="password" name="password" placeholder="Mot de passe" class="form-input">
            <p style="color:red;"><?= $errors['password'] ?></p>

            <input type="submit" name="search" value="Se connecter" class="submit-btn">

            <div class="form-footer">
                Pas encore inscrit ? <a href="formulaire.php">Créer un compte</a>
            </div>
        </form>
    </div>
</body>

</html>