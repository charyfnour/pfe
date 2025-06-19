<?php 
 include "../config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up </title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 120px auto;
            padding: 30px;
            background-color:rgb(255, 255, 255);
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(139, 69, 19, 0.06);
        }

        .form-title {
            color:rgb(0, 0, 0);
            font-size: 24px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color:rgb(0, 0, 0);
            margin-bottom: 8px;
            font-size: 15px;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            font-size: 15px;
            color:rgb(0, 0, 0);
         
        }

        input:focus, select:focus {
            outline: none;
            border-color:rgb(0, 0, 0);
            box-shadow: 0 0 0 2px rgba(193, 120, 23, 0.1);
        }

        .submit-btn {
            background-color: #FFE210;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            letter-spacing: 0.5px;
            box-shadow: 0 3px 10px rgba(193, 120, 23, 0.15);
        }

        .password-info {
            font-size: 12px;
            color: #8b4513;
            margin-top: 5px;
            opacity: 0.8;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color:rgb(0, 0, 0);
        }

        .form-footer a {
            color:#FFE210;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">créer un compte</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="nom">First name</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="form-group">
                <label for="prenom">Last name</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="telephone">Phone number</label>
                <input type="tel" id="telephone" name="telephone" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm">
            </div>

            <button type="submit" class="submit-btn" name="submit">Create my account</button>

            <div class="form-footer">
                Already a member? <a href="formulaireconnexion.php">Log in</a>
            </div>
        </form>
    </div>
<?php  


if (isset($_POST['submit'])) {     
    $nom = htmlspecialchars(trim($_POST['nom']));     
    $prenom = htmlspecialchars(trim($_POST['prenom']));     
    $email = htmlspecialchars(trim($_POST['email']));     
    $telephone = htmlspecialchars(trim($_POST['telephone']));     
    $password = $_POST['password'];     
    $password_confirm = $_POST['password_confirm'];      

    if ($password !== $password_confirm) {         
        echo "error"; 
    } else {         
       $search = $pdo->prepare("SELECT * FROM users where email = ?");
       $search->execute([$email]);
       $search = $search->fetch(PDO::FETCH_ASSOC);
       if(empty($search)){
        $data_password = password_hash($password,PASSWORD_DEFAULT);
        $add_user = $pdo->prepare("INSERT INTO users(first_name,last_name,email,phone_number,password) values(?,?,?,?,?)");
        $add_user->execute([$nom,$prenom,$email,$telephone,$data_password]);
       }
    }
}
?>

