<?php
session_start();

// Connexion à la base de données
include 'db.php';

// Vérification de l'ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
} else {
    die('ID invalide ou non fourni');
}

// Recherche de l'utilisateur à modifier
$sql = "SELECT * FROM utilisateurs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die('Aucun utilisateur trouvé avec cet ID');
}
$user = $result->fetch_assoc();

// Traitement du formulaire de mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $diplomes = $_POST['diplomes'];
    $salaire = $_POST['salaire'];
    $statut = $_POST['statut'];
    $service = $_POST['service'];
    $poste = $_POST['poste'];

    $sql = "UPDATE utilisateurs SET nom = ?, prenom = ?, date_naissance = ?, telephone = ?, email = ?, diplomes = ?, salaire = ?, statut = ?, service = ?, poste = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $nom, $prenom, $date_naissance, $telephone, $email, $diplomes, $salaire, $statut, $service, $poste, $id);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de la mise à jour: " . $stmt->error;
    } else {
        header("Location: index3.php");
        exit;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur</title>
</head>
<body>
    <h1>Modifier l'Utilisateur</h1>
    <form action="update_user.php?id=<?= htmlspecialchars($id) ?>" method="post">
        Nom: <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>"><br>
        Prénom: <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>"><br>
        Date de naissance: <input type="date" name="date_naissance" value="<?= htmlspecialchars($user['date_naissance']) ?>"><br>
        Numéro de téléphone: <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>"><br>
        Adresse mail: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br>
        Diplômes: <input type="text" name="diplomes" value="<?= htmlspecialchars($user['diplomes']) ?>"><br>
        Salaire: <input type="text" name="salaire" value="<?= isset($user['salaire']) ? htmlspecialchars($user['salaire']) : '' ?>"><br>
Statut: 
<select name="statut">
    <option value="actif" <?= ($user['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
    <option value="en conge" <?= ($user['statut'] ?? '') === 'en conge' ? 'selected' : '' ?>>En congé</option>
    <option value="en formation" <?= ($user['statut'] ?? '') === 'en formation' ? 'selected' : '' ?>>En formation</option>
    <option value="retarite" <?= ($user['statut'] ?? '') === 'retarite' ? 'selected' : '' ?>>Retraité</option>
    <option value="suspendu" <?= ($user['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
    <option value="demissionaire" <?= ($user['statut'] ?? '') === 'demissionaire' ? 'selected' : '' ?>>Démissionnaire</option>
</select><br>
Service: <input type="text" name="service" value="<?= isset($user['service']) ? htmlspecialchars($user['service']) : '' ?>"><br>
Poste: <input type="text" name="poste" value="<?= isset($user['poste']) ? htmlspecialchars($user['poste']) : '' ?>"><br>
<br>
        <button type="submit" name="update">Mettre à jour</button>
    </form>
</body>
</html>
