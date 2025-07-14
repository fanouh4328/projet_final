<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

session_start();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $nom = trim($_POST['nom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    // Vérification simple (à améliorer selon besoins)
    if (!$nom || !$date_naissance || !$genre || !$email || !$ville || !$mdp) {
        $message = '<div class="alert alert-danger">Tous les champs obligatoires doivent être remplis.</div>';
    } else {
        // Connexion à la base
        $conn = new mysqli("localhost", "root", "", "membres");
        if ($conn->connect_error) {
            die("Erreur de connexion BDD : " . $conn->connect_error);
        }

        // Gestion de l'image (optionnel)
        $imageNom = null;
        if (!empty($_FILES['image']['name'])) {
            $chemin_tmp = $_FILES['image']['tmp_name'];
            $imageNom = null;
if (!empty($_FILES['image']['name'])) {
    $chemin_tmp = $_FILES['image']['tmp_name'];
    $imageNom = uniqid() . "-" . basename($_FILES['image']['name']);
    $chemin_destination = "img/" . $imageNom;

    move_uploaded_file($chemin_tmp, $chemin_destination);
}
        }

        // Sécurisation mot de passe (hash)
        $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);

        // Préparer la requête (préparation pour éviter injection SQL)
        $stmt = $conn->prepare("INSERT INTO membre (nom, date_naissance, genre, email, ville, mdp, image_profil) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nom, $date_naissance, $genre, $email, $ville, $mdpHash, $imageNom);

        if ($stmt->execute()) {
            // Inscription OK, redirection vers acceuil.php
            $_SESSION['user_email'] = $email; // exemple stockage session
            header("Location: acceuil.php");
            exit();
        } else {
            $message = '<div class="alert alert-danger">Erreur lors de l\'inscription : ' . htmlspecialchars($stmt->error) . '</div>';
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
      padding: 2rem;
    }
    .form-container {
      max-width: 500px;
      margin: 0 auto;
      background-color: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }
    .form-title {
      margin-bottom: 1.5rem;
      font-weight: 600;
      font-size: 1.5rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <div class="form-title">Inscription</div>
    <?= $message ?? '' ?>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="nom" class="form-label">Nom complet</label>
        <input type="text" name="nom" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="date_naissance" class="form-label">Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="genre" class="form-label">Genre</label>
        <select name="genre" class="form-select" required>
          <option value="">Choisir</option>
          <option value="H">Homme</option>
          <option value="F">Femme</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="ville" class="form-label">Ville</label>
        <input type="text" name="ville" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="mdp" class="form-label">Mot de passe</label>
        <input type="password" name="mdp" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">Image de profil</label>
        <input type="file" name="image" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
    </form>
    <div class="mt-3 text-center">
      <small>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></small>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>