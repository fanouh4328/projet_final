<?php
session_start();
$message = ""; // message d'erreur ou vide

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mdp = $_POST['mdp'] ?? '';

    // Connexion à la base de données
    $conn = new mysqli("localhost", "root", "", "membres");
    if ($conn->connect_error) {
        die("Erreur connexion BDD : " . $conn->connect_error);
    }

    // Préparer et exécuter la requête sécurisée
    $stmt = $conn->prepare("SELECT id, nom, mdp FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Vérifier le mot de passe (si stocké en clair, comparer directement)
        // Idéalement, utiliser password_verify si hashé
        if ($mdp === $user['mdp']) { 
            // Authentification réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];

            header("Location: acceuil.php");
            exit();
        } else {
            $message = "Mot de passe incorrect.";
        }
    } else {
        $message = "Email non trouvé.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color:rgb(170, 199, 241);
      padding: 2rem;
    }
    .form-container {
      max-width: 400px;
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
    <div class="form-title">Connexion</div>
    <?php if (isset($message) && $message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="mdp" class="form-label">Mot de passe</label>
        <input type="password" name="mdp" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
    <div class="mt-3 text-center">
      <small>Pas encore inscrit ? <a href="inscription.php">Créer un compte</a></small>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

