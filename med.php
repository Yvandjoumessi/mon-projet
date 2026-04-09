<?php
// ==================== CONNEXION À LA BASE DE DONNÉES ====================
$conn = new mysqli("localhost", "root", "", "cesman");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// ==================== RÉCUPÉRATION DES PARAMÈTRES D'ALERTES ====================
$alertSettings = $conn->query("SELECT * FROM alert_settings WHERE id = 1 LIMIT 1");


$stock_min_threshold     = $settings['stock_min_threshold'] ?? 10;
$expiration_threshold    = $settings['expiration_threshold_days'] ?? 30;
$enable_stock_alert      = $settings['enable_stock_alert'] ?? 1;
$enable_expiration_alert = $settings['enable_expiration_alert'] ?? 1;

// ==================== RÉCUPÉRER TOUS LES MÉDICAMENTS ====================
$result = $conn->query("SELECT id_med, cathegorie_med, nom_med, quantite_med, prix_med, date_expiration 
                        FROM medicaments ORDER BY id_med DESC");

$medicaments = [];
$alerteMeds = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $medicaments[] = $row;

        // === Alertes automatiques ===
        $daysLeft = !empty($row['date_expiration']) 
            ? (strtotime($row['date_expiration']) - time()) / 86400 
            : 999;

        if ($enable_expiration_alert) {
            if ($daysLeft < 0) {
                $alerteMeds[] = "⚠️ '{$row['nom_med']}' est EXPIrÉ !";
            } elseif ($daysLeft <= $expiration_threshold) {
                $alerteMeds[] = "⏰ '{$row['nom_med']}' expire dans " . round($daysLeft) . " jours";
            }
        }

        if ($enable_stock_alert && $row['quantite_med'] <= $stock_min_threshold) {
            $alerteMeds[] = "📉 Stock bas : '{$row['nom_med']}' → seulement {$row['quantite_med']} unités";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace des Médicaments - Pharmacie du Cesseman</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f5132, #1a7d4f);
            margin: 0;
            padding: 20px;
            color: #333;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        header {
            background: #0f5132;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .search-bar {
            padding: 20px 30px;
            background: #f8f9fa;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-bar input {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 1.05rem;
        }

        .search-bar button {
            padding: 14px 28px;
            background: #9acd32; /* vert citron */
            color: #0f5132;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-bar button:hover {
            background: #7cb82a;
            transform: translateY(-2px);
        }

        .table-container {
            padding: 0 30px 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #0f5132;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background: #f1f8f4;
        }

        .status-btn {
            padding: 8px 22px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-d {
            background: #4caf50;
        }

        .status-pd {
            background: #f44336;
        }

        .no-result {
            text-align: center;
            padding: 40px;
            color: #f44336;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 30px;
        }

        button.back {
            padding: 10px 20px;
            background: #ff9800;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">

    <header>
        <h1>📋 Espace des Médicaments - Pharmacie du Cesseman</h1>
        <button class="back" onclick="window.history.back();">
            <i class="fas fa-arrow-left"></i> Retour
        </button>
    </header>

    <?php if (!empty($alerteMeds)): ?>
        <div class="alert-box">
            <strong>🛎️ Alertes automatiques :</strong><br>
            <?= nl2br(implode("<br>", $alerteMeds)); ?>
        </div>
    <?php endif; ?>

    <!-- BARRE DE RECHERCHE -->
    <div class="search-bar">
        <input type="text" id="search-input" placeholder="Rechercher un médicament par nom...">
        <button id="search-btn">Rechercher</button>
    </div>

    <div class="table-container">
        <table id="medicaments-table">
            <thead>
                <tr>
                    <th>ID Médicament</th>
                    <th>Catégorie</th>
                    <th>Nom du médicament</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Date d'expiration</th>
                    <th>Disponibilité</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($medicaments)): ?>
                    <tr><td colspan="7" class="no-result">Aucun médicament enregistré dans le système.</td></tr>
                <?php else: ?>
                    <?php foreach ($medicaments as $med): 
                        $disponible = $med['quantite_med'] > 0;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($med['id_med']) ?></td>
                            <td><?= htmlspecialchars($med['cathegorie_med']) ?></td>
                            <td><?= htmlspecialchars($med['nom_med']) ?></td>
                            <td><?= $med['quantite_med'] ?></td>
                            <td><?= number_format($med['prix_med'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= $med['date_expiration'] ?? 'Non définie' ?></td>
                            <td>
                                <button class="status-btn <?= $disponible ? 'status-d' : 'status-pd' ?>">
                                    <?= $disponible ? 'D' : 'PD' ?>
                                </button>
                                <span style="margin-left:8px; font-weight:500;">
                                    <?= $disponible ? 'Disponible' : 'Pas disponible' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Recherche en temps réel + bouton
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const tableBody = document.querySelector('#medicaments-table tbody');

function filterTable() {
    const term = searchInput.value.toLowerCase().trim();
    const rows = tableBody.getElementsByTagName('tr');
    let found = false;

    for (let row of rows) {
        if (row.cells.length < 3) continue; // ignorer ligne "aucun médicament"

        const nomMed = row.cells[2].textContent.toLowerCase(); // colonne Nom du médicament
        if (nomMed.includes(term)) {
            row.style.display = '';
            found = true;
        } else {
            row.style.display = 'none';
        }
    }

    // Message si aucun résultat
    let noResultRow = document.getElementById('no-result-row');
    if (!noResultRow) {
        noResultRow = document.createElement('tr');
        noResultRow.id = 'no-result-row';
        noResultRow.innerHTML = `<td colspan="7" class="no-result">Le médicament recherché n'est pas disponible.</td>`;
        tableBody.appendChild(noResultRow);
    }

    noResultRow.style.display = found || term === '' ? 'none' : '';
}

searchBtn.addEventListener('click', filterTable);
searchInput.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') filterTable();
});

// Filtre initial au chargement
filterTable();
</script>

</body>
</html>