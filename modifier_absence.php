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
$query = $conn->prepare("SELECT * FROM absence WHERE id = ?");
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
    $updateQuery = $conn->prepare("UPDATE absence SET date_debut = ?, date_fin = ?, motif = ? WHERE id = ?");
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
    <h1>Modifier une absence</h1>
    <form method="post" action="">
        <label for="dateDebut">Date de début :</label>
        <input type="date" name="dateDebut" value="<?= $absence['date_debut'] ?>" required>
        <label for="dateFin">Date de fin :</label>
        <input type="date" name="dateFin" value="<?= $absence['date_fin'] ?>" required>
        <label for="justificatif">Justificatif :</label>
        <textarea name="justificatif"><?= $absence['motif'] ?></textarea>
        <br>
        <button type="submit"><i class="fas fa-pencil-alt"></i> Modifier</button>
    </form>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    var deleteButtons = document.querySelectorAll('.deleteButton');

    deleteButtons.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            var confirmDelete = confirm("Êtes-vous sûr de vouloir supprimer cette demande d'absence ?");
            if (confirmDelete) {
                var id = deleteButton.dataset.id;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'supprimer_absence.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Deletion successful
                        alert("La demande d'absence a été supprimée avec succès.");
                        // Redirect to gestion_conges.php
                        window.location.href = 'gestion_absences.php';
                    } else {
                        // Failed to delete
                        alert("Erreur lors de la suppression de la demande d'absence. Veuillez réessayer.");
                    }
                };
                xhr.send('id=' + id);
            }
        });
    });
});
    </script>
</body>
</html>

<?php
$conn->close();
?>
