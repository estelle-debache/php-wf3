<?php
/* 
- si le panier est vide :  afficher un message
- sinon afficher un tableau HTML avec pour chaque produit du panier:
nom du produit, prix unitaire,quantité, prix total pour le produit
- faire une fonction getTotalpanier() qui calcule le montant total du panier
et l'utiliser sous le tableau pour afficher le total
-remplacer l'affichage de la quantité par un formulaire avec 
 - un <input type = number> 
 - un input hidden pour voir l'id du produit dont on modifie la quantité
 - un bouton submit
 faire une fonction modifierQuantitepanier() qui met à jour la quantité pour le produit si la quantité n'est pas 0, et qui supprime le produit du panier sinon.
 appeler cette fonction quand un des formulaire est envoyé
*/

require_once __DIR__ . '/include/init.php';

if (isset($_POST['commander'])){
   $query = <<<EOS
    INSERT INTO commande (
        utilisateur_id,
        montant_total
    )  VALUES (
        :utilisateur_id,
        :montant_total
    )
 

EOS;

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':utilisateur_id', $_SESSION['utilisateur']['id']);
    $stmt->bindValue(':montant_total', getTotalPanier());
    $stmt->execute();
    // recuperation de l'id de la commande que l'on vient d'inserer
    $commandeId = $pdo->lastInsertId();
    
    $query = <<<EOS
INSERT INTO detail_commande (
    commande_id,
    produit_id,
    prix,
    quantite
)VALUES (
    :commande_id,
    :produit_id,
    :prix,
    :quantite
)
EOS;
 $stmt = $pdo->prepare($query);
 $stmt->bindValue(':commande_id', $commandeId);

 foreach ($_SESSION['panier'] as $produitId => $produit){
     $stmt->bindValue(':produit_id', $produitId);
     $stmt->bindValue(':prix', $produit['prix']);
     $stmt->bindValue(':quantite', $produit['quantite']);
     $stmt->execute();
 }
 setFlashMessage('La commande est enregistrée');
// on vide le panier
 $_SESSION['panier'] = [];

}
    

        // Enregistrer la commande et son détail en bdd
        // Afficher un message de confirmation
        // Vider le panier
    



if (isset($_POST['modifier-quantite'])){
    modifierQuantitepanier($_POST['produit-id'], $_POST['quantite']);
    setFlashMessage('La quantité a été modifiée');
}




//$_SESSION['panier'] = [];

include __DIR__ . '/layout/top.php';
?>
<h1>Mon Panier</h1>
<?php

if (empty($_SESSION['panier'])):
?>
	<div class="alert alert-info">
		Le panier est vide
	</div>
<?php
	else:
?>
	<table class="table table-bordered">

	    <tr>
	    	<th class="text-center" scope="col">Image</th>
	      	<th class="text-center" scope="col">Nom du produit</th>
	      	<th class="text-center" scope="col">Prix unitaire</th>
	      	<th  class="text-center" scope="col">Quantité</th>
	      	<th class="text-center"  scope="col">Prix total</th>
	      	
	    </tr>
	 
<?php	
        foreach ($_SESSION['panier'] as $produitId => $produit):
            $src = (!empty($produit['photo'])) 
                ? PHOTO_WEB . $produit['photo']
                : PHOTO_DEFAULT
            ;
?>
	    <tr>
	    	<td class="text-center"> <img src="<?= $src; ?>" height="200px"></td>
	      	<td class="text-center"><?= $produit['nom']; ?></td>
	      	<td class="text-center"><?= prixFr($produit['prix']); ?></td>
	      	<td class="text-center">
	      		<form method="post" class="form-inline">
	      			<input type="number" min="0"  class="form-control col-sm-2" value="<?= $produit['quantite']; ?>" name="quantite" >
	      			<input type="hidden" value="<?= $produitId; ?>" name="produit-id">
	      			<button class="btn btn-black" name="modifier-quantite" type="submit">Modifier</button>
	      		</form>
	      	</td>
	      	<td class="text-center"><?= prixFr($produit['quantite'] * $produit['prix']); ?> </td>
	      	
	    </tr>
<?php
endforeach;
?>
<tr>
	<td colspan="4"><b>Total</b></td>
	<td class="font-weight-bold"><?= prixFr(getTotalpanier()); ?></td>
</tr>
<?php
endif;
?>

	</table>
<?php
if (isUserConnected()) :
?>
    <form method="post">
        <p class="text-right">
            <button type="submit" name="commander" class="btn btn-dark">
                Valider la commande
            </button>
        </p>
    </form>
<?php
else :
?>
    <div class="alert alert-info">
		Vous devez vous connecter ou vous inscrire pour valider la commande
	</div>
<?php
endif;
?>


<?php
include __DIR__. '/layout/bottom.php';