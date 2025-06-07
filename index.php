
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Récupérer les messages de session
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Recherche des articles avec prepared statement pour la sécurité
$search = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search = htmlspecialchars($_POST['search']);
    $stmt = $conn->prepare("SELECT * FROM Articles WHERE nom LIKE ?");
    $search_param = "%$search%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM Articles");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiraShop - Gestion de Ventes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
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
                    <h1>📦 GESTION DE VENTES</h1>
                </div>
                <div class="logout-btn">
                    <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
                </div>
            </div>
        </header>

        <!-- Messages de feedback -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>        <!-- Carte principale -->
        <div class="main-card">
            <div class="page-header">
                <h1 class="page-title">📦 Inventaire des Articles</h1>
                <div class="view-toggle">
                    <button id="gridView" class="btn btn-sm active">🔲 Grille</button>
                    <button id="tableView" class="btn btn-sm">📋 Tableau</button>
                </div>
            </div>
            
            <!-- Formulaire de recherche -->
            <form method="POST" class="search-form">
                <div class="search-container">
                    <input type="text" name="search" placeholder="🔍 Rechercher des articles..." value="<?php echo htmlspecialchars($search); ?>">
                    <input type="submit" value="Rechercher" class="btn btn-primary">
                </div>
            </form>
            
            <!-- Vue en grille moderne (par défaut) -->
            <div id="gridContainer" class="articles-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $stock_status = $row['quantité'] > 10 ? 'in-stock' : ($row['quantité'] > 0 ? 'low-stock' : 'out-of-stock');
                        $stock_text = $row['quantité'] > 10 ? 'En stock' : ($row['quantité'] > 0 ? 'Stock faible' : 'Rupture');
                        echo "<div class='col-4'>";
                        echo "<article class='article-card animate-in' data-id='" . $row['id'] . "'>";
                        echo "<div class='article-image'>";
                        echo "<span class='article-icon'>📦</span>";
                        echo "<div class='stock-badge $stock_status'>$stock_text</div>";
                        echo "</div>";
                        echo "<div class='article-content'>";
                        echo "<h3>" . htmlspecialchars($row['nom']) . "</h3>";
                        echo "<p class='article-description'>" . htmlspecialchars($row['description']) . "</p>";
                        echo "<div class='article-meta'>";
                        echo "<div class='price-tag'>" . number_format($row['prix'], 2) . " FC</div>";
                        echo "<div class='quantity'>Qté: " . $row['quantité'] . "</div>";
                        echo "</div>";
                        echo "<div class='article-actions'>";
                        echo "<a href='modifier.php?id=" . $row['id'] . "' class='btn btn-sm btn-secondary'>✏️ Modifier</a>";
                        echo "<a href='delete.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');\">🗑️</a>";
                        echo "</div>";
                        echo "</div>";
                        echo "</article>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='empty-state'>";
                    echo "<div class='empty-icon'>📦</div>";
                    echo "<h3>Aucun article trouvé</h3>";
                    echo "<p>Ajoutez votre premier article pour commencer</p>";
                    echo "<a href='ajouter.php' class='btn btn-primary'>➕ Ajouter un Article</a>";
                    echo "</div>";
                }
                ?>
            </div>
            
            <!-- Vue tableau (cachée par défaut) -->
            <div id="tableContainer" class="table-container" style="display: none;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Prix Unitaire</th>
                            <th>Quantité</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Reset result pointer for table view
                        if ($search) {
                            $stmt = $conn->prepare("SELECT * FROM Articles WHERE nom LIKE ?");
                            $search_param = "%$search%";
                            $stmt->bind_param("s", $search_param);
                            $stmt->execute();
                            $result_table = $stmt->get_result();
                        } else {
                            $result_table = $conn->query("SELECT * FROM Articles");
                        }
                        
                        if ($result_table->num_rows > 0) {
                            while ($row = $result_table->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td>" . number_format($row['prix'], 2) . " FC</td>";
                                echo "<td>" . htmlspecialchars($row['quantité']) . "</td>";
                                echo "<td>";
                                echo "<a href='modifier.php?id=" . $row['id'] . "' class='action-link'>✏️ Modifier</a> | ";
                                echo "<a href='delete.php?id=" . $row['id'] . "' class='action-link delete-link' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');\">🗑️ Supprimer</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='empty-message'>Aucun article trouvé</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Boutons d'action -->
           
        </div>
         <?php
    
    include 'footer.php'
    ?>
    </div>

    

    <script>
        // Toggle entre vue grille et tableau
        const gridView = document.getElementById('gridView');
        const tableView = document.getElementById('tableView');
        const gridContainer = document.getElementById('gridContainer');
        const tableContainer = document.getElementById('tableContainer');

        gridView.addEventListener('click', () => {
            gridView.classList.add('active');
            tableView.classList.remove('active');
            gridContainer.style.display = 'grid';
            tableContainer.style.display = 'none';
        });

        tableView.addEventListener('click', () => {
            tableView.classList.add('active');
            gridView.classList.remove('active');
            gridContainer.style.display = 'none';
            tableContainer.style.display = 'block';
        });

        // Animation d'entrée pour les cartes
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideInUp 0.6s ease forwards';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.article-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>


