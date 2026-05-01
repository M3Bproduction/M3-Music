<?php
// api.php - API pour récupérer les données

header('Content-Type: application/json');
require 'config.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

switch($action) {
    case 'get_artistes':
        $result = $conn->query("SELECT id, nom, description FROM artistes ORDER BY nom");
        $artistes = [];
        while($row = $result->fetch_assoc()) {
            $artistes[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $artistes]);
        break;

    case 'get_albums':
        if ($id) {
            $stmt = $conn->prepare("SELECT a.id, a.titre, a.description, a.date_sortie, art.nom as artiste_nom 
                                  FROM albums a
                                  JOIN artistes art ON a.artiste_id = art.id
                                  WHERE a.artiste_id = ?
                                  ORDER BY a.titre");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT a.id, a.titre, a.description, a.date_sortie, art.nom as artiste_nom 
                                   FROM albums a
                                   JOIN artistes art ON a.artiste_id = art.id
                                   ORDER BY a.titre");
        }
        
        $albums = [];
        while($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $albums]);
        break;

    case 'get_chansons':
        if ($id) {
            $stmt = $conn->prepare("SELECT c.id, c.titre, c.artiste_principale, c.description, 
                                         a.titre as album_titre, art.nom as artiste_nom
                                  FROM chansons c
                                  JOIN albums a ON c.album_id = a.id
                                  JOIN artistes art ON c.artiste_id = art.id
                                  WHERE c.album_id = ?
                                  ORDER BY c.titre");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT c.id, c.titre, c.artiste_principale, c.description,
                                         a.titre as album_titre, art.nom as artiste_nom
                                  FROM chansons c
                                  JOIN albums a ON c.album_id = a.id
                                  JOIN artistes art ON c.artiste_id = art.id
                                  ORDER BY c.titre LIMIT 100");
        }
        
        $chansons = [];
        while($row = $result->fetch_assoc()) {
            $chansons[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $chansons]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
}

$conn->close();
?>
