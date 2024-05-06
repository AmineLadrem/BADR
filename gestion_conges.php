<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données
 

$email = $_SESSION['email'];
$matricule = $_SESSION['matricule'];
$dateToday = date("Y-m-d");  // Récupère la date d'aujourd'hui au format année-mois-jour


// Vérifier et afficher les jours de congés restants
$daysQuery = $conn->prepare("SELECT joursCongesRestants FROM utilisateurs WHERE matricule = ?");
$daysQuery->bind_param("s", $matricule);
$daysQuery->execute();
$daysQuery->bind_result($joursRestants);
$daysQuery->fetch();
$daysQuery->close();

// Traitement du formulaire de demande de congé
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'] ?? '';

    if ($dateDebut >= $dateToday && $dateFin >= $dateDebut) {
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        $interval = $debut->diff($fin);
        $daysRequested = $interval->days + 1;

        if ($daysRequested <= $joursRestants) {
            $joursRestants -= $daysRequested;
            $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = ? WHERE email = ?");
            $updateDays->bind_param("is", $joursRestants, $email);
            $updateDays->execute();
            $updateDays->close();

            $stmt = $conn->prepare("INSERT INTO demandesConges (email, dateDebut, dateFin, justificatif, statut) VALUES (?, ?, ?, ?, 'En attente')");
            $stmt->bind_param("ssss", $email, $dateDebut, $dateFin, $justificatif);
            $stmt->execute();
            $stmt->close();
            
            // Redirection pour éviter la resoumission du formulaire
            header("Location: gestion_conges.php");
            exit;
        } else {
            $error = "Vous ne pouvez pas demander $daysRequested jours. Il vous reste seulement $joursRestants jours de congés.";
        }
    } else {
        $error = "Les dates fournies sont invalides.";
    }
}

// Récupérer les demandes de congés de l'utilisateur connecté
$query = $conn->prepare("SELECT  id, dateDebut, dateFin, statut, justificatif FROM demandesConges WHERE email = ?");


$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Congés</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_conges.css">.
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
      
    <script>
        window.onload = function() {
            var rows = document.querySelectorAll('table tr[data-start-date]');
            var today = new Date().setHours(0,0,0,0);

            rows.forEach(function(row) {
                var startDate = new Date(row.getAttribute('data-start-date')).setHours(0,0,0,0);
                if (startDate < today) {
                    row.classList.add('past');
                }
            });
        };
    </script>
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
    <h1>Gestion des Congés</h1>
    <h2>Vous avez <?= $joursRestants ?> jours de congés restants.</h2>
    <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <label for="dateDebut">Date de début</label>
    <input type="date" name="dateDebut" id="dateDebut" min="<?= $dateToday ?>" required>
    
    <label for="dateFin">Date de fin</label>
    <input type="date" name="dateFin" id="dateFin" min="<?= $dateToday ?>" required>
    
    <label for="justificatif">Justificatif (facultatif)</label>
    <textarea name="justificatif" id="justificatif"></textarea>
    <br>
    
    <button type="submit">Envoyer demande de congé</button>
</form>


    <h2>Vos demandes de congés</h2>
    <table>
        <thead>
            <tr>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Statut</th>
                <th>Justificatif</th>
                <th>Actions</th>
                

            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr data-start-date="<?= htmlspecialchars($row['dateDebut']) ?>">
                <td><?= htmlspecialchars($row['dateDebut']) ?></td>
                <td><?= htmlspecialchars($row['dateFin']) ?></td>
                <td><?= htmlspecialchars($row['statut']) ?></td>
                <td><?= htmlspecialchars($row['justificatif']) ?></td>
                <td>
                <a href="modifier_conge.php?id=<?= $row['id'] ?>"><i class="fas fa-edit edit-icon"></i></a> | 
                <a href="supprimer_conge.php?id=<?= $row['id'] ?>"><i class="fas fa-trash-alt delete-icon"></i></a>
            
                </td>

               
                
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
    </div>
</body>
</html>
<?php
$conn->close();
?>
