<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();
$query = 'SELECT * FROM categorie'; 
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll();


include __DIR__ . '/../layout/top.php';
?>

<h1>Gestion catégories</h1>

<p>
    <a class="btn btn-info" href="categorie-edit.php">Ajouter une catégorie</a>
    
</p>
<!-- Le tableau HTMl ici -->

<?php
     echo '<table class="table">';
     echo '<tr>';
     echo '<th>Id</th>';
     echo '<th>Nom</th>';
     echo '<th width="250px"></th>';
     echo '</tr>';
     foreach($categories as $categorie):
 ?>
		<tr>
			<td><?=$categorie['id']; ?></td>
			<td><?=$categorie['nom']; ?></td>
			<td><a class="btn btn-info"
				href="categorie-edit.php?id=<?=$categorie['id']; ?>">  Modifier  </a>
                <a class="btn btn-danger"
				href="categorie-delete.php?id=<?=$categorie['id']; ?>">Supprimer</a></td>
		</tr>
<?php
endforeach;

echo '</table>';
?>
<?php
include __DIR__. '/../layout/bottom.php';