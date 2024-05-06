<?php
session_start();

// Vérifiez si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclure le fichier de connexion à la base de données
    include 'db.php';

    // Récupérez les données du formulaire
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $motif = $_POST['motif'];
    $statut = $_POST['statut']; // Actif, En attente, Approuvé, etc. à définir selon vos besoins

    // Requête SQL pour insérer une nouvelle absence
    $sql = "INSERT INTO absences (date_debut, date_fin, motif, statut) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $date_debut, $date_fin, $motif, $statut);
    $stmt->execute();

    // Rediriger l'utilisateur vers la page précédente ou une autre page appropriée après l'ajout de l'absence
    header("Location: gestion_absences.php");
    exit();
}

// Si le formulaire n'a pas été soumis, afficher le formulaire d'ajout d'absence
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Absence</title>
    <!-- Ajoutez vos liens CSS et autres en-têtes si nécessaire -->
</head>
<body>
    <h1>Ajouter une Absence</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="date_debut">Date de Début :</label>
        <input type="date" id="date_debut" name="date_debut" required><br><br>
        <label for="date_fin">Date de Fin :</label>
        <input type="date" id="date_fin" name="date_fin" required><br><br>
        <label for="motif">Motif :</label>
        <input type="text" id="motif" name="motif" required><br><br>
        <label for="statut">Statut :</label>
        <select name="statut" required>
            <option value="Actif">Actif</option>
            <option value="En attente">En attente</option>
            <option value="Approuvé">Approuvé</option>
            <!-- Ajoutez d'autres options de statut au besoin -->
        </select><br><br>
        <button type="submit">Ajouter Absence</button>
    </form>
</body>
</html>
