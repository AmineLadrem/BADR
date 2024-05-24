<?php
session_start();

include 'db.php';

function insertDiplomaData($conn, $id, $type_diplome, $domaine, $lieu_obtention, $date_obtention) {

    if (empty($type_diplome) || empty($domaine) || empty($lieu_obtention) || empty($date_obtention)) {
        return "Aucun diplôme à insérer";
    }

    $count = count($type_diplome);
    if (count($domaine) !== $count || count($lieu_obtention) !== $count || count($date_obtention) !== $count) {
        return "Erreur: Les tableaux doivent avoir la même longueur";
    }
    

    $sql = "INSERT INTO diplome (matricule, type_diplome, domaine, lieu_obtention, date_obtention) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);


    for ($i = 0; $i < $count; $i++) {
        $stmt->bind_param("sssss", $id, $type_diplome[$i], $domaine[$i], $lieu_obtention[$i], $date_obtention[$i]);
        $stmt->execute();

        if ($stmt->error) {
            return "Erreur lors de l'insertion du diplôme: " . $stmt->error;
        }
    }

    return "Diplômes insérés avec succès";
}



function insertExperienceData($conn,  $id, $entreprise, $poste, $date_debut, $date_fin,$motif) {
    if (empty($entreprise) || empty($poste) || empty($date_debut) || empty($date_fin) || empty($motif)) {
        return "Aucune expérience à insérer";
    }

    $count = count($entreprise);
    if (count($poste) !== $count || count($date_debut) !== $count || count($date_fin) !== $count) {
        return "Erreur: Les tableaux doivent avoir la même longueur";
    }
    

    $sql = "INSERT INTO experience (matricule, entreprise, poste, date_debut, date_fin, motif) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);


    for ($i = 0; $i < $count; $i++) {
        $stmt->bind_param("ssssss",  $id, $entreprise[$i], $poste[$i], $date_debut[$i], $date_fin[$i], $motif[$i]);
        $stmt->execute();
    
        if ($stmt->error) {
            return "Erreur lors de l'insertion de l'expérience: " . $stmt->error;
        }
    }
    

    return "Expériences insérées avec succès";
}



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


    $type_diplome = $_POST['type_diplome'];
    $domaine = $_POST['domaine'];
    $lieu_obtention = $_POST['lieu_obtention'];
    $date_obtention = $_POST['date_obtention'];
    $date_debut_exp = $_POST['date_debut'];
    $date_fin_exp = $_POST['date_fin'];
    $poste_experience = $_POST['poste_experience'];
    $entreprise = $_POST['entreprise'];
    $motif = $_POST['motif'];


    if (!empty($type_diplome)) {
        insertDiplomaData($conn, $id, $type_diplome, $domaine, $lieu_obtention, $date_obtention);
    }


    if (!empty($entreprise)) {
        insertExperienceData($conn, $id, $entreprise, $poste_experience, $date_debut_exp, $date_fin_exp,$motif);
    }

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

        <>
            <legend>Diplome</legend>
                <div class="form-wrapper diplome-info">
                    <div class="flex-container">
                        <label for="type_diplome">Type de diplôme:</label>
                        <select id="type_diplome" name="type_diplome" >
                            <option value="Licence">Licence</option>
                            <option value="Master">Master</option>
                            <option value="Doctorat">Doctorat</option>
                        </select>
                    </div>
                    <div class="flex-container">
                        <label for="domaine">Domaine:</label>
                        <input type="text" id="domaine" name="domaine" >
                    </div>
                    <div class="flex-container">
                        <label for="lieu_obtention">Lieu d'obtention:</label>
                        <input type="text" id="lieu_obtention" name="lieu_obtention" ><br>
                    </div>
                    <div class="flex-container">
                        <label for="date_obtention">Date d'obtention:</label>
                        <input type="date" id="date_obtention" name="date_obtention" ><br>
                    </div>
                    <br>
                    <br>

                    <table>
                        <thead>
                            <tr>
                                <th>Type de diplôme</th>
                                <th>Domaine</th>
                                <th>Lieu d'obtention</th>
                                <th>Date d'obtention</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button type="button" onclick="addDiplomeRow()"> + Ajouter Diplôme</button>
                </div>
                

                <fieldset>
            <legend>Experience</legend>

             
            <div class="form-wrapper experience-info">
                    <div class="flex-container">
                        <label for="date_debut">Date de début:</label>
                        <input type="date" id="date_debut" name="date_debut" ><br>
                    </div>
                    <div class="flex-container">
                        <label for="date_fin">Date de fin:</label>
                        <input type="date" id="date_fin" name="date_fin" ><br>
                    </div>
                    <div class="flex-container">
                        <label for="poste_experience">Poste:</label>
                        <input type="text" id="poste_experience" name="poste_experience" ><br>
                    </div>
                    <div class="flex-container">
                        <label for="entreprise">Entreprise:</label>
                        <input type="text" id="entreprise" name="entreprise" ><br>
                    </div>
                    <div class="flex-container">
                        <label for="motif">Motif:</label>
                        <select id="motif" name="motif" required>
                            <option value="demission">Démission</option>
                            <option value="retraite">Retraite</option>
                            <option value="Fin de contrat">Fin de contrat</option>
                        </select><br>
                    </div>
                    <br>
                    <br>
                    <table>
                        <thead>
                            <tr>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Poste</th>
                                <th>Entreprise</th>
                                <th>Motif</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button type="button" onclick="addExperienceRow()"> + Ajouter Expérience</button>


                </div>
                    </fieldset>
   
        <button type="submit" name="update">
    <i class="fas fa-sync-alt"></i> Mettre à jour
