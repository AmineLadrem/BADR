<?php
$pdo = new PDO('mysql:host=localhost;dbname=gestion_utilisateurs', 'root', '');

// Traitement de l'envoi du formulaire
if (isset($_POST["submit"])) {
    $file_name = $_FILES["fileToUpload"]["name"];
    $file_content = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

    $query = "INSERT INTO fichiers (file_name, file_content) VALUES (:file_name, :file_content)";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['file_name' => $file_name, 'file_content' => $file_content]);
}

// Récupération des fichiers stockés
$query = "SELECT file_name, file_content FROM fichiers";
$stmt = $pdo->query($query);
$fichiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
    <style>
        .miniature {
            display: inline-block;
            margin: 10px;
            border: 1px solid #ccc;
            padding: 5px;
            cursor: pointer;
        }

        .miniature img {
            max-width: 100px;
            max-height: 100px;
        }

        #imageAgrandie {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        #imageAgrandie img {
            max-width: 90%;
            max-height: 90%;
        }
    </style>
</head>
<body>
    <h2>Uploader un fichier</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Uploader" name="submit">
    </form>

    <h2>Fichiers stockés</h2>
    <div id="galerie">
        <?php foreach ($fichiers as $fichier): ?>
            <div class="miniature" onclick="afficherImage('data:image;base64,<?= base64_encode($fichier['file_content']) ?>')">
                <img src="data:image;base64,<?= base64_encode($fichier['file_content']) ?>" alt="<?= $fichier['file_name'] ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <div id="imageAgrandie">
        <span onclick="masquerImage()" style="position: absolute; top: 5px; right: 10px; cursor: pointer;">&#10006;</span>
        <img id="imageAffichee">
    </div>

    <script>
        function afficherImage(src) {
            var imageAffichee = document.getElementById('imageAffichee');
            imageAffichee.src = src;
            document.getElementById('imageAgrandie').style.display = 'block';
        }

        function masquerImage() {
            document.getElementById('imageAgrandie').style.display = 'none';
        }
    </script>
</body>
</html>
