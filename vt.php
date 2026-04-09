<?php
// Connexion à la base de données avec MySQLi
$host = 'localhost';
$dbname = 'cesman';   // ← Change avec le nom de ta base de données
$username = 'root';           // ← Ton utilisateur MySQL
$password = '';               // ← Ton mot de passe (vide si pas de mot de passe)

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Requête pour récupérer toutes les ventes (triées par date descendante)
$sql = "SELECT id_ventes, nom_med, quantite, prix_med, date_vente 
        FROM ventes 
        ORDER BY date_vente DESC";

$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    die("Erreur de préparation de la requête : " . $mysqli->error);
}

$stmt->execute();
$result = $stmt->get_result();

$ventes = [];
while ($row = $result->fetch_assoc()) {
    $ventes[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>

<!-- BOUTON RETOUR -->
<div style="margin-bottom: 20px; text-align: left;">
    <button onclick="window.history.back();" 
            style="padding: 10px 20px; background:#3498db; color:white; border:none; border-radius:5px; cursor:pointer; transition:0.3s;">
        <i class="fas fa-arrow-left"></i> Retour
    </button>
</div>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte des Ventes - Pharmacie</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #06a766;
        }
        h1 { 
            text-align: center; 
            color: #2c3e50; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #9fa09e; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background-color: #d80d1e; 
            color: white; 
        }
        tr:nth-child(even) { 
            background-color: #ece5e5; 
        }
        .montant { 
            font-weight: bold; 
            color: #27ae60; 
        }
        .total { 
            font-size: 1.2em; 
            margin-top: 20px; 
            text-align: right; 
        }
    </style>
</head>
<body>

    <h1>Compte des Ventes de la Pharmacie</h1>
    <p style="text-align:center;">Toutes les ventes enregistrées par les pharmaciens</p>

    <table>
        <thead>
            <tr>
                <th>ID Vente</th>
                <th>Nom du Médicament</th>
                <th>Quantité</th>
                <th>Prix Unitaire (FCFA)</th>
                <th>Montant (FCFA)</th>
                <th>Date de Vente</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($ventes) > 0): ?>
                <?php foreach ($ventes as $ventes): ?>
                    <tr>
                        <td><?= htmlspecialchars($ventes['id_ventes']) ?></td>
                        <td><?= htmlspecialchars($ventes['nom_med']) ?></td>
                        <td><?= htmlspecialchars($ventes['quantite']) ?></td>
                        <td><?= number_format($ventes['prix_med'], 2) ?></td>
                        <td class="id_ventes"><?= number_format($ventes['id_ventes'], 2) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($ventes['date_vente'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Aucune vente enregistrée pour le moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    // Calcul du total des ventes
    $total = 0;
    foreach ($ventes as $vente) {
        $total += $ventes['id_ventes'];
    }
    ?>
    <div class="total">
        <strong>Total des ventes : <?= number_format($total, 2) ?> FCFA</strong>
    </div>

</body>
</html>

<?php
// Fermeture de la connexion
$mysqli->close();
?>