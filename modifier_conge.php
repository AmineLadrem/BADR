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

$query = $conn->prepare("SELECT * FROM conge WHERE id = ?");
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

    // Calculate the difference in days between the old and new dates
    $oldStartDate = $conge['dateDebut'];
    $oldEndDate = $conge['dateFin'];
    $oldStartDateObj = new DateTime($oldStartDate);
    $oldEndDateObj = new DateTime($oldEndDate);
    $oldInterval = $oldStartDateObj->diff($oldEndDateObj);
    $oldDaysRequested = $oldInterval->days + 1;

    $newStartDateObj = new DateTime($dateDebut);
    $newEndDateObj = new DateTime($dateFin);
    $newInterval = $newStartDateObj->diff($newEndDateObj);
    $newDaysRequested = $newInterval->days + 1;

    // Calculate the difference in days between the old and new dates
    $daysDifference = $newDaysRequested - $oldDaysRequested;

    // Update the vacation request in the database
    $updateQuery = $conn->prepare("UPDATE conge SET dateDebut = ?, dateFin = ?, justificatif = ? WHERE id = ?");
    $updateQuery->bind_param("sssi", $dateDebut, $dateFin, $justificatif, $id_conge);
    $updateQuery->execute();

    // Fetch the remaining vacation days after updating
    $daysQuery = $conn->prepare("SELECT joursCongesRestants FROM utilisateurs WHERE matricule = ?");
    $daysQuery->bind_param("s", $matricule);
    $daysQuery->execute();
    $daysQuery->bind_result($joursRestants);
    $daysQuery->fetch();
    $daysQuery->close();

    // Calculate and update the remaining vacation days
    $joursRestants -= $daysDifference;
    $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = ? WHERE matricule = ?");
    $updateDays->bind_param("is", $joursRestants, $matricule);
    $updateDays->execute();
    $updateDays->close();

    header("Location: gestion_conges.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une demande de congé</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_conge.css">.
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Styles CSS -->
</head>
<body>
<div class="navbar">
        <a class="logo" href="menu_utilisateurs_nrml.html"><img src="badrPFE.png" alt="Accueil"></a>
        <a href="gestion_conges.php">Gestion des congés</a>
        <a href="gestion_absences.php">Gestion des absences</a> 
        <a href="gestion_sorties.php">Gestion des sorties</a>
        <a href="mes_appreciations.php">Mes appréciations</a>
        <div class="user-info">
            <span id="userWelcome"></span>
            <button class="logout-button" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </div>
    <div class="container">
    <h1>Modifier une demande de congé</h1>
    <form method="post" action="">
        <label for="dateDebut">Date de début :</label>
        <input type="date" name="dateDebut" value="<?= $conge['dateDebut'] ?>" required>
        <label for="dateFin">Date de fin :</label>
        <input type="date" name="dateFin" value="<?= $conge['dateFin'] ?>" required>
        <label for="justificatif">Justificatif :</label>
        <textarea name="justificatif"><?= $conge['justificatif'] ?></textarea>
        <br>
        <button type="submit"><i class="fas fa-pencil-alt"></i> Modifier</button>

    </form>

    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
    <script>
        // JavaScript code here...
    </script>
</body>
</html>

<?php
$conn->close();
?>
