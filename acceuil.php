<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_membre'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli("localhost", "root", "", "emprunt_objets");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer les catégories pour le filtre
$result_cat = $conn->query("SELECT * FROM categorie_objet");

// Récupérer la catégorie choisie en GET
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';

// Construire la requête SQL selon filtre
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Liste des objets</title>
</head>
<body>

<h1>Bienvenue sur le site d'emprunt d'objets</h1>
<p><a href="logout.php">Se déconnecter</a></p>

<form method="GET">
    <label for="categorie">Filtrer par catégorie :</label>
    <select name="categorie" id="categorie" onchange="this.form.submit()">
        <option value="all">Toutes</option>
        <?php while ($cat = $result_cat->fetch_assoc()) : ?>
            <option value="<?= $cat['id_categorie'] ?>" <?= ($categorie_filter == $cat['id_categorie']) ? "selected" : "" ?>>
                <?= htmlspecialchars($cat['nom_categorie']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<table border="1" cellpadding="8" cellspacing="0" style="margin-top:20px;">
    <thead>
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
                    <td><?= $objet['date_retour'] ? $objet['date_retour'] : "Disponible" ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr><td colspan="3">Aucun objet trouvé.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
