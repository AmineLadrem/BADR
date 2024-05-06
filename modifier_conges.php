<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Demande non spécifiée.";
    exit;
}

// Récupération des informations de la demande
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT * FROM demandesConges WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($demande = $result->fetch_assoc()) {
        // Formulaire avec les informations de la demande
        echo "<form method='post'>";
        echo "Date de début: <input type='date' name='dateDebut' value='" . $demande['dateDebut'] . "' required><br>";
        echo "Date de fin: <input type='date' name='dateFin' value='" . $demande['dateFin'] . "' required><br>";
        echo "Justificatif (facultatif): <textarea name='justificatif'>" . $demande['justificatif'] . "</textarea><br>";
        echo "<button type='submit'>Mettre à jour</button>";
        echo "</form>";
    } else {
        echo "Demande non trouvée.";
    }
    $stmt->close();
}

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'];

    $stmt = $conn->prepare("UPDATE demandesConges SET dateDebut = ?, dateFin = ?, justificatif = ? WHERE id = ?");
    $stmt->bind_param("sssi", $dateDebut, $dateFin, $justificatif, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Mise à jour réussie.";
    } else {
        echo "Aucune modification effectuée.";
    }
    $stmt->close();
    header("Location: gestion_conges.php");
    exit;
}

$conn->close();
?>
