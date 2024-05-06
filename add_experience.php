<?php
session_start();
include_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_utilisateur'])) {
    $id_utilisateur = $_POST['id_utilisateur'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $poste = $_POST['poste'];
    $entreprise = $_POST['entreprise'];
    $motif = $_POST['motif']; // Ajout du champ motif

    $sql = "INSERT INTO experiences (id_utilisateur, date_debut, date_fin, poste, entreprise, motif) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $id_utilisateur, $date_debut, $date_fin, $poste, $entreprise, $motif); // Ajout de 's' pour le motif
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de l'ajout: " . $stmt->error;
    } else {
        header("Location: gestion_experiences.php?id=$id_utilisateur"); 
        exit;
    }
    $stmt->close();
}
?>
