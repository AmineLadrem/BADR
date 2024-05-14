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

$id_sortie = $_GET['id'];

// Récupérer les détails de l'absence à modifier
$query = $conn->prepare("SELECT * FROM sortie WHERE id = ?");
$query->bind_param("i", $id_sortie);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    header('Location: erreur.php'); // Rediriger vers une page d'erreur si aucune absence correspondante n'est trouvée
    exit;
}

$sortie = $result->fetch_assoc();

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'];

    // Mettre à jour l'absence dans la base de données
    $updateQuery = $conn->prepare("UPDATE sortie SET date_sortie = ?, heure_sortie = ?, motif = ? WHERE id = ?");
    $updateQuery->bind_param("sssi", $dateDebut, $dateFin, $justificatif, $id_sortie);
    $updateQuery->execute();

    header("Location: gestion_sorties.php"); // Rediriger vers la page de gestion des absences après la modification
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une sortie</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_conge.css">.
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
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
    <h1>Modifier une sortie</h1>
    <form method="post" action="">
        <label for="dateDebut">Date de sortie :</label>
        <input type="date" name="dateDebut" value="<?= $sortie['date_sortie'] ?>" required>
        <label for="dateFin">Heure de sortie :</label>
        <input type="time" name="dateFin" value="<?= $sortie['heure_sortie'] ?>" required>
        <label for="justificatif">Justificatif :</label>
        <textarea name="justificatif"><?= $sortie['motif'] ?></textarea>
        <br>
        <button type="submit"><i class="fas fa-pencil-alt"></i> Modifier</button>
    </form>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
</body>
</html>

<?php
$conn->close();
?>
