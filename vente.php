<?php
include 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Enregistrer une vente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vente'])) {
    $id_article = $_POST["id_article"];
    $quantité = $_POST["quantité"];
    $remise = isset($_POST["remise"]) ? floatval($_POST["remise"]) : 0;
    
    // Validation des données
    if (empty($id_article) || empty($quantité) || $quantité <= 0) {
        $message = "Veuillez remplir tous les champs avec des valeurs valides.";
        $messageType = "error";
    } else if ($remise < 0 || $remise > 100) {
        $message = "La remise doit être entre 0 et 100%.";
        $messageType = "error";
    } else {
        // Récupérer le prix et stock de l'article
        $stmt = $conn->prepare("SELECT nom, prix, quantité FROM Articles WHERE id = ?");
        $stmt->bind_param("i", $id_article);
        $stmt->execute();
        $article_result = $stmt->get_result();
        
        if ($article_result->num_rows > 0) {
            $article = $article_result->fetch_assoc();
            $prix = $article['prix'];
            $nom_article = $article['nom'];
            $quantité_disponible = $article['quantité'];

            if ($quantité > $quantité_disponible) {
                $message = "Stock insuffisant ! Quantité demandée: $quantité, stock disponible: $quantité_disponible";
                $messageType = "error";
            } else {                // Commencer une transaction
                $conn->begin_transaction();
                try {
                    // Calculer le total avec remise
                    $total_avant_remise = $prix * $quantité;
                    $montant_remise = ($total_avant_remise * $remise) / 100;
                    $total = $total_avant_remise - $montant_remise;
                    
                    // Enregistrer la vente avec remise
                    $remise_str = $remise > 0 ? $remise . '%' : null;
                    $stmt_vente = $conn->prepare("INSERT INTO ventes (id_article, quantité, prix, remise) VALUES (?, ?, ?, ?)");
                    $stmt_vente->bind_param("iids", $id_article, $quantité, $total, $remise_str);
                    $stmt_vente->execute();
                    
                    // Mettre à jour le stock
                    $nouvelle_quantité = $quantité_disponible - $quantité;
                    $stmt_update = $conn->prepare("UPDATE Articles SET quantité = ? WHERE id = ?");
                    $stmt_update->bind_param("ii", $nouvelle_quantité, $id_article);
                    $stmt_update->execute();
                    
                    // Ajouter dans l'historique (utilise les vrais noms de colonnes)
                    $action = 'Vente';
                    $reduction = 0.00; // Pas de réduction pour cette vente
                    $stmt_hist = $conn->prepare("INSERT INTO historique (id_article, action, quantité, prix, reduction) VALUES (?, ?, ?, ?, ?)");
                    $stmt_hist->bind_param("isidd", $id_article, $action, $quantité, $total, $reduction);
                    $stmt_hist->execute();
                      // Valider la transaction
                    $conn->commit();
                    
                    $message_remise = $remise > 0 ? " (Remise: {$remise}%, Économie: " . number_format($montant_remise, 2) . " FC)" : "";
                    $message = "Vente enregistrée avec succès ! Total: " . number_format($total, 2) . " FC" . $message_remise;
                    $messageType = "success";
                    
                    // Nettoyer les statements
                    $stmt_vente->close();
                    $stmt_update->close();
                    $stmt_hist->close();
                    
                } catch (Exception $e) {
                    // Annuler la transaction en cas d'erreur
                    $conn->rollback();
                    $message = "Erreur lors de l'enregistrement de la vente.";
                    $messageType = "error";
                }
            }
        } else {
            $message = "Article introuvable.";
            $messageType = "error";
        }
        
        $stmt->close();
    }
}

