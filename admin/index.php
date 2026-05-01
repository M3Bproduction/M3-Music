<?php
// admin/index.php - Accueil du panneau d'administration

session_start();
require 'functions.php';
check_admin();

$artistes_count = count(get_artistes());
$albums_count = count(get_albums());
$chansons_count = count(get_chansons());

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - M3'Music</title>
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
        
        .sidebar .logout a:hover {
            background: rgba(255, 255, 255, 0.3);
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stat-card i {
            font-size: 40px;
            color: #667eea;
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        
        .quick-actions {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .quick-actions h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .action-btn i {
            display: block;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
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
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="artistes.php"><i class="fas fa-users"></i> Artistes</a></li>
                    <li><a href="albums.php"><i class="fas fa-compact-disc"></i> Albums</a></li>
                    <li><a href="chansons.php"><i class="fas fa-music"></i> Chansons</a></li>
                    <li><a href="playlists.php"><i class="fas fa-list"></i> Playlists</a></li>
                </ul>
            </nav>
            <div class="logout">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Tableau de Bord</h1>
                <p><?php echo date('d/m/Y H:i'); ?></p>
            </header>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3>Artistes</h3>
                        <div class="number"><?php echo $artistes_count; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-compact-disc"></i>
                    <div>
                        <h3>Albums</h3>
                        <div class="number"><?php echo $albums_count; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-music"></i>
                    <div>
                        <h3>Chansons</h3>
                        <div class="number"><?php echo $chansons_count; ?></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Actions Rapides</h2>
                <div class="actions-grid">
                    <a href="artistes.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        Ajouter Artiste
                    </a>
                    <a href="albums.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        Ajouter Album
                    </a>
                    <a href="chansons.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        Ajouter Chanson
                    </a>
                    <a href="artistes.php" class="action-btn">
                        <i class="fas fa-eye"></i>
                        Voir Artistes
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
