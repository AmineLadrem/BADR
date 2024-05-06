<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_utilisateurs";
// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, nom, prenom, date_naissance, telephone, email, diplomes FROM utilisateurs";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Date de Naissance</th><th>Téléphone</th><th>Email</th><th>Diplômes</th><th>Actions</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["nom"]."</td><td>".$row["prenom"]."</td><td>".$row["date_naissance"]."</td><td>".$row["telephone"]."</td><td>".$row["email"]."</td><td>".$row["diplomes"]."</td>";
        echo "<td><a href='update_user.php?id=".$row["id"]."'><img src='pencil.png' alt='Modifier'/></a> <a href='delete_user.php?id=".$row["id"]."'><img src='cross.png' alt='Supprimer'/></a></td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
