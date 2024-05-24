<?php
session_start();
include_once 'db.php';

// Vérifier si l'utilisateur est connecté en tant que superviseur
if (!isset($_SESSION['email']) || $_SESSION['est_superieur_hierarchique'] != 1) {
    header("Location: index.php");
    exit;
}


function updateDecision($conn, $id, $decision, $dec_rh) {
    $dec_pdg_value = $decision == 'accepter' ? 1 : 0;
    $sql = "UPDATE conge SET dec_pdg = '$dec_pdg_value'";

    // Update statut based on dec_rh and dec_pdg values
    switch ($dec_rh) {
        case 0:
            $statut = 'Refuse';
            break;
        case 1:
            $statut = $decision == 'accepter' ? 'Accepte' : 'Refuse';
            break;
        case 2:
            $statut = $decision == 'accepter' ? 'En Attente' : 'Refuse';
            break;
    }

    $sql .= ", statut = '$statut'"; // Properly append statut update with a comma

    // Complete the SQL query with the WHERE clause
    $sql .= " WHERE id = '$id'";

    // Debug output
   // echo "SQL Query: $sql<br>";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
       // echo "Error updating record: " . $conn->error; // Display any errors
        return false;
    }
}




 
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $decision = isset($_POST['accepter']) ? 'accepter' : (isset($_POST['refuser']) ? 'refuser' : '');
    $dec_rh = $_POST['dec_rh'];
    
    // Debug output
   // echo "ID: $id, Decision: $decision, Dec_rh: $dec_rh<br>";
    
    // Update the decision and statut in the database
    if (!empty($decision)) {
        if (updateDecision($conn, $id, $decision, $dec_rh)) {
           // echo "Decision updated successfully";
        } else {
          //  echo "Error updating decision";
        }
    }
}

