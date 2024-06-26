<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: index.php');
    exit;
}
$dateToday = date("Y-m-d");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['formType']) && $_POST['formType'] === 'form1') {
    $decision = $_POST['decision'];
    $justificatif = $_POST['justificatif'] ?? '';
    $demandeId = $_POST['demandeId'];

    $demandeInfo = $conn->prepare("SELECT matricule, dateDebut, dateFin , justificatif,dec_pdg FROM conge WHERE id = ?");
    $demandeInfo->bind_param("i", $demandeId);
    $demandeInfo->execute();
    $result = $demandeInfo->get_result();

    if ($result->num_rows > 0) {
        $info = $result->fetch_assoc();

        $getUser = $conn->prepare("SELECT nom, prenom FROM utilisateurs WHERE matricule = ?");
$getUser->bind_param("i", $info['matricule']);

    $getUser->execute();
    $result = $getUser->get_result();
    $infoUser = $result->fetch_assoc();
    $joursRestants=$info['matricule'];

         
        if ($info['dateDebut'] !== null && $info['dateFin'] !== null) {
            $debut = new DateTime($info['dateDebut']);
            $fin = new DateTime($info['dateFin']);
            $interval = $debut->diff($fin);
            $daysRequested = $interval->days + 1;

            if ($decision === 'accepter') {
                $status = 'Accepté';
                $dec_rh=1;
            } else {
                $status = 'Refusé';
                $dec_rh=0;

     
                $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = joursCongesRestants + ? WHERE matricule = ?");
                $updateDays->bind_param("is", $daysRequested, $ininfoUserfo['matricule']);
                $updateDays->execute();
                $updateDays->close();
            }

            $sql = "UPDATE conge SET dec_rh = '$dec_rh'";

   
    switch ($info['dec_pdg']) {
        case 0:
            $statut = 'Refusé';
            break;
        case 1:
            $statut = $decision == 'accepter' ? 'Accepté' : 'Refusé';
            break;
        case 2:
            $statut = $decision == 'accepter' ? 'En Attente' : 'Refusé';
            break;
    }

    $sql .= ", statut = '$statut'"; 

    
    $sql .= " WHERE id = '$demandeId'";




            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->close();
        } else {
         
            echo "Les dates de la demande sont nulles.";
        }
    } else {
       
        echo "Aucune demande de congé correspondante trouvée.";
    }
}
elseif (isset($_POST['formType']) && $_POST['formType'] === 'form2') {
 
    $matricule_user = $_POST['matricule'];
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'] ?? '';
    $remarque = $_POST['remarque'] ?? '';
    
 
    $congeExceptionnel = isset($_POST['congeExceptionnel']) ? 1 : 0;

    $debut = new DateTime($dateDebut);
    $fin = new DateTime($dateFin);
    $interval = $debut->diff($fin);
    $daysRequested = $interval->days + 1;

    $getUser = $conn->prepare("SELECT nom, prenom, joursCongesRestants FROM utilisateurs WHERE matricule = ?");
    $getUser->bind_param("i", $matricule_user);
    $getUser->execute();
    
    if (!$getUser) {
        die("Error executing query: " . $conn->error);
    }
    
    $result = $getUser->get_result();
    
    if (!$result) {
        die("Error getting result: " . $getUser->error);
    }
    
    if ($result->num_rows > 0) {
        $infoUser = $result->fetch_assoc();
        $joursRestants = $infoUser['joursCongesRestants'];
    } else {
        die("No user found with the provided matricule.");
    }
    

    if ($daysRequested <= $joursRestants) {
        $joursRestants -= $daysRequested;
        $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = ? WHERE matricule = ?");
        $updateDays->bind_param("is", $joursRestants, $matricule_user);
        $updateDays->execute();
        $updateDays->close();

       
            $insertConge = $conn->prepare("INSERT INTO conge (matricule, dateDebut, dateFin, justificatif, dec_rh, dec_pdg, statut) VALUES (?, ?, ?, ?, 1, 1, 'Accepté')");
        $insertConge->bind_param("isss", $matricule_user, $dateDebut, $dateFin, $justificatif);

        if ($insertConge->execute()) {
           
           
                $excep = $remarque;
                
                $insertCongeExcep = $conn->prepare("INSERT INTO conge_excep (matricule, excep, id_cong) VALUES (?, ?, LAST_INSERT_ID())");
                $insertCongeExcep->bind_param("is", $matricule_user, $excep);
                $insertCongeExcep->execute();
                $insertCongeExcep->close();
            

            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo "Erreur lors de l'envoi de la demande de congé.";
        }

        $insertConge->close();
    } else {
        $error = "Vous ne pouvez pas demander $daysRequested jours. Il vous reste seulement $joursRestants jours de congés.";
    }
}

}


$result = $conn->query("SELECT * FROM conge ORDER BY CASE 
WHEN statut = 'En Attente' THEN 1 
WHEN statut = 'Accepté' THEN 2 
ELSE 3 
END");
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
<div class="left-panel">
    <div class="container">
    <table>
        <tr>
            <th>Matriucle</th>
            <th>Employé</th>
            <th>Date de début</th>
            <th>Date de fin</th>
            <th>Justificatif</th>
            <th>Decision PDG</th>
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
        <td><?= $row["dec_pdg"] == 1 ? 'Acceptée' : ($row["dec_pdg"] == 2 ? 'En Attente' : 'Refusée') ?></td>
        <td>
            <div class="form-container">
                <form method="post">
                <input type="hidden" name="formType" value="form1">
                    <input type="hidden" name="demandeId" value="<?= $row['id'] ?>">
 
    <?php
            if ($row['dec_rh'] == 2 && ($row['dec_pdg'] == 2 || $row['dec_pdg'] == 1)) {
                echo '      <input type="textarea" name="justificatif" placeholder="Justificatif (facultatif)">
                <br>
<br>';
                echo '<button type="submit" name="decision" value="accepter">Accepter</button>';
                echo '<button type="submit" name="decision" value="refuser">Refuser</button>';
            }
            ?>
                </form>
            </div>
        </td>
    </tr>
<?php endwhile; ?>

    </table>
    </div>
</div>
<div class="right-panel">
    <div class="container">
   
    <form method="post" action="">
 
    <input type="hidden" name="formType" value="form2">

    <label for="justificatif">Matricule</label>
    <input type="number" name="matricule" id="matricule"></input>

    <label for="dateDebut">Date de début</label>
    <input type="date" name="dateDebut" id="dateDebut" min="<?= $dateToday ?>" required>
    
    <label for="dateFin">Date de fin</label>
    <input type="date" name="dateFin" id="dateFin" min="<?= $dateToday ?>" required>
    
    <label for="justificatif">Justificatif (facultatif)</label>
    <textarea name="justificatif" id="justificatif"></textarea>

    
    <br>

    <br>
 
    
    <div id="congeExceptionnelInputs">
        <label for="pieceJointe">Pièce jointe</label>
        <input type="file" id="actual-btn" hidden/>


<label name="upload" for="actual-btn"> + Attacher piece jointe</label>


<span id="file-chosen">Aucun fichier attache</span>

<br>
    <br>      
        <label for="remarque">Exception</label>
        <select id="remarque" name="remarque" required>
        <option value="Maladie">Maladie</option>
        <option value="Maternité">Maternité</option>
    </select>
    </div>
    <br>


    
    <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <br>
    <br>
    <button name="send_req" type="submit"><i class="fas fa-paper-plane"></i> Envoyer demande de congé</button>

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
