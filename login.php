<?php

session_start();

include 'config.php';

$error = '';
$success = '';

// Gérer les messages de retour
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'logout') {
        $success = 'Vous avez été déconnecté avec succès.';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Requête pour vérifier les informations de connexion
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
        }
    } else {
        $error = 'Utilisateur non trouvé.';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MiraShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    <style>
        /* Styles spécifiques pour la page de connexion */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }

        .container {
            max-width: 550px;
            width: 150%;
            padding: 2rem 2rem;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-logo span {
            color: var(--secondary-color);
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .floating-shapes::before,
        .floating-shapes::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .floating-shapes::before {
            width: 200px;
            height: 200px;
            top: -100px;
            right: -100px;
            animation-delay: -2s;
        }

        .floating-shapes::after {
            width: 150px;
            height: 150px;
            bottom: -75px;
            left: -75px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="floating-shapes"></div>
    <div class="container">
        <div class="login-card">            <div class="login-header">
                <div class="login-logo">🛍️ Mira<span>Shop</span></div>
                <h1 class="login-title">Bienvenue</h1>
                <p class="login-subtitle">Connectez-vous pour accéder à votre espace de gestion</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="modern-form">
                <div class="form-group">
                    <label for="username">👤 Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Entrez votre nom d'utilisateur" required>
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Mot de passe</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Entrez votre mot de passe" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="login" class="btn btn-primary">
                        🚀 Se connecter
                    </button>
                </div>
                
                <div class="login-footer">
                    <a href="register.php" class="btn btn-secondary">
                        👨‍💼 Administration
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Animation des champs au focus
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Effet de chargement sur le bouton submit
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '⏳ Connexion...';
        });

        // Auto-hide des alertes après 5 secondes
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'fadeOut 0.5s ease-in forwards';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    </script>
</body>
</html>