<?php
session_start();
include 'db.php'; // Connexion à la base de données "gestion_utilisateurs"

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header('Location: login.php'); // Redirection si non connecté
    exit;
}

$email = $_SESSION['email'];
$dateToday = date("Y-m-d"); // Date au format année-mois-jour

// Gestion du CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = ''; // Pour afficher un message de confirmation ou d'erreur

// Traitement du formulaire de demande de congé avec pièce jointe
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Validation du token CSRF échouée');
    }

    // Récupérer les données du formulaire
    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'] ?? '';

    // Traiter le fichier joint
    $attachmentId = null; // Pour stocker l'ID de l'attachement
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['attachment']['name']);
        $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
        $fileSize = $_FILES['attachment']['size'];

        // Stockage dans le système de fichiers
        $uploadDir = 'uploads/'; // Chemin vers le dossier de stockage
        $uploadPath = $uploadDir . $fileName;

        // Déplacer le fichier dans le dossier
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath)) {
            // Insérer dans la table des attachements
            $stmt = $conn->prepare("INSERT INTO attachements (file_name, file_path, file_type, file_size, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssi", $fileName, $uploadPath, $fileType, $fileSize);
            $stmt->execute();
            $attachmentId = $stmt->insert_id; // Obtenir l'ID de l'attachement
            $stmt->close();
        }
    }

    // Validation des dates et insertion de la demande de congé
    if ($dateDebut >= $dateToday && $dateFin >= $dateDebut) {
        // Insérer la demande de congé avec l'ID de l'attachement
        $stmt = $conn->prepare("INSERT INTO demandesConges (email, dateDebut, dateFin, justificatif, statut, entity_id) VALUES (?, ?, ?, ?, 'En attente', ?)");
        $stmt->bind_param("sssss", $email, $dateDebut, $dateFin, $justificatif, $attachmentId);
        $stmt->execute();
        $stmt->close();

        $message = "Votre demande de congé a été soumise avec succès.";
    } else {
        $message = "Les dates fournies sont invalides.";
    }
}

// Récupérer les demandes de congés de l'utilisateur connecté
$query = $conn->prepare("SELECT dateDebut, dateFin, statut, justificatif, entity_id FROM demandesConges WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Congés</title>
    <!-- Liens vers les ressources CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <!-- Styles personnalisés -->
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }

        .title {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .btn {
            background: #435d7d;
            color: white;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        tr:hover {
            background: #e9e9e9;
        }

        .confirmation {
            color: green;
            text-align: center;
            font-size: 16px.
        }

        .error {
            color: red.
            text-align: center.
            font-size: 16px.
        }

        /* Bouton d'envoi */
        button[type="submit"] {
            background: #435d7d.
            color: white.
            border: none.
            border-radius: 5px.
            padding: 10px 20px.
            cursor: pointer.
        }

        button[type="submit"]:hover {
            background: #333.
        }
    </style>

    <!-- Script pour mettre en évidence les lignes avec des dates passées -->
    <script>
        window.onload = function() {
            var rows = document.querySelectorAll('table tr[data-start-date]');
            var today = new Date().setHours(0, 0, 0, 0).

            rows.forEach(function(row) {
                var startDate = new Date(row.getAttribute('data-start-date')).setHours(0, 0, 0, 0).
                if (startDate < today) {
                    row.style.backgroundColor = "#ffdddd"; // Indiquer une date passée
                }
            });
        };
    </script>
</head>

<body>
    <div class="container">
        <div class="title">Gestion des Congés exceptionnels</div>
        <!-- Affichage des messages de confirmation ou d'erreur -->
        <?php if ($message): ?>
            <p class="<?= strpos($message, 'succès') !== false ? 'confirmation' : 'error' ?>"><?= $message ?></p>
        <?php endif; ?>

        <!-- Formulaire pour envoyer une demande de congé -->
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label>Date de début:</label>
            <input type="date" name="dateDebut" min="<?= $dateToday ?>" required>
            <label>Date de fin:</label>
            <input type="date" name="dateFin" min="<?= $dateToday ?>" required>
            <label>Justificatif (facultatif):</label>
            <textarea name="justificatif"></textarea>
            <label>Pièce jointe (facultatif):</label>
            <input type="file" name="attachment">
            <button type="submit">Envoyer la Demande</button>
        </form>

        <!-- Tableau des demandes de congé -->
        <h2>Vos demandes de congé</h2>
        <table>
            <thead>
                <tr>
                    <th>Date de début</th>
                    <th>Date de fin</th>
                    <th>Statut</th>
                    <th>Justificatif</th>
                    <th>Pièce jointe</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-start-date="<?= htmlspecialchars($row['dateDebut']) ?>">
                        <td><?= htmlspecialchars($row['dateDebut']) ?></td>
                        <td><?= htmlspecialchars($row['dateFin']) ?></td>
                        <td><?= htmlspecialchars($row['statut']) ?></td>
                        <td><?= htmlspecialchars($row['justificatif']) ?></td>
                        <td>
                            <?php if ($row['entity_id']): ?>
                                <a href="download_attachment.php?id=<?= htmlspecialchars($row['entity_id']) ?>">Télécharger</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
