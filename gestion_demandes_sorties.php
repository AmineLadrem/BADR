<?php
session_start();
include 'db.php'; // Connexion à la base de données

// Vérifiez si l'utilisateur est autorisé (par exemple, un superviseur)
if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: index.php'); // Redirection si non autorisé
    exit;
}

// Récupérer les demandes de sorties
$query = $conn->prepare("SELECT * FROM demandes_de_sorties WHERE statut = 'en attente'");
$query->execute();
$result = $query->get_result(); // Obtenir les résultats

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $justificatif = $_POST['justificatif'] ?? '';
    $demandeId = $_POST['demandeId'];

    // Récupérer le nombre de jours demandés et l'email de l'employé
    $demandeInfo = $conn->prepare("SELECT id, email, date_sortie, heure_sortie, motif, statut FROM demandes_de_sorties WHERE id = ?");
    $demandeInfo->bind_param("i", $demandeId);
    $demandeInfo->execute();
    $result = $demandeInfo->get_result();
    $info = $result->fetch_assoc();
    $demandeInfo->close();

    
    if ($decision === 'accepter') {
        $status = 'Acceptée';
    } else {
        $status = 'Refusée';
        // Restituer les jours si la demande est refusée
        $updateDays = $conn->prepare("UPDATE utilisateurs SET joursCongesRestants = joursCongesRestants + ? WHERE email = ?");
        $updateDays->bind_param("is", $daysRequested, $info['email']);
        $updateDays->execute();
        $updateDays->close();
    }

    // Mettre à jour le statut de la demande
    $stmt = $conn->prepare("UPDATE demandes_de_sorties SET statut = ?, justificatif = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $justificatif, $demandeId);
    $stmt->execute();
    $stmt->close();
}

$result = $conn->query("SELECT * FROM demandes_de_sorties WHERE statut = 'En attente'");
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
                    <th>email</th>
                    <th>Date de Sortie</th>
                    <th>Heure de Sortie</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    include 'db.php'; // Connexion à la base de données
                    $query = $conn->prepare("SELECT id, email, date_sortie, heure_sortie, motif, statut FROM demandes_de_sorties");
                    $query->execute();
                    $result = $query->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date_sortie']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['heure_sortie']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motif']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
                        echo "<td>";
                        echo "<a href='edit_request.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-warning'>Accepter</a>";
                        echo " ";
                        echo "<a href='delete_request.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette demande?\");'>Rejeter</a>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    $conn->close(); // Fermer la connexion à la base de données
                    ?>
        </table>
    </div>
</body>
</html>

