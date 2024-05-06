<?php
// Vérifier si l'ID du diplôme à supprimer est présent dans l'URL
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Includez votre fichier de connexion à la base de données
    include_once 'db.php';

    // Préparez la requête SQL de suppression
    $sql = "DELETE FROM diplomes WHERE id_diplome = ?";

    if($stmt = $conn->prepare($sql)) {
        // Liaison des paramètres
        $stmt->bind_param("i", $param_id);

        // Définir les paramètres
        $param_id = $_GET['id'];

        // Exécuter la requête
        if($stmt->execute()) {
            // Rediriger vers la page de gestion des diplômes après suppression
            header("location: gestion_diplomess.php");
            exit();
        } else {
            echo "Erreur lors de la suppression.";
        }
    }
     
    // Fermer la déclaration
    $stmt->close();
     
    // Fermer la connexion
    $conn->close();
} else {
    // L'ID n'est pas présent dans l'URL, redirection
    header("location: gestion_diplomes.php");
    exit();
}
?>
