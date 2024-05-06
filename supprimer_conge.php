<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: erreur.php');
    exit;
}

$id_conge = $_GET['id'];

$query = $conn->prepare("SELECT * FROM demandesConges WHERE id = ?");
$query->bind_param("i", $id_conge);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    header('Location: erreur.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteQuery = $conn->prepare("DELETE FROM demandesConges WHERE id = ?");
    $deleteQuery->bind_param("i", $id_conge);
    $deleteQuery->execute();

    header("Location: gestion_conges.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer une demande de congé</title>
    <!-- Styles CSS -->
</head>
<body>
    <h1>Supprimer une demande de congé</h1>
    <p>Êtes-vous sûr de vouloir supprimer cette demande de congé ?</p>
    <form method="post" action="">
        <button type="submit">Confirmer la suppression</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>
