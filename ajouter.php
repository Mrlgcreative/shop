<?php
include 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Ajouter un article
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = $_POST['prix'];
    $quantité = $_POST['quantité'];
    $devise = isset($_POST['devise']) ? $_POST['devise'] : 'FC';

    $stmt = $conn->prepare("INSERT INTO Articles (nom, description, prix, quantité, devise) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $nom, $description, $prix, $quantité, $devise);
    
    if ($stmt->execute()) {        // Ajouter l'entrée dans l'historique
        $id_article = $conn->insert_id;
        $action = 'Ajout';
        $stmt_hist = $conn->prepare("INSERT INTO Historique (id_article, action, quantité, prix, devise) VALUES (?, ?, ?, ?, ?)");
        $stmt_hist->bind_param("isids", $id_article, $action, $quantité, $prix, $devise);
        $stmt_hist->execute();
        $stmt_hist->close();
        
        $message = "Nouvel article ajouté avec succès !";
        $messageType = "success";
    } else {
        $message = "Erreur lors de l'ajout de l'article : " . $conn->error;
        $messageType = "error";
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
    <title>Ajouter un Article - MiraShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="shop/modern-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛍️</text></svg>">
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .form-group select {
            background-color: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-group select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .form-group select option {
            padding: 8px;
            font-size: 16px;
        }
    </style>
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
            </div>
            <div class="header-title">
                <h1>➕ AJOUTER UN ARTICLE</h1>
            </div>
            <div class="logout-btn">
                <a href="logout.php" class="btn-outline">🚪 Déconnexion</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-card">
            <div class="card-header">
                <h2>➕ Ajouter un Nouvel Article</h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $messageType === 'success' ? '✅' : '❌'; ?> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" class="modern-form">
                    <div class="form-group">
                        <label for="nom">📝 Nom de l'article</label>
                        <input type="text" id="nom" name="nom" 
                               placeholder="Ex: iPhone 15 Pro Max" required>
                    </div>

                    <div class="form-group">
                        <label for="description">📄 Description détaillée</label>
                        <textarea id="description" name="description" 
                                  placeholder="Décrivez l'article en détail (couleur, caractéristiques, état...)" required></textarea>
                    </div>                    <div class="form-row">
                        <div class="form-group">
                            <label for="prix">💰 Prix unitaire</label>
                            <input type="number" id="prix" name="prix" 
                                   step="0.01" min="0" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label for="devise">💱 Devise</label>
                            <select id="devise" name="devise" required>
                                <option value="FC" selected>🇨🇩 FC (Franc Congolais)</option>
                                <option value="USD">🇺🇸 USD (Dollar Américain)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantité">📊 Quantité en stock</label>
                            <input type="number" id="quantité" name="quantité" 
                                   min="0" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            ✅ Ajouter l'article
                        </button>
                        <button type="button" onclick="window.location.href='index.php'" class="btn btn-secondary">
                            🔙 Retour à la liste
                        </button>
                        <button type="reset" class="btn btn-warning">
                            🔄 Réinitialiser
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

    <script>        // Animation des champs au focus
        document.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Gestion du changement de devise
        document.getElementById('devise').addEventListener('change', function() {
            const prixInput = document.getElementById('prix');
            const selectedDevise = this.value;
            
            if (selectedDevise === 'USD') {
                prixInput.placeholder = '0.00 (USD)';
            } else {
                prixInput.placeholder = '0.00 (FC)';
            }
            
            // Animation du changement
            prixInput.style.animation = 'pulse 0.3s ease-in-out';
            setTimeout(() => {
                prixInput.style.animation = '';
            }, 300);
        });        // Effet de chargement sur le bouton submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const nomField = this.querySelector('#nom');
            const prixField = this.querySelector('#prix');
            const quantiteField = this.querySelector('#quantité');
            const deviseField = this.querySelector('#devise');
            
            // Validation simple
            if (!nomField.value.trim()) {
                e.preventDefault();
                nomField.focus();
                alert('⚠️ Veuillez saisir le nom de l\'article');
                return;
            }
            
            if (prixField.value <= 0) {
                e.preventDefault();
                prixField.focus();
                alert('⚠️ Le prix doit être supérieur à 0');
                return;
            }
            
            if (quantiteField.value < 0) {
                e.preventDefault();
                quantiteField.focus();
                alert('⚠️ La quantité ne peut pas être négative');
                return;
            }
            
            // Confirmation avec détails
            const devise = deviseField.value;
            const deviseSymbol = devise === 'USD' ? '$' : 'FC';
            const confirmMessage = `✅ Confirmer l'ajout de l'article ?\n\n` +
                                 `📝 Nom: ${nomField.value}\n` +
                                 `💰 Prix: ${prixField.value} ${deviseSymbol}\n` +
                                 `📊 Quantité: ${quantiteField.value}\n` +
                                 `💱 Devise: ${devise}`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
            
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '⏳ Ajout en cours...';
        });

        // Auto-hide des messages après 5 secondes
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'fadeOut 0.5s ease-in forwards';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });

        // Animation pour le bouton reset
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            if (confirm('🤔 Êtes-vous sûr de vouloir réinitialiser tous les champs ?')) {
                // Animation de reset
                document.querySelectorAll('.form-group').forEach((group, index) => {
                    setTimeout(() => {
                        group.style.animation = 'slideInLeft 0.3s ease-out';
                    }, index * 100);
                });
            }
        });
    </script>
</body>
</html>
   



