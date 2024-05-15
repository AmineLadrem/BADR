<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_utilisateurs";

$dateToday = date("Y-m-d");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function emailExists($conn, $email) {
    $sql = "SELECT COUNT(*) AS count FROM utilisateurs WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

function phoneExists($conn, $telephone) {
    $sql = "SELECT COUNT(*) AS count FROM utilisateurs WHERE telephone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $telephone);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

function generateMatricule($date_debut, $date_naissance, $telephone) {
    // Format the dates (assuming they're in YYYY-MM-DD format)
    $formatted_date_debut = date("Ymd", strtotime($date_debut));
    $formatted_date_naissance = date("Ymd", strtotime($date_naissance));

    // Concatenate formatted dates and phone number
    $matricule = $formatted_date_debut . $formatted_date_naissance . $telephone;

    // Optionally, you can hash the matricule to ensure uniqueness
    // $matricule = md5($matricule);

    return $matricule;
}

function insertUserData($conn, $nom, $prenom, $date_naissance, $telephone, $email, $matricule, $joursCongesRestants, $is_supervisor, $salaire, $statut, $service, $poste, $est_superieur_hierarchique) {
    $emailExist = emailExists($conn, $email);
    $phoneExist = phoneExists($conn, $telephone);

    if ($emailExist || $phoneExist) {
        $error = '';
        if ($emailExist) {
            $error .= "L'email est déjà utilisé. ";
        }
        if ($phoneExist) {
            $error .= "Le numéro de téléphone est déjà utilisé.";
        }
        return $error;
    }
    
    $sql = "INSERT INTO utilisateurs (nom, prenom, date_naissance, telephone, email, matricule, joursCongesRestants, is_supervisor, salaire, statut, service, poste, est_superieur_hierarchique) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssiiissss", $nom, $prenom, $date_naissance, $telephone, $email, $matricule, $joursCongesRestants, $is_supervisor, $salaire, $statut, $service, $poste, $est_superieur_hierarchique);
    $stmt->execute();
    return $stmt->error;
}

function insertDiplomaData($conn, $email, $type_diplome, $domaine, $lieu_obtention, $date_obtention) {

    if (empty($type_diplome) || empty($domaine) || empty($lieu_obtention) || empty($date_obtention)) {
        return "Aucun diplôme à insérer";
    }

    // Check if all arrays have the same length
    $count = count($type_diplome);
    if (count($domaine) !== $count || count($lieu_obtention) !== $count || count($date_obtention) !== $count) {
        return "Erreur: Les tableaux doivent avoir la même longueur";
    }
    
    // Prepare the SQL statement for inserting diplomas
    $sql = "INSERT INTO diplome (matricule, type_diplome, domaine, lieu_obtention, date_obtention) VALUES ((SELECT matricule FROM utilisateurs WHERE email = ? LIMIT 1), ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters and execute the statement for each diploma record
    for ($i = 0; $i < $count; $i++) {
        $stmt->bind_param("sssss", $email, $type_diplome[$i], $domaine[$i], $lieu_obtention[$i], $date_obtention[$i]);
        $stmt->execute();

        if ($stmt->error) {
            return "Erreur lors de l'insertion du diplôme: " . $stmt->error;
        }
    }

    return "Diplômes insérés avec succès";
}

// Insert experience data

function insertExperienceData($conn, $email, $entreprise, $poste, $date_debut, $date_fin,$motif) {
    if (empty($entreprise) || empty($poste) || empty($date_debut) || empty($date_fin) || empty($motif)) {
        return "Aucune expérience à insérer";
    }
    // Check if all arrays have the same length
    $count = count($entreprise);
    if (count($poste) !== $count || count($date_debut) !== $count || count($date_fin) !== $count) {
        return "Erreur: Les tableaux doivent avoir la même longueur";
    }
    
    // Prepare the SQL statement for inserting experiences
    $sql = "INSERT INTO experience (matricule, entreprise, poste, date_debut, date_fin, motif) VALUES ((SELECT matricule FROM utilisateurs WHERE email = ? LIMIT 1), ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // Bind parameters and execute the statement for each experience record
    for ($i = 0; $i < $count; $i++) {
        $stmt->bind_param("ssssss", $email, $entreprise[$i], $poste[$i], $date_debut[$i], $date_fin[$i], $motif[$i]);
        $stmt->execute();
    
        if ($stmt->error) {
            return "Erreur lors de l'insertion de l'expérience: " . $stmt->error;
        }
    }
    

    return "Expériences insérées avec succès";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $matricule = generateMatricule($dateToday, $date_naissance, $telephone);
    $joursCongesRestants = 30;
    $is_supervisor = 0;
    $salaire = $_POST['salaire'];
    $statut = $_POST['statut'];
    $service = $_POST['service'];
    $poste = $_POST['poste'];
    $est_superieur_hierarchique = 0;
    $type_diplome = $_POST['type_diplome'];
    $domaine = $_POST['domaine'];
    $lieu_obtention = $_POST['lieu_obtention'];
    $date_obtention = $_POST['date_obtention'];
    $date_debut_exp = $_POST['date_debut'];
    $date_fin_exp = $_POST['date_fin'];
    $poste_experience = $_POST['poste_experience'];
    $entreprise = $_POST['entreprise'];
    $motif = $_POST['motif'];

    // Insert user data


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert user data
    insertUserData($conn, $nom, $prenom, $date_naissance, $telephone, $email, $matricule, $joursCongesRestants, $is_supervisor, $salaire, $statut, $service, $poste, $est_superieur_hierarchique);



    if (!empty($type_diplome)) {
        insertDiplomaData($conn, $email, $type_diplome, $domaine, $lieu_obtention, $date_obtention);
    }

    // Insert experience data if the array is not empty
    if (!empty($entreprise)) {
        insertExperienceData($conn, $email, $entreprise, $poste_experience, $date_debut_exp, $date_fin_exp,$motif);
    }



    $currentDate = date('Y-m-d H:i:s');

    // Insert into compte_utilisateur table
    $sql = "INSERT INTO compte_utilisateur (mdp, matricule, date_creation) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $matricule, $matricule, $currentDate);
    $stmt->execute();
    
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
  
}

}

