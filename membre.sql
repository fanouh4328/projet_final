CREATE DATABASE IF NOT EXISTS membres
use membres;

-- Table des membres
CREATE TABLE membre (
    id_membre INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    date_naissance DATE,
    genre ENUM('H', 'F'),
    email VARCHAR(100),
    ville VARCHAR(100),
    mdp VARCHAR(255),
    image_profil VARCHAR(255)
);

-- Table des catégories d'objet
CREATE TABLE objets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membre_id INT,
    nom VARCHAR(255),
    description TEXT,
    categorie VARCHAR(100),
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Table des objets
CREATE TABLE objet (
    id_objet INT AUTO_INCREMENT PRIMARY KEY,
    nom_objet VARCHAR(100),
    id_categorie INT,
    id_membre INT,
    FOREIGN KEY (id_categorie) REFERENCES categorie_objet(id_categorie),
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre)
);

-- Table des images des objets
CREATE TABLE images_objet (
    id_image INT AUTO_INCREMENT PRIMARY KEY,
    id_objet INT,
    nom_image VARCHAR(255),
    FOREIGN KEY (id_objet) REFERENCES objet(id_objet)
);

-- Table des emprunts
CREATE TABLE emprunt (
    id_emprunt INT AUTO_INCREMENT PRIMARY KEY,
    id_objet INT,
    id_membre INT,
    date_emprunt DATE,
    date_retour DATE,
    FOREIGN KEY (id_objet) REFERENCES objet(id_objet),
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre)
);
-- Table images
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    objet_id INT,
    nom_fichier VARCHAR(255),
    est_principale BOOLEAN DEFAULT FALSE
);





INSERT INTO membre (nom, date_naissance, genre, email, ville, mdp, image_profil) VALUES
('Alice', '1990-03-12', 'F', 'alice@example.com', 'Antananarivo', 'pass123', 'alice.jpg'),
('Bob', '1985-07-22', 'H', 'bob@example.com', 'Fianarantsoa', 'pass456', 'bob.jpg'),
('Charlie', '1992-11-05', 'H', 'charlie@example.com', 'Toamasina', 'pass789', 'Charlie.jpg'),
('Dina', '1995-01-19', 'F', 'dina@example.com', 'Toliara', 'pass321', 'Dina.jpg');

INSERT INTO categorie_objet (nom_categorie) VALUES
('esthétique'), ('bricolage'), ('mécanique'), ('cuisine');

INSERT INTO objet (nom_objet, id_categorie, id_membre) VALUES
('Sèche-cheveux', 1, 1),
('Lisseur', 1, 1),
('Tournevis', 2, 1),
('Marteau', 2, 1),
('Clé à molette', 3, 1),
('Pompe à vélo', 3, 1),
('Fouet', 4, 1),
('Mixeur', 4, 1),
('Balance cuisine', 4, 1),
('Brosse coiffante', 1, 1);

INSERT INTO images_objet (id_objet, nom_image) VALUES
(1, 'seche_cheveux.jpg'),
(2, 'lisseur.jpg'),
(3, 'tournevis.jpg'),
(4, 'marteau.jpg'),
(5, 'cle_molette.jpg');

INSERT INTO emprunt (id_objet, id_membre, date_emprunt, date_retour) VALUES
(1, 2, '2025-07-01', '2025-07-10'),
(2, 3, '2025-07-02', '2025-07-12'),
(5, 4, '2025-07-03', '2025-07-13'),
(6, 1, '2025-07-04', '2025-07-14'),
(8, 2, '2025-07-05', '2025-07-15'),
(10, 3, '2025-07-06', '2025-07-16'),
(12, 4, '2025-07-07', '2025-07-17'),
(15, 1, '2025-07-08', '2025-07-18'),
(18, 2, '2025-07-09', '2025-07-19'),
(20, 3, '2025-07-10', '2025-07-20');

SELECT o.id_objet, o.nom_objet, c.nom_categorie,
       e.date_retour
FROM objet o
JOIN categorie_objet c ON o.id_categorie = c.id_categorie
LEFT JOIN emprunt e ON o.id_objet = e.id_objet
    AND e.date_retour >= CURDATE()
ORDER BY c.nom_categorie, o.nom_objet;
