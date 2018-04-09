<?php

/* 
Faire le formulaire d'édition de produit 
-nom : input text - obligatoire
- description : textarea - obligatoire
- reference : input text- obligatoire , 50 caracteres max, unique
- prix : input text - obligatoire 
- categorie : select - obligatoire 
Si le formulaire est bien rempli : INSERT en bdd et redirection vers la liste avec message de confirmation, 
sinon messages d'erreurs et champs pré-remplis avec les valeurs saisies 
Adapter la page pour la modification :
- avoir un bouton dans la page de liste qui pointe vers cette page en passant d'id
    du produit dans l'URL
- si on a un produit dans l'url sans retour de post, faire une requête select pour
    pré-remplir le formulaire
- apdater le traitement pour faire un update au lieu d'un insert si on a un id dans l'url
- adapter la vérification de l'unicité de la référence pour exclure la référence du     produit que l'on modifie de la requête */


require_once __DIR__ . '/../include/init.php';
adminSecurity();
$nom = $description = $reference = $prix =  $categorieId = $photoActuelle ='';

$query = 'SELECT * FROM categorie'; 
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll();

$errors = [];


if (!empty($_POST)){ 
    
    
    // si on a des données venant du formulaire
    // "nettoyage" des données venues du formulaire
    sanitizePost();
    // creer des variables a partir d'un tableau ( les variables ont les noms des clés dans le tableau )
    extract($_POST);
    $categorieId = $_POST['categorie'];

    if (empty($_POST['nom'])){
        $errors[] = 'Le nom est obligatoire';
    } elseif (strlen($_POST['nom'])>50){
        $errors[] = 'Le nom ne doit pas faire plus de 50 caractères';
    }
    if (empty($_POST['description'])){
        $errors[] = 'La description est obligatoire';
    }
    if (empty($_POST['reference'])){
        $errors[] = 'La référence est obligatoire';
    } elseif (strlen($_POST['reference'])>50){
        $errors[] = 'La référence ne doit pas faire plus de 50 caractères';
    }
    else{
        $query = 'SELECT count(*) FROM produit WHERE reference = :reference';
        // en modification, on exclut de la vérification le produit
        // que l'on est en train de modifier
       if (isset($_GET['id'])){
            $query.= ' AND id != ' . $_GET['id'];

        }

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':reference', $_POST['reference']);
        $stmt->execute();
        $nb = $stmt->fetchColumn();

        if($nb != 0){
            $errors[] = "Il existe déjà un produit ayant cette référence";
        }
    }
    if (empty($_POST['prix'])){
        $errors[] = 'Le prix est obligatoire';
    }
    if (empty($_POST['categorie'])){
        $errors[] = 'La catégorie est obligatoire';
    }

    // si une image a été téléchargée 
    if(!empty($_FILES['photo']['tmp_name'])){
        // $_FILES['photo']['size'] = le poids du fichier en octets
        if($_FILES['photo']['size'] > 1000000){
            $errors[] = 'La photo ne doit pas faire plus de 1Mo';
        }
        $allowedMimeTypes = [
            'image/png', 
            'image/jpeg',
            'image/gif'

        ];
        if (!in_array($_FILES['photo']['type'], $allowedMimeTypes)){
            $errors[] = 'La photo doit être une image GIF, JPG ou PNG';

        }

    }

    if(empty($errors)){
        if(!empty($_FILES['photo']['tmp_name'])){
            $originalName = $_FILES['photo']['tmp_name'];
            // on retrouve l'extension du fichier original à partir de son nom
            // (ex: .png pour mon_fichier.png)
            $extension = substr($originalName, strpos($originalName, '.'));
            // le nom que va avoir le fichier dans le répertoire photo
            $nomPhoto = $_POST['reference'] . $extension;

            // En modification, si le produit avait déjà une photo 
            // on la supprime 
            if (!empty($photoActuelle)){
                unlink(PHOTO_DIR . $photoActuelle);
            }
            // enregistrement du fichier dans le répertoire photo
            move_uploaded_file($_FILES['photo']['tmp_name'], PHOTO_DIR . $nomPhoto);
        } else{
            $nomPhoto = $photoActuelle;
        }
    }

    // si le formulaire est correctement rempli
    if(empty($errors)) {
        if (isset($_GET['id'])){
            $query =<<<EOS
                UPDATE produit SET
                    nom = :nom,
                    description = :description,
                    reference = :reference,
                    prix = :prix,
                    categorie_id = :categorie_id,
                    photo = :photo
                WHERE id = :id
EOS;
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':nom', $_POST['nom']);
            $stmt->bindValue(':description', $_POST['description']);
            $stmt->bindValue(':reference', $_POST['reference']);
            $stmt->bindValue(':prix', $_POST['prix']);
            $stmt->bindValue(':categorie_id', $_POST['categorie']);
            $stmt->bindValue(':photo', $nomPhoto);
            $stmt->bindValue(':id', $_GET['id']);
            $stmt->execute();

        }else{
            
    

        // insertion en bdd
            $query =<<<EOS
            INSERT INTO produit(
                nom,
                description,
                reference,
                prix,
                categorie_id,
                photo
            )VALUES(
                :nom,
                :description,
                :reference,
                :prix,
                :categorie_id,
                :photo
            )
EOS;
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':nom', $_POST['nom']);
            $stmt->bindValue(':description', $_POST['description']);
            $stmt->bindValue(':reference', $_POST['reference']);
            $stmt->bindValue(':prix', $_POST['prix']);
            $stmt->bindValue(':categorie_id', $_POST['categorie']);
            $stmt->bindValue(':photo', $nomPhoto);
            $stmt->execute();

            }
        // enregistrement d'un message en session
        setFlashMessage('Le produit est enregistré');

        header('Location: produits.php');
        die;

    }
}
    elseif (isset($_GET['id'])){
        // en modification, si on n'a pas de retour de formulaire
        //on va chercher la catégorie en BDD pour affichage
        $query = 'SELECT * FROM produit WHERE id= '. $_GET['id'];
        $stmt = $pdo->query($query);
        $produit = $stmt->fetch();
        $nom = $produit['nom'];
        $description = $produit['description'];
        $reference = $produit['reference'];
        $prix = $produit['prix'];
        $categorieId = $produit['categorie_id'];
        $photoActuelle = $produit['photo'];
    }



