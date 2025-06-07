<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Récupérer les statistiques générales
$stats = [];

// Total des articles
$result = $conn->query("SELECT COUNT(*) as total FROM Articles");
$stats['total_articles'] = $result->fetch_assoc()['total'];

// Total des ventes
$result = $conn->query("SELECT COUNT(*) as total FROM Ventes");
$stats['total_ventes'] = $result->fetch_assoc()['total'];

// Chiffre d'affaires total par devise
$result_fc = $conn->query("SELECT SUM(prix * quantité) as total FROM Ventes WHERE devise = 'FC' OR devise IS NULL");
$ca_total_fc = $result_fc->fetch_assoc()['total'];
$stats['chiffre_affaires_fc'] = $ca_total_fc ? $ca_total_fc : 0;

$result_usd = $conn->query("SELECT SUM(prix * quantité) as total FROM Ventes WHERE devise = 'USD'");
$ca_total_usd = $result_usd->fetch_assoc()['total'];
$stats['chiffre_affaires_usd'] = $ca_total_usd ? $ca_total_usd : 0;

// Articles en rupture de stock
$result = $conn->query("SELECT COUNT(*) as total FROM Articles WHERE quantité = 0");
$stats['rupture_stock'] = $result->fetch_assoc()['total'];

// Articles avec stock faible (moins de 5)
$result = $conn->query("SELECT COUNT(*) as total FROM Articles WHERE quantité > 0 AND quantité < 5");
$stats['stock_faible'] = $result->fetch_assoc()['total'];

// Ventes du mois en cours
$result = $conn->query("SELECT COUNT(*) as total FROM Ventes WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())");
$stats['ventes_mois'] = $result->fetch_assoc()['total'];

// CA du mois en cours par devise
$result_fc_mois = $conn->query("SELECT SUM(prix * quantité) as total FROM Ventes WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE()) AND (devise = 'FC' OR devise IS NULL)");
$ca_mois_fc = $result_fc_mois->fetch_assoc()['total'];
$stats['ca_mois_fc'] = $ca_mois_fc ? $ca_mois_fc : 0;

$result_usd_mois = $conn->query("SELECT SUM(prix * quantité) as total FROM Ventes WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE()) AND devise = 'USD'");
$ca_mois_usd = $result_usd_mois->fetch_assoc()['total'];
$stats['ca_mois_usd'] = $ca_mois_usd ? $ca_mois_usd : 0;

