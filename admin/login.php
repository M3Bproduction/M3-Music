<?php
// admin/login.php - Page de connexion

session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    // Mot de passe par défaut: "admin123"
    // Générez le vôtre avec: php -r "echo password_hash('votre_mot_de_passe', PASSWORD_BCRYPT);"
    $correct_hash = '$2y$10$ANhdUz7cVxj4ocomHXGBoekfAYefcOyUwrvo/rG.Dvs/8FfrKyTZ6'; // Remplacez!
    
    if (password_verify($password, $correct_hash)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $error = 'Mot de passe incorrect';
    }
}

// Si vous n'avez pas de mot de passe, générez-le avec:
// php -r "echo password_hash('votre_mot_de_passe', PASSWORD_BCRYPT);"
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - M3'Music</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #0c5aa0;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>M3'Music</h1>
            <p>Panneau d'Administration</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            
            <button type="submit">Se connecter</button>
        </form>
        
        <div class="info-box">
            <strong>⚠️ Important:</strong>
            Changez le mot de passe par défaut (admin123) dès que possible pour la sécurité!
        </div>
    </div>
</body>
</html>
