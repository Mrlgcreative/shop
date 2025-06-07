# 🛍️ MiraShop - Application de Gestion de Ventes

## 📋 Aperçu

MiraShop est une application web moderne de gestion de ventes développée en PHP et MySQL. L'interface a été complètement rénovée avec un design contemporain, une expérience utilisateur optimisée et des fonctionnalités de sécurité renforcées.

## ✨ Fonctionnalités

### 🔐 Authentification
- **Connexion sécurisée** avec hashage des mots de passe
- **Inscription d'administrateurs** 
- **Déconnexion avec confirmation** pour éviter les déconnexions accidentelles
- **Messages de feedback** pour toutes les actions

### 📦 Gestion des Articles
- **Ajout d'articles** avec validation des données
- **Modification d'articles** avec interface moderne
- **Suppression sécurisée** avec confirmations
- **Recherche en temps réel** dans la liste des articles
- **Alertes de stock faible** (moins de 10 unités)

### 🛒 Gestion des Ventes
- **Interface de vente intuitive** avec sélection d'articles
- **Calcul automatique** des totaux
- **Historique complet** des ventes
- **Statistiques en temps réel**

### 📊 Rapports et Analyses
- **Tableau de bord** avec statistiques générales
- **Historique des actions** (ajouts, modifications, suppressions, ventes)
- **Inventaire journalier** avec chiffre d'affaires
- **Rapports imprimables**

## 🎨 Améliorations de l'Interface

### Design Moderne
- **Thème sombre** avec dégradés et effets glass
- **Icônes émoji** pour une meilleure lisibilité
- **Animations fluides** et transitions
- **Cards avec effets de survol**

### Responsive Design
- **Compatible mobile** avec adaptation automatique
- **Navigation optimisée** pour tous les écrans
- **Tableaux scrollables** sur petits écrans

### Expérience Utilisateur
- **Messages de feedback** visuels (succès, erreur, avertissement)
- **Placeholders descriptifs** dans les formulaires
- **Validation côté client** et serveur
- **Boutons d'action intuitifs**

## 🔒 Sécurité

### Protection des Données
- **Prepared Statements** pour toutes les requêtes SQL
- **Échappement HTML** avec `htmlspecialchars()`
- **Validation des entrées** utilisateur
- **Sessions sécurisées**

### Prévention des Attaques
- **Protection contre l'injection SQL**
- **Prévention XSS** (Cross-Site Scripting)
- **Contrôle d'accès** sur toutes les pages

## 📁 Structure des Fichiers

```
shop/
├── index.php          # Page d'accueil - Liste des articles
├── login.php          # Page de connexion
├── logout.php         # Déconnexion avec confirmation
├── register.php       # Inscription administrateur
├── ajouter.php        # Ajout d'articles
├── modifier.php       # Modification d'articles
├── delete.php         # Suppression d'articles
├── vente.php          # Interface de vente
├── liste_ventes.php   # Historique des ventes
├── rapport.php        # Rapports et statistiques
├── style.css          # Styles modernes
└── config.php         # Configuration base de données
```

## 🚀 Installation

1. **Télécharger** les fichiers dans votre dossier web (ex: `c:\xampp\htdocs\shop\`)

2. **Configurer la base de données** dans `config.php` :
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "mirashop";
   ```

3. **Créer les tables** nécessaires :
   - `users` (utilisateurs)
   - `Articles` (produits)
   - `Ventes` (transactions)
   - `Historique` (actions)

4. **Accéder à l'application** via votre navigateur

## 🎯 Fonctionnalités Techniques

### Base de Données
- **MySQL/MariaDB** avec structure optimisée
- **Relations entre tables** bien définies
- **Index** pour de meilleures performances

### Frontend
- **CSS3** avec variables et grilles
- **Responsive Design** avec Media Queries
- **Animations CSS** et transitions
- **Print Styles** pour les rapports

### Backend
- **PHP 7.4+** avec bonnes pratiques
- **Gestion d'erreurs** robuste
- **Sessions** sécurisées
- **Logging** des actions

## 📱 Compatibilité

- ✅ **Navigateurs modernes** (Chrome, Firefox, Safari, Edge)
- ✅ **Responsive** (Desktop, Tablette, Mobile)
- ✅ **PHP 7.4+**
- ✅ **MySQL 5.7+** ou MariaDB

## 🎨 Thème de Couleurs

- **Primaire** : Dégradé violet-bleu (#4f46e5 → #7c3aed)
- **Secondaire** : Dégradé rose-orange (#ec4899 → #f97316)
- **Accent** : Dégradé vert (#10b981 → #059669)
- **Fond** : Dégradé sombre (#0f172a → #1e293b)

## 📞 Support

Pour toute question ou problème, l'interface fournit des messages d'erreur détaillés et des guides visuels pour aider les utilisateurs.

---

**Développé avec ❤️ pour une gestion de ventes moderne et efficace**
