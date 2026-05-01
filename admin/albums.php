<?php
// admin/albums.php - Gestion des albums

session_start();
require 'functions.php';
check_admin();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $artiste_id = $_POST['artiste_id'] ?? 0;
        $titre = sanitize_input($_POST['titre'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $date_sortie = $_POST['date_sortie'] ?? null;
        $image = sanitize_input($_POST['image'] ?? '');
        
        if ($artiste_id && $titre) {
            if (add_album($artiste_id, $titre, $description, $date_sortie, $image)) {
                $message = 'Album ajouté avec succès!';
                $action = 'list';
            } else {
                $error = 'Erreur lors de l\'ajout de l\'album';
            }
        } else {
            $error = 'Veuillez remplir tous les champs obligatoires';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $titre = sanitize_input($_POST['titre'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $date_sortie = $_POST['date_sortie'] ?? null;
        $image = sanitize_input($_POST['image'] ?? '');
        
        if ($titre) {
            if (update_album($id, $titre, $description, $date_sortie, $image)) {
                $message = 'Album modifié avec succès!';
                $action = 'list';
            } else {
                $error = 'Erreur lors de la modification';
            }
        } else {
            $error = 'Veuillez remplir tous les champs obligatoires';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if (delete_album($id)) {
            $message = 'Album supprimé avec succès!';
            $action = 'list';
        } else {
            $error = 'Erreur lors de la suppression';
        }
    }
}

$albums = get_albums();
$artistes = get_artistes();
$album = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $album = $albums[0] ?? null;
    foreach ($albums as $alb) {
        if ($alb['id'] == $_GET['id']) {
            $album = $alb;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 15px;
        }
        
        .sidebar nav ul {
            list-style: none;
        }
        
        .sidebar nav ul li {
            margin-bottom: 15px;
        }
        
        .sidebar nav ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .form-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2><i class="fas fa-music"></i> M3'Music</h2>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="artistes.php"><i class="fas fa-users"></i> Artistes</a></li>
                    <li><a href="albums.php" class="active"><i class="fas fa-compact-disc"></i> Albums</a></li>
                    <li><a href="chansons.php"><i class="fas fa-music"></i> Chansons</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <header>
                <h1><?php echo $action === 'edit' ? 'Modifier Album' : 'Albums'; ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=add" class="btn"><i class="fas fa-plus"></i> Ajouter</a>
                <?php else: ?>
                    <a href="?" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                <?php endif; ?>
            </header>

            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="form-container">
                    <form method="POST">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $album['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="artiste_id">Artiste *</label>
                            <select id="artiste_id" name="artiste_id" required <?php echo $action === 'edit' ? 'disabled' : ''; ?>>
                                <option value="">-- Sélectionnez un artiste --</option>
                                <?php foreach ($artistes as $art): ?>
                                    <option value="<?php echo $art['id']; ?>" <?php echo ($album && $album['artiste_id'] == $art['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($art['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="titre">Titre de l'album *</label>
                            <input type="text" id="titre" name="titre" required value="<?php echo $album['titre'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_sortie">Date de sortie</label>
                            <input type="date" id="date_sortie" name="date_sortie" value="<?php echo $album['date_sortie'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo $album['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn"><?php echo $action === 'edit' ? 'Modifier' : 'Ajouter'; ?></button>
                        <a href="?" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Artiste</th>
                                <th>Date de sortie</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($albums as $alb): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($alb['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($alb['artiste_nom']); ?></td>
                                    <td><?php echo $alb['date_sortie'] ?? '-'; ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $alb['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">Modifier</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                            <input type="hidden" name="id" value="<?php echo $alb['id']; ?>">
                                            <button type="submit" class="btn" style="padding: 5px 10px; font-size: 12px; background: #dc3545;">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
