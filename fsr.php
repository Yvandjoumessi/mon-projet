<?php
// bon_commande.php
$admin = "Jumi Patric";
$date = date('d/m/Y');
$numero_bc = "BC-" . date('Ymd') . "-" . rand(100, 999);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon de Commande</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0a3d2a, #0f5132);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .header-bc {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-placeholder {
            font-size: 2.2rem;
            font-weight: bold;
            color: #28a745;
        }
        h1 {
            text-align: center;
            color: #0f5132;
            margin: 0 0 10px 0;
        }
        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #0f5132;
        }
        .info input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1.05rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .input-cell input, .input-cell select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .btn-group {
            text-align: center;
            margin-top: 40px;
        }
        .btn {
            padding: 14px 35px;
            margin: 0 10px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary {
            background: #28a745;
            color: white;
        }
        .btn-primary:hover { background: #218838; }
        .btn-pdf {
            background: #dc3545;
            color: white;
        }
        .btn-pdf:hover { background: #c82333; }

        @media print {
            body { background: white; padding: 0; }
            .container { box-shadow: none; padding: 20px; }
            .btn-group { display: none; }
            input { border: none; background: transparent; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- En-tête du Bon de Commande -->
    <div class="header-bc">
        <div class="logo-placeholder">
            <i class="fas fa-hospital"></i> CESMAN
        </div>
        <div>
            <h1>BON DE COMMANDE</h1>
            <p style="text-align:center; margin:5px 0;"><strong>N° :</strong> <?php echo $numero_bc; ?></p>
        </div>
        <div style="text-align:right;">
            <strong>Date :</strong> <?php echo $date; ?>
        </div>
    </div>

    <form id="bonCommandeForm">
        
        <!-- Informations Commandé par et Adresse -->
        <div class="info">
            <div>
                <label>Commandé par :</label>
                <input type="text" id="commande_par" value="<?php echo $admin; ?>" required>
            </div>
            <div>
                <label>Adresse :</label>
                <input type="text" id="adresse" value="Douala, Littoral - Cameroun" required>
            </div>
        </div>

        <!-- Fournisseur -->
        <div style="margin-bottom: 25px;">
            <label><strong>Fournisseur :</strong></label>
            <select id="fournisseur" style="width:100%; padding:10px; font-size:1.05rem;" onchange="updateFournisseur()">
                <option value="Pharmacie Centrale">Pharmacie Centrale - Fournisseur 1</option>
                <option value="Medica Supply">Medica Supply - Fournisseur 2</option>
            </select>
        </div>

        <!-- Tableau des produits -->
        <table id="tableProduits">
            <thead>
                <tr>
                    <th width="40%">Désignation (Médicament / Produit)</th>
                    <th width="15%">Quantité</th>
                    <th width="20%">Prix Unitaire (FCFA)</th>
                    <th width="20%">Montant Total</th>
                    <th width="5%"></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="input-cell"><input type="text" placeholder="Ex: Paracétamol 500mg" required></td>
                    <td class="input-cell"><input type="number" min="1" value="50" oninput="calculerTotal(this)"></td>
                    <td class="input-cell"><input type="number" min="0" value="2500" step="100" oninput="calculerTotal(this)"></td>
                    <td class="montant">125000</td>
                    <td><button type="button" onclick="supprimerLigne(this)" style="color:red;border:none;background:none;cursor:pointer;font-size:1.5rem;">×</button></td>
                </tr>
            </tbody>
        </table>

        <div style="text-align:right; margin:20px 0;">
            <button type="button" onclick="ajouterLigne()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </button>
        </div>

        <!-- Total Général -->
        <table style="width:40%; margin-left:auto;">
            <tr class="total-row">
                <td><strong>TOTAL HT</strong></td>
                <td id="totalGeneral" style="text-align:right; font-size:1.4rem;">125000 FCFA</td>
            </tr>
        </table>

        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="enregistrerCommande()">
                <i class="fas fa-save"></i> Enregistrer la commande
            </button>
            <button type="button" class="btn btn-pdf" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer en PDF
            </button>
        </div>
    </form>
</div>

<script>
// Mise à jour du nom du fournisseur
function updateFournisseur() {
    // Optionnel : tu peux afficher le fournisseur sélectionné ailleurs si besoin
}

// Calcul du total d'une ligne
function calculerTotal(input) {
    const row = input.parentElement.parentElement;
    const qty = parseFloat(row.cells[1].querySelector('input').value) || 0;
    const prix = parseFloat(row.cells[2].querySelector('input').value) || 0;
    const montant = qty * prix;
    row.cells[3].textContent = montant.toLocaleString('fr-FR');
    calculerTotalGeneral();
}

// Calcul du total général
function calculerTotalGeneral() {
    let total = 0;
    document.querySelectorAll('.montant').forEach(m => {
        total += parseFloat(m.textContent.replace(/\s/g, '')) || 0;
    });
    document.getElementById('totalGeneral').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

// Ajouter une nouvelle ligne
function ajouterLigne() {
    const tbody = document.querySelector('#tableProduits tbody');
    const nouvelleLigne = document.createElement('tr');
    nouvelleLigne.innerHTML = `
        <td class="input-cell"><input type="text" placeholder="Ex: Paracétamol 500mg" required></td>
        <td class="input-cell"><input type="number" min="1" value="10" oninput="calculerTotal(this)"></td>
        <td class="input-cell"><input type="number" min="0" value="3000" step="100" oninput="calculerTotal(this)"></td>
        <td class="montant">30000</td>
        <td><button type="button" onclick="supprimerLigne(this)" style="color:red;border:none;background:none;cursor:pointer;font-size:1.5rem;">×</button></td>
    `;
    tbody.appendChild(nouvelleLigne);
    calculerTotalGeneral();
}

// Supprimer une ligne
function supprimerLigne(btn) {
    if (confirm("Supprimer cette ligne ?")) {
        btn.parentElement.parentElement.remove();
        calculerTotalGeneral();
    }
}

// Enregistrer (simulation)
function enregistrerCommande() {
    const commandePar = document.getElementById('commande_par').value;
    alert("✅ Bon de commande N° " + "<?php echo $numero_bc; ?>" + " enregistré avec succès par " + commandePar + " !");
}
</script>

</body>
</html>