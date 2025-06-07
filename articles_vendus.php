<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Paramètres de pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Recherche
$search = '';
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
}

// Filtre par date
$date_filter = '';
if (isset($_GET['date_filter'])) {
    $date_filter = $_GET['date_filter'];
}

// Construction de la requête avec filtres
$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where_clause .= " AND Articles.nom LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $where_clause .= " AND DATE(Ventes.date_vente) = CURDATE()";
            break;
        case 'week':
            $where_clause .= " AND Ventes.date_vente >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_clause .= " AND MONTH(Ventes.date_vente) = MONTH(CURDATE()) AND YEAR(Ventes.date_vente) = YEAR(CURDATE())";
            break;
    }
}

// Récupérer le nombre total pour la pagination
$count_sql = "SELECT COUNT(*) as total FROM Ventes JOIN Articles ON Ventes.article_id = Articles.id $where_clause";
if ($params) {
    $count_stmt = $conn->prepare($count_sql);
    if ($types) $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_items = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_items / $items_per_page);

// Récupérer les articles vendus avec pagination
$sql = "SELECT Ventes.id, Articles.nom, Ventes.quantité, Ventes.prix_unitaire, Ventes.date_vente, 
               (Ventes.quantité * Ventes.prix_unitaire) as total_vente
        FROM Ventes 
        JOIN Articles ON Ventes.article_id = Articles.id 
        $where_clause 
        ORDER BY Ventes.date_vente DESC 
        LIMIT ? OFFSET ?";

$params[] = $items_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Calculer les statistiques pour la période filtrée
$stats_sql = "SELECT 
                COUNT(*) as total_ventes,
                SUM(quantité) as total_articles,
                SUM(quantité * prix_unitaire) as chiffre_affaires,
                AVG(quantité * prix_unitaire) as vente_moyenne
              FROM Ventes 
              JOIN Articles ON Ventes.article_id = Articles.id 
              $where_clause";

if ($search || $date_filter) {
    $stats_params = array_slice($params, 0, -2); // Enlever LIMIT et OFFSET
    $stats_types = substr($types, 0, -2);
    if ($stats_params) {
        $stats_stmt = $conn->prepare($stats_sql);
        if ($stats_types) $stats_stmt->bind_param($stats_types, ...$stats_params);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
    } else {
        $stats = $conn->query($stats_sql)->fetch_assoc();
    }
} else {
    $stats = $conn->query($stats_sql)->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiraShop - Articles Vendus</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📋</text></svg>">
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
                    <h1>📋 ARTICLES VENDUS</h1>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
                </div>
            </div>
        </header>

        <!-- Carte principale -->
        <div class="main-card">
            <div class="page-header">
                <h1 class="page-title">📋 Historique des Ventes</h1>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary">← Retour</a>
                    <button onclick="window.print()" class="btn btn-info">🖨️ Imprimer</button>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="sales-stats">
                <div class="stat-item">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $stats['total_ventes'] ?? 0; ?></div>
                        <div class="stat-label">Ventes</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $stats['total_articles'] ?? 0; ?></div>
                        <div class="stat-label">Articles vendus</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['chiffre_affaires'] ?? 0, 2); ?> FC</div>
                        <div class="stat-label">Chiffre d'affaires</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo number_format($stats['vente_moyenne'] ?? 0, 2); ?> FC</div>
                        <div class="stat-label">Vente moyenne</div>
                    </div>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="search">🔍 Rechercher un article</label>
                        <input type="text" id="search" name="search" placeholder="Nom de l'article..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="date_filter">📅 Période</label>
                        <select id="date_filter" name="date_filter">
                            <option value="">Toutes les dates</option>
                            <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Aujourd'hui</option>
                            <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>7 derniers jours</option>
                            <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>Ce mois</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <input type="submit" value="Filtrer" class="btn btn-primary">
                        <a href="articles_vendus.php" class="btn btn-secondary">Réinitialiser</a>
                    </div>
                </form>
            </div>

            <!-- Tableau des ventes -->
            <div class="sales-table-container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th>ID Vente</th>
                                    <th>Article</th>
                                    <th>Quantité</th>
                                    <th>Prix Unitaire</th>
                                    <th>Total</th>
                                    <th>Date de Vente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="sale-row">
                                        <td class="sale-id">#<?php echo $row['id']; ?></td>
                                        <td class="article-name">
                                            <div class="article-info">
                                                <span class="name"><?php echo htmlspecialchars($row['nom']); ?></span>
                                            </div>
                                        </td>
                                        <td class="quantity">
                                            <span class="quantity-badge"><?php echo $row['quantité']; ?></span>
                                        </td>
                                        <td class="unit-price"><?php echo number_format($row['prix_unitaire'], 2); ?> FC</td>
                                        <td class="total-price">
                                            <span class="total-amount"><?php echo number_format($row['total_vente'], 2); ?> FC</span>
                                        </td>
                                        <td class="sale-date">
                                            <div class="date-info">
                                                <span class="date"><?php echo date('d/m/Y', strtotime($row['date_vente'])); ?></span>
                                                <span class="time"><?php echo date('H:i', strtotime($row['date_vente'])); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <div class="pagination-info">
                                Affichage de <?php echo $offset + 1; ?> à <?php echo min($offset + $items_per_page, $total_items); ?>
                                sur <?php echo $total_items; ?> résultat(s)
                            </div>
                            <div class="pagination-controls">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&date_filter=<?php echo $date_filter; ?>"
                                        class="btn btn-sm btn-secondary">← Précédent</a>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date_filter=<?php echo $date_filter; ?>"
                                        class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&date_filter=<?php echo $date_filter; ?>"
                                        class="btn btn-sm btn-secondary">Suivant →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>Aucune vente trouvée</h3>
                        <p>
                            <?php if ($search || $date_filter): ?>
                                Aucune vente ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Aucune vente n'a encore été enregistrée.
                            <?php endif; ?>
                        </p>
                        <?php if ($search || $date_filter): ?>
                            <a href="articles_vendus.php" class="btn btn-primary">Voir toutes les ventes</a>
                        <?php else: ?>
                            <a href="vente.php" class="btn btn-primary">Enregistrer une vente</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="action-buttons">
                <a href="vente.php" class="btn btn-success">💰 Nouvelle Vente</a>
                <a href="rapport.php" class="btn btn-info">📊 Voir le Rapport</a>
                <a href="liste_ventes.php" class="btn btn-warning">📈 Statistiques</a>
            </div>
        </div>
        <?php
    
    include 'footer.php'
    ?>
    </div>

    <style>
        @media print {

            .header,
            .filters-section,
            .pagination,
            .action-buttons,
            .btn {
                display: none !important;
            }

            .main-card {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
            }

            .page-title {
                text-align: center;
                margin-bottom: 2rem;
            }

            .sales-stats {
                display: flex;
                justify-content: space-around;
                margin-bottom: 2rem;
                page-break-inside: avoid;
            }

            .stat-item {
                text-align: center;
            }

            .sales-table {
                font-size: 0.875rem;
            }
        }
    </style>
</body>

</html>

<?php
$conn->close();
?>