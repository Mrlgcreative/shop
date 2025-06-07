<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    // Vérifier si l'utilisateur existe déjà
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = 'Nom d\'utilisateur déjà utilisé.';
    } else {
        // Ajouter le nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $email);
        if ($stmt->execute()) {
            $success = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
        } else {
            $error = 'Erreur lors de l\'inscription. Veuillez réessayer.';
        }
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
    <title>Inscription Admin - MiraShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    <style>
        /* Styles spécifiques pour la page d'inscription */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            position: relative;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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
            max-width: 580px;
            width: 95%;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .register-card {
            background: #fff;
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 3rem 2.5rem;
            box-shadow: var(--shadow-2xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .register-logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .register-logo span {
            color: var(--secondary-color);
        }

        .register-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-subtitle {
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            transition: color var(--transition-smooth);
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #bbf7d0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all var(--transition-smooth);
            background: var(--white);
            color: var(--dark-gray);
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-group input:focus + label {
            color: var(--primary-color);
        }

        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-smooth);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .auth-footer p {
            color: var(--medium-gray);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .auth-footer .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all var(--transition-smooth);
        }

        .auth-footer .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideInDown 0.5s ease-out;
        }

        .message.error {
            background: linear-gradient(135deg, #fef2f2, #fecaca);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .message.success {
            background: linear-gradient(135deg, #f0fdf4, #bbf7d0);
            color: #16a34a;
            border-left: 4px solid #16a34a;
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

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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

        /* Styles responsifs */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .register-card {
                padding: 2rem 1.5rem;
            }

            .register-title {
                font-size: 1.25rem;
            }

            .register-subtitle {
                font-size: 0.875rem;
            }
        }

        /* Animation pour les champs de saisie */
        .form-group input:focus {
            animation: inputFocus 0.3s ease-out;
        }

        @keyframes inputFocus {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* Validation visuelle */
        .form-group input:valid {
            border-color: #16a34a;
        }

        .form-group input:invalid:not(:placeholder-shown) {
            border-color: #dc2626;
        }
    </style>

</head>
<body><body>
    <div class="floating-shapes"></div>
    
    <div class="container">
        <div class="register-card">
            <div class="register-header">
                <a href="login.php" class="register-logo">
                    🛍️ Mira<span>Shop</span>
                </a>
                <h1 class="register-title">👤 Inscription Administrateur</h1>
                <p class="register-subtitle">Créez un compte administrateur pour gérer votre boutique en ligne</p>
            </div>

            <?php if ($error): ?>
                <div class="message error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                    <script>
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    </script>
                </div>
            <?php endif; ?>

            <form method="POST" class="register-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">👤 Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" 
                               placeholder="Choisissez un nom d'utilisateur unique" 
                               required minlength="3" maxlength="50"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">📧 Adresse email</label>
                        <input type="email" id="email" name="email" 
                               placeholder="votre.email@exemple.com" 
                               required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">🔒 Mot de passe</label>
                        <input type="password" id="password" name="password" 
                               placeholder="Minimum 6 caractères" 
                               required minlength="6"
                               title="Le mot de passe doit contenir au moins 6 caractères">
                    </div>
                </div>

                <button type="submit" name="register" class="register-btn">
                    ✅ Créer le compte administrateur
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous avez déjà un compte ?</p>
                <a href="login.php" class="btn-outline">
                    🔑 Se connecter
                </a>
            </div>

            <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <p style="font-size: 0.875rem; color: var(--medium-gray);">
                    🔒 <strong>Sécurisé</strong> - Vos données sont protégées et chiffrées
                </p>
            </div>
        </div>
    </div>

    <script>
        // Animation d'entrée progressive
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.register-card');
            const formGroups = document.querySelectorAll('.form-group');
            
            // Animation de la carte principale
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);

            // Animation progressive des champs de formulaire
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    group.style.transition = 'all 0.4s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateX(0)';
                }, 300 + (index * 100));
            });
        });

        // Validation en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            // Validation du nom d'utilisateur
            username.addEventListener('input', function() {
                if (this.value.length >= 3) {
                    this.style.borderColor = '#16a34a';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#dc2626';
                }
            });

            // Validation de l'email
            email.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(this.value)) {
                    this.style.borderColor = '#16a34a';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#dc2626';
                }
            });

            // Validation du mot de passe
            password.addEventListener('input', function() {
                if (this.value.length >= 6) {
                    this.style.borderColor = '#16a34a';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#dc2626';
                }
            });
        });

        // Effet de survol sur le bouton
        document.querySelector('.register-btn').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });

        document.querySelector('.register-btn').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-2px)';
        });

        // Animation du logo
        document.querySelector('.register-logo').addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });

        document.querySelector('.register-logo').addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });

        // Gestion du formulaire avec feedback visuel
        document.querySelector('.register-form').addEventListener('submit', function(e) {
            const button = document.querySelector('.register-btn');
            button.innerHTML = '⏳ Création en cours...';
            button.disabled = true;
        });
    </script>
</body>
</html>