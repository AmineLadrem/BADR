<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $justificatif = $_POST['justificatif'] ?? '';
    $demandeId = $_POST['demandeId'];

    $demandeInfo = $conn->prepare("SELECT email, dateDebut, dateFin FROM demandesConges WHERE id = ?");
    $demandeInfo->bind_param("i", $demandeId);
    $demandeInfo->execute();
    $result = $demandeInfo->get_result();

    if ($result->num_rows > 0) {
        $info = $result->fetch_assoc();

        // Vérifier si les dates sont non nulles avant de les utiliser
        if ($info['dateDebut'] !== null && $info['dateFin'] !== null) {
            $debut = new DateTime($info['dateDebut']);
            $fin = new DateTime($info['dateFin']);
            $interval = $debut->diff($fin);
            $daysRequested = $interval->days + 1;

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
            $stmt = $conn->prepare("UPDATE demandesConges SET statut = ?, justificatif = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $justificatif, $demandeId);
            $stmt->execute();
            $stmt->close();
        } else {
            // Gérer le cas où les dates sont nulles
            echo "Les dates de la demande sont nulles.";
        }
    } else {
        // Gérer le cas où aucune demande correspondante n'est trouvée
        echo "Aucune demande de congé correspondante trouvée.";
    }
}

$result = $conn->query("SELECT * FROM demandesConges WHERE statut = 'En attente'");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Demandes de Congés</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    padding: 20px;
}

h1 {
    color: #333;
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
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

input[type="text"], textarea {
    width: 95%;
    padding: 5px;
    margin-top: 4px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background-color: #008CBA;
    color: white;
    padding: 10px 15px;
    margin: 5px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #007B8A;
}

.form-container {
    margin-bottom: 20px;
}

        </style>
</head>
<body>
    <h1>Gestion des Demandes de Congés</h1>
    <table>
        <tr>
            <th>Employé</th>
            <th>Date de début</th>
            <th>Date de fin</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['dateDebut']) ?></td>
            <td><?= htmlspecialchars($row['dateFin']) ?></td>
            <td>
                <div class="form-container">
                    <form method="post">
                        <input type="hidden" name="demandeId" value="<?= $row['id'] ?>">
                        <input type="text" name="justificatif" placeholder="Justificatif (facultatif)">
                        <button type="submit" name="decision" value="accepter">Accepter</button>
                        <button type="submit" name="decision" value="refuser">Refuser</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php
$conn->close();
?>
