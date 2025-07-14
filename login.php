<?php
session_start();
$conn = new mysqli("localhost", "root", "", "membres");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $mdp = $_POST["mdp"];

    $stmt = $conn->prepare("SELECT id_membre, mdp FROM membre WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_membre, $hash);
        $stmt->fetch();
        if (password_verify($mdp, $hash)) {
            $_SESSION["id_membre"] = $id_membre;
            header("Location: acceuil.php");
            exit();
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Email non trouvé.";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="mdp" placeholder="Mot de passe" required><br>
    <button type="submit">Se connecter</button>
</form>


