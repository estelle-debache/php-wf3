<?php
require_once __DIR__ .'/include/init.php';

$query = 'SELECT * FROM produit WHERE id = ' . $_GET['id'];
$stmt = $pdo->query($query);
$produit = $stmt->fetch();

        $src  = (!empty($produit['photo']))
        ? PHOTO_WEB . $produit['photo']
        : PHOTO_DEFAULT
        ;

        if(!empty($_POST)){
            ajoutPanier($produit, $_POST['quantite']);
            setFlashMessage('le produit est ajouté au panier');
        }
        

include __DIR__ .'/layout/top.php';
?>

<h1><?= $produit['nom']; ?></h1>

<div class="row">
    <div class="col-sm-4 text-center">
        <img src="<?= $src; ?>" height="200px">
        <p><?= prixFr($produit['prix']); ?></p>
        <form method="post" class="form-inline">
        <label>Qté</label>
        <select name="quantite" class="form-control">
        <?php
        for ($i = 1; $i <= 10; $i++) :
        ?>
            <option value="<?= $i; ?>"><?= $i; ?></option>
        <?php
        endfor;
        ?>
        </select>
      
        <button type="submit" class="btn btn-dark">
            Ajouter au panier
        </button>

    </div>
    <div class="col-sm-8">
        <p><?= $produit['description']; ?></p>

    </div>

</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>