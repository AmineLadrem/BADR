<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

// Inclure la connexion à la base de données
include_once 'db.php';

// Vérifier si un ID de diplôme est spécifié dans l'URL
if(isset($_GET['id_diplome'])) {
    $id_diplome = $_GET['id_diplome'];
    
    // Récupérer les informations sur le diplôme à partir de la base de données
    $sql = "SELECT * FROM diplomes WHERE id_diplome = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_diplome);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $type_diplome = $row['type_diplome'];
        $domaine = $row['domaine'];
        $lieu_obtention = $row['lieu_obtention'];
        $date_obtention = $row['date_obtention'];
    } else {
        echo "Diplôme non trouvé.";
        exit();
    }
} else {
    echo "ID de diplôme non spécifié.";
    exit();
}

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token']) && $_POST['token'] === $_SESSION['form_token']) {
    // Récupérer les valeurs du formulaire
    $type_diplome = $_POST['type_diplome'];
    $domaine = $_POST['domaine'];
    $lieu_obtention = $_POST['lieu_obtention'];
    $date_obtention = $_POST['date_obtention'];
    
    // Mettre à jour les informations du diplôme dans la base de données
    $sql = "UPDATE diplomes SET type_diplome = ?, domaine = ?, lieu_obtention = ?, date_obtention = ? WHERE id_diplome = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $type_diplome, $domaine, $lieu_obtention, $date_obtention, $id_diplome);
    $stmt->execute();
    
    if ($stmt->error) {
        echo "Erreur lors de la mise à jour du diplôme: " . $stmt->error;
    } else {
        header("Location: gestion_diplomess.php");
        exit();
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Diplôme</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Modifier un Diplôme</h1>
    <form action="update_diplome.php?id_diplome=<?= $id_diplome ?>" method="post">
        <input type="hidden" name="token" value="<?= $_SESSION['form_token']; ?>">
        <label for="type_diplome">Type de Diplôme:</label>
        <input type="text" name="type_diplome" value="<?= htmlspecialchars($type_diplome) ?>" required>
        <label for="domaine">Domaine:</label>
        <select name="domaine">
            <option value="Informatique" <?php if($domaine == 'Informatique') echo 'selected'; ?>>Informatique</option>
            <option value="Finance" <?php if($domaine == 'Finance') echo 'selected'; ?>>Finance</option>
            <option value="Marketing" <?php if($domaine == 'Marketing') echo 'selected'; ?>>Marketing</option>
            <!-- Ajouter d'autres options selon les domaines possibles -->
        </select>
        <label for="lieu_obtention">Lieu d'Obtention:</label>
        <input type="text" name="lieu_obtention" value="<?= htmlspecialchars($lieu_obtention) ?>" required>
        <label for="date_obtention">Date d'Obtention:</label>
        <input type="date" name="date_obtention" value="<?= htmlspecialchars($date_obtention) ?>" required>
        <button type="submit">Modifier Diplôme</button>
    </form>
</body>
</html>
