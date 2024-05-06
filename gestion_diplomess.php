<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Diplômes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestion des Diplômes</h1>
    
    <!-- Inclure le formulaire d'ajout de diplôme -->
    <?php include_once 'adds_diplome.php'; ?>
    
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
            <?php
            // Assurez-vous que vous avez une connexion à la base de données active
            include_once 'db.php'; // Incluez le fichier de connexion à la base de données
            
            // Récupérer tous les diplômes de la base de données
            $sql = "SELECT * FROM diplomes WHERE id_utilisateur = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row["id_diplome"] ?></td>
                        <td><?= isset($row["type_diplome"]) ? htmlspecialchars($row["type_diplome"]) : "" ?></td>
                        <td><?= isset($row["domaine"]) ? htmlspecialchars($row["domaine"]) : "" ?></td>
                        <td><?= isset($row["lieu_obtention"]) ? htmlspecialchars($row["lieu_obtention"]) : "" ?></td>
                        <td><?= isset($row["date_obtention"]) ? htmlspecialchars($row["date_obtention"]) : "" ?></td>
                        <td>
                            <a href="updates_diplome.php?id_diplome=<?= $row['id_diplome'] ?>">Modifier</a>
                            <a href="deletes_diplome.php?id_diplome=<?= $row['id_diplome'] ?>" class="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce diplôme?');">Supprimer</a>

                        </td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="6">Aucun diplôme trouvé</td>
                </tr>
            <?php endif;
            $conn->close(); ?>
        </tbody>
    </table>
</body>
</html>
