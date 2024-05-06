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

$conge = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'];

    $updateQuery = $conn->prepare("UPDATE demandesConges SET dateDebut = ?, dateFin = ?, justificatif = ? WHERE id = ?");
    $updateQuery->bind_param("sssi", $dateDebut, $dateFin, $justificatif, $id_conge);
    $updateQuery->execute();

    header("Location: gestion_conges.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une demande de congé</title>
    <!-- Styles CSS -->
</head>
<body>
    <h1>Modifier une demande de congé</h1>
    <form method="post" action="">
        <label for="dateDebut">Date de début :</label>
        <input type="date" name="dateDebut" value="<?= $conge['dateDebut'] ?>" required>
        <label for="dateFin">Date de fin :</label>
        <input type="date" name="dateFin" value="<?= $conge['dateFin'] ?>" required>
        <label for="justificatif">Justificatif :</label>
        <textarea name="justificatif"><?= $conge['justificatif'] ?></textarea>
        <button type="submit">Modifier</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>
