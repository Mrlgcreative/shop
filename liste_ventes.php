<?php
include 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les ventes avec prepared statement pour la sécurité
$sql = "SELECT Ventes.id, Articles.nom, Ventes.quantité, Ventes.prix, 
               (Ventes.quantité * Ventes.prix) as total, Ventes.date 
        FROM Ventes 
        JOIN Articles ON Ventes.id_article = Articles.id 
        ORDER BY Ventes.date DESC";
$result = $conn->query($sql);

// Calculer le total général
$total_general = 0;
$total_articles = 0;
if ($result->num_rows > 0) {
    $result_temp = $conn->query($sql);
    while ($row = $result_temp->fetch_assoc()) {
        $total_general += $row['total'];
        $total_articles += $row['quantité'];
    }
}

// Calculer les totaux journaliers
$sql_daily = "SELECT DATE(Ventes.date) as date_jour, 
                     SUM(Ventes.quantité * Ventes.prix) as total_jour,
                     COUNT(*) as nombre_ventes,
                     SUM(Ventes.quantité) as articles_vendus
              FROM Ventes 
              JOIN Articles ON Ventes.id_article = Articles.id 
              GROUP BY DATE(Ventes.date) 
              ORDER BY DATE(Ventes.date) DESC 
              LIMIT 30";
