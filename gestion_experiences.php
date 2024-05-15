<?php
session_start();
include_once 'db.php';

if (!isset($_GET['id'])) {
    echo "Identifiant de l'utilisateur non spécifié.";
    exit;
}

$id_utilisateur = $_GET['id'];

 
$sql_user = "SELECT nom, prenom FROM utilisateurs WHERE id=?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $id_utilisateur);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_row = $result_user->fetch_assoc();
$user_nom = $user_row['nom'];
$user_prenom = $user_row['prenom'];


$sql_exp = "SELECT id_experience, date_debut, date_fin, poste, entreprise, motif FROM experiences WHERE id_utilisateur=?";
$stmt_exp = $conn->prepare($sql_exp);
$stmt_exp->bind_param("i", $id_utilisateur);
$stmt_exp->execute();
$result_exp = $stmt_exp->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Expériences de <?php echo $user_prenom . ' ' . $user_nom; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestion des Expériences de <?php echo $user_prenom . ' ' . $user_nom; ?></h1>
    <h2>Ajouter une Expérience</h2>
    <form action="add_experience.php" method="post">
        <input type="hidden" name="id_utilisateur" value="<?php echo $id_utilisateur; ?>">
        <label for="date_debut">Date de début:</label>
        <input type="date" id="date_debut" name="date_debut" required><br><br>
        <label for="date_fin">Date de fin:</label>
        <input type="date" id="date_fin" name="date_fin" required><br><br>
        <label for="poste">Poste:</label>
        <input type="text" id="poste" name="poste" required><br><br>
        <label for="entreprise">Entreprise:</label>
        <input type="text" id="entreprise" name="entreprise" required><br><br>
        <label for="motif">Motif:</label>
        <label for="motif">Motif:</label>
    <select name="motif" id="motif">
        <option value="demission">demission</option>
        <option value="retraite">retraite</option>
        <option value="fin de contrat">fin de contrat</option>
        <!-- Ajoutez d'autres options de motif si nécessaire -->
    </select><br><br>
        <button type="submit">Ajouter Expérience</button>
    </form>

    <h2>Historique des Expériences</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date de Début</th>
                <th>Date de Fin</th>
                <th>Poste</th>
                <th>Entreprise</th>
                <th>Motif</th> <!-- Colonne pour le motif -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($exp_row = $result_exp->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $exp_row['id_experience']; ?></td>
                    <td><?php echo $exp_row['date_debut']; ?></td>
                    <td><?php echo $exp_row['date_fin']; ?></td>
                    <td><?php echo $exp_row['poste']; ?></td>
                    <td><?php echo $exp_row['entreprise']; ?></td>
                    <td><?php echo $exp_row['motif']; ?></td> <!-- Affichage du motif -->
                    <td>
                        <a href="update_experience.php?id_experience=<?php echo $exp_row['id_experience']; ?>">Modifier</a>
                        <a href="delete_experience.php?id_experience=<?php echo $exp_row['id_experience']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette expérience?');">Supprimer</a>

                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