$sql = "SELECT id, nom, prenom, date_naissance, telephone, email, salaire, statut, service, poste FROM utilisateurs";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="styles/gestion_employes.css">
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
    <h1>Gestion des Utilisateurs</h1>
    <div class="button-container">

        <button onclick="showAddUserPanel()">Liste des utilisateurs</button>
        <button onclick="showUserListPanel()">Ajouter un utilisateur</button>

    </div>
    <div class="container">
        <<div class="right-panel">
            <h1>Les informations personnelles</h1>
            <form class="app_form" method="post">
                <!-- Fields for utilisateur table -->
                <div class="form-wrapper personal-info">
                    <div class="flex-container">
                        <label for="nom">Nom:</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="flex-container">
                        <label for="prenom">Prénom:</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                    <div class="flex-container">
                        <label for="date_naissance">Date de naissance:</label>
                        <input type="date" id="date_naissance" name="date_naissance" required>
                    </div>

                    <div class="flex-container">
                        <label for="email">Statut:</label>
                        <input type="text" id="statut" name="statut" required>
                    </div>

                    <div class="flex-container">
                        <label for="telephone">Téléphone:</label>
                        <input type="text" id="telephone" name="telephone" required>
                    </div>
                    <div class="flex-container">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="flex-container">
                        <label for="salaire">Salaire:</label>
                        <input type="number" id="salaire" name="salaire" min="0" required>
                        <label for="salaire">/ D.A</label>
                    </div>

                    <div class="flex-container">
                        <label for="salaire">Service:</label>
                        <input type="text" id="service" name="service" required>
                    </div>


                    <div class="flex-container">
                        <label for="salaire">Poste:</label>
                        <input type="text" id="poste" name="poste"  required>
             
                    </div>



                </div>

                <!-- Fields for diplome table -->
                <h1>Diplôme</h1>
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


                <!-- Fields for experiences table -->
                <h1>Experience</h1>
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
                <button name="add-user" type="submit" style="margin-bottom: 15px;"><i class="fas fa-plus"></i> Ajouter Utilisateur</button>

                <!-- Buttons -->

                <!--   <button type="button" onclick="window.print()"><i class="fas fa-print"></i></button>-->
                <?php if (isset($error)) : ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if (isset($success)) : ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
            </form>
    </div>

    <div class="left-panel">
        <h1>Liste des utilisateurs</h1>

        <!-- Formulaire de filtrage -->
        <form class="filter-form" action="" method="GET">
            <input class="filter-input" type="text" name="matricule" placeholder="Matricule...">
            <button class="filter-button" type="submit">Filtrer</button>
            <button name="reset-button" class="reset-button" type="submit">Réinitialiser</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de naissance</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Salaire</th>
                    <th>Statut</th>
                    <th>Service</th>
                    <th>Poste</th>
                    <th>Diplome</th>
                    <th>Experience</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer les valeurs filtrées si elles existent
                $filter_matricule = isset($_GET['matricule']) ? $_GET['matricule'] : '';

                // Construction de la requête SQL en fonction du filtrage par matricule
                $sql = "SELECT matricule, nom, prenom, date_naissance, telephone, email, salaire, statut, service, poste, est_superieur_hierarchique FROM utilisateurs";
                if (!empty($filter_matricule)) {
                    $sql .= " WHERE matricule LIKE '%$filter_matricule%'";
                }

                // Exécution de la requête SQL
                $result = $conn->query($sql);

                // Affichage des résultats
                while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $row["matricule"] ?></td>
                        <td><?= $row["nom"] ?></td>
                        <td><?= $row["prenom"] ?></td>
                        <td><?= $row["date_naissance"] ?></td>
                        <td><?= $row["telephone"] ?></td>
                        <td><?= $row["email"] ?></td>
                        <td><?= $row["salaire"] ?></td>
                        <td><?= $row["statut"] ?></td>
                        <td><?= $row["service"] ?></td>
                        <td><a><i class="fas fa-plus add" style="color: #148d04;     padding-left: 14px;"></i></a></td>
                        <td><a><i class="fas fa-plus add" style="color: #148d04;     padding-left: 14px;"></i></a></td>
                        <td><?= $row["poste"] ?></td>
                        <td>
                        <a class="update-user" href="update_user.php?id=<?= $row['matricule'] ?>"><i class="fas fa-edit" style="color: orange;"></i></a>
    <a class="delete-user"><i class="fas fa-trash-alt" style="color: red;"></i></a>
