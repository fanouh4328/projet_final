<?php
session_start();
if (!isset($_SESSION['id_membre'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "membres");
$conn->set_charset("utf8mb4");

// Récupération des infos du membre connecté
$id_membre = $_SESSION['id_membre'];
$req_user = $conn->prepare("SELECT nom, image_profil FROM membre WHERE id_membre = ?");
$req_user->bind_param("i", $id_membre);
$req_user->execute();
$user = $req_user->get_result()->fetch_assoc();

// Liste des catégories
$result_cat = $conn->query("SELECT * FROM categorie_objet");

// Filtrage
$categorie_filter = $_GET['categorie'] ?? '';

if ($categorie_filter && $categorie_filter !== 'all') {
    $stmt = $conn->prepare("SELECT o.nom_objet, c.nom_categorie, e.date_retour
                            FROM objet o
                            JOIN categorie_objet c ON o.id_categorie = c.id_categorie
                            LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE()
                            WHERE c.id_categorie = ?
                            ORDER BY o.nom_objet");
    $stmt->bind_param("i", $categorie_filter);
} else {
    $stmt = $conn->prepare("SELECT o.nom_objet, c.nom_categorie, e.date_retour
                            FROM objet o
                            JOIN categorie_objet c ON o.id_categorie = c.id_categorie
                            LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE()
                            ORDER BY c.nom_categorie, o.nom_objet");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil - Emprunt d'objets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .table td, .table th {
      vertical-align: middle;
    }
    .profile-img {
      width: 35px;
      height: 35px;
      object-fit: cover;
      border-radius: 50%;
    }
  </style>
</head>
<body class="bg-light">

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"> Emprunt d'objets</a>
    <div class="d-flex align-items-center">
      <?php if ($user): ?>
        <span class="text-white me-3">Bonjour, <strong><?= htmlspecialchars($user['nom']) ?></strong></span>
        <?php if ($user['img']) : ?>
          <img src="uploads/<?= htmlspecialchars($user['img']) ?>" class="profile-img me-2" alt="Profil">
        <?php endif; ?>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-outline-light">Déconnexion</a>
    </div>
  </div>
</nav>

<!-- Contenu principal -->
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-primary">Liste des objets</h1>
  </div>

  <!-- Filtre -->
  <form method="GET" class="mb-4">
    <div class="row g-2 align-items-end">
      <div class="col-md-6">
        <label for="categorie" class="form-label">Filtrer par catégorie :</label>
        <select name="categorie" id="categorie" class="form-select" onchange="this.form.submit()">
          <option value="all">Toutes</option>
          <?php while ($cat = $result_cat->fetch_assoc()) : ?>
            <option value="<?= $cat['id_categorie'] ?>" <?= ($categorie_filter == $cat['id_categorie']) ? "selected" : "" ?>>
              <?= htmlspecialchars($cat['nom_categorie']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
  </form>

  <!-- Tableau des objets -->
  <div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm rounded">
      <thead class="table-dark">
        <tr>
          <th>Objet</th>
          <th>Catégorie</th>
          <th>Disponibilité</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0) : ?>
          <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
              <td><?= htmlspecialchars($row['nom_objet']) ?></td>
              <td><?= htmlspecialchars($row['nom_categorie']) ?></td>
              <td>
                <?php if ($row['date_retour']) : ?>
                  <span class="badge bg-warning text-dark">
                    Emprunté jusqu’au <?= date("d/m/Y", strtotime($row['date_retour'])) ?>
                  </span>
                <?php else : ?>
                  <span class="badge bg-success">Disponible</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else : ?>
          <tr><td colspan="3" class="text-center">Aucun objet trouvé.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
