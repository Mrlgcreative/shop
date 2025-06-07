<?php
session_start();

// Si l'utilisateur confirme la déconnexion
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    session_unset();
    session_destroy();
    header("Location: login.php?message=logout");
    exit();
}

// Si l'utilisateur annule
if (isset($_GET['confirm']) && $_GET['confirm'] === 'no') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - MiraShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
</head>
<body class="logout-page">
    <div class="logout-container">
        <div class="logout-card">
            <div class="logout-header">
                <div class="brand-logo">
                    <a href="index.php">🛍️ Mira<span>Shop</span></a>
                </div>
                <div class="logout-title">
                    <div class="logout-icon">🚪</div>
                    <h1>Déconnexion</h1>
                    <p class="subtitle">Gestion de votre session</p>
                </div>
            </div>            <div class="confirmation-section">
                <div class="confirmation-visual">
                    <div class="confirmation-icon">
                        🤔
                    </div>
                    <div class="confirmation-pulse"></div>
                </div>
                
                <div class="confirmation-content">
                    <h2>Êtes-vous sûr de vouloir vous déconnecter ?</h2>
                    <p>Vous devrez vous reconnecter pour accéder à votre espace de gestion des ventes.</p>
                    
                    <div class="session-info">
                        <div class="info-item">
                            <span class="info-icon">👤</span>
                            <span>Session active</span>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">🕐</span>
                            <span>Connecté depuis aujourd'hui</span>
                        </div>
                    </div>
                </div>
            </div>            <div class="action-buttons">
                <a href="logout.php?confirm=yes" class="btn btn-logout">
                    <span class="btn-icon">✅</span>
                    <span class="btn-text">Oui, me déconnecter</span>
                </a>
                <a href="logout.php?confirm=no" class="btn btn-stay">
                    <span class="btn-icon">🔄</span>
                    <span class="btn-text">Rester connecté</span>
                </a>
            </div>

            <div class="quick-actions">
                <p class="actions-label">Actions rapides :</p>
                <div class="action-links">
                    <a href="index.php" class="quick-link">
                        <span>🏠</span> Accueil
                    </a>
                    <a href="vente.php" class="quick-link">
                        <span>💰</span> Ventes
                    </a>
                    <a href="rapport.php" class="quick-link">
                        <span>📊</span> Rapports
                    </a>
                </div>
            </div>
        </div>
    </div>    <style>
        /* ===========================================
           STYLES MODERNES POUR LOGOUT.PHP
           =========================================== */

        .logout-page {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        .logout-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            pointer-events: none;
        }

        .logout-container {
            width: 100%;
            max-width: 500px;
            z-index: 1;
            position: relative;
        }

        .logout-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logout-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .brand-logo a {
            font-size: 2.5rem;
            font-weight: 800;
            color: #6366f1;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .brand-logo a:hover {
            transform: scale(1.05);
            color: #4f46e5;
        }

        .brand-logo span {
            color: #f59e0b;
        }

        .logout-title {
            margin-bottom: 1rem;
        }

        .logout-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .logout-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            letter-spacing: -0.025em;
        }

        .subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0.5rem 0 0 0;
            font-weight: 500;
        }

        .confirmation-section {
            margin-bottom: 2.5rem;
        }

        .confirmation-visual {
            position: relative;
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .confirmation-icon {
            font-size: 4rem;
            z-index: 2;
            position: relative;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .confirmation-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulseRing 2s infinite;
        }

        @keyframes pulseRing {
            0% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 1;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        .confirmation-content {
            text-align: center;
        }

        .confirmation-content h2 {
            color: #1f2937;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .confirmation-content p {
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 1rem;
        }

        .session-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(99, 102, 241, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #4b5563;
            font-weight: 500;
        }

        .info-icon {
            font-size: 1rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-logout {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-stay {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .btn-stay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        }

        .btn-icon {
            font-size: 1.1rem;
        }

        .quick-actions {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .actions-label {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .action-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .quick-link:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .logout-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .session-info {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .action-links {
                flex-direction: column;
                align-items: center;
            }

            .quick-link {
                min-width: 150px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .logout-card {
                padding: 1.5rem 1rem;
            }

            .brand-logo a {
                font-size: 2rem;
            }

            .logout-title h1 {
                font-size: 1.5rem;
            }

            .confirmation-content h2 {
                font-size: 1.25rem;
            }

            .btn {
                padding: 0.875rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
</body>
</html>
