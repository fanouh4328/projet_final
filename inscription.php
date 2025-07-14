<?php
session_start();
$conn = new mysqli("localhost", "root", "", "membres");
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"] ?? '');
    $date_naissance = $_POST["date_naissance"] ?? '';
    $genre = $_POST["genre"] ?? '';
    $email = trim($_POST["email"] ?? '');
    $ville = trim($_POST["ville"] ?? '');
    $mdp = $_POST["mdp"] ?? '';

    if (!$nom || !$date_naissance || !$genre || !$email || !$ville || !$mdp) {
        $message = '<div class="alert alert-warning">Veuillez remplir tous les champs obligatoires.</div>';
    } else {
        $stmt = $conn->prepare("SELECT id_membre FROM membre WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = '<div class="alert alert-danger">Cet email est déjà utilisé.</div>';
        } else {
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
            $image_profil = "";

            if (!empty($_FILES["image"]["name"])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES["image"]["type"], $allowed_types)) {
                    $image_profil = uniqid() . "_" . basename($_FILES["image"]["name"]);
                    if (!move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $image_profil)) {
                        $message = '<div class="alert alert-danger">Erreur lors de l\'upload de l\'image.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Format d\'image non supporté (jpeg, png, gif uniquement).</div>';
                }
            }

            if (!$message) {
                $stmt = $conn->prepare("INSERT INTO membre (nom, date_naissance, genre, email, ville, mdp, image_profil) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $nom, $date_naissance, $genre, $email, $ville, $mdp_hash, $image_profil);
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Inscription réussie. <a href="login.php">Connectez-vous ici</a>.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur : ' . htmlspecialchars($conn->error) . '</div>';
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Inscription</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body, html {
      height: 100%;
      background-color: #f8f9fa;
    }
    .register-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 15px;
    }
    .register-box {
      max-width: 500px;
      width: 100%;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    }
  </style>
</head>
<body>

<div class="register-wrapper">
  <div class="register-box">
    <h2 class="mb-4 text-center">Inscription</h2>

    <?= $message ?? '' ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="mb-3">
        <label for="nom" class="form-label">Nom :</label>
        <input type="text" name="nom" id="nom" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="date_naissance" class="form-label">Date de naissance :</label>
        <input type="date" name="date_naissance" id="date_naissance" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="genre" class="form-label">Genre :</label>
        <select name="genre" id="genre" class="form-select" required>
          <option value="" disabled selected>Choisir</option>
          <option value="H">Homme</option>
          <option value="F">Femme</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email :</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="ville" class="form-label">Ville :</label>
        <input type="text" name="ville" id="ville" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="mdp" class="form-label">Mot de passe :</label>
        <input type="password" name="mdp" id="mdp" class="form-control" required>
      </div>

      <div class="mb-4">
        <label for="image" class="form-label">Image de profil :</label>
        <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/gif">
      </div>

      <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
    </form>

    <p class="mt-3 text-center">
      Déjà inscrit ? <a href="login.php">Se connecter</a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
