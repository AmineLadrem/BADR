<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT email, is_supervisor, est_superieur_hierarchique, nom FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
      
        $password = $_POST['password'];

        $stmt2 = $conn->prepare("SELECT email FROM compte WHERE mdp = ?");
        $stmt2->bind_param("s", $password);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
        
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_supervisor'] = $user['is_supervisor'];
            $_SESSION['est_superieur_hierarchique'] = $user['est_superieur_hierarchique'];

     
            setcookie("nom", $user['nom'], time() + (86400 * 30), "/"); 

            if ($user['est_superieur_hierarchique'] == 1) {
                header("Location: menu_PDG.html");
                exit;
            } elseif ($user['is_supervisor'] == 1) {
                header("Location: PFE.html");
                exit;
            } else {
                header("Location: menu_utilisateurs_nrml.html");
                exit;
            }
        } else {
          
            $error = "Mot de passe incorrect.";
        }

        $stmt2->close();
    } else {
      
        $error = "Utilisateur non trouvé.";
    }

    $stmt->close();
}



?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - BADR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;

            background-image: radial-gradient(circle at 24% 76%, rgb(253, 255, 255) 0%, rgb(253, 255, 255) 10%, transparent 10%, transparent 100%), radial-gradient(circle at 76% 76%, rgb(253, 255, 255) 0%, rgb(253, 255, 255) 10%, transparent 10%, transparent 100%), radial-gradient(circle at 76% 24%, rgb(253, 255, 255) 0%, rgb(253, 255, 255) 10%, transparent 10%, transparent 100%), radial-gradient(circle at 24% 24%, rgb(253, 255, 255) 0%, rgb(253, 255, 255) 10%, transparent 10%, transparent 100%), radial-gradient(circle at center center, rgb(153, 218, 84) 0%, rgb(153, 218, 84) 71%, transparent 71%, transparent 100%), linear-gradient(90deg, rgb(253, 255, 255), rgb(253, 255, 255));
            background-size: 23px 23px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
            max-width: 90%;
            box-sizing: border-box;
        }

        .login-container img {
            width: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .login-container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin-top: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #148d04;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .login-container button:hover {
            background-color: #003d82;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="badrPFE.png" alt="Bienvenue à la BADR">
        <h2>Bienvenue à la BADR</h2>
        <form action="index.php" method="post">
            <input type="email" name="email" placeholder="Entrez votre email" required>
            <input type="password" name="password" placeholder="Entrez votre mot de passe" required>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Connexion</button>
        </form>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</body>

</html>