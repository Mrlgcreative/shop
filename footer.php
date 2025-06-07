
<style>
    .site-footer {
        background: linear-gradient(135deg,rgb(48, 61, 119) 0%,rgb(59, 38, 80) 100%);
        color: #fff;
        padding: 40px 0 20px;
        margin-top: auto;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        font-size: 9px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 40px;
        margin-bottom: 20px;
    }

    .footer-brand h3 {
        margin: 0 0 15px 0;
        font-size: 1.8em;
        letter-spacing: 2px;
    }

    .footer-brand p {
        margin: 0;
        opacity: 0.8;
        text-align: center;
    }

    .footer-links {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .footer-section h4 {
        margin: 0 0 15px 0;
        font-size: 1.1em;
        color: #fff;
        padding: 5px;
    }

    .col-3 {
        display: flex;
        justify-content: center;
        align-items: center;
        list-style: none;
        flex-wrap: wrap;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section li {
        margin-bottom: 8px;
    }

    .footer-section a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-section a:hover {
        color: #fff;
    }

    .footer-section p {
        margin: 5px 0;
        color: #ccc;
    }

    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .footer-links {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
</style>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-content">
            

            <div class="footer-links col-3">
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="products.php">Produits</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>

                <div class="col-3">
                    <div class="footer-section">
                        <h4>Informations</h4>
                        <ul>
                            <li><a href="about.php">À propos</a></li>
                            <li><a href="terms.php">Conditions</a></li>
                            <li><a href="privacy.php">Confidentialité</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-3">
                    <div class="footer-section ">
                        <h4>Services</h4>
                        <ul>
                            <li><a href="support.php">Support</a></li>
                            <li><a href="maintenance.php">Maintenance</a></li>
                            <li><a href="consulting.php">Conseil</a></li>
                        </ul>
                    </div>
                </div>

                 <div class="col-3">
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: contact@skyboardsoft.com</p>
                    <p>Téléphone: +243 97 90 99 031</p>
                </div>
            </div>
            </div>

            <p>&copy; <?php echo date('Y'); ?> Skyboard Soft. Tous droits réservés.</p>
        </div>
    </div>
    </div>
</footer>
