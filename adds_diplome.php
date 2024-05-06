<?php
// Assurez-vous que vous avez une connexion à la base de données active
include_once 'db.php'; // Incluez le fichier de connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérez les données du formulaire
    $id_utilisateur = $_POST['id_utilisateur'];
    $type_diplome = $_POST['type_diplome'];
    $domaine = $_POST['domaine'];
    $lieu_obtention = $_POST['lieu_obtention'];
    $date_obtention = $_POST['date_obtention'];

    // Insérez les données dans la base de données
    $sql = "INSERT INTO diplomes (id_utilisateur, type_diplome, domaine, lieu_obtention, date_obtention) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_utilisateur, $type_diplome, $domaine, $lieu_obtention, $date_obtention);
    $stmt->execute();

    if ($stmt->error) {
        echo "Erreur lors de l'ajout du diplôme: " . $stmt->error;
    } else {
        // Redirigez l'utilisateur vers la page précédente ou une autre page appropriée
        // après l'ajout du diplôme.
        header("Location: gestion_diplomess.php");
        exit; // Assurez-vous de terminer le script après la redirection
    }
}
?>


<h2>Ajouter un Diplôme</h2>
<!-- Formulaire d'ajout de diplôme -->
<form action="adds_diplome.php" method="post">
    <input type="hidden" name="id_utilisateur" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
    <label for="type_diplome">Type de Diplôme:</label>
    <input type="text" id="type_diplome" name="type_diplome" required><br><br>
    <label for="domaine">Domaine:</label>
    <select name="domaine" required>
        <option value="Informatique">Informatique</option>
        <option value="Mathématiques">Mathématiques</option>
        <option value="Physique">Physique</option>
        <!-- Ajouter d'autres options au besoin -->
    </select><br><br>
    <label for="lieu_obtention">Lieu d'Obtention:</label>
    <input type="text" id="lieu_obtention" name="lieu_obtention" required><br><br>
    <label for="date_obtention">Date d'Obtention:</label>
    <input type="date" id="date_obtention" name="date_obtention" required><br><br>
    <button type="submit">Ajouter Diplôme</button>
</form>