</td>


                    </tr>
                <?php endwhile; ?>

            </tbody>
        </table>
    </div>

    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="get_name.js"></script>

    <script>

function editUser(matricule) {
    // Retrieve user information via AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_user_info.php?matricule=' + encodeURIComponent(matricule), true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const userData = JSON.parse(xhr.responseText);

            // Populate form fields with user information
            document.getElementById('nom').value = userData.nom;
            document.getElementById('prenom').value = userData.prenom;
            document.getElementById('date_naissance').value = userData.date_naissance;
            document.getElementById('telephone').value = userData.telephone;
            document.getElementById('email').value = userData.email;
            document.getElementById('salaire').value = userData.salaire;
            document.getElementById('statut').value = userData.statut;
            document.getElementById('service').value = userData.service;
            document.getElementById('poste').value = userData.poste;

            // Hide the add user panel and show the edit user panel
            showUserListPanel();
            showAddUserPanel();
        } else {
            alert('Error retrieving user information');
        }
    };
    xhr.send();
}


document.addEventListener('DOMContentLoaded', function () {
        // Add event listener to all delete user icons
        const deleteIcons = document.querySelectorAll('.delete-user');
        deleteIcons.forEach(icon => {
            icon.addEventListener('click', function () {
                // Get the corresponding matricule from the table row
                const row = this.closest('tr');
                const matricule = row.querySelector('td:first-child').textContent;

                // Confirm deletion
                if (confirm("Are you sure you want to delete this user?")) {
                    // Send AJAX request to delete user
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_user.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            // Remove row from table upon successful deletion
                            row.remove();
                        } else {
                            alert('Error deleting user');
                        }
                    };
                    xhr.send('matricule=' + encodeURIComponent(matricule));
                }
            });
        });
    });

        function showAddUserPanel() {
            document.querySelector('.left-panel').style.display = 'block';
            document.querySelector('.right-panel').style.display = 'none';
        }

        function showUserListPanel() {
            document.querySelector('.left-panel').style.display = 'none';
            document.querySelector('.right-panel').style.display = 'block';
        }

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

            // Clear input fields
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

            // Clear input fields
            document.getElementById('date_debut').value = '';
            document.getElementById('date_fin').value = '';
            document.getElementById('poste_experience').value = '';
            document.getElementById('entreprise').value = '';
            document.getElementById('motif').value = 'demission'; // Reset dropdown to default
        }




        function removeDiplomeRow(button) {
            // Demander confirmation avant de supprimer la ligne
            if (confirm("Êtes-vous sûr de vouloir supprimer ce diplôme ?")) {
                const row = button.closest('tr');
                row.parentNode.removeChild(row);
            }
        }

        function removeExperienceRow(button) {
            // Demander confirmation avant de supprimer la ligne
            if (confirm("Êtes-vous sûr de vouloir supprimer cette expérience ?")) {
                const row = button.closest('tr');
                row.parentNode.removeChild(row);
            }
        }
    </script>

</body>

</html>