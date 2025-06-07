<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$id = $_GET['id'];
$confirmed = isset($_GET['confirmed']) && $_GET['confirmed'] === '1';

if (!$id) {
    $_SESSION['message'] = "❌ Erreur : ID manquant.";
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Si la suppression n'est pas confirmée, rediriger vers la page de confirmation
if (!$confirmed) {
    header("Location: confirm_delete.php?id=" . urlencode($id));
    exit();
}

// Vérification de l'article avec prepared statement
$stmt = $conn->prepare("SELECT * FROM Articles WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();
    $prix = $article['prix'];
    $nom = $article['nom'];
    $quantité_actuelle = $article['quantité'];    // Commencer une transaction pour assurer la cohérence
    $conn->begin_transaction();
    
    try {
        // 1. D'abord ajouter une entrée dans l'historique pour tracer la suppression (avant de supprimer l'article)
        $action = 'Suppression article: ' . $nom . ' (Qté: ' . $quantité_actuelle . ', Prix: ' . $prix . ' FC)';
        $stmt_hist = $conn->prepare("INSERT INTO Historique (id_article, action, quantité, prix) VALUES (?, ?, ?, ?)");
        $stmt_hist->bind_param("isid", $id, $action, $quantité_actuelle, $prix);
        $stmt_hist->execute();
        $stmt_hist->close();
          // 2. Supprimer les entrées liées dans la table Ventes (si elles existent)
        $stmt_ventes = $conn->prepare("DELETE FROM Ventes WHERE id_article = ?");
        $stmt_ventes->bind_param("i", $id);
        $stmt_ventes->execute();
        $stmt_ventes->close();
        
        // 3. Supprimer toutes les autres entrées de l'historique liées à cet article
        $stmt_hist_delete = $conn->prepare("DELETE FROM Historique WHERE id_article = ? AND action != ?");
        $stmt_hist_delete->bind_param("is", $id, $action);
        $stmt_hist_delete->execute();
        $stmt_hist_delete->close();
        
        // 4. Enfin, supprimer l'article
        $stmt_delete = $conn->prepare("DELETE FROM Articles WHERE id=?");
        $stmt_delete->bind_param("i", $id);
        
        if (!$stmt_delete->execute()) {
            throw new Exception("Erreur lors de la suppression de l'article : " . $stmt_delete->error);
        }
        $stmt_delete->close();
        
        // Si tout s'est bien passé, valider la transaction
        $conn->commit();
        
        $_SESSION['message'] = "✅ Article '$nom' supprimé avec succès !";
        $_SESSION['message_type'] = 'success';
        
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollback();
        $_SESSION['message'] = "❌ Erreur lors de la suppression : " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "❌ Erreur : Article non trouvé.";
    $_SESSION['message_type'] = 'error';
}

$stmt->close();
$conn->close();
header("Location: index.php");
exit();
?>