<?php
//lister les commandes dans un tableau HTMl :
// - id de la commande
// - nom, prenom de l'utilisateur qui a passé la commande
// - montant formaté
// - la date de la commande formatée ( function date() et strtotime() de PHP)
// - statut de la commande 
// - la date du statut formatée ( function date() et strtotime() de PHP)
/* Passer le statut en liste déroulante (en cours, envoyé, livré)
     avec bouton modifier pour changer le statut de la commande
     => traiter le changement de sttaut en mettant à jour sttaut et date_statut
     dans la table commande
*/

require_once __DIR__ . '/../include/init.php';
adminSecurity();

if(isset($_POST['modifier-statut'])){
    $query = 'UPDATE commande SET'
    . ' statut = :statut,'
    . ' date_statut = now()'
    . ' WHERE id= :id'
    ;
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':statut', $_POST['statut']);
    $stmt->bindValue(':id', $_POST['commande-id']);
    $stmt->execute();

    setFlashMessage('Le statut est modifié');

}

$query = "SELECT c.*, concat_ws(' ', u.prenom, u.nom) AS utilisateur"
    . ' FROM commande c'
    . ' JOIN utilisateur u ON c.utilisateur_id = u.id'

;

$stmt = $pdo->query($query);
$commandes =$stmt->fetchAll();

$statuts = [
    'en cours',
    'envoyé',
    'livré'
];

include __DIR__ . '/../layout/top.php';
?> 
<h1>Gestion commandes</h1>

	<table class="table table-bordered">

	    <tr style="background-color:black; color:white" >
	      	<th class="text-center" scope="col">Référence commande</th>
	      	<th class="text-center" scope="col">Client</th>
	      	<th  class="text-center" scope="col">Montant commande</th>
	      	<th class="text-center"  scope="col">Date commande</th>
	      	<th class="text-center"  scope="col">Statut</th>
			<th class="text-center"  scope="col">Date statut</th>
		
	      	
	    </tr>
        <?php
        foreach ($commandes as $commande) :
        ?>
            <tr >
                <td class="text-center" ><?= $commande['id']; ?></td>
                <td class="text-center"><?= $commande['utilisateur']; ?></td>
                <td class="text-center"><?= prixFr($commande['montant_total']); ?></td>
                <td class="text-center"><?= $commande['date_commande']; ?></td>
                <td class="text-center">
                    <form method="post" class="form-inline">
                        <select name="statut" class="form-controle" style="margin-right:1rem">
                        <?php
                        foreach ($statuts as $statut) :
                            $selected = ($statut == $commande['statut'])
                                ? 'selected'
                                : ''
                                ;
                        ?>
                            <option value="<?= $statut; ?>" <?= $selected; ?>> <?= ucfirst($statut); ?></option> 
                        <?php
                        endforeach;
                        ?>
                  
                </select>
                <input type="hidden" name="commande-id" value="<?= $commande['id']; ?>">
                <button type="submit" name="modifier-statut" class="btn btn-dark">Modifer</button>
                </form>
                </td>
                <td class="text-center"><?= dateFr($commande['date_statut']); ?></td>
            <tr>
        <?php
        endforeach;
        ?>
    
    </table>






<?php
include __DIR__ . '/../layout/bottom.php';