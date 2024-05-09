<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: index.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit;
}

$email_utilisateur = $_SESSION['email'];
$matricule = $_SESSION['matricule'];
$nom = $_SESSION['nom'];

// Récupérer les appréciations de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM appreciation WHERE matricule = ?");
$stmt->bind_param("s", $matricule);
$stmt->execute();
$result = $stmt->get_result();

$appreciations = [];
while ($row = $result->fetch_assoc()) {
    $appreciations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Appréciations</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/mes_app.css">.
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
        <h1>Mes Appréciations</h1>
        <p>Bienvenue, <?php echo $nom; ?></p>
        <table>
            <thead>
                <tr>
                    <th>Note Assiduité</th>
                    <th>Note Travail</th>
                    <th>Note Absences</th>
                    <th>Point Fort</th>
                    <th>Point Faible</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appreciations as $appreciation): ?>
                    <tr>
                        <td><?php echo $appreciation['note_assiduite']; ?></td>
                        <td><?php echo $appreciation['note_travail']; ?></td>
                        <td><?php echo $appreciation['note_absences']; ?></td>
                        <td><?php echo $appreciation['point_fort']; ?></td>
                        <td><?php echo $appreciation['point_faibles']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
</body>
</html>
