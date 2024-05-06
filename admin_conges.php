<?php
session_start();
include 'db.php';  // Assurez-vous que ce fichier contient les bonnes informations de connexion

// Vérification de la connexion du PDG ou autorisation appropriée
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'PDG') {
    header('Location: login.php');
    exit;
}

// Traitement de l'approbation ou du refus des demandes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $demandeId = $_POST['demandeId'];
    $action = $_POST['action'];  // 'Approuver' ou 'Refuser'
    $justificatif = $_POST['justificatif'] ?? '';

    $statut = ($action === 'Approuver') ? 'Approuvée' : 'Refusée';
    $stmt = $conn->prepare("UPDATE demandesConges SET statut = ?, justificatif = ? WHERE id = ?");
    $stmt->bind_param("ssi", $statut, $justificatif, $demandeId);
    $stmt->execute();
    $stmt->close();
}

// Récupérer toutes les demandes de congés
$query = $conn->query("SELECT * FROM demandesConges WHERE statut = 'En attente'");
$demandes = $query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Congés - Admin</title>
    <style>
        /* Ajoutez votre CSS ici */
    </style>
</head>
<body>
    <h1>Gestion des demandes de congés</h1>
    <table>
        <tr>
            <th>Email</th>
            <th>Date de début</th>
            <th>Date de fin</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
        <?php foreach ($demandes as $demande): ?>
        <tr>
            <td><?= htmlspecialchars($demande['email']) ?></td>
            <td><?= htmlspecialchars($demande['dateDebut']) ?></td>
            <td><?= htmlspecialchars($demande['dateFin']) ?></td>
            <td><?= htmlspecialchars($demande['statut']) ?></td>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="demandeId" value="<?= $demande['id'] ?>">
                    <textarea name="justificatif" placeholder="Justificatif (facultatif)"></textarea><br>
                    <button type="submit" name="action" value="Approuver">Approuver</button>
                    <button type="submit" name="action" value="Refuser">Refuser</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<?php $conn->close(); ?>
