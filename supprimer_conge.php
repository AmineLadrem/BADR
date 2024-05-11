<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401); // Unauthorized
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        http_response_code(400); // Bad Request
        exit;
    }

    $id_conge = $_POST['id'];

    // Fetching the data before deleting the row
    $fetchQuery = $conn->prepare("SELECT matricule, dateDebut, dateFin FROM conge WHERE id = ?");
    $fetchQuery->bind_param("i", $id_conge);
    $fetchQuery->execute();
    $fetchQuery->bind_result($matricule, $dateDebut, $dateFin);
    $fetchQuery->fetch();
    $fetchQuery->close();

    $deleteQuery = $conn->prepare("DELETE FROM conge WHERE id = ?");
    $deleteQuery->bind_param("i", $id_conge);
    if ($deleteQuery->execute()) {
        // Calculating the number of days
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        $interval = $debut->diff($fin);
        $daysRequested = $interval->days + 1;

        // Fetching the user's remaining days
        $daysQuery = $conn->prepare("SELECT joursCongesRestants FROM utilisateurs WHERE matricule = ?");
        $daysQuery->bind_param("s", $matricule);
        $daysQuery->execute();
        $daysQuery->bind_result($joursRestants);
        $daysQuery->fetch();
        $daysQuery->close();

        // Updating the user's remaining days
        $joursRestants += $daysRequested;
        $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = ? WHERE matricule = ?");
        $updateDays->bind_param("is", $joursRestants, $matricule);
        $updateDays->execute();
        $updateDays->close();

        http_response_code(200); // OK
        header("Location: gestion_conges.php");
        exit;
    } else {
        http_response_code(500); // Internal Server Error
        header("Location: gestion_conges.php");
        exit;
    }
} else {
    http_response_code(405); // Method Not Allowed
    header("Location: gestion_conges.php");
    exit;
}
?>
