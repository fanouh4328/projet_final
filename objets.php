<?php
$conn = new mysqli("localhost", "root", "", "membres");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer catégories
$result_cat = $conn->query("SELECT * FROM categorie_objet");

// Catégorie sélectionnée
$categorie_filter = $_GET['categorie'] ?? 'all';

// Préparer requête selon filtre
if ($categorie_filter !== 'all' && $categorie_filter !== '') {
    $stmt = $conn->prepare("
        SELECT o.id_objet, o.nom_objet, c.nom_categorie, e.date_retour
        FROM objet o
        INNER JOIN categorie_objet c ON o.id_categorie = c.id_categorie
        LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE()
        WHERE c.id_categorie = ?
        ORDER BY o.nom_objet ASC
    ");
    $stmt->bind_param("i", $categorie_filter);
} else {
    $stmt = $conn->prepare("
        SELECT o.id_objet, o.nom_objet, c.nom_categorie, e.date_retour
        FROM objet o
        INNER JOIN categorie_objet c ON o.id_categorie = c.id_categorie
        LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE()
        ORDER BY c.nom_categorie ASC, o.nom_objet ASC
    ");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Liste des objets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
      .card-img-top {
        height: 180px;
        object-fit: cover;
        background: #ddd; /* placeholder gris si pas d'image */
      }
    </style>
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="text-center mb-4">Liste des objets à emprunter</h1>

    <form method="GET" class="mx-auto mb-4" style="max-width: 360px;">
        <label for="categorie" class="form-label fw-semibold">Filtrer par catégorie :</label>
        <select id="categorie" name="categorie" class="form-select" onchange="this.form.submit()">
            <option value="all" <?= $categorie_filter === 'all' ? 'selected' : '' ?>>Toutes</option>
            <?php while ($cat = $result_cat->fetch_assoc()) : ?>
                <option value="<?= $cat['id_categorie'] ?>" <?= $categorie_filter == $cat['id_categorie'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom_categorie']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($result->num_rows > 0): ?>
    <div class="row g-4">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <!-- Image principale - tu dois adapter si tu as un champ image -->
                    <img src="images/balance.jpeg" alt="<?= htmlspecialchars($row['nom_objet']) ?>" class="card-img-top" />
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($row['nom_objet']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($row['nom_categorie']) ?></p>
                        <p>
                          <?php if ($row['date_retour']): ?>
                            <span class="badge bg-warning text-dark">
                              Emprunté jusqu'au <?= date('d/m/Y', strtotime($row['date_retour'])) ?>
                            </span>
                          <?php else: ?>
                            <span class="badge bg-success">Disponible</span>
                          <?php endif; ?>
                        </p>
                        <a href="fiche_objet.php?id=<?= $row['id_objet'] ?>" class="btn btn-primary mt-auto">Voir plus</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <p class="text-center fst-italic">Aucun objet trouvé.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
