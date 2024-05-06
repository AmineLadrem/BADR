<?php
session_start();
include_once 'db.php';

if (!isset($_GET['id_experience'])) {
    echo "Identifiant de l'expérience non spécifié.";
    exit;
}

$id_experience = $_GET['id_experience'];

// Récupérer les informations sur l'expérience
$sql_exp = "SELECT date_debut, date_fin, poste, entreprise, motif FROM experiences WHERE id_experience=?";
$stmt_exp = $conn->prepare($sql_exp);
$stmt_exp->bind_param("i", $id_experience);
$stmt_exp->execute();
$result_exp = $stmt_exp->get_result();
$exp_row = $result_exp->fetch_assoc();

$date_debut = $exp_row['date_debut'];
$date_fin = $exp_row['date_fin'];
$poste = $exp_row['poste'];
$entreprise = $exp_row['entreprise'];
$motif = $exp_row['motif'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Expérience</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Modifier Expérience</h1>
    <form action="update_experience.php" method="post">
        <input type="hidden" name="id_experience" value="<?php echo $id_experience; ?>">
        <label for="date_debut">Date de début:</label>
        <input type="date" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>" required><br><br>
        <label for="date_fin">Date de fin:</label>
        <input type="date" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>" required><br><br>
        <label for="poste">Poste:</label>
        <input type="text" id="poste" name="poste" value="<?php echo $poste; ?>" required><br><br>
        <label for="entreprise">Entreprise:</label>
        <input type="text" id="entreprise" name="entreprise" value="<?php echo $entreprise; ?>" required><br><br>
        <label for="motif">Motif:</label>
        <select name="motif" id="motif">
            <option value="demission" <?php if ($motif == 'demission') echo 'selected'; ?>>Démission</option>
            <option value="retraite" <?php if ($motif == 'retraite') echo 'selected'; ?>>Retraite</option>
            <option value="fin de contrat" <?php if ($motif == 'fin de contrat') echo 'selected'; ?>>Fin de contrat</option>
            <!-- Ajoutez d'autres options de motif si nécessaire -->
        </select><br><br>
        <button type="submit">Enregistrer Modifications</button>
    </form>
</body>
</html>
