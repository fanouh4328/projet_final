<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "membres");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $categorie = $_POST['categorie'];
    $membre_id = $_SESSION['user_id'];

    // Insertion de l'objet
    $stmt = $conn->prepare("INSERT INTO objets (membre_id, nom, description, categorie) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $membre_id, $nom, $description, $categorie);

    if ($stmt->execute()) {
        $objet_id = $stmt->insert_id;

        // Gestion des images
        $isFirst = true;
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                $filename = uniqid() . '-' . basename($_FILES['images']['name'][$index]);
                $destination = "img/" . $filename;

                if (move_uploaded_file($tmpName, $destination)) {
                    $est_principale = $isFirst ? 1 : 0;
                    $stmt_img = $conn->prepare("INSERT INTO images (objet_id, nom_fichier, est_principale) VALUES (?, ?, ?)");
                    $stmt_img->bind_param("isi", $objet_id, $filename, $est_principale);
                    $stmt_img->execute();
                    $stmt_img->close();
                    $isFirst = false;
                }
            }
        } else {
            // Aucune image, image par défaut
            $stmt_img = $conn->prepare("INSERT INTO images (objet_id, nom_fichier, est_principale) VALUES (?, 'default.jpg', 1)");
            $stmt_img->bind_param("i", $objet_id);
            $stmt_img->execute();
            $stmt_img->close();
        }

        header("Location: listes_objets.php");
        exit();
    } else {
        $message = "Erreur d'ajout de l'objet.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Ajouter un objet</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h2>Ajouter un objet</h2>
  <?= $message ? '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>' : '' ?>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Nom</label>
      <input type="text" name="nom" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label>Catégorie</label>
      <select name="categorie" class="form-select">
        <option>Livres</option>
        <option>Vêtements</option>
        <option>Électronique</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Images (plusieurs possible)</label>
      <input type="file" name="images[]" class="form-control" multiple>
    </div>
    <button type="submit" class="btn btn-primary">Ajouter</button>
  </form>
</div>
</body>
</html>