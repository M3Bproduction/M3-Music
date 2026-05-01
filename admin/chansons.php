<?php
// admin/chansons.php - Gestion des chansons

session_start();
require 'functions.php';
check_admin();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $album_id = $_POST['album_id'] ?? 0;
        $artiste_id = $_POST['artiste_id'] ?? 0;
        $titre = sanitize_input($_POST['titre'] ?? '');
        $artiste_principale = sanitize_input($_POST['artiste_principale'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $fichier_audio = sanitize_input($_POST['fichier_audio'] ?? '');
        $image = sanitize_input($_POST['image'] ?? '');
        
        if ($album_id && $artiste_id && $titre) {
            if (add_chanson($album_id, $artiste_id, $titre, $artiste_principale, $description, $fichier_audio, $image)) {
                $message = 'Chanson ajoutée avec succès!';
                $action = 'list';
            } else {
                $error = 'Erreur lors de l\'ajout de la chanson';
            }
        } else {
            $error = 'Veuillez remplir tous les champs obligatoires';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if (delete_chanson($id)) {
            $message = 'Chanson supprimée avec succès!';
            $action = 'list';
        } else {
            $error = 'Erreur lors de la suppression';
        }
    }
}

$chansons = get_chansons();
$albums = get_albums();
$artistes = get_artistes();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chansons - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            padding: 10px 15px;
            border-radius: 5px;
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
        
        .form-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
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
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
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
                    <li><a href="albums.php"><i class="fas fa-compact-disc"></i> Albums</a></li>
                    <li><a href="chansons.php" class="active"><i class="fas fa-music"></i> Chansons</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <header>
                <h1>Chansons</h1>
                <?php if ($action !== 'add'): ?>
                    <a href="?action=add" class="btn"><i class="fas fa-plus"></i> Ajouter</a>
                <?php else: ?>
                    <a href="?" class="btn" style="background: #6c757d;"><i class="fas fa-arrow-left"></i> Retour</a>
                <?php endif; ?>
            </header>

            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($action === 'add'): ?>
                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label for="artiste_id">Artiste *</label>
                            <select id="artiste_id" name="artiste_id" required>
                                <option value="">-- Sélectionnez un artiste --</option>
                                <?php foreach ($artistes as $art): ?>
                                    <option value="<?php echo $art['id']; ?>"><?php echo $art['nom']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="album_id">Album *</label>
                            <select id="album_id" name="album_id" required>
                                <option value="">-- Sélectionnez un album --</option>
                                <?php foreach ($albums as $alb): ?>
                                    <option value="<?php echo $alb['id']; ?>"><?php echo $alb['titre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="titre">Titre de la chanson *</label>
                            <input type="text" id="titre" name="titre" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="artiste_principale">Artiste principal</label>
                            <input type="text" id="artiste_principale" name="artiste_principale">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Ajouter</button>
                        <a href="?" class="btn" style="background: #6c757d;">Annuler</a>
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
                                <th>Album</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chansons as $chanson): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($chanson['titre']); ?></td>
                                    <td><?php echo htmlspecialchars($chanson['artiste_nom']); ?></td>
                                    <td><?php echo htmlspecialchars($chanson['album_titre']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                            <input type="hidden" name="id" value="<?php echo $chanson['id']; ?>">
                                            <button type="submit" class="btn" style="padding: 5px 10px; background: #dc3545; font-size: 12px;">Supprimer</button>
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
