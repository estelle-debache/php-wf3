<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();


$query = 'SELECT photo FROM produit WHERE id = ' . $_GET['id'];
$stmt = $pdo->query($query);
$photo = $stmt->fetchColumn();


// on supprome l'image du produit dans le répertoir photo s'il en a une
if(!empty($photo)){
    unlink(PHOTO_DIR . $photoActuelle);
}


$query = 'DELETE FROM produit WHERE id = ' . $_GET['id'];
$pdo->exec($query);

setFlashMessage('Le produit est supprimé');
header('Location: produits.php');
die;