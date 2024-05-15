<?php
session_start();
// Vérifie si l'utilisateur est connecté et s'il est un superviseur hiérarchique
if (!isset($_SESSION['email']) || !isset($_SESSION['est_superieur_hierarchique']) || $_SESSION['est_superieur_hierarchique'] != 1) {
    header("Location: index.php");
    exit;
}

include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Absences</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
        font-family: Consolas, monospace;
    }
    table {
    width: calc(100% - 60px); 
    margin-left: 30px;
    margin-right: 30px;
    border-collapse: separate; 
    border-spacing: 0; 
    border: 1px solid #ddd; 
    border-radius: 10px; 
}


th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}


th {
    background-color: #f2f2f2;
}


tr:nth-child(even) {
    background-color: #f2f2f2;
}


a {
    color: blue;
    text-decoration: none;
}


a:hover {
    text-decoration: underline;
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
    <h1>Historique des Absences</h1>
    <form class="filter-form" action="" method="GET">
    <input class="filter-input" type="text" name="matricule" placeholder="Matricule...">
    <button class="filter-button" type="submit">Filtrer</button>
    <button class="reset-button" type="button" onclick="resetFilter()">Réinitialiser</button>
</form>
    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>motif</th>
                <th>Date de Début</th>
                <th>Date de Fin</th>
                <th>statut</th>


                <!-- Ajoutez d'autres colonnes si nécessaire -->
            </tr>
        </thead>
        <tbody>



        <?php
    
    $filter_matricule = isset($_GET['matricule']) ? $_GET['matricule'] : '';


$sql = "SELECT a.id, a.date_debut, a.date_fin, a.motif, a.statut, u.matricule, u.nom, u.prenom, u.email  
FROM absence a 
JOIN utilisateurs u ON u.matricule = a.matricule";

if (!empty($filter_matricule)) {
$sql .= " WHERE a.matricule LIKE '%$filter_matricule%'";
}

$sql .= " ORDER BY CASE 
    WHEN a.statut = 'En attente' THEN 1 
    WHEN a.statut = 'Accepté' THEN 2 
    ELSE 3 
  END";


    $result = $conn->query($sql);

   
    while ($row = $result->fetch_assoc()): ?>
        <tr>
        <td><?= $row["matricule"] ?></td>
            <td><?= $row["nom"] ?></td>
            <td><?= $row["prenom"] ?></td>
            <td><?= $row["motif"] ?></td>
            <td><?= $row["date_debut"] ?></td>
            <td><?= $row["date_fin"] ?></td>
            <td><?= $row["statut"] ?></td>            
            
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

    
    function logout() {
       
        document.cookie = "nom=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        
        window.location.href = "index.php"; 
    }
</script>
</body>
</html>
