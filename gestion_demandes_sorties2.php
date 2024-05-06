<?php
session_start();
include 'db.php'; // Connexion à la base de données

// Vérifiez si l'utilisateur est autorisé (par exemple, un superviseur)
if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: login.php'); // Redirection si non autorisé
    exit;
}

// Récupérer les demandes de sorties
$query = $conn->prepare("SELECT * FROM demandes_de_sorties WHERE statut = 'en attente'");
$query->execute();
$result = $query->get_result(); // Obtenir les résultats

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Demandes de Sorties</title>
    <!-- Liens vers les ressources CSS/JS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <!-- Styles personnalisés -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solide #ddd;
            text-align: left;
        }

        th {
            background: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background: #e9e9e9;
        }

        .form-container {
            margin: 10px 0;
        }

        .btn {
            background: #435d7d;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-danger {
            background: #F44336;
        }

        .btn:hover {
            background: #333;
        }

        .btn-danger:hover {
            background: #D32F2F;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Liste des Demandes de Sorties</h1>

        <!-- Tableau des demandes de sortie -->
        <table>
            <thead>
                <tr>
                    <th>Date de Sortie</th>
                    <th>Heure de Sortie</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Boucle pour afficher les demandes de sortie -->
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['date_sortie']) ?></td>
                        <td><?= htmlspecialchars($row['heure_sortie']) ?></td>
                        <td><?= htmlspecialchars($row['motif']) ?></td>
                        <td><?= htmlspecialchars($row['statut']) ?></td>
                        <td>
                            <div class='form-container'>
                                <!-- Formulaire pour accepter ou refuser -->
                                <form method='post' action='decision_sortie.php'>
                                    <input type='hidden' name='demandeId' value='<?= htmlspecialchars($row['id']) ?>'>
                                    <input type='text' name='justificatif' placeholder='Justificatif (facultatif)'>
                                    <button type='submit' name='decision' value='accepter' class='btn'>Accepter</button>
                                    <button type='submit' name='decision' value='refuser' class='btn btn-danger'>Refuser</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Fermer la connexion à la base de données
    $conn->close();
    ?>
</body>
</html>