$result_daily = $conn->query($sql_daily);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Liste des Ventes - MiraShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="stylesheet" href="shop/table-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    
    <style>
        /* Styles pour les totaux journaliers */
        .daily-totals-card {
            margin: 2rem 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .daily-totals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }

        .daily-total-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #6366f1;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .daily-total-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        .daily-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }

        .date-main {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .date-year {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 0.2rem;
        }

        .weekday {
            font-size: 0.7rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-top: 0.5rem;
        }

        .daily-stats {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .daily-amount {
            margin-bottom: 0.5rem;
        }

        .amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #10b981;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .daily-details {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .transactions,
        .items {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .transactions::before {
            content: "🛒 ";
            margin-right: 0.5rem;
        }

        .items::before {
            content: "📦 ";
            margin-right: 0.5rem;
        }

        .daily-performance {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 80px;
        }

        .performance-bar {
            width: 60px;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .performance-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 4px;
            transition: width 1s ease;
        }

        .performance-text {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
        }

        .empty-daily {
            text-align: center;
            padding: 3rem;
            color: #64748b;
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-daily h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .empty-daily p {
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* Responsivité pour les petits écrans */
        @media (max-width: 768px) {
            .daily-totals-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0.5rem;
            }

            .daily-total-item {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .daily-date {
                min-width: auto;
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 1rem;
            }

            .weekday {
                margin-top: 0;
            }

            .daily-stats {
                width: 100%;
                text-align: center;
            }

            .daily-performance {
                width: 100%;
            }

            .performance-bar {
                width: 100%;
                max-width: 200px;
            }
        }

        /* Animation d'entrée pour les éléments journaliers */
        .daily-total-item {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        .daily-total-item:nth-child(1) { animation-delay: 0.1s; }
        .daily-total-item:nth-child(2) { animation-delay: 0.2s; }
        .daily-total-item:nth-child(3) { animation-delay: 0.3s; }
        .daily-total-item:nth-child(4) { animation-delay: 0.4s; }
        .daily-total-item:nth-child(5) { animation-delay: 0.5s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Amélioration des gradients */
        .daily-total-item:nth-child(odd) {
            border-left-color: #6366f1;
        }

        .daily-total-item:nth-child(even) {
            border-left-color: #10b981;
        }

        .daily-total-item:nth-child(even) .daily-date {
            background: linear-gradient(135deg, #10b981, #059669);
        }
    </style>
</head>
<body class="page-transition">    
    <div class="container">
        <?php
    
    include 'navbar.php'
    ?>
        <!-- Header moderne avec navigation -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">🛍️ Mira<span>Shop</span></a>
                </div>
                <div class="header-title">
                    <h1>📊 HISTORIQUE DES VENTES</h1>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
                </div>
            </div>
        </header>
        <main class="main-content">
            <!-- Statistiques en cartes -->
            <div class="daily-totals-card">
                <div class="card-header">
                    <h2>📊 Statistiques Générales</h2>
                    <p class="card-subtitle">Vue d'ensemble des performances de vente</p>
                </div>
                <div class="card-body">
                    <div class="daily-totals-grid">
                        <div class="daily-total-item">
                            <div class="daily-date">
                                <div class="date-main">💰</div>
                                <div class="weekday">CA Total</div>
                            </div>
                            <div class="daily-stats">
                                <div class="daily-amount">
                                    <span class="amount"><?php echo number_format($total_general, 2); ?> FC</span>
                                </div>
                                <div class="daily-details">
                                    <span class="transactions">Chiffre d'affaires total</span>
                                </div>
                            </div>
                            <div class="daily-performance">
                                <div class="performance-bar">
                                    <div class="performance-fill" style="width: 100%"></div>
                                </div>
                                <span class="performance-text">↗️ +15%</span>
                            </div>
                        </div>
                        <div class="daily-total-item">
                            <div class="daily-date">
                                <div class="date-main">📦</div>
                                <div class="weekday">Articles</div>
                            </div>
                            <div class="daily-stats">
                                <div class="daily-amount">
                                    <span class="amount"><?php echo $total_articles; ?></span>
                                </div>
                                <div class="daily-details">
                                    <span class="transactions">Articles vendus</span>
                                </div>
                            </div>
                            <div class="daily-performance">
                                <div class="performance-bar">
                                    <div class="performance-fill" style="width: 80%"></div>
                                </div>
                                <span class="performance-text">↗️ +8%</span>
                            </div>
                        </div>
                        <div class="daily-total-item">
                            <div class="daily-date">
                                <div class="date-main">🛒</div>
                                <div class="weekday">Ventes</div>
                            </div>
                            <div class="daily-stats">
                                <div class="daily-amount">
                                    <span class="amount"><?php echo $result->num_rows; ?></span>
                                </div>
                                <div class="daily-details">
                                    <span class="transactions">Transactions</span>
                                </div>
                            </div>
                            <div class="daily-performance">
                                <div class="performance-bar">
                                    <div class="performance-fill" style="width: 90%"></div>
                                </div>
                                <span class="performance-text">↗️ +12%</span>
                            </div>
                        </div>
                        <div class="daily-total-item">
                            <div class="daily-date">
                                <div class="date-main">📈</div>
                                <div class="weekday">Panier</div>
                            </div>
                            <div class="daily-stats">
                                <div class="daily-amount">
                                    <span class="amount"><?php echo $result->num_rows > 0 ? number_format($total_general / $result->num_rows, 2) : '0.00'; ?> FC</span>
                                </div>
                                <div class="daily-details">
                                    <span class="transactions">Panier moyen</span>
                                </div>
                            </div>
                            <div class="daily-performance">
                                <div class="performance-bar">
                                    <div class="performance-fill" style="width: 70%"></div>
                                </div>
                                <span class="performance-text">↗️ +5%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totaux journaliers -->
            <div class="card daily-totals-card">
                <div class="card-header">
                    <h2>📊 Totaux Journaliers</h2>
                    <p class="card-subtitle">Chiffre d'affaires par jour (30 derniers jours)</p>
                </div>
                <div class="card-body">
                    <div class="daily-totals-grid">
                        <?php if ($result_daily->num_rows > 0): ?>
                            <?php while ($daily = $result_daily->fetch_assoc()): ?>
                                <div class="daily-total-item">
                                    <div class="daily-date">
                                        <div class="date-main"><?php echo date('d/m', strtotime($daily['date_jour'])); ?></div>
                                        <div class="date-year"><?php echo date('Y', strtotime($daily['date_jour'])); ?></div>
                                        <div class="weekday"><?php 
                                            $weekdays = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                                            echo $weekdays[date('w', strtotime($daily['date_jour']))]; 
                                        ?></div>
                                    </div>
                                    <div class="daily-stats">
                                        <div class="daily-amount">
                                            <span class="amount"><?php echo number_format($daily['total_jour'], 2); ?> FC</span>
                                        </div>
                                        <div class="daily-details">
                                            <span class="transactions"><?php echo $daily['nombre_ventes']; ?> vente<?php echo $daily['nombre_ventes'] > 1 ? 's' : ''; ?></span>
                                            <span class="items"><?php echo $daily['articles_vendus']; ?> article<?php echo $daily['articles_vendus'] > 1 ? 's' : ''; ?></span>
                                        </div>
                                    </div>
                                    <div class="daily-performance">
                                        <?php 
                                        $avg_daily = $total_general / max(1, $result_daily->num_rows);
                                        $performance = ($daily['total_jour'] / max(1, $avg_daily)) * 100;
                                        ?>
                                        <div class="performance-bar">
                                            <div class="performance-fill" style="width: <?php echo min(100, $performance); ?>%"></div>
                                        </div>
                                        <span class="performance-text"><?php echo number_format($performance, 0); ?>%</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-daily">
                                <div class="empty-icon">📅</div>
                                <h3>Aucune vente enregistrée</h3>
                                <p>Les totaux journaliers apparaîtront ici</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tableau des ventes -->
            <div class="card table-card">
                <div class="card-header">
                    <h2>📋 Détail des Ventes</h2>
                    <p class="card-subtitle">Historique complet de toutes les transactions</p>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>🆔 ID</th>
                                    <th>📦 Article</th>
                                    <th>📊 Qté</th>
                                    <th>💰 Prix Unit.</th>
                                    <th>💵 Total</th>
                                    <th>📅 Date & Heure</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>                                        <tr class="sale-row fade-in-up">
                                            <td>
                                                <span class="badge badge-primary"><?php echo htmlspecialchars($row['id']); ?></span>
                                            </td>
                                            <td class="article-name">
                                                <?php echo htmlspecialchars($row['nom']); ?>
                                            </td>
                                            <td>
                                                <span class="quantity-badge"><?php echo htmlspecialchars($row['quantité']); ?></span>
                                            </td>
                                            <td class="price">
                                                <?php echo number_format($row['prix'], 2); ?> FC
                                            </td>
                                            <td class="total-amount">
                                                <?php echo number_format($row['total'], 2); ?> FC
                                            </td>
                                            <td class="date-time">
                                                <div class="date"><?php echo date('d/m/Y', strtotime($row['date'])); ?></div>
                                                <div class="time"><?php echo date('H:i', strtotime($row['date'])); ?></div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-icon">📭</div>
                                            <h3>Aucune vente enregistrée</h3>
                                            <p>Commencez par effectuer votre première vente</p>
                                            <a href="vente.php" class="btn btn-primary">🛒 Effectuer une vente</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-section">
                <div class="card actions-card">
                    <div class="card-body">
                        <div class="actions-grid">
                            <button onclick="window.print()" class="btn btn-outline-secondary">
                                🖨️ Imprimer
                            </button>
                            <a href="rapport.php" class="btn btn-outline-info">
                                📈 Rapports détaillés
                            </a>
                            <a href="vente.php" class="btn btn-outline-success">
                                🛒 Nouvelle vente
                            </a>
                            <a href="index.php" class="btn btn-outline-primary">
                                🏠 Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
    include 'footer.php';
    ?>
    </div>

    <script>
        // Animation d'entrée pour les cartes
        const cards = document.querySelectorAll('.card, .stat-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Effet de survol pour les lignes du tableau
        const saleRows = document.querySelectorAll('.sale-row');
        saleRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.backgroundColor = 'rgba(99, 102, 241, 0.05)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.backgroundColor = '';
            });
        });

        // Animation pour les statistiques
        const statNumbers = document.querySelectorAll('.stat-content h3');
        statNumbers.forEach(stat => {
            const finalValue = stat.textContent;
            const isFC = finalValue.includes('FC');
            const numericValue = parseFloat(finalValue.replace(/[^\d.]/g, ''));
            
            if (!isNaN(numericValue)) {
                stat.textContent = isFC ? '0.00 FC' : '0';
                animateValue(stat, 0, numericValue, 2000, isFC);
            }
        });

        function animateValue(element, start, end, duration, isFC = false) {
            const range = end - start;
            const increment = range / (duration / 50);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                
                if (isFC) {
                    element.textContent = current.toFixed(2) + ' FC';
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 50);
        }
    </script>
</body>
</html>
