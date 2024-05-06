<?php
session_start();
// Vérifiez que l'utilisateur est bien connecté ou que le token CSRF est correct si nécessaire.

// Paramètres de connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_utilisateurs";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assurez-vous que l'ID de l'utilisateur à supprimer est bien passé et est valide
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Préparez une déclaration pour la suppression
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Liez les paramètres pour les marqueurs
    $stmt->bind_param('i', $id);

    // Exécutez la requête préparée
    $stmt->execute();

    // Vérifiez si la suppression a réussi
    if ($stmt->affected_rows > 0) {
        echo "L'utilisateur a été supprimé avec succès.";
    } else {
        echo "Aucun utilisateur correspondant trouvé ou déjà supprimé.";
    }

    $stmt->close();
} else {
    echo "ID invalide ou non fourni.";
}

$conn->close();

// Redirection vers la page principale après la suppression
header("Location: index3.php");
exit;
?>
