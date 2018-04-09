<?php
require_once __DIR__ .'/include/init.php';

$query= 'SELECT nom FROM categorie WHERE id= '.$_GET['id'];
$stmt= $pdo->query($query);
$titre= $stmt->fetch();


$query= 'SELECT * FROM produit WHERE categorie_id= '.$_GET['id'];
$stmt= $pdo->query($query);
$produits= $stmt->fetchAll();

include __DIR__. '/layout/top.php';
?>

<h2><?= $titre['nom']?></h2>




<div class="row">

<?php
	foreach($produits as $produit){
        $src  = (!empty($produit['photo']))
        ? PHOTO_WEB . $produit['photo']
        : PHOTO_DEFAULT
        ;
?>
  <div class="col-sm-3">
        <div class="card">
    <img class="card-img-top" style="width:240px" src="<?= PHOTO_WEB.$produit['photo'];?>" alt="Card image cap">
    <div class="card-body">
      <h5 class="card-title text-center"><?= $produit['nom'];?></h5>
      <p class="card-text text-center"><?= $produit['description'];?></p>
      <p class="card-text text-center"><small class="text-muted"><?= prixFr($produit['prix']);?></small></p>
      <p class="card-text text-center">
        <a class="btn btn-dark" href="produit.php?id=<?=$produit['id'];?>"> Voir </a>
      </p>
      </div>
    </div>
  </div>


<?php
}
?>



</div>



<?php
include __DIR__. '/layout/bottom.php';