<?php
$conn= new mysqli('localhost','root', '','SHOP');
if (!$conn) {
    error_reporting(0);
    die("impossible de se connecter a la base de donnees!".mysqli_error($conn));
}