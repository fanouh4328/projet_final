<?php
$conn = new mysqli("localhost", "root", "", "membres");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $date_naissance = $_POST["date_naissance"];
    $genre = $_POST["genre"];
    $email = $_POST["email"];
    $ville = $_POST["ville"];
    $mdp = password_hash($_POST["mdp"], PASSWORD_DEFAULT);
    $image_profil = $_FILES["image"]["name"];
    $image_tmp = $_FILES["image"]["tmp_name"];
    
    move_uploaded_file($image_tmp, "uploads/" . $image_profil);

    $stmt = $conn->prepare("INSERT INTO membre (nom, date_naissance, genre, email, ville, mdp, image_profil)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nom, $date_naissance, $genre, $email, $ville, $mdp, $image_profil);
    $stmt->execute();

    echo "Inscription réussie. <a href='login.php'>Se connecter</a>";
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="nom" placeholder="Nom" required><br>
    <input type="date" name="date_naissance" required><br>
    <select name="genre" required>
        <option value="H">Homme</option>
        <option value="F">Femme</option>
    </select><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="text" name="ville" placeholder="Ville" required><br>
    <input type="password" name="mdp" placeholder="Mot de passe" required><br>
    <input type="file" name="image" accept="image/*"><br>
    <button type="submit">S'inscrire</button>
</form>
