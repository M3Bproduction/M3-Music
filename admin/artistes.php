<?php
// admin/artistes.php - Gestion des artistes

session_start();
require 'functions.php';
check_admin();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $nom = sanitize_input($_POST['nom'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $dossier = sanitize_input($_POST['dossier'] ?? '');
        $image = sanitize_input($_POST['image'] ?? '');
        
        if ($nom && $dossier) {
            if (add_artiste($nom, $description, $dossier, $image)) {
                $message = 'Artiste ajouté avec succès!';
                $action = 'list';
            } else {
                $error = 'Erreur lors de l\'ajout de l\'artiste';
            }
        } else {
            $error = 'Veuillez remplir tous les champs obligatoires';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $nom = sanitize_input($_POST['nom'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $dossier = sanitize_input($_POST['dossier'] ?? '');
        $image = sanitize_input($_POST['image'] ?? '');
        
        if ($nom && $dossier) {
            if (update_artiste($id, $nom, $description, $dossier, $image)) {
                $message = 'Artiste modifié avec succès!';
                $action = 'list';
            } else {
                $error = 'Erreur lors de la modification';
            }
        } else {
            $error = 'Veuillez remplir tous les champs obligatoires';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if (delete_artiste($id)) {
            $message = 'Artiste supprimé avec succès!';
            $action = 'list';
        } else {
            $error = 'Erreur lors de la suppression';
        }
    }
}

$artistes = get_artistes();
$artiste = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $artiste = get_artiste($_GET['id']);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artistes - Admin Panel</title>
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
        
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .sidebar nav ul li a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .sidebar .logout {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        
        .sidebar .logout a {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: background 0.3s;
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
        
        header h1 {
            font-size: 28px;
            color: #333;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
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
            color: #333;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
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
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .action-link {
            padding: 5px 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
            transition: background 0.2s;
        }
        
        .action-link:hover {
            background: #0056b3;
        }
        
        .action-link.delete {
            background: #dc3545;
        }
        
        .action-link.delete:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2><i class="fas fa-music"></i> M3'Music</h2>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="artistes.php" class="active"><i class="fas fa-users"></i> Artistes</a></li>
                    <li><a href="albums.php"><i class="fas fa-compact-disc"></i> Albums</a></li>
                    <li><a href="chansons.php"><i class="fas fa-music"></i> Chansons</a></li>
                </ul>
            </nav>
            <div class="logout">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1><?php echo $action === 'edit' ? 'Modifier Artiste' : 'Artistes'; ?></h1>
                <?php if ($action === 'list'): ?>
                    <a href="?action=add" class="btn"><i class="fas fa-plus"></i> Ajouter</a>
                <?php else: ?>
                    <a href="?" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
                <?php endif; ?>
            </header>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Form -->
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="form-container">
                    <form method="POST">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $artiste['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="nom">Nom de l'artiste *</label>
                            <input type="text" id="nom" name="nom" required value="<?php echo $artiste['nom'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="dossier">Dossier (nom du répertoire) *</label>
                            <input type="text" id="dossier" name="dossier" required value="<?php echo $artiste['dossier'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo $artiste['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image (URL ou chemin)</label>
                            <input type="text" id="image" name="image" placeholder="Ex: images/artiste.jpg" value="<?php echo $artiste['image'] ?? ''; ?>">
                            <small style="color: #666; font-size: 12px;">Chemin vers l'image de l'artiste (optionnel)</small>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn"><?php echo $action === 'edit' ? 'Modifier' : 'Ajouter'; ?></button>
                            <a href="?" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <?php if ($action === 'list'): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Dossier</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($artistes as $art): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($art['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($art['dossier']); ?></td>
                                    <td><?php echo substr(htmlspecialchars($art['description']), 0, 50); ?>...</td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&id=<?php echo $art['id']; ?>" class="action-link"><i class="fas fa-edit"></i> Modifier</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                                <input type="hidden" name="id" value="<?php echo $art['id']; ?>">
                                                <button type="submit" class="action-link delete" style="padding: 0; background: none; border: none; cursor: pointer;">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
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
