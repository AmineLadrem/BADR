<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $matricule=$_SESSION['matricule'];

        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (empty($password) || empty($confirm_password)) {
            $error = "Veuillez saisir un mot de passe.";
        } else {
            // Perform SQL query to update password in the database
            $stmt = $conn->prepare("UPDATE compte_utilisateur SET mdp = ? , mdp_reset=1 WHERE matricule= ?");
            $stmt->bind_param("si", $password,$matricule);
            $stmt->execute();
            $stmt->close();
            session_destroy();
            // Redirect user to appropriate page after password update
            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changement de Mot de Passe - BADR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles/pw.css">
</head>

<body>
    <div class="login-container">
        <img src="badrPFE.png" alt="Bienvenue à la BADR">
        <h2>Changement de Mot de Passe</h2>
        <form method="post">
            <div class="password-input">
                <input type="password" name="password" id="password" placeholder="Nouveau Mot de Passe" required>
                <button type="button" onclick="revealPassword('password')"><i class="fas fa-eye"></i></button>
            </div>
            <div class="password-input">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmer le Mot de Passe" required>
                <button type="button" onclick="revealPassword('confirm_password')"><i class="fas fa-eye"></i></button>
            </div>
            <button type="submit"><i class="fas fa-sync-alt"></i> Confirmer le changement de mot de passe</button>
        </form>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>

    <script>
        function revealPassword(inputId) {
            var input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
    </script>
</body>

</html>
