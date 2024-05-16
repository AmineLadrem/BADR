<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT email, is_supervisor, est_superieur_hierarchique, nom , matricule FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
      
        $password = $_POST['password'];

        $stmt2 = $conn->prepare("SELECT * FROM compte_utilisateur  WHERE matricule=? and mdp = ?");
        $stmt2->bind_param("is", $user['matricule'],$password);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows > 0) {
            $user2 = $result2->fetch_assoc();
            $_SESSION['email'] = $user['email'];
            $_SESSION['matricule'] = $user['matricule'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['is_supervisor'] = $user['is_supervisor'];
            $_SESSION['est_superieur_hierarchique'] = $user['est_superieur_hierarchique'];

     
            setcookie("nom", $user['nom'], time() + (86400 * 30), "/"); 
            setcookie("matricule", $user['matricule'], time() + (86400 * 30), "/"); 

            if($user2['mdp_reset']==0){
                header("Location: pw_reset.php");
            }
            else {
                if ($user['est_superieur_hierarchique'] == 1) {
                    header("Location: menu_PDG.html");
                    exit;
                } elseif ($user['is_supervisor'] == 1) {
                    header("Location: RH.html");
                    exit;
                } else {
                    header("Location: menu_utilisateurs_nrml.html");
                    exit;
                }

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
    <link rel="stylesheet" href="styles/index.css">
</head>

<body>
    <div class="login-container">
        <img src="badrPFE.png" alt="Bienvenue à la BADR">
        <h2>Bienvenue à la BADR</h2>
        <form action="index.php" method="post">
            <input type="email" name="email" placeholder="Entrez votre email" required>
            <input type="password" name="password" placeholder="Entrez votre mot de passe" required>
            <a class="pw" href="#">Mot de passe oublié?</a>
            <div class="line"></div>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Connexion</button>

        </form>
        
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>

    
</body>

</html>