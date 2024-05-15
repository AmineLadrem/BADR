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


if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $dateSortie = $_POST['dateSortie'];
    $heureSortie = $_POST['heureSortie'];
    $justificatif = $_POST['justificatif'] ?? '';

    if ($dateSortie >= $dateToday) {
        $stmt = $conn->prepare("INSERT INTO sortie (matricule, date_sortie, heure_sortie, motif,dec_rh, dec_pdg, statut) VALUES (?, ?, ?, ?,2,2, 'En Attente')");
        $stmt->bind_param("ssss", $matricule, $dateSortie, $heureSortie, $justificatif);
        $stmt->execute();
        $stmt->close();
        

        header("Location: gestion_sorties.php");
        exit;
    } else {
        $error = "La date de sortie fournie est invalide.";
    }
}


$query = $conn->prepare("SELECT id, date_sortie ,heure_sortie, motif, statut FROM sortie WHERE matricule = ? ORDER BY CASE WHEN statut = 'En Attente' THEN 1 WHEN statut = 'Accepté' THEN 2 ELSE 3 END");


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
        
        <label for="dateSortie">Date de sortie</label>
        <input type="date" name="dateSortie" id="dateSortie" min="<?= $dateToday ?>" required>
        
        <label for="heureSortie">Heure de sortie</label>
        <input type="time" name="heureSortie" id="heureSortie" min="<?= $dateToday ?>" required>
        
        <label for="justificatif">Justificatif (facultatif)</label>
        <textarea name="justificatif" id="justificatif"></textarea>
        <br>
        
        <button type="submit"><i class="fas fa-paper-plane"></i> Envoyer demande de sortie</button>
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
                    <?php if ($row['statut'] === 'En Attente'): ?>
                        <a href="modifier_sortie.php?id=<?= $row['id'] ?>"><i class="fas fa-edit edit-icon"></i></a> | 
                        <a class="deleteButton" data-id="<?= $row['id'] ?>"><i class="fas fa-trash-alt delete-icon"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="get_name.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var deleteButtons = document.querySelectorAll('.deleteButton');

    deleteButtons.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            var confirmDelete = confirm("Êtes-vous sûr de vouloir supprimer cette demande de sortie ?");
            if (confirmDelete) {
                var id = deleteButton.dataset.id;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'supprimer_sortie.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                   
                        alert("La demande de sortie a été supprimée avec succès.");
                    
                        window.location.href = 'gestion_sorties.php';
                    } else {
                    
                        alert("Erreur lors de la suppression de la demande de sortie. Veuillez réessayer.");
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
