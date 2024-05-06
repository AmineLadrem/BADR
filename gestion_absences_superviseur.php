<?php
session_start();
include 'db.php'; // Inclure votre connexion à la base de données

if (!isset($_SESSION['email']) || $_SESSION['is_supervisor'] != 1) {
    header('Location: login.php'); // Rediriger vers la page de connexion si l'utilisateur n'est pas un superviseur
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $absenceId = $_POST['absenceId'];

    // Mettre à jour le statut de l'absence dans la base de données
    $sql = "UPDATE absences SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $decision, $absenceId);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de la mise à jour du statut de l'absence: " . $stmt->error;
    } else {
        // Rediriger vers la page de gestion des absences du superviseur
        header("Location: gestion_absences_superviseur.php");
        exit;
    }
}

// Récupérer les absences en attente
$result = $conn->query("SELECT * FROM absences WHERE statut = 'en attente'");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Absences (Superviseur)</title>
    <style>
       body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    padding: 20px;
}

h1 {
    text-align: center;
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
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

button {
    background-color: #008CBA;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #005f80;
}

.error {
    color: red;
    font-size: 16px;
    text-align: center;
    margin-bottom: 20px;
}

    </style>
</head>
<body>
    <h1>Gestion des Absences (Superviseur)</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Email Utilisateur</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Motif</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= isset($row["email_utilisateur"]) ? $row["email_utilisateur"] : "Non défini" ?></td>
                        <td><?= isset($row["date_debut"]) ? $row["date_debut"] : "Non défini" ?></td>
                        <td><?= isset($row["date_fin"]) ? $row["date_fin"] : "Non défini" ?></td>
                        <td><?= isset($row["motif"]) ? $row["motif"] : "Non défini" ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="absenceId" value="<?= $row['id'] ?>">
                                <button type="submit" name="decision" value="approuve">Approuver</button>
                                <button type="submit" name="decision" value="refuse">Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune absence en attente.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>
