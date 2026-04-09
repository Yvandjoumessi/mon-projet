<?php
// -------------------------------
// CONFIGURATION
// -------------------------------
$nomCentre = "CENTRE DE SANTE DES MAJORS DE NKONGSAMBA";
$sigleCentre = "CESMAN";
$logoPath = "lg.jpeg"; // Placez votre logo dans le même dossier sous ce nom

// -------------------------------
// GENERATION AUTOMATIQUE NUMERO FACTURE
// -------------------------------
$counterFile = "facture_counter.txt";

if (!file_exists($counterFile)) {
    file_put_contents($counterFile, "0");
}

$lastNumber = (int) file_get_contents($counterFile);
$currentNumber = $lastNumber + 1;

// Numéro formaté : FAC-00001
$numeroFacture = "FAC-" . str_pad($currentNumber, 5, "0", STR_PAD_LEFT);

// -------------------------------
// DONNEES PAR DEFAUT
// -------------------------------
$dateFacture = date("Y-m-d");
$clientNom = "";
$clientContact = "";
$lignes = [];

// 5 lignes vides par défaut
for ($i = 0; $i < 5; $i++) {
    $lignes[] = [
        'designation' => '',
        'quantite' => '',
        'prix_unitaire' => ''
    ];
}

// -------------------------------
// TRAITEMENT FORMULAIRE
// -------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateFacture = $_POST['date_facture'] ?? date("Y-m-d");
    $clientNom = $_POST['client_nom'] ?? "";
    $clientContact = $_POST['client_contact'] ?? "";

    $designations = $_POST['designation'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prixUnitaires = $_POST['prix_unitaire'] ?? [];

    $lignes = [];
    for ($i = 0; $i < count($designations); $i++) {
        $lignes[] = [
            'designation' => trim($designations[$i]),
            'quantite' => trim($quantites[$i]),
            'prix_unitaire' => trim($prixUnitaires[$i])
        ];
    }

    // On incrémente uniquement lorsque la facture est générée
    file_put_contents($counterFile, $currentNumber);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générateur de facture - CESMAN</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #f4f6f8;
        }

        .container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .formulaire, .facture-zone {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 20px;
        }

        .formulaire {
            width: 38%;
        }

        .facture-zone {
            width: 62%;
        }

        h2, h3 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 12px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cfd6dd;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .saisie-table th,
        .saisie-table td,
        .facture-table th,
        .facture-table td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }

        .saisie-table th,
        .facture-table th {
            background: #e9eef3;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        button {
            padding: 12px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }

        .btn-generate {
            background: #007bff;
        }

        .btn-print {
            background: #28a745;
        }

        .btn-reset {
            background: #dc3545;
        }

        /* FACTURE */
        .facture {
            width: 100%;
            border: 2px solid #222;
            padding: 20px;
            background: white;
        }

        .facture-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .logo-box {
            width: 140px;
            height: 120px;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fafafa;
        }

        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .header-center {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }

        .header-center h1 {
            font-size: 24px;
            margin: 0 0 8px;
            text-transform: uppercase;
        }

        .header-center h2 {
            margin: 0;
            font-size: 18px;
            font-weight: normal;
        }

        .facture-infos {
            margin-bottom: 20px;
        }

        .facture-infos p {
            margin: 6px 0;
            font-size: 15px;
        }

        .facture-table td {
            height: 38px;
        }

        .text-right {
            text-align: right;
        }

        .total-box {
            margin-top: 15px;
            width: 100%;
        }

        .total-box table {
            width: 100%;
        }

        .montant-total {
            font-size: 18px;
            font-weight: bold;
            background: #f0f0f0;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 13px;
            text-align: center;
            color: #555;
        }

        .no-print {
            display: block;
        }

        .print-only {
            display: none;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .container {
                display: block;
            }

            .facture-zone {
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }

            .facture {
                border: none;
                width: 100%;
                padding: 10mm;
            }

            .print-only {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- FORMULAIRE DE SAISIE -->
    <div class="formulaire no-print">
        <h2>Formulaire de remplissage</h2>

        <form method="POST" id="factureForm">
            <div class="form-group">
                <label>Date de facture</label>
                <input type="date" name="date_facture" id="date_facture" value="<?= htmlspecialchars($dateFacture) ?>">
            </div>

            <div class="form-group">
                <label>Nom du patient / client</label>
                <input type="text" name="client_nom" id="client_nom" value="<?= htmlspecialchars($clientNom) ?>" placeholder="Nom complet">
            </div>

            <div class="form-group">
                <label>Contact</label>
                <input type="text" name="client_contact" id="client_contact" value="<?= htmlspecialchars($clientContact) ?>" placeholder="Téléphone / autre">
            </div>

            <h3>Articles / Médicaments</h3>
            <table class="saisie-table">
                <thead>
                    <tr>
                        <th>Médicaments / Articles</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lignes as $index => $ligne): ?>
                    <tr>
                        <td>
                            <input 
                                type="text" 
                                name="designation[]" 
                                class="designation" 
                                value="<?= htmlspecialchars($ligne['designation']) ?>" 
                                oninput="mettreAJourFacture()"
                            >
                        </td>
                        <td>
                            <input 
                                type="number" 
                                name="quantite[]" 
                                class="quantite" 
                                min="0" 
                                step="1" 
                                value="<?= htmlspecialchars($ligne['quantite']) ?>" 
                                oninput="mettreAJourFacture()"
                            >
                        </td>
                        <td>
                            <input 
                                type="number" 
                                name="prix_unitaire[]" 
                                class="prix_unitaire" 
                                min="0" 
                                step="0.01" 
                                value="<?= htmlspecialchars($ligne['prix_unitaire']) ?>" 
                                oninput="mettreAJourFacture()"
                            >
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="btn-group">
                <button type="submit" class="btn-generate">Générer la facture</button>
                <button type="button" class="btn-print" onclick="window.print()">Imprimer / PDF</button>
                <button type="reset" class="btn-reset" onclick="setTimeout(mettreAJourFacture, 100)">Vider</button>
            </div>
        </form>
    </div>

    <!-- APERCU FACTURE -->
    <div class="facture-zone">
        <div class="facture" id="zoneFacture">
            <div class="facture-header">
                <div class="logo-box">
                    <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo du centre">
                </div>

                <div class="header-center">
                    <h1><?= htmlspecialchars($nomCentre) ?></h1>
                    <h2>(<?= htmlspecialchars($sigleCentre) ?>)</h2>
                </div>

                <div style="width: 160px; text-align: right;">
                    <p><strong>FACTURE</strong></p>
                    <p>N° : <span id="aff_numero_facture"><?= htmlspecialchars($numeroFacture) ?></span></p>
                    <p>Date : <span id="aff_date_facture"><?= htmlspecialchars($dateFacture) ?></span></p>
                </div>
            </div>

            <div class="facture-infos">
                <p><strong>Nom du patient / client :</strong> <span id="aff_client_nom"><?= htmlspecialchars($clientNom) ?></span></p>
                <p><strong>Contact :</strong> <span id="aff_client_contact"><?= htmlspecialchars($clientContact) ?></span></p>
            </div>

            <table class="facture-table">
                <thead>
                    <tr>
                        <th>Médicaments / Articles</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody id="factureBody">
                    <?php foreach ($lignes as $ligne): 
                        $qte = is_numeric($ligne['quantite']) ? (float)$ligne['quantite'] : 0;
                        $pu = is_numeric($ligne['prix_unitaire']) ? (float)$ligne['prix_unitaire'] : 0;
                        $montant = $qte * $pu;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['designation']) ?></td>
                        <td><?= htmlspecialchars($ligne['quantite']) ?></td>
                        <td><?= $pu ? number_format($pu, 2, ',', ' ') : '' ?></td>
                        <td><?= $montant ? number_format($montant, 2, ',', ' ') : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
                $total = 0;
                foreach ($lignes as $ligne) {
                    $qte = is_numeric($ligne['quantite']) ? (float)$ligne['quantite'] : 0;
                    $pu = is_numeric($ligne['prix_unitaire']) ? (float)$ligne['prix_unitaire'] : 0;
                    $total += $qte * $pu;
                }
            ?>

            <div class="total-box">
                <table>
                    <tr class="montant-total">
                        <td class="text-right" style="padding-right: 20px;">Montant total à payer :</td>
                        <td style="width: 220px;" id="montantTotal"><?= number_format($total, 2, ',', ' ') ?> FCFA</td>
                    </tr>
                </table>
            </div>

            <div class="footer-note">
                Merci pour votre confiance.
            </div>
        </div>
    </div>
