# Modifications apportées au système MiraShop

## 1. Remplacement des symboles Euro (€) par FC

**Fichiers modifiés :**
- `vente.php` : Messages de succès, affichage des prix, JavaScript
- `shop/modern-style.css` : Content CSS 
- `modifier.php` : Label prix
- `ajouter.php` : Label prix
- `rapport.php` : Affichages des montants
- `articles_vendus.php` : Statistiques et tableaux
- `delete.php` : Message historique
- `confirm_delete.php` : Affichage des prix et valeurs totales

**Résultat :** Tous les montants sont maintenant affichés en "FC" au lieu d'€

## 2. Ajout de la fonctionnalité Remise

### Base de données
- **Table `ventes`** : Ajout de la colonne `remise varchar(10) NULL`
- **Script de migration** : `update_ventes_table.sql` pour les bases existantes

### Interface utilisateur (`vente.php`)
- **Nouveau champ remise** : Input de type number (0-100%)
- **Validation** : Vérification côté client et serveur (0-100%)
- **Calcul en temps réel** : Le total se met à jour automatiquement avec la remise
- **Affichage améliore** : Montre le prix barré, le nouveau prix et l'économie réalisée

### Logique métier
- **Calcul automatique** : 
  - Total avant remise = prix × quantité
  - Montant remise = (total avant remise × remise%) / 100
  - Total final = total avant remise - montant remise
- **Enregistrement** : Remise stockée au format "X%" ou NULL si aucune remise
- **Messages** : Affichage des détails de la remise dans les messages de succès

### Fonctionnalités JavaScript
- **Mise à jour en temps réel** : Le total se recalcule automatiquement
- **Validation** : Vérification que la remise est entre 0 et 100%
- **Affichage visuel** : Prix original barré + nouveau prix + économie

## Utilisation

1. **Nouvelle vente avec remise :**
   - Sélectionner un article
   - Saisir la quantité
   - (Optionnel) Saisir une remise en %
   - Le total se calcule automatiquement
   - Cliquer sur "Enregistrer la Vente"

2. **Migration base existante :**
   - Exécuter le script `update_ventes_table.sql` pour ajouter la colonne remise

## Tests suggérés

1. Vente sans remise (comportement normal)
2. Vente avec remise de 10% (vérifier les calculs)
3. Vente avec remise de 0% (identique à sans remise)
4. Tentative de remise > 100% (doit être rejetée)
5. Tentative de remise négative (doit être rejetée)
