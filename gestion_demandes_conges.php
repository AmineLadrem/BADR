<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: index.php');
    exit;
}
$dateToday = date("Y-m-d");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $justificatif = $_POST['justificatif'] ?? '';
    $demandeId = $_POST['demandeId'];

    $demandeInfo = $conn->prepare("SELECT matricule, dateDebut, dateFin , justificatif FROM demandesConges WHERE id = ?");
    $demandeInfo->bind_param("i", $demandeId);
    $demandeInfo->execute();
    $result = $demandeInfo->get_result();

    if ($result->num_rows > 0) {
        $info = $result->fetch_assoc();

        $getUser = $conn->prepare("SELECT nom, prenom FROM utilisateurs WHERE matricule = ?");
$getUser->bind_param("i", $info['matricule']);

    $getUser->execute();
    $result = $getUser->get_result();
    $info = $result->fetch_assoc();

        // Vérifier si les dates sont non nulles avant de les utiliser
        if ($info['dateDebut'] !== null && $info['dateFin'] !== null) {
            $debut = new DateTime($info['dateDebut']);
            $fin = new DateTime($info['dateFin']);
            $interval = $debut->diff($fin);
            $daysRequested = $interval->days + 1;

            if ($decision === 'accepter') {
                $status = 'Acceptée';
            } else {
                $status = 'Refusée';

                // Restituer les jours si la demande est refusée
                $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = joursCongesRestants + ? WHERE email = ?");
                $updateDays->bind_param("is", $daysRequested, $info['email']);
                $updateDays->execute();
                $updateDays->close();
            }

            // Mettre à jour le statut de la demande
            $stmt = $conn->prepare("UPDATE conge SET statut = ?, justificatif = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $justificatif, $demandeId);
            $stmt->execute();
            $stmt->close();
        } else {
            // Gérer le cas où les dates sont nulles
            echo "Les dates de la demande sont nulles.";
        }
    } else {
        // Gérer le cas où aucune demande correspondante n'est trouvée
        echo "Aucune demande de congé correspondante trouvée.";
    }
}

$result = $conn->query("SELECT * FROM conge WHERE statut = 'En attente'");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Demandes de Congés</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_demande_conges.css">
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
    <h1>Gestion des Demandes de Congés</h1>
    <div class="button-container">

<button onclick="showAddUserPanel()">Liste des Congés</button>
<button onclick="showUserListPanel()">Congé exceptionnels</button>

</div>
<div class="right-panel">
    <div class="container">
    <table>
        <tr>
            <th>Matriucle</th>
            <th>Employé</th>
            <th>Date de début</th>
            <th>Date de fin</th>
            <th>Justificatif</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
    <?php
    $getUser = $conn->prepare("SELECT nom, prenom FROM utilisateurs WHERE matricule = ?");
    $getUser->bind_param("i", $row['matricule']);
    $getUser->execute();
    $userInfo = $getUser->get_result()->fetch_assoc();
    ?>
    <tr>
    <td><?= htmlspecialchars($row['matricule']) ?></td>
        <td><?= htmlspecialchars($userInfo['nom'] . ' ' . $userInfo['prenom']) ?></td>
        <td><?= htmlspecialchars($row['dateDebut']) ?></td>
        <td><?= htmlspecialchars($row['dateFin']) ?></td>
        <td><?= htmlspecialchars($row['justificatif']) ?></td>
        <td>
            <div class="form-container">
                <form method="post">
                    <input type="hidden" name="demandeId" value="<?= $row['id'] ?>">
                    <input type="textarea" name="justificatif" placeholder="Justificatif (facultatif)">
                    <button type="submit" name="decision" value="accepter">Accepter</button>
                    <button type="submit" name="decision" value="refuser">Refuser</button>
                </form>
            </div>
        </td>
    </tr>
<?php endwhile; ?>

    </table>
    </div>
</div>
<div class="left-panel">
    <div class="container">
   
    <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <label for="dateDebut">Date de début</label>
    <input type="date" name="dateDebut" id="dateDebut" min="<?= $dateToday ?>" required>
    
    <label for="dateFin">Date de fin</label>
    <input type="date" name="dateFin" id="dateFin" min="<?= $dateToday ?>" required>
    
    <label for="justificatif">Justificatif (facultatif)</label>
    <textarea name="justificatif" id="justificatif"></textarea>
    
    <br>

    <br>

    <label for="congeExceptionnel">Congé exceptionnel ?</label>
    <input type="checkbox" id="congeExceptionnel" name="congeExceptionnel">
 
    
    <div id="congeExceptionnelInputs" style="display: none;">
        <label for="pieceJointe">Pièce jointe</label>
        <input type="file" id="actual-btn" hidden/>

<!-- our custom upload button -->
<label name="upload" for="actual-btn"> + Attacher piece jointe</label>

<!-- name of file chosen -->
<span id="file-chosen">Aucun fichier attache</span>

<br>
    <br>      
        <label for="remarque">Remarque</label>
        <textarea name="remarque" id="remarque"></textarea>
    </div>
    <br>


    
    <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <br>
    <br>
    <button type="submit"><i class="fas fa-paper-plane"></i> Envoyer demande de congé</button>

</form>

    </div>
</div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>

    <script>
        function showAddUserPanel() {
            document.querySelector('.left-panel').style.display = 'block';
            document.querySelector('.right-panel').style.display = 'none';
        }

        function showUserListPanel() {
            document.querySelector('.left-panel').style.display = 'none';
            document.querySelector('.right-panel').style.display = 'block';
        }

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
</body>
</html>
<?php
$conn->close();
?>
