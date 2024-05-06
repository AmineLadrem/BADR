<?php
session_start();
include_once 'db.php';

// Vérifier si l'utilisateur est connecté en tant que PDG
if (!isset($_SESSION['email']) || $_SESSION['est_superieur_hierarchique'] != 1) {
    header("Location: index.php");
    exit;
}


// Traitement du formulaire d'ajout d'appréciation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST['matricule'];
    $note_assiduite = $_POST['note_assiduite'];
    $note_travail = $_POST['note_travail'];
    $note_absences = $_POST['note_absences'];
    $point_fort = $_POST['point_fort'];
    $point_faibles = $_POST['point_faibles'];

    // Insérer les données dans la table des appréciations
    $sql_insert = "INSERT INTO appreciations (matricule, note_assiduite, note_travail,note_absences, point_fort, point_faibles) VALUES (?,?,?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sidiss", $matricule, $note_assiduite, $note_travail, $note_absences, $point_fort, $point_faibles);
    $stmt_insert->execute();

    if ($stmt_insert->error) {
        $error = "Erreur lors de l'ajout de l'appréciation: " . $stmt_insert->error;
    } else {
        $success = "Appréciation ajoutée avec succès.";
    }

    $stmt_insert->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Appréciations</title>
    <link rel="stylesheet" href="style4.css"> <!-- Assurez-vous d'avoir un fichier style.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        body {
            font-family: Consolas, monospace;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: space-between;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .left-panel,
        .right-panel {
            width: 48%;
            /* Each panel takes up 48% of the container width */
        }

        .left-panel {
            margin-right: 2%;
            /* Add a small gap between the panels */
        }

        .right-panel {
            margin-left: 2%;
            /* Add a small gap between the panels */
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .app_form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"] {
            width: calc(100% - 12px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button[type="submit"] {
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }


        button[type="submit"] i {
            margin-right: 5px;
        }

     

        button[type="button"] {
            margin-left: 210px;
            margin-top: 10px;
            background-color: #1E27B2;
            width: 50px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button[type="button"]:hover {
            background-color: #131970;
        }

        button[name="add-app"]:hover {
            background-color: #0F6403;
        }

        .error {
            color: #ff0000;
            margin-top: 10px;
        }

        .success {
            color: #148d04;
            margin-top: 10px;
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

        .navbar a {
            display: block;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            color: inherit;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s, border-radius 0.3s;
            border-radius: 10px;
        }

        .navbar a:hover {
            background-color: #fff;
            color: #148d04;
            border-radius: 10px;
            /* Bordure courbée au survol */
        }

        /* Style pour l'image dans le lien */
        .navbar .logo {
            margin-right: 10px;
            margin-left: 5px;
            /* Espace entre le logo et le premier lien */
        }

        .navbar .logo img {
            height: 30px;
            /* Taille légèrement agrandie du logo */
            vertical-align: middle;
            /* pour aligner l'image verticalement avec le texte s'il y en a */
        }

        .user-info {
            margin-left: auto;
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
            transition: background-color 0.3s, color 0.3s, border-radius 0.3s;
            border-radius: 10px;
        }

        .logout-button:hover {
            background-color: #aaa;
            color: white;
            border-radius: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        /* Style pour le formulaire de filtrage */
        .filter-form {
            width: 500px;
            margin-bottom: 20px;
            margin-left: 30px;
            display: flex;
            
        }

        /* Style pour le champ de saisie */
        .filter-input {
            width: 50px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        /* Style pour le bouton de soumission */
        .filter-button {
            height: 40px;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            background-color: #148d04;
            color: white;
            cursor: pointer;
            margin-right: 7px;
        }

        .filter-button:hover{ 

 background-color: #0F6403;
          }

        /* Style pour le bouton de réinitialisation */
        .reset-button {
            background-color: #aaa;
            height: 40px;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            background-color: #ccc;
            color: #333;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s, border-radius 0.3s;
        }

        .reset-button:hover {
            background-color: gray;
        }

        table {
            width: calc(100% - 60px);
            /* 100% width minus 30px left and 30px right margins */
            margin-left: 30px;
            margin-right: 30px;
            border-collapse: separate;
            /* Separate border model for curved borders */
            border-spacing: 0;
            /* No spacing between table cells */
            border: 1px solid #ddd;
            /* Light border color */
            border-radius: 10px;
            /* Curved border radius */
        }

        th,
        td {
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
    <div class="container">
        <div class="left-panel">
            <h1>Appréciations</h1>
            <form class="app_form" action="apprecations.php" method="post">
                <label for="matricule">Matricule d'Employer:</label>
                <input type="text" id="matricule" name="matricule" required><br>
                <label for="note_assiduite">Note d'assiduité:</label>
                <input type="number" id="note_assiduite" name="note_assiduite" min="0" max="10" required><br>
                <label for="note_absences">Note des absences:</label>
                <input type="number" id="note_absences" name="note_absences" min="0" max="10" required><br>
                <label for="note_travail">Note du travail:</label>
                <input type="number" id="note_travail" name="note_travail" min="0" max="10" required><br>
                <label for="point_fort">Point fort:</label>
                <input type="text" id="point_fort" name="point_fort" required><br>
                <label for="point_faibles">Point faibles:</label>
                <input type="text" id="point_faibles" name="point_faibles" required><br>
                <button name="add-app" type="submit"><i class="fas fa-plus"></i> Ajouter Appréciation</button>
                <button type="button" onclick="window.print()"><i class="fas fa-print"></i></button>
                <?php if (isset($error)) : ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if (isset($success)) : ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
            </form>
        </div>
        <div class="right-panel">
            <h1>Historique des Appréciations</h1>

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
                        <th>Assiduité</th>
                        <th>Travail</th>
                        <th>Absences</th>
                        <th>Point Fort</th>
                        <th>Point Faible</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Récupérer les valeurs filtrées si elles existent
                    $filter_matricule = isset($_GET['matricule']) ? $_GET['matricule'] : '';

                    // Construction de la requête SQL en fonction du filtrage par matricule
                    $sql = "SELECT u.matricule, u.nom, u.prenom, a.note_assiduite, a.note_travail, a.note_absences, a.point_fort, a.point_faibles
            FROM appreciations a  , utilisateurs u 
           where a.matricule = u.matricule";
                    if (!empty($filter_matricule)) {
                        $sql .= " and u.matricule LIKE '%$filter_matricule%'";
                    }

                    // Exécution de la requête SQL
                    $result = $conn->query($sql);

                    // Affichage des résultats
                    while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row["matricule"] ?></td>
                            <td><?= $row["nom"] ?></td>
                            <td><?= $row["prenom"] ?></td>
                            <td><?= $row["note_assiduite"] ?></td>
                            <td><?= $row["note_travail"] ?></td>
                            <td><?= $row["note_absences"] ?></td>
                            <td><?= $row["point_fort"] ?></td>
                            <td><?= $row["point_faibles"] ?></td>
                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
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