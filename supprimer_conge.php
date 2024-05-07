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

    $id_conge = $_POST['id'];

    $deleteQuery = $conn->prepare("DELETE FROM demandesConges WHERE id = ?");
    $deleteQuery->bind_param("i", $id_conge);
    if ($deleteQuery->execute()) {
        http_response_code(200); // OK
        exit;
    } else {
        http_response_code(500); // Internal Server Error
        exit;
    }
} else {
    http_response_code(405); // Method Not Allowed
    exit;
}
?>
