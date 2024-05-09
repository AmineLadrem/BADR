<?php
session_start();
include 'db.php'; // Inclure votre connexion à la base de données

if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: login.php'); // Rediriger vers la page de connexion si l'utilisateur n'est pas un superviseur
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $absenceId = $_POST['absenceId'];

    // Mettre à jour le statut de l'absence dans la base de données
    $sql = "UPDATE absences SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $decision, $absenceId);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de la mise à jour du statut de l'absence: " . $stmt->error;
    } else {
        // Rediriger vers la page de gestion des absences du superviseur
        header("Location: gestion_absences_superviseur.php");
        exit;
    }
}

// Récupérer les absences en attente
$result = $conn->query("SELECT * FROM absences WHERE statut = 'en attente'");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Absences</title>

    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_demandes_absences.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
<div class="navbar">
        <a class="logo" href="RH.html"><img src="badrPFE.png" alt="Accueil"></a>
        <a href="gestion_employe.php">Gestion des employés</a>
        <a href="gestion_demandes_conges.php">Gestion des congés</a>
        <a href="gestion_absences_superviseur.php">Gestion des absences</a>
        <a href="gestion_demandes_sorties.php">Gestion des sorties</a>
 
        <div class="user-info">
            <span id="userWelcome"></span>
            <button class="logout-button" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </div>
    <div class="container">
    <h1>Gestion des Absences  </h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Email Utilisateur</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Motif</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= isset($row["email_utilisateur"]) ? $row["email_utilisateur"] : "Non défini" ?></td>
                        <td><?= isset($row["date_debut"]) ? $row["date_debut"] : "Non défini" ?></td>
                        <td><?= isset($row["date_fin"]) ? $row["date_fin"] : "Non défini" ?></td>
                        <td><?= isset($row["motif"]) ? $row["motif"] : "Non défini" ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="absenceId" value="<?= $row['id'] ?>">
                                <button type="submit" name="decision" value="accepter">Accepter</button>
                    <button type="submit" name="decision" value="refuser">Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune absence en attente.</p>
    <?php endif; ?>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
</body>
</html>

<?php
$conn->close();
?>