</div>

<script>
function formatNombre(nombre) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(nombre);
}

function mettreAJourFacture() {
    const dateFacture = document.getElementById('date_facture').value;
    const clientNom = document.getElementById('client_nom').value;
    const clientContact = document.getElementById('client_contact').value;

    document.getElementById('aff_date_facture').textContent = dateFacture;
    document.getElementById('aff_client_nom').textContent = clientNom;
    document.getElementById('aff_client_contact').textContent = clientContact;

    const designations = document.querySelectorAll('.designation');
    const quantites = document.querySelectorAll('.quantite');
    const prixUnitaires = document.querySelectorAll('.prix_unitaire');
    const factureBody = document.getElementById('factureBody');

    factureBody.innerHTML = '';
    let totalGeneral = 0;

    for (let i = 0; i < designations.length; i++) {
        const designation = designations[i].value;
        const quantite = parseFloat(quantites[i].value) || 0;
        const prixUnitaire = parseFloat(prixUnitaires[i].value) || 0;
        const montant = quantite * prixUnitaire;

        totalGeneral += montant;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${designation}</td>
            <td>${quantite ? quantite : ''}</td>
            <td>${prixUnitaire ? formatNombre(prixUnitaire) : ''}</td>
            <td>${montant ? formatNombre(montant) : ''}</td>
        `;
        factureBody.appendChild(tr);
    }

    document.getElementById('montantTotal').textContent = formatNombre(totalGeneral) + ' FCFA';
}

// Mise à jour initiale au chargement
mettreAJourFacture();
</script>

</body>
</html>