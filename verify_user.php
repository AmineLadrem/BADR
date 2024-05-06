<?php
session_start();
include 'db.php';

$email = $_POST['email'];

// Vérifiez si l'email existe dans la base de données des utilisateurs
$sql = "SELECT * FROM utilisateurs WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Si l'email est trouvé, stockez-le dans la session et redirigez vers la page de gestion des congés
    $_SESSION['email'] = $email;
    header("Location: gestion_conges.php");
    exit;
} else {
    // Si l'email n'est pas trouvé, redirigez à nouveau vers la page de connexion avec un message d'erreur
    header("Location: login.php?error=invalidemail");
    exit;
}

$stmt->close();
$conn->close();
?>
