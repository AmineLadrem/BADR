<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Demandes de Sortie</title>
    <!-- Bootstrap et autres ressources CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <!-- Styles personnalisés -->
    <style>
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .btn {
            background: #435d7d;
            color: white;
            border-radius: 5px;
        }

        .btn:hover {
            background: #333;
        }

        .message {
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table th {
            background: #f4f4f4;
        }

        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion des Demandes de Sortie</h1>
        
        <!-- Formulaire pour ajouter une nouvelle demande -->
        <form method="post" action="ajouter_demandesortie.php">
            <div class="form-group">
                <label>Date de Sortie:</label>
                <input type="date" name="date_sortie" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Heure de Sortie:</label>
                <input type="time" name="heure_sortie" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Motif:</label>
                <textarea name="motif" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn">Ajouter la Demande</button>
        </form>

        <!-- Affichage des demandes de sortie -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Date de Sortie</th>
                        <th>Heure de Sortie</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Boucle pour afficher les demandes de sortie -->
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
                        echo "<a href='modifier_sortie.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-warning'>Modifier</a>";
                        echo " ";
                        echo "<a href='delete_request.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette demande?\");'>Supprimer</a>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    $conn->close(); // Fermer la connexion à la base de données
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
