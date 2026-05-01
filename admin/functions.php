<?php
// admin/functions.php - Fonctions utilitaires pour l'admin

require '../config.php';

// Vérifier la session admin
function check_admin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

// Vérifier le mot de passe admin
function verify_admin_password($password) {
    // Utilisez un mot de passe personnalisé. Format: password_hash('votre_mot_de_passe', PASSWORD_BCRYPT)
    $correct_hash = '$2y$10$YWRtaW4xMjM='; // Remplacez par votre hash
    return password_verify($password, $correct_hash);
}

// Obtenir tous les artistes
function get_artistes() {
    global $conn;
    $result = $conn->query("SELECT * FROM artistes ORDER BY nom");
    $artistes = [];
    while($row = $result->fetch_assoc()) {
        $artistes[] = $row;
    }
    return $artistes;
}

// Obtenir un artiste par ID
function get_artiste($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM artistes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Ajouter un artiste
function add_artiste($nom, $description, $dossier, $image = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO artistes (nom, description, dossier, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $description, $dossier, $image);
    return $stmt->execute();
}

// Modifier un artiste
function update_artiste($id, $nom, $description, $dossier, $image = null) {
    global $conn;
    $stmt = $conn->prepare("UPDATE artistes SET nom = ?, description = ?, dossier = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nom, $description, $dossier, $image, $id);
    return $stmt->execute();
}

// Supprimer un artiste
function delete_artiste($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM artistes WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Obtenir tous les albums d'un artiste
function get_albums($artiste_id = null) {
    global $conn;
    if ($artiste_id) {
        $stmt = $conn->prepare("SELECT a.*, art.nom as artiste_nom FROM albums a 
                              JOIN artistes art ON a.artiste_id = art.id 
                              WHERE a.artiste_id = ? 
                              ORDER BY a.titre");
        $stmt->bind_param("i", $artiste_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT a.*, art.nom as artiste_nom FROM albums a 
                               JOIN artistes art ON a.artiste_id = art.id 
                               ORDER BY a.titre");
    }
    
    $albums = [];
    while($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }
    return $albums;
}

// Ajouter un album
function add_album($artiste_id, $titre, $description, $date_sortie, $image = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO albums (artiste_id, titre, description, date_sortie, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $artiste_id, $titre, $description, $date_sortie, $image);
    return $stmt->execute();
}

// Modifier un album
function update_album($id, $titre, $description, $date_sortie, $image = null) {
    global $conn;
    $stmt = $conn->prepare("UPDATE albums SET titre = ?, description = ?, date_sortie = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $titre, $description, $date_sortie, $image, $id);
    return $stmt->execute();
}

// Supprimer un album
function delete_album($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM albums WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Obtenir toutes les chansons
function get_chansons($album_id = null) {
    global $conn;
    if ($album_id) {
        $stmt = $conn->prepare("SELECT c.*, a.titre as album_titre, art.nom as artiste_nom 
                              FROM chansons c 
                              JOIN albums a ON c.album_id = a.id 
                              JOIN artistes art ON c.artiste_id = art.id 
                              WHERE c.album_id = ? 
                              ORDER BY c.titre");
        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT c.*, a.titre as album_titre, art.nom as artiste_nom 
                               FROM chansons c 
                               JOIN albums a ON c.album_id = a.id 
                               JOIN artistes art ON c.artiste_id = art.id 
                               ORDER BY c.titre LIMIT 50");
    }
    
    $chansons = [];
    while($row = $result->fetch_assoc()) {
        $chansons[] = $row;
    }
    return $chansons;
}

// Ajouter une chanson
function add_chanson($album_id, $artiste_id, $titre, $artiste_principale, $description, $fichier_audio = null, $image = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO chansons (album_id, artiste_id, titre, artiste_principale, description, fichier_audio, image) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $album_id, $artiste_id, $titre, $artiste_principale, $description, $fichier_audio, $image);
    return $stmt->execute();
}

// Modifier une chanson
function update_chanson($id, $titre, $artiste_principale, $description, $fichier_audio = null, $image = null) {
    global $conn;
    $stmt = $conn->prepare("UPDATE chansons SET titre = ?, artiste_principale = ?, description = ?, fichier_audio = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $titre, $artiste_principale, $description, $fichier_audio, $image, $id);
    return $stmt->execute();
}

// Supprimer une chanson
function delete_chanson($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM chansons WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Valider l'entrée
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

?>
