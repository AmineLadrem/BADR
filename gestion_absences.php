<?php
session_start();
include 'db.php'; // Assurez-vous que ce fichier inclut votre connexion à la base de données

if (!isset($_SESSION['email'])) {
    header('Location: login.php'); // Rediriger vers la page de connexion si aucun utilisateur n'est connecté
    exit;
}

$email = $_SESSION['email'];
$dateToday = date("Y-m-d");  // Récupère la date d'aujourd'hui au format année-mois-jour

// Gestion de CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire de demande d'absence
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $dateDebut = $_POST['dateDebut'];
    $dateFin = $_POST['dateFin'];
    $justificatif = $_POST['justificatif'] ?? '';

    if ($dateDebut >= $dateToday && $dateFin >= $dateDebut) {
        $stmt = $conn->prepare("INSERT INTO absences (email_utilisateur, date_debut, date_fin, motif, statut) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->bind_param("ssss", $email, $dateDebut, $dateFin, $justificatif);
        $stmt->execute();
        $stmt->close();
        
        // Redirection pour éviter la resoumission du formulaire
        header("Location: gestion_absences.php");
        exit;
    } else {
        $error = "Les dates fournies sont invalides.";
    }
}

// Récupérer les absences de l'utilisateur connecté
$query = $conn->prepare("SELECT id, date_debut, date_fin, motif, statut FROM absences WHERE email_utilisateur = ?");

$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Absences</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        form {
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .past {
            text-decoration: line-through;
            color: #999;
        }
        input, textarea, button {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #0056b3;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #003d82;
        }
        .error {
            color: red;
            font-size: 16px;
            text-align: center;
        }
    </style>
    <script>
        window.onload = function() {
            var rows = document.querySelectorAll('table tr[data-start-date]');
            var today = new Date().setHours(0,0,0,0);

            rows.forEach(function(row) {
                var startDate = new Date(row.getAttribute('data-start-date')).setHours(0,0,0,0);
                if (startDate < today) {
                    row.classList.add('past');
                }
            });
        };
    </script>
</head>
<body>
    <h1>Gestion des Absences</h1>
    <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        Date de début: <input type="date" name="dateDebut" min="<?= $dateToday ?>" required>
        Date de fin: <input type="date" name="dateFin" min="<?= $dateToday ?>" required>
        Justificatif (facultatif): <textarea name="justificatif"></textarea>
        <button type="submit">Envoyer demande d'absence</button>
    </form>

    <h2>Vos absences</h2>
    <table>
        <thead>
            <tr>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Motif</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr data-start-date="<?= htmlspecialchars($row['date_debut']) ?>">
                <td><?= htmlspecialchars($row['date_debut']) ?></td>
                <td><?= htmlspecialchars($row['date_fin']) ?></td>
                <td><?= htmlspecialchars($row['motif']) ?></td>
                <td><?= htmlspecialchars($row['statut']) ?></td>
                <td>
                <a href="modifier_absence.php?id=<?= $row['id'] ?>">Modifier</a> | 
                <a href="supprimer_absence.php?id=<?= $row['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette absence?');">Supprimer</a>
            </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>
