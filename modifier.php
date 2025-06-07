<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = $_POST['prix'];
    $quantité = $_POST['quantité'];

    // Utiliser prepared statements pour la sécurité
    $stmt = $conn->prepare("UPDATE Articles SET nom=?, description=?, prix=?, quantité=? WHERE id=?");
    $stmt->bind_param("ssdii", $nom, $description, $prix, $quantité, $id);
    
    if ($stmt->execute()) {
        // Ajouter l'entrée dans l'historique
        $action = 'Modification';
        $stmt_hist = $conn->prepare("INSERT INTO Historique (id_article, action, quantité, prix) VALUES (?, ?, ?, ?)");
        $stmt_hist->bind_param("isid", $id, $action, $quantité, $prix);
        $stmt_hist->execute();
        
        $message = "✅ Article mis à jour avec succès !";
        $message_type = 'success';
        $stmt_hist->close();
    } else {
        $message = "❌ Erreur lors de la mise à jour : " . $conn->error;
        $message_type = 'error';
    }
    $stmt->close();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM Articles WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Article - MiraShop</title>
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
    <!-- Header uniforme -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">🛍️ Mira<span>Shop</span></a>
            </div>            <div class="header-title">
                <h1>✏️ MODIFIER UN ARTICLE</h1>
            </div>
            <div class="logout-btn">
                <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-card">
            <div class="card-header">
                <h2>✏️ Modifier l'Article</h2>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" class="modern-form">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($article['id']); ?>">
                    
                    <div class="form-group">
                        <label for="nom">📝 Nom de l'article</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($article['nom']); ?>" 
                               placeholder="Entrez le nom de l'article" required>
                    </div>

                    <div class="form-group">
                        <label for="description">📄 Description</label>
                        <textarea id="description" name="description" 
                                  placeholder="Décrivez l'article en détail" required><?php echo htmlspecialchars($article['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">💰 Prix (FC)</label>
                            <input type="number" id="prix" name="prix" value="<?php echo htmlspecialchars($article['prix']); ?>" 
                                   step="0.01" min="0" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label for="quantité">📊 Quantité</label>
                            <input type="number" id="quantité" name="quantité" value="<?php echo htmlspecialchars($article['quantité']); ?>" 
                                   min="0" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            ✅ Modifier l'article
                        </button>
                        <button type="button" onclick="window.location.href='index.php'" class="btn btn-secondary">
                            🔙 Retour à la liste
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    
    include 'footer.php'
    ?>
  </div>

    <script>
        // Animation des champs au focus
        document.querySelectorAll('input, textarea').forEach(input => {
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
            submitBtn.innerHTML = '⏳ Modification en cours...';
        });

        // Auto-hide des messages après 5 secondes
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.animation = 'fadeOut 0.5s ease-in forwards';
                setTimeout(() => {
                    message.remove();
                }, 500);
            }, 5000);
        });
    </script>
</body>
</html>