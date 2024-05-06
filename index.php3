<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
</head>
<body>
    <h1>Ajouter un nouvel utilisateur</h1>
    <form action="add_user.php" method="post">
        Nom: <input type="text" name="nom" required><br>
        Prénom: <input type="text" name="prenom" required><br>
        Date de Naissance: <input type="date" name="date_naissance" required><br>
        Téléphone: <input type="text" name="telephone" required><br>
        Email: <input type="email" name="email" required><br>
        Diplômes: <input type="text" name="diplomes" required><br>
        <input type="submit" value="Ajouter">
    </form>
</body>
</html>
