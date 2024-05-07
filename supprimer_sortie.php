<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

// Vérifier si l'identifiant de la demande d'absence est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gestion_sorites.php'); // Rediriger si l'identifiant est invalide
    exit;
}

$id = $_GET['id'];

// Supprimer la demande d'absence correspondant à l'identifiant
$stmt = $conn->prepare("DELETE FROM demandes_de_sorties WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Rediriger l'utilisateur vers la page de gestion des absences
header('Location: gestion_sorties.php');
exit;

// Fermer la connexion à la base de données
$conn->close();
?>
