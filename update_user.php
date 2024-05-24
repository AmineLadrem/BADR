<?php
session_start();

include 'db.php';


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
} else {
    die('ID invalide ou non fourni');
}


$sql = "SELECT * FROM utilisateurs WHERE matricule = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die('Aucun utilisateur trouvé avec cet ID');
}
$user = $result->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $diplomes = $_POST['diplomes'];
    $salaire = $_POST['salaire'];
    $statut = $_POST['statut'];
    $service = $_POST['service'];
    $poste = $_POST['poste'];

    $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, date_naissance = ?, telephone = ?, email = ?, diplomes = ?, salaire = ?, statut = ?, service = ?, poste = ? WHERE matricule = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $nom, $prenom, $date_naissance, $telephone, $email, $diplomes, $salaire, $statut, $service, $poste, $id);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de la mise à jour: " . $stmt->error;
    } else {
        header("Location: gestion_employe.php");
        exit;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_employes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
       

fieldset {
    border: 1px solid #ccc;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 5px;
}

legend {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}

label {
    display: inline-block;
    width: 120px;
    margin-bottom: 5px;
}

input[type="text"],
input[type="date"],
input[type="email"],
select {
    width: 250px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    margin-bottom: 10px;
}

button[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

    </style>
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
    <div style="margin: 20px 20px 20px 20px;">
    <h1>Modifier l'Utilisateur</h1>
    <form action="update_user.php?id=<?= htmlspecialchars($id) ?>" method="post">
        <fieldset>
            <legend>Informations Personnelles</legend>
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>"><br>
            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>"><br>
            <label for="date_naissance">Date de naissance:</label>
            <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance']) ?>"><br>
            <label for="telephone">Numéro de téléphone:</label>
            <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>"><br>
            <label for="email">Adresse mail:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br>
        </fieldset>
        <fieldset>
            <legend>Autres Informations</legend>
            <label for="diplomes">Diplômes:</label>
            <input type="text" id="diplomes" name="diplomes" value="<?= htmlspecialchars($user['diplomes']) ?>"><br>
            <label for="salaire">Salaire:</label>
            <input type="text" id="salaire" name="salaire" value="<?= isset($user['salaire']) ? htmlspecialchars($user['salaire']) : '' ?>"><br>
            <label for="statut">Statut:</label>
            <select id="statut" name="statut">
                <option value="actif" <?= ($user['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="en conge" <?= ($user['statut'] ?? '') === 'en conge' ? 'selected' : '' ?>>En congé</option>
                <option value="en formation" <?= ($user['statut'] ?? '') === 'en formation' ? 'selected' : '' ?>>En formation</option>
                <option value="retraite" <?= ($user['statut'] ?? '') === 'retraite' ? 'selected' : '' ?>>Retraité</option>
                <option value="suspendu" <?= ($user['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                <option value="demissionnaire" <?= ($user['statut'] ?? '') === 'demissionnaire' ? 'selected' : '' ?>>Démissionnaire</option>
            </select><br>
            <label for="service">Service:</label>
            <input type="text" id="service" name="service" value="<?= isset($user['service']) ? htmlspecialchars($user['service']) : '' ?>"><br>
            <label for="poste">Poste:</label>
            <input type="text" id="poste" name="poste" value="<?= isset($user['poste']) ? htmlspecialchars($user['poste']) : '' ?>"><br>
        </fieldset>
        <button type="submit" name="update">
    <i class="fas fa-sync-alt"></i> Mettre à jour
</button>

    </form>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>
</body>
</html>
