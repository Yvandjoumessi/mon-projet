<?php
// ==================== CONNEXION À LA BASE DE DONNÉES ====================
$conn = new mysqli("localhost", "root", "", "cesman");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// ==================== RÉCUPÉRATION DES PARAMÈTRES D'ALERTES ====================
// On récupère les seuils configurés par l'administrateur (table alert_settings)
$alertSettings = $conn->query("SELECT * FROM alert_settings WHERE id = 1 LIMIT 1");


$stock_min_threshold     = $settings['stock_min_threshold'] ?? 10;      // Seuil stock bas
$expiration_threshold    = $settings['expiration_threshold_days'] ?? 30; // Jours avant expiration
$enable_stock_alert      = $settings['enable_stock_alert'] ?? 1;
$enable_expiration_alert = $settings['enable_expiration_alert'] ?? 1;

// ==================== RÉCUPÉRER TOUS LES MÉDICAMENTS ====================
$result = $conn->query("SELECT * FROM medicaments ORDER BY id_med DESC");

// ==================== PRÉPARATION DES ALERTES AUTOMATIQUES ====================
$alerteMeds = [];

if ($result && $result->num_rows > 0) {
    $result->data_seek(0); // Réinitialiser le pointeur
    while ($row = $result->fetch_assoc()) {
        
        // === Alerte Expiration / Prévention ===
        if ($enable_expiration_alert && !empty($row['date_expiration'])) {
            $daysLeft = (strtotime($row['date_expiration']) - time()) / (86400); // secondes en jour
            
            if ($daysLeft < 0) {
                $alerteMeds[] = "⚠️ Le médicament '{$row['nom_med']}' est EXPIrÉ !";
            } elseif ($daysLeft <= $expiration_threshold) {
                $alerteMeds[] = "⏰ Le médicament '{$row['nom_med']}' va expirer dans {$daysLeft} jours !";
            }
        }

        // === Alerte Stock Bas ===
        if ($enable_stock_alert && $row['quantite_med'] <= $stock_min_threshold) {
            $alerteMeds[] = "📉 Stock bas ! '{$row['nom_med']}' → seulement {$row['quantite_med']} unités restantes (seuil : $stock_min_threshold)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau des Médicaments - Alertes Automatiques</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #02a071; 
            margin: 0; 
            padding: 20px; 
            color: #333;
        }
        h1 { 
            text-align: center; 
            margin-bottom: 20px; 
            color: white;
        }
        .table-container { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 12px; 
            text-align: center; 
            border-bottom: 1px solid #99d4ab; 
        }
        th { 
            background: #2c3e50; 
            color: white; 
        }
        tr:nth-child(even) { 
            background: #f8f9fa; 
        }
        .alert-expire { 
            background: #e74c3c !important; 
            color: white; 
            font-weight: bold; 
        }
        .alert-warning { 
            background: #f39c12 !important; 
            color: white; 
            font-weight: bold; 
        }
        .alert-stock-low { 
            background: #e67e22 !important; 
            color: white; 
            font-weight: bold; 
        }
        .alert-info {
            background: #3498db;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.05rem;
        }
        button {
            padding: 10px 20px; 
            background: #3498db; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

<!-- Bouton Retour -->
<div style="margin-bottom: 20px; text-align: left;">
    <button onclick="window.history.back();">
        <i class="fas fa-arrow-left"></i> Retour
    </button>
</div>

<h1>📋 Tableau Dynamique des Médicaments</h1>

<?php if (!empty($alerteMeds)): ?>
    <div class="alert-info">
        <strong>🛎️ Alertes automatiques détectées :</strong><br>
        <?php echo nl2br(implode("<br>", $alerteMeds)); ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID Méd</th>
                <th>Catégorie</th>
                <th>Nom Médicament</th>
                <th>Quantité</th>
                <th>Prix</th>
                <th>Emplacement</th>
                <th>Date d'expiration</th>
                <th>Statut Alerte</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                $result->data_seek(0); // Réinitialiser le pointeur

                while ($row = $result->fetch_assoc()) {
                    $expiryDate = $row['date_expiration'] ?? 'Non définie';
                    $daysLeft = !empty($row['date_expiration']) 
                        ? (strtotime($row['date_expiration']) - time()) / 86400 
                        : 999;

                    $rowClass = "";
                    $statusText = "";

                    // Détermination des classes et textes d'alerte
                    if ($daysLeft < 0) {
                        $rowClass = "alert-expire";
                        $statusText = "EXPIrÉ !";
                    } elseif ($daysLeft <= $expiration_threshold && $enable_expiration_alert) {
                        $rowClass = "alert-warning";
                        $statusText = "Expire bientôt (" . round($daysLeft) . " jours)";
                    } elseif ($row['quantite_med'] <= $stock_min_threshold && $enable_stock_alert) {
                        $rowClass = "alert-stock-low";
                        $statusText = "Stock bas ! (" . $row['quantite_med'] . ")";
                    }

                    echo "<tr class='$rowClass'>
                        <td>{$row['id_med']}</td>
                        <td>{$row['cathegorie_med']}</td>
                        <td>{$row['nom_med']}</td>
                        <td>{$row['quantite_med']}</td>
                        <td>{$row['prix_med']}</td>
                        <td>{$row['emplacement']}</td>
                        <td>{$expiryDate}</td>
                        <td><strong>{$statusText}</strong></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Aucun médicament enregistré.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Notification popup pour le pharmacien (toutes les alertes en une seule fenêtre)
let alertes = <?php echo json_encode($alerteMeds); ?>;

if (alertes.length > 0) {
    let message = "🛎️ ALERTES AUTOMATIQUES\n\n" + alertes.join("\n\n");
    alert(message);
}
</script>

</body>
</html>