include __DIR__ . '/../layout/top.php';

?>
<?php
if(!empty($errors)):
?>
    <div class="alert alert-danger">
        <h4 class="alert-heading">Le formulaire contient des erreurs </h4>
        <?= implode('<br>', $errors); // implode tranforme un tableau en chaine de caractere ?>
    </div>

<?php
endif;
?>

<h1>Edition produit</h1>

<!--
    L'attribut enctype est obligatoire pour un formulaire
    qui contient un télécharhement de fichier
-->

<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Nom</label>
        <input type="text"  name="nom" class="form-control" value="<?= $nom; ?>">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea  name="description"  class="form-control"><?=$description; ?></textarea>
    </div>
    <div class="form-group">
        <label>Réference</label>
        <input type="text" name="reference"  class="form-control" value="<?= $reference; ?>">
    </div>
    <div class="form-group">
        <label>Prix</label>
        <input type="text" name="prix"  class="form-control" value="<?= $prix; ?>">
    </div>
    <div class="form-group">
        <label>Catégorie</label>
        <select name="categorie"  class="form-control">
            <option value=""></option>
            <?php
            foreach ($categories as $categorie) :
                $selected = ($categorie['id'] == $categorieId)
                ?'selected'
                : '';
            ?>
            <option value="<?= $categorie['id']; ?>" <?= $selected;?>><?= $categorie['nom']; ?></option>
            <?php
            endforeach;
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>Photo</label>
        <input type="file" name="photo">
    </div>
    <?php
        if(!empty($photoActuelle)):

        echo '<p>Actuellement : <br><img src="'
         . PHOTO_WEB . $photoActuelle 
         .'"height="150px"></img></p>';
    
    endif;
    ?>
    <input type="hidden" name="photoActuelle" value="<?= $photoActuelle; ?>">
    <div class="form-btn-group text-right">
        <button type="submit" class="btn btn-primary"> Enregistrer </button>
        <a class="btn btn-secondary" href="produits.php">Retour</a>

    </div>
</form>

<?php
include __DIR__ . '/../layout/bottom.php';
?>