// Recherche d'articles
$search = '';
$articles = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search = htmlspecialchars(trim($_POST['search']));
    if (!empty($search)) {
        $stmt = $conn->prepare("SELECT * FROM Articles WHERE nom LIKE ? OR description LIKE ? ORDER BY nom ASC");
        $search_param = "%$search%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $articles = $stmt->get_result();
        $stmt->close();
    } else {
        $articles = $conn->query("SELECT * FROM Articles ORDER BY nom ASC");
    }
} else {
    $articles = $conn->query("SELECT * FROM Articles ORDER BY nom ASC");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiraShop - Enregistrer une Vente</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
</head>
<body>   
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
                    <h1>💰 ENREGISTRER UNE VENTE</h1>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
                </div>
            </div>
        </header>        <main class="main-card">
            <!-- Message de feedback -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>" id="alert-message">
                    <div class="alert-icon">
                        <?php echo $messageType === 'success' ? '✅' : '❌'; ?>
                    </div>
                    <div class="alert-content">
                        <strong><?php echo $messageType === 'success' ? 'Succès!' : 'Erreur!'; ?></strong>
                        <p><?php echo htmlspecialchars($message); ?></p>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
                </div>
            <?php endif; ?>

            <!-- Statistiques rapides -->
            <?php
            // Calcul des statistiques pour la vue d'ensemble
            $stats_query = "SELECT 
                COUNT(*) as total_articles,
                SUM(CASE WHEN quantité = 0 THEN 1 ELSE 0 END) as rupture_stock,
                SUM(CASE WHEN quantité > 0 AND quantité < 5 THEN 1 ELSE 0 END) as stock_faible,
                SUM(CASE WHEN quantité >= 5 THEN 1 ELSE 0 END) as stock_ok
                FROM Articles";
            $stats_result = $conn->query($stats_query);
            $stats = $stats_result->fetch_assoc();
            ?>
            <div class="stats-overview">
                <div class="stats-grid">
                    <div class="stat-card stat-total">
                        <div class="stat-icon">📦</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['total_articles']; ?></div>
                            <div class="stat-label">Articles total</div>
                        </div>
                    </div>
                    <div class="stat-card stat-available">
                        <div class="stat-icon">✅</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['stock_ok']; ?></div>
                            <div class="stat-label">Stock normal</div>
                        </div>
                    </div>
                    <div class="stat-card stat-low">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['stock_faible']; ?></div>
                            <div class="stat-label">Stock faible</div>
                        </div>
                    </div>
                    <div class="stat-card stat-empty">
                        <div class="stat-icon">❌</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['rupture_stock']; ?></div>
                            <div class="stat-label">Rupture stock</div>
                        </div>
                    </div>
                </div>
            </div><!-- Barre de recherche améliorée -->
            <div class="search-section">
                <div class="card search-card">
                    <div class="card-header">
                        <h2>🔍 Rechercher des Articles</h2>
                        <p class="card-subtitle">Trouvez rapidement l'article à vendre</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="search-form">
                            <div class="search-group">
                                <input type="text" name="search" placeholder="Rechercher par nom ou description..." 
                                       value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                                <button type="submit" class="btn btn-primary search-btn">
                                    🔍 Rechercher
                                </button>
                            </div>
                        </form>
                        <?php if ($search): ?>
                            <div style="margin-top: 1rem;">
                                <span style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                                    🔍 Recherche active : "<?php echo htmlspecialchars($search); ?>"
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Conteneur principal avec grille -->
            <div class="grid-container">
                <!-- Formulaire de vente -->
                <div class="card form-card">
                    <div class="card-header">
                        <h2>📋 Nouvelle Vente</h2>
                        <p class="card-subtitle">Enregistrer une nouvelle transaction</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="sale-form" id="saleForm">
                            <div class="form-group">
                                <label for="id_article" class="form-label">📦 Article</label>
                                <select id="id_article" name="id_article" class="form-select" required>
                                    <option value="">Sélectionnez un article</option>
                                    <?php
                                    if ($articles && $articles->num_rows > 0) {
                                        // Reset pointer pour le select
                                        $articles->data_seek(0);
                                        while ($article = $articles->fetch_assoc()) {
                                            $stock_info = $article['quantité'] < 5 ? " ⚠️ Stock faible" : "";
                                            $disabled = $article['quantité'] == 0 ? "disabled" : "";
                                            echo "<option value='" . $article['id'] . "' data-stock='" . $article['quantité'] . "' data-price='" . $article['prix'] . "' $disabled>" . 
                                                 htmlspecialchars($article['nom']) . " - " . 
                                                 number_format($article['prix'], 2) . " FC (Stock: " . 
                                                 $article['quantité'] . "$stock_info)</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>                            <div class="form-group">
                                <label for="quantité" class="form-label">📊 Quantité</label>
                                <input type="number" id="quantité" name="quantité" min="1" 
                                       placeholder="Quantité à vendre" class="form-input" required>
                                <div class="stock-info" id="stockInfo" style="display: none;">
                                    <small class="text-muted">📦 Stock disponible: <span id="availableStock">0</span> unités</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="remise" class="form-label">🏷️ Remise (%)</label>
                                <input type="number" id="remise" name="remise" min="0" max="100" step="0.1" 
                                       placeholder="Remise en pourcentage (optionnel)" class="form-input" value="0">
                                <small class="text-muted">💡 Saisissez un pourcentage entre 0 et 100</small>
                            </div>                            <!-- Prévisualisation améliorée du total -->
                            <div class="total-preview" id="totalPreview" style="display: none;">
                                <div class="total-info">
                                    <div>
                                        <span class="total-label">💰 Total à payer</span>
                                    </div>
                                    <div>
                                        <span class="total-amount" id="totalAmount">0.00 FC</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="vente" class="btn btn-success btn-lg">
                                💰 Enregistrer la Vente
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tableau des articles -->
                <div class="card table-card">
                    <div class="card-header">
                        <h2>📦 Articles Disponibles</h2>
                        <p class="card-subtitle">
                            <?php echo $search ? "Résultats pour: \"$search\"" : "Tous les articles"; ?>
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($articles && $articles->num_rows > 0) {
                                        // Reset pointer pour le tableau
                                        $articles->data_seek(0);
                                        while ($row = $articles->fetch_assoc()) {
                                            $stock_class = '';
                                            $stock_badge = '';
                                            
                                            if ($row['quantité'] == 0) {
                                                $stock_class = 'out-of-stock';
                                                $stock_badge = '<span class="badge badge-danger">Rupture</span>';
                                            } elseif ($row['quantité'] < 5) {
                                                $stock_class = 'low-stock';
                                                $stock_badge = '<span class="badge badge-warning">Stock faible</span>';
                                            } else {
                                                $stock_badge = '<span class="badge badge-success">Disponible</span>';
                                            }
                                            
                                            echo "<tr class='$stock_class'>";
                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                            echo "<td><strong>" . htmlspecialchars($row['nom']) . "</strong></td>";
                                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                            echo "<td class='price'>" . number_format($row['prix'], 2) . " FC</td>";
                                            echo "<td class='stock-cell'>" . $row['quantité'] . "</td>";
                                            echo "<td>$stock_badge</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='empty-state'>
                                                <div class='empty-icon'>📦</div>
                                                <p>Aucun article trouvé</p>
                                              </td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>            <!-- Actions rapides améliorées -->
            <div class="actions-section">
                <div class="card actions-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border-radius: 20px 20px 0 0;">
                        <h3 style="margin: 0; font-size: 1.3rem; font-weight: 600;">🚀 Actions Rapides</h3>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.9;">Naviguez rapidement vers les autres sections</p>
                    </div>
                    <div class="card-body">
                        <div class="actions-grid">
                            <a href="index.php" class="btn btn-outline-primary action-btn">
                                <div class="action-icon">🏠</div>
                                <div class="action-content">
                                    <div class="action-title">Accueil</div>
                                    <div class="action-desc">Dashboard principal</div>
                                </div>
                            </a>
                            <a href="liste_ventes.php" class="btn btn-outline-info action-btn">
                                <div class="action-icon">📋</div>
                                <div class="action-content">
                                    <div class="action-title">Historique</div>
                                    <div class="action-desc">Voir les ventes</div>
                                </div>
                            </a>
                            <a href="ajouter.php" class="btn btn-outline-success action-btn">
                                <div class="action-icon">➕</div>
                                <div class="action-content">
                                    <div class="action-title">Ajouter</div>
                                    <div class="action-desc">Nouvel article</div>
                                </div>
                            </a>
                            <a href="rapport.php" class="btn btn-outline-warning action-btn">
                                <div class="action-icon">📊</div>
                                <div class="action-content">
                                    <div class="action-title">Rapports</div>
                                    <div class="action-desc">Statistiques</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
    
    include 'footer.php'
    ?>
    </div>

    <script>
        // Calcul automatique du total
        const articleSelect = document.getElementById('id_article');
        const quantityInput = document.getElementById('quantité');
        const stockInfo = document.getElementById('stockInfo');
        const availableStock = document.getElementById('availableStock');
        const totalPreview = document.getElementById('totalPreview');
        const totalAmount = document.getElementById('totalAmount');

        function updateStockInfo() {
            const selectedOption = articleSelect.options[articleSelect.selectedIndex];
            if (selectedOption.value) {
                const stock = selectedOption.dataset.stock;
                const price = selectedOption.dataset.price;
                
                availableStock.textContent = stock;
                stockInfo.style.display = 'block';
                quantityInput.max = stock;
                
                updateTotal();
            } else {
                stockInfo.style.display = 'none';
                totalPreview.style.display = 'none';
                quantityInput.max = '';
            }
        }        function updateTotal() {
            const selectedOption = articleSelect.options[articleSelect.selectedIndex];
            const quantity = parseInt(quantityInput.value) || 0;
            const remise = parseFloat(document.getElementById('remise').value) || 0;
            
            if (selectedOption.value && quantity > 0) {
                const price = parseFloat(selectedOption.dataset.price);
                const totalAvantRemise = price * quantity;
                const montantRemise = (totalAvantRemise * remise) / 100;
                const total = totalAvantRemise - montantRemise;
                
                if (remise > 0) {
                    totalAmount.innerHTML = `
                        <div style="font-size: 0.9em; color: #888; text-decoration: line-through;">
                            ${totalAvantRemise.toFixed(2)} FC
                        </div>
                        <div style="font-weight: bold; color: #28a745;">
                            ${total.toFixed(2)} FC
                        </div>
                        <div style="font-size: 0.8em; color: #17a2b8;">
                            Économie: ${montantRemise.toFixed(2)} FC (${remise}%)
                        </div>
                    `;
                } else {
                    totalAmount.textContent = total.toFixed(2) + ' FC';
                }
                
                totalPreview.style.display = 'block';
            } else {
                totalPreview.style.display = 'none';
            }
        }

        articleSelect.addEventListener('change', updateStockInfo);
        quantityInput.addEventListener('input', updateTotal);
        document.getElementById('remise').addEventListener('input', updateTotal);

        // Auto-hide des messages
        const alertMessage = document.getElementById('alert-message');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.style.opacity = '0';
                setTimeout(() => {
                    alertMessage.style.display = 'none';
                }, 300);
            }, 5000);
        }

        // Animation d'entrée pour les cartes
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });        // Validation du formulaire
        document.getElementById('saleForm').addEventListener('submit', function(e) {
            const articleId = document.getElementById('id_article').value;
            const quantity = parseInt(document.getElementById('quantité').value);
            const remise = parseFloat(document.getElementById('remise').value) || 0;
            
            if (!articleId) {
                e.preventDefault();
                alert('Veuillez sélectionner un article.');
                return;
            }
            
            if (remise < 0 || remise > 100) {
                e.preventDefault();
                alert('La remise doit être comprise entre 0 et 100%.');
                return;
            }
            
            const selectedOption = document.querySelector(`option[value="${articleId}"]`);
            const maxStock = parseInt(selectedOption.dataset.stock);
            
            if (quantity > maxStock) {
                e.preventDefault();
                alert(`Quantité demandée (${quantity}) supérieure au stock disponible (${maxStock}).`);
                return;
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
