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
    $sql = "UPDATE sortie SET dec_pdg = '$dec_pdg_value'";

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
$sql = "SELECT * FROM sortie ORDER BY CASE WHEN statut = 'En attente' THEN 1 WHEN statut = 'Accepté' THEN 2 ELSE 3 END";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des sorties</title>
    <!-- Include Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles/historique_sorties.css">
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
<h1>Historique des Sorties</h1>

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
            <th>Date de sortie</th>
            <th>Heure de sortie</th>
            <th>Motif</th>
            <th>Décision de RH</th>
            <th>Statut</th>
            <th>Actions</th> <!-- New column for actions -->
           
        </tr>
    </thead>
    <tbody>
    <?php
    // Récupérer les valeurs filtrées si elles existent
    $filter_matricule = isset($_GET['matricule']) ? $_GET['matricule'] : '';

    // Construction de la requête SQL en fonction du filtrage par matricule
    // Construction de la requête SQL en fonction du filtrage par matricule
$sql = "SELECT u.matricule, u.nom, u.prenom, s.date_sortie, s.heure_sortie, s.motif, s.statut, s.dec_pdg, s.dec_rh , s.id
FROM sortie s
INNER JOIN utilisateurs u ON s.matricule = u.matricule";

if (!empty($filter_matricule)) {
$sql .= " WHERE u.matricule LIKE '%$filter_matricule%'";
}

$sql .= " ORDER BY CASE 
    WHEN s.statut = 'En attente' THEN 1 
    WHEN s.statut = 'Accepté' THEN 2 
    ELSE 3 
END";


    // Exécution de la requête SQL
    $result = $conn->query($sql);

    // Affichage des résultats
    while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row["matricule"] ?></td>
            <td><?= $row["nom"] ?></td>
            <td><?= $row["prenom"] ?></td>
            <td><?= $row["date_sortie"] ?></td>
            <td><?= $row["heure_sortie"] ?></td>
            <td><?= $row["motif"] ?></td>
            <td><?= $row["dec_rh"] == 1 ? 'Acceptée' : ($row["dec_rh"] == 2 ? 'En attente' : 'Refusée') ?></td> <!-- Display decision de RH -->
            <td><?= $row["statut"] ?></td>
            <td>
            <?php if ($row["statut"] == "En attente" && ($row["dec_rh"] == 2 || $row["dec_rh"] == 1) && $row["dec_pdg"] == 2): ?>
    <!-- Display buttons if both RH and PDG have made decisions -->
      <form method="post" action="historique_sorties.php">
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
        // Remove the matricule parameter from the URL and resubmit the form
        window.location.href = window.location.pathname;
    }
    // Function to get the value of a cookie by its name
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

    // Get the value of the 'nom' cookie
    const userName = getCookie('nom');

    // Display the user's name if it exists
    if (userName) {
        document.getElementById('userWelcome').innerText = "Bienvenue, " + userName;
    }

    // Logout function
    function logout() {
        // Clear the 'nom' cookie
        document.cookie = "nom=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        // Redirect to the logout page
        window.location.href = "index.php"; // Replace "logout.php" with your logout page URL
    }
</script>
</body>
</html>
