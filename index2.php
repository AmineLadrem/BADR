<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formulaire d'inscription</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div id="formContainer">
        <h2>Ajouter un utilisateur</h2>
        <form id="userForm">
            <input type="text" id="nom" placeholder="Nom" required>
            <input type="text" id="prenom" placeholder="Prénom" required>
            <input type="date" id="dateNaissance" placeholder="Date de naissance" required>
            <input type="text" id="telephone" placeholder="Numéro de téléphone" required>
            <input type="email" id="email" placeholder="Adresse email" required>
            <input type="text" id="diplomes" placeholder="Diplômes" required>
            <button type="button" onclick="ajouterUtilisateur()">Ajouter</button>
        </form>
    </div>

    <div id="tableContainer">
        <h2>Liste des Utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de Naissance</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Diplômes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            </tbody>
        </table>
    </div>

    <script>
    function ajouterUtilisateur() {
        var data = {
            nom: $('#nom').val(),
            prenom: $('#prenom').val(),
            dateNaissance: $('#dateNaissance').val(),
            telephone: $('#telephone').val(),
            email: $('#email').val(),
            diplomes: $('#diplomes').val()
        };

        $.post('add_user.php', data, function(response) {
            $('#tableBody').append(response);
            $('#userForm')[0].reset(); // Clear the form
        });
    }

    function supprimerUtilisateur(id) {
        $.post('delete_user.php', { id: id }, function(response) {
            $('#row-' + id).remove();
        });
    }

    function modifierUtilisateur(id) {
        var cells = $('#row-' + id).find('td').not(':last');
        if ($('#save-' + id).text() === '🖉') { // If in edit mode
            var data = {
                id: id,
                nom: cells.eq(0).find('input').val(),
                prenom: cells.eq(1).find('input').val(),
                dateNaissance: cells.eq(2).find('input').val(),
                telephone: cells.eq(3).find('input').val(),
                email: cells.eq(4).find('input').val(),
                diplomes: cells.eq(5).find('input').val()
            };
            $.post('update_user.php', data, function(response) {
                location.reload(); // Reload to see the updated data
            });
        } else {
            cells.each(function() {
                var content = $(this).text();
                $(this).html('<input type="text" value="' + content + '">');
            });
            $('#save-' + id).text('💾'); // Change to save icon
        }
    }

    $(document).ready(function() {
        $.get('fetch_users.php', function(data) {
            $('#tableBody').html(data);
        });
    });
    </script>
</body>
</html>
