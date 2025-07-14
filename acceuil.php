<?php
session_start();
if (!isset($_SESSION['id_membre'])) {
    header('Location: login.php');
    exit();
}
$conn = new mysqli("localhost", "root", "", "membres");
if ($conn->connect_error) die("Erreur: " . $conn->connect_error);

$result_cat = $conn->query("SELECT * FROM categorie_objet");
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';

if ($categorie_filter && $categorie_filter != 'all') {
    $stmt = $conn->prepare("SELECT o.id_objet, o.nom_objet, c.nom_categorie, e.date_retour
                            FROM objet o
                            JOIN categorie_objet c ON o.id_categorie = c.id_categorie
                            LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE()
                            WHERE c.id_categorie = ?
                            ORDER BY o.nom_objet");
    $stmt->bind_param("i", $categorie_filter);
} else {
    $stmt = $conn->prepare("SELECT o.id_objet, o.nom_objet, c.nom_categorie, e.date_retour
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
    <title>Accueil - Liste des objets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">Emprunt d'objets</a>
    <div>
      <a href="logout.php" class="btn btn-outline-light">Se déconnecter</a>
    </div>
  </div>
</nav>

<div class="container">

    <h1 class="mb-4">Liste des objets</h1>

    <form method="GET" class="mb-3 w-50">
      <label for="categorie" class="form-label">Filtrer par catégorie :</label>
      <select name="categorie" id="categorie" class="form-select" onchange="this.form.submit()">
        <option value="all">Toutes</option>
        <?php while ($cat = $result_cat->fetch_assoc()) : ?>
          <option value="<?= $cat['id_categorie'] ?>" <?= ($categorie_filter == $cat['id_categorie']) ? "selected" : "" ?>>
            <?= htmlspecialchars($cat['nom_categorie']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </form>

    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-primary">
          <tr>
            <th>Objet</th>
            <th>Catégorie</th>
            <th>Date retour (emprunt en cours)</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0) : ?>
            <?php while ($objet = $result->fetch_assoc()) : ?>
              <tr>
                <td><?= htmlspecialchars($objet['nom_objet']) ?></td>
                <td><?= htmlspecialchars($objet['nom_categorie']) ?></td>
                <td>
                  <?= $objet['date_retour'] ? 
                      date("d/m/Y", strtotime($objet['date_retour'])) : 
                      '<span class="badge bg-success">Disponible</span>' ?>
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
