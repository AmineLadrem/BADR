<?php
include 'db.php'; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_sortie = $_POST['date_sortie'];
    $heure_sortie = $_POST['heure_sortie'];
    $motif = $_POST['motif'];

    // Insérer une nouvelle demande
    $stmt = $conn->prepare("INSERT INTO demandes_de_sorties (date_sortie, heure_sortie, motif, statut) VALUES (?, ?, ?, 'planifiée')");
    $stmt->bind_param("sss", $date_sortie, $heure_sortie, $motif);

    if ($stmt->execute()) {
        echo "Demande de sortie ajoutée avec succès.";
    } else {
        echo "Erreur lors de l'ajout de la demande: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close(); // Fermer la connexion à la base de données
