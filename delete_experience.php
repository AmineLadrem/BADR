<?php
session_start();
include_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id_experience'])) {
    $id_experience = $_GET['id_experience'];

    // Supprimer l'expérience avec l'identifiant donné
    $sql = "DELETE FROM experiences WHERE id_experience=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_experience);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de la suppression: " . $stmt->error;
    } else {
        // Rediriger vers la page de gestion des expériences
        header("Location: gestion_experiences.php"); 
        exit;
    }
    $stmt->close();
}
?>
