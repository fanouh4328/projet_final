<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 

$conn = new mysqli("localhost", "root", "", "membres");
$conn->set_charset("utf8mb4");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $mdp = $_POST["mdp"] ?? '';

    if (!$email || !$mdp) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        // Requête préparée
        $stmt = $conn->prepare("SELECT id_membre, mdp FROM membre WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_membre, $hash);
            $stmt->fetch();
            if (password_verify($mdp, $hash)) {
                $_SESSION["id_membre"] = $id_membre;
                header("Location: accueil.php");
                exit();
            } else {
                $message = "Mot de passe incorrect.";
            }
        } else {
            $message = "Email non trouvé.";
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body, html {
      height: 100%;
      background-color: #f8f9fa;
    }
    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 15px;
    }
    .login-box {
      max-width: 400px;
      width: 100%;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-box">
      <h2 class="mb-4 text-center">Se connecter</h2>

      <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label for="email" class="form-label">Email :</label>
          <input type="email" id="email" name="email" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
        </div>
        <div class="mb-4">
          <label for="mdp" class="form-label">Mot de passe :</label>
          <input type="password" id="mdp" name="mdp" class="form-control" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
      </form>

      <p class="mt-3 text-center">
        Pas encore inscrit ? <a href="inscription.php">Inscription</a>
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
