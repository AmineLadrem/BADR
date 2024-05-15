<?php
session_start();
// Vérifiez que l'utilisateur est bien connecté ou que le token CSRF est correct si nécessaire.

// Paramètres de connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_utilisateurs";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['matricule'])) {
    $matricule = $_POST['matricule'];
    

    $sql = "DELETE FROM utilisateurs WHERE matricule = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricule);
    $stmt->execute();


    if ($stmt->affected_rows > 0) {
        echo 'User deleted successfully';
    } else {
        echo 'Error deleting user';
    }
} else {
    echo 'Matricule not provided';
}

$conn->close();

header("Location: gestion_employe.php");
exit;
?>
