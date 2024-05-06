<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header('Location: login.php'); // Rediriger vers la page de connexion si aucun utilisateur n'est connecté
    exit;
}

// Vérifier si l'identifiant de l'absence est spécifié dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: erreur.php'); // Rediriger vers une page d'erreur si l'identifiant est manquant
    exit;
}

$id_absence = $_GET['id'];

// Récupérer les détails de l'absence à modifier
$query = $conn->prepare("SELECT * FROM absences WHERE id = ?");
$query->bind_param("i", $id_absence);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    header('Location: erreur.php'); // Rediriger vers une page d'erreur si aucune absence correspondante n'est trouvée
    exit;
}

$absence = $result->fetch_assoc();

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'];

    // Mettre à jour l'absence dans la base de données
    $updateQuery = $conn->prepare("UPDATE absences SET date_debut = ?, date_fin = ?, motif = ? WHERE id = ?");
    $updateQuery->bind_param("sssi", $dateDebut, $dateFin, $justificatif, $id_absence);
    $updateQuery->execute();

    header("Location: gestion_absences.php"); // Rediriger vers la page de gestion des absences après la modification
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une absence</title>
    <style>
        /* Styles CSS */
    </style>
</head>
<body>
    <h1>Modifier une absence</h1>
    <form method="post" action="">
        <label for="dateDebut">Date de début :</label>
        <input type="date" name="dateDebut" value="<?= $absence['date_debut'] ?>" required>
        <label for="dateFin">Date de fin :</label>
        <input type="date" name="dateFin" value="<?= $absence['date_fin'] ?>" required>
        <label for="justificatif">Justificatif :</label>
        <textarea name="justificatif"><?= $absence['motif'] ?></textarea>
        <button type="submit">Modifier</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>
