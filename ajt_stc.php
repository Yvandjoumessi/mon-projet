<?php
$host = 'localhost';
$dbname = 'cesman';   // ← Change avec ton nom de base
$username = 'root';
$password = '';

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) die("Erreur connexion : " . $mysqli->connect_error);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nom_med  = trim($_POST['nom_med'] ?? '');
    $type_med = trim($_POST['type_med'] ?? '');
    $cathegorie_med       = trim($_POST['cathegorie_med'] ?? '');
    $quantite        = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 0;
    $prix_unitaire   = isset($_POST['prix_unitaire']) ? (float)$_POST['prix_unitaire'] :  0;
    $date_expiration        = $_POST['date_expiration'] ?? '';
    $stock_critique  = isset($_POST['stock_critique']) ? (int)$_POST['stock_critique'] : 20;

    if (empty($nom_med)){
$message = '<p style="color:red;">X Le nom du medicament est obligatoire.</p>';
    } elseif(empty($type_med) || $type_med ==='--Choisir--')  { 
        $message = '<p style="color:red;">X Veuillez selectionner un type.</p>';

    }elseif(empty($cathegorie_med)){
$message = '<p style="color:red;">X La cathegorie est obligatoire.</p>';

} elseif($quantite <= 0) {
        $message = '<p style="color:red;">X La quantite doit etre spperieur a 0 . </p>';

    }elseif(empty($date_expiration)){
        $message = '<p style="color:red;">X La date date_expiration est obligatoire . </p>';

    } else {
        $sql = "INSERT INTO stocks (nom_med, type_med, cathegorie_med, quantite, prix_unitaire, date_expiration, stock_critique) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $mysqli->prepare($sql);

if($stmt){

        $stmt->bind_param("sssidsi", $nom_med, $type_med, $cathegorie_med, $quantite, $prix_unitaire, $date_expiration, $stock_critique);
        
        if ($stmt->execute()) {
            $message = '<p style="color:green;">✅ Produit ajouté avec succès !</p>';
            $_POST =[];
        } else {
            $message = '<p style="color:red;">Erreur de la prepartion de la requete !</p>';
        }
            
        $stmt->close();
    }else{
  $message = '<p style="color:green;">erreur de la preparation de la requête !</p>';    
    }
  }
    
}
  
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter au Stock</title>
    <style>
        body {font-family: Arial; margin:40px; background:#f9f9f9;}
        .container {max-width:700px; margin:auto; background:white; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        label {display:block; margin:15px 0 5px; font-weight:bold;}
        input, select {width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;}
        button {background:#27ae60; color:white; padding:12px; width:100%; border:none; border-radius:5px; font-size:16px; cursor:pointer;}
        .message {padding:15px; text-align:center; margin:20px 0;}
    </style>
</head>
<body>
<div class="container">
    <h1>Ajouter un produit au stock</h1>
    <?= $message ?>
    
    <form method="POST">
        <label>Nom du médicament / produit *</label>
        <input type="text" name="nom_med" class = "form-control" placeholder="nom du medicament / produit" required>

        <label>Type *</label>
        <select name="type_med" required>
            <option value="">-- Choisir --</option>
            <option value="Médicament">Médicament</option>
            <option value="Perfusion">Perfusion</option>
            <option value="Solution saline">Solution saline</option>
            <option value="Injectable">Injectable</option>
            <option value="Autre">Autre</option>
        </select>

        <label>Catégorie *</label>

       

        <input type="text" name="cathegorie_med" placeholder="Ex: Antibiotique, Analgésique..." required>

        <label>Quantité ajoutée *</label>
        <input type="number" name="quantite" min="1" required>

        <label>Prix unitaire (FCFA)</label>
        <input type="number" name="prix_unitaire" step="0.01" min="0">

        <label>Date d'expiration *</label>
        <input type="date" name="date_expiration" required>

        <label>Seuil stock critique (ex: 20) *</label>
        <input type="number" name="stock_critique" value="20" min="1" required>

        <button type="submit">Ajouter au stock</button>
    </form>
    
    <p style="text-align:center; margin-top:30px;">
        <a href="stc.php?page=stock">← Retour à la liste du stock</a>
    </p>
</div>
</body>
</html>
<?php $mysqli->close(); ?>