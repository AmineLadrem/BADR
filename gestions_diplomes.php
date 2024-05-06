<?php
// Assurez-vous que vous avez une connexion à la base de données active
include_once 'db.php'; // Incluez le fichier de connexion à la base de données

// Vérifiez si l'ID utilisateur est passé dans la requête GET
$id_utilisateur = null;
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_utilisateur = $_GET['id'];
} else {
    die('ID utilisateur invalide ou non fourni');
}

// Traitement du formulaire d'ajout de diplôme
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérez les données du formulaire
    $type_diplome = $_POST['type_diplome'];
    $domaine = $_POST['domaine'];
    $lieu_obtention = $_POST['lieu_obtention'];
    $date_obtention = $_POST['date_obtention'];

    // Insérez les données dans la base de données
    $sql = "INSERT INTO diplomes (id_utilisateur, type_diplome, domaine, lieu_obtention, date_obtention) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_utilisateur, $type_diplome, $domaine, $lieu_obtention, $date_obtention);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de l'ajout du diplôme: " . $stmt->error;
    } else {
        // Redirigez l'utilisateur vers la page précédente ou une autre page appropriée
        // après l'ajout du diplôme.
        header("Location: gestion_diplomes.php?id=$id_utilisateur");
        exit; // Assurez-vous de terminer le script après la redirection
    }
}

// Récupérer tous les diplômes de la base de données
$sql = "SELECT * FROM diplomes WHERE id_utilisateur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilisateur);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Diplômes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestion des Diplômes</h1>
    <h2>Ajouter un Diplôme</h2>
    <!-- Formulaire d'ajout de diplôme -->
    <form action="add_diplome.php" method="post">
        <input type="hidden" name="id_utilisateur" value="<?= $id_utilisateur ?>">
        <label for="type_diplome">Type de Diplôme:</label>
        <input type="text" id="type_diplome" name="type_diplome" required><br><br>
        <label for="domaine">Domaine:</label>
        <select name="domaine" required>
            <option value="Informatique">Informatique</option>
            <option value="Mathématiques">Mathématiques</option>
            <option value="Physique">Physique</option>
            <!-- Ajouter d'autres options au besoin -->
        </select><br><br>
        <label for="lieu_obtention">Lieu d'Obtention:</label>
        <input type="text" id="lieu_obtention" name="lieu_obtention" required><br><br>
        <label for="date_obtention">Date d'Obtention:</label>
        <input type="date" id="date_obtention" name="date_obtention" required><br><br>
        <button type="submit">Ajouter Diplôme</button>
    </form>

    <h2>Liste des Diplômes</h2>
    <!-- Tableau des diplômes -->
    <table>
        <thead>
            <tr>
                <th>ID Diplôme</th>
                <th>Type de Diplôme</th>
                <th>Domaine</th>
                <th>Lieu d'Obtention</th>
                <th>Date d'Obtention</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row["id_diplome"] ?></td>
                        <td><?= isset($row["type_diplome"]) ? htmlspecialchars($row["type_diplome"]) : "" ?></td>
                        <td><?= isset($row["domaine"]) ? htmlspecialchars($row["domaine"]) : "" ?></td>
                        <td><?= isset($row["lieu_obtention"]) ? htmlspecialchars($row["lieu_obtention"]) : "" ?></td>
                        <td><?= isset($row["date_obtention"]) ? htmlspecialchars($row["date_obtention"]) : "" ?></td>
                        <td>
                            <a href="update_diplome.php?id_diplome=<?= $row['id_diplome'] ?>">Modifier</a>
                            <a href="delete_diplome.php?id=<?= $row['id_diplome'] ?>" class="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce diplôme?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Aucun diplôme trouvé</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php $conn->close(); ?>