// Récupérer l'historique des congés depuis la base de données
$sql = "SELECT * FROM conge";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Congés</title>
    <!-- Include Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style> 
    body {
        font-family: Consolas, monospace;
    }
    .navbar {
        overflow: hidden;
        background-color: #148d04;
        display: flex;
        align-items: center;
        color: white;
        font-family: Consolas, monospace;
        margin: 10px;
        border-radius: 15px;
        padding: 5px;
    }

    /* Les liens de la barre de navigation */
    .navbar a {
        display: block;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
        color: inherit; /* Utilise la couleur de texte par défaut pour les liens */
        font-size: 16px;
        transition: background-color 0.3s, color 0.3s, border-radius 0.3s; /* Ajout de la transition pour le survol */
        border-radius: 10px; /* Bordure courbée par défaut */
    }

    /* Changement de couleur des liens au survol */
    .navbar a:hover {
            background-color: #fff;
            color: #148d04;
            border-radius: 10px; /* Bordure courbée au survol */
        }

        /* Style pour l'image dans le lien */
        .navbar .logo {
            margin-right: 10px;
            margin-left: 5px; /* Espace entre le logo et le premier lien */
        }

        .navbar .logo img {
            height: 30px; /* Taille légèrement agrandie du logo */
            vertical-align: middle; /* pour aligner l'image verticalement avec le texte s'il y en a */
        }

    .user-info {
        margin-left: auto; /* met le nom de l'utilisateur à droite */
        padding: 14px 16px;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .logout-button {
        margin-left: 10px;
        padding: 8px 12px;
        background-color: #ddd;
        border: none;
        color: #333;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s, border-radius 0.3s; /* Ajout de la transition pour le survol */
        border-radius: 10px; /* Bordure courbée par défaut */
    }

    .logout-button:hover {
        background-color: #aaa;
        color: white;
        border-radius: 10px; /* Bordure courbée au survol */
    }

    /* Style pour le formulaire de filtrage */
    .filter-form {
        margin-bottom: 20px;
        margin-left: 30px;
    }

    /* Style pour le champ de saisie */
    .filter-input {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
        margin-right: 10px;
    }

    /* Style pour le bouton de soumission */
    .filter-button {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        background-color: #148d04;
        color: white;
        cursor: pointer;
    }

    /* Style pour le bouton de réinitialisation */
    .reset-button {
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        background-color: #ccc;
        color: #333;
        cursor: pointer;
    }

    /* Style pour la table */
    table {
    width: calc(100% - 60px); /* 100% width minus 30px left and 30px right margins */
    margin-left: 30px;
    margin-right: 30px;
    border-collapse: separate; /* Separate border model for curved borders */
    border-spacing: 0; /* No spacing between table cells */
    border: 1px solid #ddd; /* Light border color */
    border-radius: 10px; /* Curved border radius */
}
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
    }
    h1 {
        text-align: center;
    }
    button[value="accepter"] {
    color: #fff;
    margin-right: 5px;
    background-color: #4CAF50;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

button[value="accepter"]:hover {
    background-color: #45a049;
}

button[value="refuser"] {
    color: #fff;
    margin-right: 5px;
    background-color: #dd3939;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

button[value="refuser"]:hover {
    background-color: #e41d1d;
}
    </style>
</head>

<body>

<div class="navbar">
    <a class="logo" href="menu_PDG.html"><img src="badrPFE.png" alt="Accueil"></a>
    <a href="apprecations.php">Appreciation</a>
    <a href="historique_sorties.php">Historique des sorties</a>
    <a href="historique_absences.php">Historique absences des Employer</a>
    <a href="historique_conges.php">Historique conges des Employers</a>
    <a href="historique_conges_excep.php">Historique conges exceptionnels des Employers</a>
    <div class="user-info">
        <span id="userWelcome"></span>
        <button class="logout-button" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
    </div>
</div>
<h1>Historique des Congés</h1>

<!-- Formulaire de filtrage -->
<!-- Formulaire de filtrage -->
<form class="filter-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
    <input class="filter-input" type="text" name="matricule" placeholder="Matricule...">
    <button class="filter-button" type="submit">Filtrer</button>
    <button class="reset-button" type="button" onclick="resetFilter()">Réinitialiser</button>

</form>


<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Date de Début</th>
            <th>Date de Fin</th>
            <th>Justificatif</th>
            <th>Décision de RH</th>
            <th>Statut</th>
            <th>Actions</th> <!-- New column for actions -->
           
        </tr>
    </thead>
    <tbody>
    <?php
    
    $filter_matricule = isset($_GET['matricule']) ? $_GET['matricule'] : '';


$sql = "SELECT u.matricule, u.nom, u.prenom, dc.dateDebut, dc.dateFin, dc.justificatif, dc.statut, u.email, dc.dec_rh, dc.dec_pdg, dc.id
FROM conge dc
INNER JOIN utilisateurs u ON dc.matricule = u.matricule
LEFT JOIN conge_excep ce ON dc.id = ce.id_cong
WHERE ce.id_cong IS NULL";

if (!empty($filter_matricule)) {
$sql .= " WHERE u.matricule LIKE '%$filter_matricule%'";
}

$sql .= " ORDER BY CASE 
    WHEN dc.statut = 'En attente' THEN 1 
    WHEN dc.statut = 'Accepté' THEN 2 
    ELSE 3 
END";



    $result = $conn->query($sql);


    while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row["matricule"] ?></td>
            <td><?= $row["nom"] ?></td>
            <td><?= $row["prenom"] ?></td>
            <td><?= $row["dateDebut"] ?></td>
            <td><?= $row["dateFin"] ?></td>
            <td><?= $row["justificatif"] ?></td>
            <td><?= $row["dec_rh"] == 1 ? 'Acceptée' : ($row["dec_rh"] == 2 ? 'En attente' : 'Refusée') ?></td> 
            <td><?= $row["statut"] ?></td>
            <td>
                
            <?php  if ($row["statut"] == "En Attente" && ($row["dec_rh"] == 2 || $row["dec_rh"] == 1) && $row["dec_pdg"] == 2): ?>
      <form method="post" action="historique_conges.php">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="dec_rh" value="<?= $row['dec_rh'] ?>">
        <button type="submit" name="accepter" value="accepter">Accepter</button>
        <button type="submit" name="refuser" value="refuser">Refuser</button>
    </form>
<?php endif; ?>

            </td>
        </tr>
    <?php endwhile; ?>

    </tbody>
</table>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
     function resetFilter() {
        document.querySelector('.filter-input').value = '';
        window.location.href = window.location.pathname;
    }
 
    function getCookie(cookieName) {
        const name = cookieName + "=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const cookieArray = decodedCookie.split(';');
        for (let i = 0; i < cookieArray.length; i++) {
            let cookie = cookieArray[i];
            while (cookie.charAt(0) == ' ') {
                cookie = cookie.substring(1);
            }
            if (cookie.indexOf(name) == 0) {
                return cookie.substring(name.length, cookie.length);
            }
        }
        return "";
    }


    const userName = getCookie('nom');

  
    if (userName) {
        document.getElementById('userWelcome').innerText = "Bienvenue, " + userName;
    }

    // Logout function
    function logout() {
       
        document.cookie = "nom=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
     
        window.location.href = "index.php"; 
    }
</script>
</body>
</html>
