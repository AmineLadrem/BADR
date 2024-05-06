<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

if (!isset($_SESSION['email'])) {
    header('Location: index.php'); // Rediriger vers la page de connexion si aucun utilisateur n'est connecté
    exit;
}

$email = $_SESSION['email'];
$matricule = $_SESSION['matricule'];
$dateToday = date("Y-m-d");  // Récupère la date d'aujourd'hui au format année-mois-jour

// Gestion de CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire de demande d'absence
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'] ?? '';

    if ($dateDebut >= $dateToday && $dateFin >= $dateDebut) {
        $stmt = $conn->prepare("INSERT INTO absences (email_utilisateur, date_debut, date_fin, motif, statut) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->bind_param("ssss", $email, $dateDebut, $dateFin, $justificatif);
        $stmt->execute();
        $stmt->close();
        
        // Redirection pour éviter la resoumission du formulaire
        header("Location: gestion_absences.php");
        exit;
    } else {
        $error = "Les dates fournies sont invalides.";
    }
}

// Récupérer les absences de l'utilisateur connecté
$query = $conn->prepare("SELECT id, date_sortie ,heure_sortie, motif, statut FROM demandes_de_sorties WHERE matricule = ?");

$query->bind_param("s", $matricule);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des sorties</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_sortie.css">.
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
    <h1>Gestion des Sorties</h1>
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
    
    <button type="submit">Envoyer demande de sortie</button>
</form>
    <h2>Vos demandes de sortie</h2>
    <table>
        <thead>
            <tr>    
                <th>Date de sortie</th>
                <th>Heure de sortie</th>
                <th>Motif</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr >
            <td><?= htmlspecialchars($row['date_sortie']) ?></td>
                <td><?= htmlspecialchars($row['heure_sortie']) ?></td>
                <td><?= htmlspecialchars($row['motif']) ?></td>
                <td><?= htmlspecialchars($row['statut']) ?></td>
                <td>
                <a href="modifier_absence.php?id=<?= $row['id'] ?>"><i class="fas fa-edit edit-icon"></i></a> | 
                <a href="supprimer_absence.php?id=<?= $row['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette absence?');"><i class="fas fa-trash-alt delete-icon"></i></a>
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
