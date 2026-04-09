<?php
// Connexion MySQLi
$host = 'localhost';
$dbname = 'cesman';   // ← Change ici
$username = 'root';
$password = '';

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Requête pour récupérer tous les stocks avec alertes
$sql = "SELECT id_stocks, nom_med, type_med, cathegorie_med, quantite, 
               prix_unitaire, date_enregistrement, date_expiration
        FROM stocks 
        ORDER BY date_expiration ASC";   // Tri par date d'expiration la plus proche

        

$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    die("Erreur de préparation de la requête : " . $mysqli->error);
}



$stmt->execute();
$result = $stmt->get_result();

$stocks = [];
while ($row = $result->fetch_assoc()) {
    $stocks[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Stock - Pharmacie</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #098f5c; }
        h1 { text-align: center; color: #2c3e50; }
        .container { max-width: 1200px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #3498db; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        
        /* Alertes */
        .alert-danger { background-color: #f8d7da; color: #721c24; font-weight: bold; }
        .alert-warning { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .normal { background-color: #d4edda; }
        
        .total { font-size: 1.2em; margin: 20px 0; text-align: right; }
        .btn { padding: 8px 15px; background: #27ae60; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Stock des Médicaments & Produits</h1>
    <p style="text-align:center;">Gestion complète du stock (médicaments, perfusions, solutions salées, etc.)</p>
    
    <a href="ajt_stc.php" class="btn">+ Ajouter un nouveau produit au stock</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Produit</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Quantité en stock</th>
                <th>Prix Unitaire (FCFA)</th>
                <th>Date d'enregistrement</th>
                <th>Date d'expiration</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($stocks) > 0): ?>
                <?php foreach ($stocks as $item): 
                    $today = date('Y-m-d');
                    $jours_restants = (strtotime($item['date_expiration']) - strtotime($today)) / (60*60*24);
                    
                    $classe = '';
                    $statut = 'Normal';
                    
                    if ($item['quantite'] <= 10) { 
                        $classe = 'alert-danger'; 
                        $statut = 'Stock faible / Rupture !';
                    } elseif ($jours_restants <= 90 && $jours_restants > 0) { 
                        $classe = 'alert-warning'; 
                        $statut = 'Expiration proche (' . round($jours_restants) . ' jours)';
                    } elseif ($jours_restants <= 0) { 
                        $classe = 'alert-danger'; 
                        $statut = 'Périmé !';
                    }
                ?>
                    <tr class="<?= $classe ?>">
                        <td><?= htmlspecialchars($item['id_stocks']) ?></td>
                        <td><?= htmlspecialchars($item['nom_med']) ?></td>
                        <td><?= htmlspecialchars($item['type_med'] ?? 'Non défini') ?></td>
                        <td><?= htmlspecialchars($item['cathegorie_med'] ?? 'Non définie') ?></td>
                        <td><strong><?= htmlspecialchars($item['quantite']) ?></strong></td>
                        <td><?= $item['prix_unitaire'] ? number_format($item['prix_unitaire'], 2) : '—' ?></td>
                        <td><?= date('d/m/Y', strtotime($item['date_enregistrement'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['date_expiration'])) ?></td>
                        <td><?= $statut ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:30px;">Aucun produit dans le stock pour le moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total">
        <strong>Nombre total de produits en stock : <?= count($stocks) ?></strong>
    </div>
</div>

</body>
</html>

<?php $mysqli->close(); ?>