// Top 5 des articles les plus vendus avec devise
$top_articles = $conn->query("
    SELECT a.nom, a.prix, a.devise as article_devise, SUM(v.quantité) as total_vendu, SUM(v.prix * v.quantité) as ca_article, v.devise as vente_devise
    FROM Articles a 
    JOIN Ventes v ON a.id = v.id_article
    GROUP BY a.id, a.nom, a.prix, a.devise, v.devise 
    ORDER BY total_vendu DESC 
    LIMIT 5
");

// Articles en rupture ou stock faible avec devise
$articles_alerte = $conn->query("
    SELECT id, nom, quantité, prix, devise 
    FROM Articles 
    WHERE quantité <= 5 
    ORDER BY quantité ASC
");

// Évolution des ventes (7 derniers jours) par devise
$ventes_evolution = $conn->query("
    SELECT DATE(date) as date, COUNT(*) as nb_ventes, 
           SUM(CASE WHEN devise = 'FC' OR devise IS NULL THEN prix * quantité ELSE 0 END) as ca_jour_fc,
           SUM(CASE WHEN devise = 'USD' THEN prix * quantité ELSE 0 END) as ca_jour_usd
    FROM Ventes 
    WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date)
    ORDER BY date DESC
");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiraShop - Tableau de Bord</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📊</text></svg>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
         /* Statistiques mensuelles */
            .monthly-stats {
                display: flex;
                gap: 1.5rem;
                margin: 2rem 0;
                flex-wrap: wrap;
                justify-content: center;
                align-items: stretch;
            }

            /* Cartes statistiques mini */
            .stat-card-mini {
                flex: 1;
                min-width: 280px;
                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                border-radius: 16px;
                padding: 1.5rem;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.2);
                position: relative;
                overflow: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                backdrop-filter: blur(10px);
            }

            .stat-card-mini::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: var(--gradient);
                opacity: 0.8;
            }

            .stat-card-mini:hover {
                transform: translateY(-8px);
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
                border-color: rgba(255, 255, 255, 0.4);
            }

            /* Variantes de couleurs */
            .stat-card-mini.success {
                --gradient: linear-gradient(135deg, #10b981, #059669);
                --accent-color: #10b981;
                background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
            }

            .stat-card-mini.primary {
                --gradient: linear-gradient(135deg, #3b82f6, #2563eb);
                --accent-color: #3b82f6;
                background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
            }

            .stat-card-mini.warning {
                --gradient: linear-gradient(135deg, #f59e0b, #d97706);
                --accent-color: #f59e0b;
                background: linear-gradient(135deg, #fffbeb 0%, #fefce8 100%);
            }

            /* Contenu de la carte */
            .stat-card-mini {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            /* Icône */
            .mini-icon {
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                background: var(--accent-color);
                color: white;
                border-radius: 12px;
                flex-shrink: 0;
                position: relative;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            }

            .mini-icon::after {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: 12px;
                background: linear-gradient(45deg, rgba(255,255,255,0.2), transparent);
                pointer-events: none;
            }

            /* Contenu texte */
            .mini-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .mini-number {
                font-size: 1.75rem;
                font-weight: 700;
                color: #1f2937;
                line-height: 1.1;
                font-family: 'Inter', sans-serif;
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .mini-label {
                font-size: 0.875rem;
                font-weight: 500;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                opacity: 0.8;
            }

            /* Animations */
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .stat-card-mini {
                animation: slideInUp 0.6s ease-out;
            }

            /* Responsive design */
            @media (max-width: 1024px) {
                .monthly-stats {
                    gap: 1rem;
                }
                
                .stat-card-mini {
                    min-width: 250px;
                    padding: 1.25rem;
                }
                
                .mini-icon {
                    width: 50px;
                    height: 50px;
                    font-size: 1.5rem;
                }
                
                .mini-number {
                    font-size: 1.5rem;
                }
            }

            @media (max-width: 768px) {
                .monthly-stats {
                    flex-direction: column;
                    gap: 1rem;
                }
                
                .stat-card-mini {
                    min-width: 100%;
                    padding: 1rem;
                }
                
                .mini-number {
                    font-size: 1.25rem;
                }
                
                .mini-label {
                    font-size: 0.8rem;
                }
            }

            /* Effet de focus pour l'accessibilité */
            .stat-card-mini:focus-within {
                outline: 2px solid var(--accent-color);
                outline-offset: 2px;
            }

            /* Animation au hover des icônes */
            .stat-card-mini:hover .mini-icon {
                transform: scale(1.1) rotate(5deg);
                transition: transform 0.3s ease;
            }

            /* Effet de pulsation pour les nombres */
            .mini-number {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.8;
                }
            }

            /* Style spécial pour les grand écrans */
            @media (min-width: 1200px) {
                .stat-card-mini {
                    padding: 2rem;
                }
                
                .mini-icon {
                    width: 70px;
                    height: 70px;
                    font-size: 2.25rem;
                }
                
                .mini-number {
                    font-size: 2rem;
                }
            }
    </style>
</head>

<body class="page-transition">
    <div class="container">
        <?php
    
    include 'navbar.php'
    ?>
        <!-- Header moderne -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">🛍️ Mira<span>Shop</span></a>
                </div>
                <div class="header-title">
                    <h1>📊 TABLEAU DE BORD</h1>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
                </div>
            </div>
        </header>

        <!-- Carte principale -->
        <div class="main-card">
           

            <!-- Statistiques du mois -->
            <div class="section-title">
                <h2>📈 Performance du mois</h2>
            </div>
              <div class="monthly-stats">
                <div class="stat-card-mini success">
                    <div class="mini-icon">🎯</div>
                    <div class="mini-content">
                        <div class="mini-number"><?php echo $stats['ventes_mois']; ?></div>
                        <div class="mini-label">Ventes ce mois</div>
                    </div>
                </div>

                <div class="stat-card-mini primary">
                    <div class="mini-icon">💰</div>
                    <div class="mini-content">
                        <div class="mini-number"><?php echo number_format($stats['ca_mois_fc'], 2); ?> FC</div>
                        <div class="mini-label">CA FC ce mois</div>
                    </div>
                </div>

                <div class="stat-card-mini primary">
                    <div class="mini-icon">💵</div>
                    <div class="mini-content">
                        <div class="mini-number"><?php echo number_format($stats['ca_mois_usd'], 2); ?> USD</div>
                        <div class="mini-label">CA USD ce mois</div>
                    </div>
                </div>

                <div class="stat-card-mini warning">
                    <div class="mini-icon">📉</div>
                    <div class="mini-content">
                        <div class="mini-number"><?php echo $stats['stock_faible']; ?></div>
                        <div class="mini-label">Stock faible</div>
                    </div>
                </div>
            </div><!-- Contenu organisé en tableaux -->
            <div class="dashboard-content">
                <!-- Section Top Articles en tableau -->
                <div class="dashboard-section">
                    <h3>🏆 Top des ventes</h3>
                    <div class="table-container">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Article</th>
                                    <th>Quantité vendue</th>
                                    <th>Chiffre d'affaires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($top_articles->num_rows > 0): ?>
                                    <?php $rank = 1; while ($article = $top_articles->fetch_assoc()): ?>
                                        <tr class="rank-row rank-<?php echo $rank; ?>">
                                            <td>
                                                <span class="rank-badge rank-<?php echo $rank; ?>"><?php echo $rank; ?></span>
                                            </td>
                                            <td class="article-name-cell">
                                                <strong><?php echo htmlspecialchars($article['nom']); ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="quantity-badge"><?php echo $article['total_vendu']; ?></span>
                                            </td>                                            <td class="text-right">
                                                <?php 
                                                $devise = isset($article['vente_devise']) && !empty($article['vente_devise']) ? $article['vente_devise'] : 'FC'; 
                                                ?>
                                                <span class="revenue-amount"><?php echo number_format($article['ca_article'], 2) . ' ' . $devise; ?></span>
                                            </td>
                                        </tr>
                                        <?php $rank++; ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            <div class="empty-icon">📊</div>
                                            <p>Aucune vente enregistrée</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>                <!-- Section Evolution des ventes -->
                <div class="dashboard-section">
                    <h3>📈 Évolution des ventes (7 derniers jours)</h3>
                    <div class="table-container">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Nombre de ventes</th>
                                    <th>CA FC</th>
                                    <th>CA USD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($ventes_evolution->num_rows > 0): ?>
                                    <?php while ($evolution = $ventes_evolution->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo date('d/m/Y', strtotime($evolution['date'])); ?></strong></td>
                                            <td class="text-center">
                                                <span class="sales-count"><?php echo $evolution['nb_ventes']; ?></span>
                                            </td>
                                            <td class="text-right">
                                                <span class="revenue-amount"><?php echo number_format($evolution['ca_jour_fc'], 2); ?> FC</span>
                                            </td>
                                            <td class="text-right">
                                                <span class="revenue-amount"><?php echo number_format($evolution['ca_jour_usd'], 2); ?> USD</span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="empty-state">
                                            <div class="empty-icon">📈</div>
                                            <p>Aucune donnée d'évolution disponible</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Alertes Stock en tableau -->
                <div class="dashboard-section">
                    <h3>⚠️ Alertes de stock</h3>
                    <div class="table-container">                        <table class="modern-table alert-table">
                            <thead>
                                <tr>
                                    <th>Statut</th>
                                    <th>Article</th>
                                    <th>Prix</th>
                                    <th>Stock restant</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($articles_alerte->num_rows > 0): ?>
                                    <?php while ($article = $articles_alerte->fetch_assoc()): ?>
                                        <tr class="alert-row <?php echo $article['quantité'] == 0 ? 'critical' : 'warning'; ?>">
                                            <td class="text-center">
                                                <span class="alert-status <?php echo $article['quantité'] == 0 ? 'critical' : 'warning'; ?>">
                                                    <?php echo $article['quantité'] == 0 ? '🔴 Critique' : '🟡 Attention'; ?>
                                                </span>
                                            </td>
                                            <td class="article-name-cell">
                                                <strong><?php echo htmlspecialchars($article['nom']); ?></strong>
                                            </td>
                                            <td class="text-right">
                                                <?php 
                                                $devise_article = isset($article['devise']) && !empty($article['devise']) ? $article['devise'] : 'FC'; 
                                                ?>
                                                <span class="price-badge"><?php echo number_format($article['prix'], 2) . ' ' . $devise_article; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="stock-badge <?php echo $article['quantité'] == 0 ? 'critical' : 'warning'; ?>">
                                                    <?php echo $article['quantité']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="modifier.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">
                                                    Réapprovisionner
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="empty-state success">
                                            <div class="empty-icon">✅</div>
                                            <p>Tous les stocks sont suffisants</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <!-- Boutons d'action -->
            <div class="action-buttons">
                <a href="ajouter.php" class="btn btn-primary">➕ Ajouter un Article</a>
                <a href="vente.php" class="btn btn-success">💰 Enregistrer une vente</a>
                <a href="index.php" class="btn btn-info">📊 Retour à l'accueil</a>
                <a href="liste_ventes.php" class="btn btn-warning">📋 Liste des ventes</a>
            </div>
        </div>
        <?php
    
    include 'footer.php'
    ?>
    </div>  
      <script>
        // Animation des compteurs
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number[data-target]');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        if (current > target) current = target;
                          if (counter.textContent.includes('FC')) {
                            counter.textContent = Math.floor(current).toLocaleString('fr-FR') + ' FC';
                        } else {
                            counter.textContent = Math.floor(current);
                        }
                        
                        requestAnimationFrame(updateCounter);
                    }
                };
                
                updateCounter();
            });
        }

        // Animation d'entrée des tableaux
        function animateElements() {
            const cards = document.querySelectorAll('.stat-card, .dashboard-section, .action-card, .table-container');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.animation = 'slideInUp 0.6s ease forwards';
                        }, index * 100);
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                observer.observe(card);
            });
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            animateCounters();
            animateElements();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>