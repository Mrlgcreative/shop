<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "❌ Erreur : ID manquant.";
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Récupérer les informations de l'article
$stmt = $conn->prepare("SELECT * FROM Articles WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "❌ Erreur : Article non trouvé.";
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

$article = $result->fetch_assoc();
$stmt->close();

// Vérifier s'il y a des ventes liées à cet article
$stmt_ventes = $conn->prepare("SELECT COUNT(*) as count FROM Ventes WHERE id_article = ?");
$stmt_ventes->bind_param("i", $id);
$stmt_ventes->execute();
$ventes_result = $stmt_ventes->get_result();
$ventes_count = $ventes_result->fetch_assoc()['count'];
$stmt_ventes->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmer la suppression - MiraShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .confirmation-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .warning-icon {
            font-size: 4rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .confirmation-title {
            color: var(--danger-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .article-info {
            background: linear-gradient(135deg, #fef2f2, #fecaca);
            border-left: 4px solid var(--danger-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: var(--border-radius);
            text-align: left;
        }

        .article-info h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }

        .article-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: var(--border-radius);
        }

        .meta-label {
            font-size: 0.875rem;
            color: var(--medium-gray);
            margin-bottom: 0.25rem;
        }

        .meta-value {
            font-weight: 600;
            color: var(--dark-gray);
        }

        .warning-message {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: var(--border-radius);
            color: #92400e;
        }

        .warning-message strong {
            display: block;
            margin-bottom: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-smooth);
            box-shadow: var(--shadow-sm);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-gray);
            border: 1px solid var(--border-color);
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-smooth);
        }

        .btn-secondary:hover {
            background: var(--medium-gray);
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .confirmation-card {
                padding: 1.5rem;
                margin: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .article-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">🛍️ Mira<span>Shop</span></a>
                </div>
                <div class="header-title">
                    <h1>⚠️ CONFIRMATION DE SUPPRESSION</h1>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="confirmation-container">
                <div class="confirmation-card">
                    <div class="warning-icon">⚠️</div>
                    
                    <h1 class="confirmation-title">Supprimer cet article ?</h1>
                    
                    <p style="color: var(--medium-gray); margin-bottom: 1.5rem;">
                        Cette action est <strong>irréversible</strong>. L'article sera définitivement supprimé de votre inventaire.
                    </p>

                    <!-- Informations de l'article -->
                    <div class="article-info">
                        <h3><?php echo htmlspecialchars($article['nom']); ?></h3>
                        <p style="color: var(--medium-gray); margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($article['description']); ?>
                        </p>
                        
                        <div class="article-meta">
                            <div class="meta-item">
                                <span class="meta-label">Prix</span>
                                <span class="meta-value"><?php echo number_format($article['prix'], 2); ?> FC</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Stock</span>
                                <span class="meta-value"><?php echo $article['quantité']; ?> unités</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Valeur totale</span>
                                <span class="meta-value"><?php echo number_format($article['prix'] * $article['quantité'], 2); ?> FC</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($ventes_count > 0): ?>
                        <div class="warning-message">
                            <strong>⚠️ Attention !</strong>
                            Cet article a été vendu <strong><?php echo $ventes_count; ?> fois</strong>. 
                            Sa suppression supprimera également toutes les ventes associées de l'historique.
                        </div>
                    <?php endif; ?>

                    <!-- Boutons d'action -->
                    <div class="action-buttons">
                        <a href="delete.php?id=<?php echo $id; ?>&confirmed=1" class="btn-danger" 
                           onclick="return confirm('Êtes-vous absolument sûr de vouloir supprimer cet article ?')">
                            🗑️ Oui, supprimer définitivement
                        </a>
                        <a href="index.php" class="btn-secondary">
                            ↩️ Non, annuler
                        </a>
                    </div>

                    <p style="font-size: 0.875rem; color: var(--medium-gray); margin-top: 1.5rem;">
                        💡 <strong>Conseil :</strong> Si vous souhaitez simplement retirer cet article de la vente, 
                        vous pouvez <a href="modifier.php?id=<?php echo $id; ?>" style="color: var(--primary-color);">modifier sa quantité</a> 
                        pour la mettre à 0 au lieu de le supprimer.
                    </p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.confirmation-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });

        // Effet de survol sur les boutons
        document.querySelectorAll('.btn-danger, .btn-secondary').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>