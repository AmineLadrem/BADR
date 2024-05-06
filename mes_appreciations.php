<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit;
}

$email_utilisateur = $_SESSION['email'];

// Récupérer les appréciations de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM appreciations WHERE email_utilisateur = ?");
$stmt->bind_param("s", $email_utilisateur);
$stmt->execute();
$result = $stmt->get_result();

$appreciations = [];
while ($row = $result->fetch_assoc()) {
    $appreciations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Appréciations</title>
    <style>
        /* Ajoutez votre CSS personnalisé ici */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mes Appréciations</h1>
        <p>Bienvenue, <?php echo $email_utilisateur; ?></p>
        <table>
            <thead>
                <tr>
                    <th>Note Assiduité</th>
                    <th>Note Travail</th>
                    <th>Note Absences</th>
                    <th>Point Fort</th>
                    <th>Point Faible</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appreciations as $appreciation): ?>
                    <tr>
                        <td><?php echo $appreciation['note_assiduite']; ?></td>
                        <td><?php echo $appreciation['note_travail']; ?></td>
                        <td><?php echo $appreciation['note_absences']; ?></td>
                        <td><?php echo $appreciation['point_fort']; ?></td>
                        <td><?php echo $appreciation['point_faibles']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
