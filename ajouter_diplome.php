<?php
session_start();

// Vérifier si un utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Traitement du formulaire d'ajout de diplôme
    // Assurez-vous de valider et de nettoyer les données avant de les utiliser dans la requête SQL

    // Inclure le fichier de connexion à la base de données
    include_once 'db.php';

    // Récupérer les données du formulaire
    $type = $_POST['type'];
    $domaine = $_POST['domaine'];
    $date_obtention = $_POST['date_obtention'];
    $lieu_obtention = $_POST['lieu_obtention'];

    // Récupérer l'ID de l'utilisateur connecté
    $user_id = $_SESSION['user_id'];

    // Préparer et exécuter la requête SQL d'insertion
    $sql = "INSERT INTO Diplomes (type, domaine, date_obtention, lieu_obtention, user_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $type, $domaine, $date_obtention, $lieu_obtention, $user_id);
    $stmt->execute();

    // Rediriger vers la page des diplômes après l'ajout
    header("Location: diplomes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Diplôme</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Ajouter un Diplôme</h1>
    <form action="" method="post">
        <label for="type">Type :</label>
        <input type="text" name="type" required><br>
        <label for="domaine">Domaine :</label>
        <input type="text" name="domaine" required><br>
        <label for="date_obtention">Date d'obtention :</label>
        <input type="date" name="date_obtention" required><br>
        <label for="lieu_obtention">Lieu d'obtention :</label>
        <input type="text" name="lieu_obtention" required><br>
        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
