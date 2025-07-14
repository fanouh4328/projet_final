<?php
session_start();
$conn = new mysqli("localhost", "root", "", "membres");

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_objet = intval($_POST['id_objet']);
    $jours = intval($_POST['jours']);
    $id_utilisateur = $_SESSION['id_membre'] ?? 1; 

    $date_emprunt = date('Y-m-d');
    $date_retour = date('Y-m-d', strtotime("+$jours days "));

    $stmt = $conn->prepare("INSERT INTO emprunt (id_objet, id_membre, date_emprunt, date_retour) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_objet, $id_membre, $date_emprunt, $date_retour);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    header("Location: liste_objets.php.php");
    exit();
}
?>