</button>

    </form>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>

    <script>
         function addDiplomeRow() {
            const typeDiplome = document.getElementById('type_diplome').value;
            const domaine = document.getElementById('domaine').value;
            const lieuObtention = document.getElementById('lieu_obtention').value;
            const dateObtention = document.getElementById('date_obtention').value;

            const table = document.querySelector('.diplome-info table tbody');
            const newRow = table.insertRow();
            newRow.innerHTML = `
        <td><input type="text" name="type_diplome[]" value="${typeDiplome}" readonly required></td>
        <td><input type="text" name="domaine[]" value="${domaine}" readonly required></td>
        <td><input type="text" name="lieu_obtention[]" value="${lieuObtention}" readonly required></td>
        <td><input type="date" name="date_obtention[]" value="${dateObtention}" readonly required></td>
        <td><a onclick="removeDiplomeRow(this)"><i class="fas fa-trash-alt"></i></a></td>
    `;

 
            document.getElementById('type_diplome').value = '';
            document.getElementById('domaine').value = '';
            document.getElementById('lieu_obtention').value = '';
            document.getElementById('date_obtention').value = '';
        }

        function addExperienceRow() {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            const posteExperience = document.getElementById('poste_experience').value;
            const entreprise = document.getElementById('entreprise').value;
            const motif = document.getElementById('motif').value;

            const table = document.querySelector('.experience-info table tbody');
            const newRow = table.insertRow();
            newRow.innerHTML = `
        <td><input type="date" name="date_debut[]" value="${dateDebut}" readonly required></td>
        <td><input type="date" name="date_fin[]" value="${dateFin}" readonly required></td>
        <td><input type="text" name="poste_experience[]" value="${posteExperience}" readonly required></td>
        <td><input type="text" name="entreprise[]" value="${entreprise}" readonly required></td>
        <td><input type="text" name="motif[]" value="${motif}" readonly required></td>
        <td><a onclick="removeExperienceRow(this)"><i class="fas fa-trash-alt"></i></a></td>
    `;

            
            document.getElementById('date_debut').value = '';
            document.getElementById('date_fin').value = '';
            document.getElementById('poste_experience').value = '';
            document.getElementById('entreprise').value = '';
            document.getElementById('motif').value = 'demission'; // Reset dropdown to default
        }




        function removeDiplomeRow(button) {
             
            if (confirm("Êtes-vous sûr de vouloir supprimer ce diplôme ?")) {
                const row = button.closest('tr');
                row.parentNode.removeChild(row);
            }
        }

        function removeExperienceRow(button) {
            
            if (confirm("Êtes-vous sûr de vouloir supprimer cette expérience ?")) {
                const row = button.closest('tr');
                row.parentNode.removeChild(row);
            }
        }
    </script>
</body>
</html>
