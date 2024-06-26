<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'db.php';
 

$email = $_SESSION['email'];
$matricule = $_SESSION['matricule'];
$dateToday = date("Y-m-d");


$daysQuery = $conn->prepare("SELECT joursCongesRestants FROM utilisateurs WHERE matricule = ?");
$daysQuery->bind_param("s", $matricule);
$daysQuery->execute();
$daysQuery->bind_result($joursRestants);
$daysQuery->fetch();
$daysQuery->close();

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
            $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = ? WHERE matricule = ?");
            $updateDays->bind_param("is", $joursRestants, $matricule);
            $updateDays->execute();
            $updateDays->close();

            $stmt = $conn->prepare("INSERT INTO conge (matricule, dateDebut, dateFin, justificatif, dec_rh, dec_pdg, statut) VALUES (?, ?, ?, ?, 2, 2, 'En Attente')");
$stmt->bind_param("ssss", $matricule, $dateDebut, $dateFin, $justificatif);

            $stmt->execute();
            $stmt->close();
            header("Location: gestion_conges.php");
            exit;
        } else {
            $error = "Vous ne pouvez pas demander $daysRequested jours. Il vous reste seulement $joursRestants jours de congés.";
        }
    } else {
        $error = "Les dates fournies sont invalides.";
    }
}
$query = $conn->prepare("SELECT  id, dateDebut, dateFin, statut, justificatif FROM conge WHERE matricule = ? ORDER BY CASE 
WHEN statut = 'En Attente' THEN 1 
WHEN statut = 'Accepté' THEN 2 
ELSE 3 
END");


$query->bind_param("s", $matricule);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Congés</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_conge.css">.
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
    <h2 name="remaining_days">Vous avez <?= $joursRestants ?> jours de congés restants.</h2>
    <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <label for="dateDebut">Date de début</label>
    <input type="date" name="dateDebut" id="dateDebut" min="<?= $dateToday ?>" required>
    
    <label for="dateFin">Date de fin</label>
    <input type="date" name="dateFin" id="dateFin" min="<?= $dateToday ?>" required>
    
    <label for="justificatif">Justificatif (facultatif)</label>
    <textarea name="justificatif" id="justificatif"></textarea>
    
    <br>

    
    <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <br>
    <br>
    <button type="submit"><i class="fas fa-paper-plane"></i> Envoyer demande de congé</button>

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
                    <?php if ($row['statut'] === 'En Attente'): ?>
                        <a href="modifier_conge.php?id=<?= $row['id'] ?>"><i class="fas fa-edit edit-icon"></i></a> | 
                        <a class="deleteButton" data-id="<?= $row['id'] ?>"><i class="fas fa-trash-alt delete-icon"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
    <script>


document.addEventListener('DOMContentLoaded', function() {
    var deleteButtons = document.querySelectorAll('.deleteButton');

    deleteButtons.forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            var confirmDelete = confirm("Êtes-vous sûr de vouloir supprimer cette demande de congé ?");
            if (confirmDelete) {
                var id = deleteButton.dataset.id;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'supprimer_conge.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        
                        alert("La demande de congé a été supprimée avec succès.");
                   
                        window.location.href = 'gestion_conges.php';
                    } else {
                   
                        alert("Erreur lors de la suppression de la demande de congé. Veuillez réessayer.");
                    }
                };
                xhr.send('id=' + id);
            }
        });
    });
});




const actualBtn = document.getElementById('actual-btn');

const fileChosen = document.getElementById('file-chosen');

actualBtn.addEventListener('change', function(){
  fileChosen.textContent = this.files[0].name
})

    document.addEventListener('DOMContentLoaded', function() {
        var congeExceptionnelCheckbox = document.getElementById('congeExceptionnel');
        var congeExceptionnelInputs = document.getElementById('congeExceptionnelInputs');

        congeExceptionnelCheckbox.addEventListener('change', function() {
            if (this.checked) {
                congeExceptionnelInputs.style.display = 'block';
            } else {
                congeExceptionnelInputs.style.display = 'none';
            }
        });
    });
</script>

    </div>
</body>
</html>
<?php
$conn->close();
?>
