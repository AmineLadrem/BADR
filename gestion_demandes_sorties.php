<?php
session_start();
include 'db.php'; // Include your database connection

if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: index.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    $demandeId = $_POST['demandeId'];
    $decision = $_POST['decision'];

    $demandeInfo = $conn->prepare("SELECT matricule, date_sortie, heure_sortie, dec_pdg FROM sortie WHERE id = ?");
    $demandeInfo->bind_param("i", $demandeId);
    $demandeInfo->execute();
    $result = $demandeInfo->get_result();

    if ($result->num_rows > 0) {
        $info = $result->fetch_assoc();      
        if ($decision === 'accepter') {
            $status = 'Accepté';
            $dec_rh=1;
        } else {
            $status = 'Refusé';
            $dec_rh=0;
        }

        $sql = "UPDATE sortie SET dec_rh = '$dec_rh'";

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
       
        echo "Aucune demande de congé correspondante trouvée.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Demandes de Sorties</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_demandes_sortie.css">
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
        <h1>Liste des Demandes de Sorties</h1>
        <form class="filter-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
    <input class="filter-input" type="text" name="matricule" placeholder="Matricule...">
    <button class="filter-button" type="submit">Filtrer</button>
    <button class="reset-button" type="button" onclick="resetFilter()">Réinitialiser</button>

</form>

        <?php 
     $filter_matricule = isset($_GET['matricule']) ? $_GET['matricule'] : '';

     
     $sql = "SELECT u.matricule, u.nom, u.prenom, s.date_sortie, s.heure_sortie, s.motif, s.statut, u.email, s.dec_rh, s.dec_pdg, s.id
     FROM sortie s
     INNER JOIN utilisateurs u ON s.matricule = u.matricule";
 
 if (!empty($filter_matricule)) {
     $sql .= " WHERE u.matricule LIKE '%$filter_matricule%' AND s.dec_rh = 2 AND (s.dec_pdg = 2 OR s.dec_pdg = 1)";
 }
 else{
    $sql .= " WHERE s.dec_rh = 2 AND (s.dec_pdg = 2 OR s.dec_pdg = 1)";
 }
 
 $sql .= " ORDER BY CASE 
     WHEN s.statut = 'En Attente' THEN 1 
     WHEN s.statut = 'Accepté' THEN 2 
     ELSE 3 
 END";
 
    

    $stmt = $conn->prepare($sql);
    
    $stmt->execute();
    $result = $stmt->get_result();
        if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Date Sortie</th>
                    <th>Heure Sortie</th>
                    <th>Motif</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= isset($row["matricule"]) ? $row["matricule"] : "Non défini" ?></td>
                        <td><?= isset($row["nom"]) ? $row["nom"] : "Non défini" ?></td>
                        <td><?= isset($row["prenom"]) ? $row["prenom"] : "Non défini" ?></td>
                        <td><?= isset($row["date_sortie"]) ? $row["date_sortie"] : "Non défini" ?></td>
                        <td><?= isset($row["heure_sortie"]) ? $row["heure_sortie"] : "Non défini" ?></td>
                        <td><?= isset($row["motif"]) ? $row["motif"] : "Non défini" ?></td>
                        <td>
                        <form method="post">
    <input type="hidden" name="formType" value="form1">
    <input type="hidden" name="demandeId" value="<?= $row['id'] ?>">
    <button type="submit" name="decision" value="accepter">Accepter</button>
    <button type="submit" name="decision" value="refuser">Refuser</button>
</form>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Aucune demande de sortie en attente.</p>
        <?php endif; ?>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
    <script>
         function resetFilter() {
        document.querySelector('.filter-input').value = '';
       
        window.location.href = window.location.pathname;
    }
    </script>
</body>
</html>
