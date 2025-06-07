<?php
/**
 * Script de mise à jour de la base de données MiraShop
 * À exécuter une seule fois pour s'assurer que la structure est correcte
 */

include 'config.php';

echo "🔄 Mise à jour de la base de données MiraShop...\n<br>";

try {
    // Vérifier et ajouter la colonne prix_unitaire dans la table Ventes si elle n'existe pas
    $result = $conn->query("SHOW COLUMNS FROM Ventes LIKE 'prix_unitaire'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE Ventes ADD COLUMN prix_unitaire DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
        echo "✅ Colonne 'prix_unitaire' ajoutée à la table Ventes\n<br>";
    } else {
        echo "ℹ️ Colonne 'prix_unitaire' déjà présente dans la table Ventes\n<br>";
    }

    // Vérifier et ajouter la colonne prix dans la table Historique si elle n'existe pas
    $result = $conn->query("SHOW COLUMNS FROM Historique LIKE 'prix'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE Historique ADD COLUMN prix DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
        echo "✅ Colonne 'prix' ajoutée à la table Historique\n<br>";
    } else {
        echo "ℹ️ Colonne 'prix' déjà présente dans la table Historique\n<br>";
    }

    // Vérifier et ajouter la colonne email dans la table users si elle n'existe pas
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE");
        echo "✅ Colonne 'email' ajoutée à la table users\n<br>";
    } else {
        echo "ℹ️ Colonne 'email' déjà présente dans la table users\n<br>";
    }

    // Mettre à jour les prix unitaires dans les ventes existantes si ils sont à 0
    $result = $conn->query("SELECT COUNT(*) as count FROM Ventes WHERE prix_unitaire = 0.00");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $update_sql = "UPDATE Ventes v 
                       JOIN Articles a ON v.id_article = a.id 
                       SET v.prix_unitaire = a.prix 
                       WHERE v.prix_unitaire = 0.00";
        $conn->query($update_sql);
        echo "✅ Prix unitaires mis à jour dans les ventes existantes\n<br>";
    }

    echo "\n<br>🎉 Mise à jour terminée avec succès !\n<br>";
    echo '<a href="index.php" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 10px;">🏠 Retour à l\'accueil</a>';

} catch (Exception $e) {
    echo "❌ Erreur lors de la mise à jour : " . $e->getMessage() . "\n<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour Base de Données - MiraShop</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
            padding: 2rem;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 MiraShop - Mise à jour Base de Données</h1>
        <!-- Le contenu PHP s'affiche ici -->
    </div>
</body>
</html>
