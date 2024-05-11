<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401); // Unauthorized
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        http_response_code(400); // Bad Request
        exit;
    }

    $id_absence = $_POST['id'];


    $deleteQuery = $conn->prepare("DELETE FROM absence WHERE id = ?");
    $deleteQuery->bind_param("i", $id_absence);
    $deleteQuery->execute();
    $deleteQuery->close();

       
        http_response_code(200); // OK
        header("Location: gestion_absences.php");
        
}
?>
