<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier le token
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['form_token']) {
        header("Location: index3.php?error=invalid_token");
        exit;
    }

    // Préparation de la requête d'insertion
    $stmt = $mysqli->prepare("INSERT INTO utilisateurs (nom, prenom, date_naissance, telephone, email, diplomes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $_POST['nom'], $_POST['prenom'], $_POST['date_naissance'], $_POST['telephone'], $_POST['email'], $_POST['diplomes']);

    // Exécution de la requête
    if ($stmt->execute()) {
        unset($_SESSION['form_token']); // Suppression du token
        header("Location: index3.php?success=added"); // Redirection pour éviter la soumission multiple
    } else {
        header("Location: index3.php?error=sqlerror");
    }
    $stmt->close();
}
$mysqli->close();
?>
