<style>
    nav {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1rem 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .menu-toggle {
        display: block;
        flex-direction: column;
        cursor: pointer;
        padding: 0.5rem;
    }

    .menu-toggle span {
        width: 25px;
        height: 3px;
        background: white;
        margin: 3px 0;
        transition: 0.3s;
    }

    .navbar-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .navbar-link {
        color: white;
        text-decoration: none;
        font-weight: 500;
        font-size: 1.1rem;
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }

   

    .navbar-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.1);
    }

    .navbar-link:hover::before {
        left: 0;
    }

    @media (max-width: 768px) {
        .menu-toggle {
            display: flex;
        }
        
        .navbar-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            flex-direction: column;
            gap: 0;
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-list.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .navbar-list li {
            width: 100%;
        }
        
        .navbar-link {
            display: block;
            padding: 1rem 2rem;
            border-radius: 0;
            text-align: left;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navbarList = document.querySelector('.navbar-list');
        
        if (menuToggle && navbarList) {
            menuToggle.addEventListener('click', function() {
                navbarList.classList.toggle('active');
            });
            
            // Fermer le menu quand on clique sur un lien
            const navLinks = document.querySelectorAll('.navbar-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    navbarList.classList.remove('active');
                });
            });
            
            // Fermer le menu quand on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!menuToggle.contains(e.target) && !navbarList.contains(e.target)) {
                    navbarList.classList.remove('active');
                }
            });
        }
    });
</script>

<nav>
    <div class="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <ul class="navbar-list">
        <li><a href="index.php" class="navbar-link">Accueil</a></li>
        <li><a href="ajouter.php" class="navbar-link">Ajouter un Article</a></li>
        <li><a href="vente.php" class="navbar-link"> Enregistrer une vente</a></li>
        <li><a href="rapport.php" class="navbar-link">Voir l'inventaire</a></li>
        <li><a href="liste_ventes.php" class="navbar-link">Liste des ventes</a></li>
    </ul>
</nav>
