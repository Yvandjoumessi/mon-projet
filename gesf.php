<?php
// ==================== CONNEXION BDD ====================
$conn = new mysqli("localhost", "root", "", "cesman");
if ($conn->connect_error) die("Erreur de connexion : " . $conn->connect_error);

// ==================== CRÉATION TABLE VENTES ====================
$conn->query("CREATE TABLE IF NOT EXISTS ventes (
    id_vente INT AUTO_INCREMENT PRIMARY KEY,
    id_med INT NOT NULL,
    nom_med VARCHAR(200) NOT NULL,
    quantite_vendue INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    date_vente TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ==================== TRAITEMENTS (AJOUT / MODIF / SUPPR / VENTE) ====================
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id_med        = $_POST['id_med'] ?? 0;
        $cathegorie    = trim($_POST['cathegorie_med']);
        $nom           = trim($_POST['nom_med']);
        $quantite      = (int)$_POST['quantite_med'];
        $prix          = (float)$_POST['prix_med'];
        $emplacement   = trim($_POST['emplacement']);
        $date_exp      = $_POST['date_expiration'];

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO medicaments (cathegorie_med, nom_med, quantite_med, prix_med, emplacement, date_expiration) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssidss", $cathegorie, $nom, $quantite, $prix, $emplacement, $date_exp);
            $stmt->execute();
            $message = "<div class='alert alert-success'>✅ Médicament ajouté avec succès !</div>";
        } else {
            $stmt = $conn->prepare("UPDATE medicaments SET cathegorie_med=?, nom_med=?, quantite_med=?, prix_med=?, emplacement=?, date_expiration=? WHERE id_med=?");
            $stmt->bind_param("ssidssi", $cathegorie, $nom, $quantite, $prix, $emplacement, $date_exp, $id_med);
            $stmt->execute();
            $message = "<div class='alert alert-success'>✅ Médicament modifié avec succès !</div>";
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id_med'];
        $conn->query("DELETE FROM medicaments WHERE id_med = $id");
        $message = "<div class='alert alert-success'>✅ Médicament supprimé !</div>";
    }

    if ($action === 'vente') {
        $id_med   = (int)$_POST['id_med_vente'];
        $qte      = (int)$_POST['quantite_vendue'];
        
        $res = $conn->query("SELECT nom_med, prix_med, quantite_med FROM medicaments WHERE id_med = $id_med");
        $med = $res->fetch_assoc();
        
        if ($med && $med['quantite_med'] >= $qte) {
            $prix_total = $qte * $med['prix_med'];
            $stmt = $conn->prepare("INSERT INTO ventes (id_med, nom_med, quantite_vendue, prix_unitaire, prix_total) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isidd", $id_med, $med['nom_med'], $qte, $med['prix_med'], $prix_total);
            $stmt->execute();
            
            $conn->query("UPDATE medicaments SET quantite_med = quantite_med - $qte WHERE id_med = $id_med");
            $message = "<div class='alert alert-success'>✅ Vente enregistrée ! Stock mis à jour.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Stock insuffisant ou médicament introuvable.</div>";
        }
    }
}

// ==================== RÉCUPÉRATION DONNÉES ====================
$medicaments = $conn->query("SELECT * FROM medicaments ORDER BY id_med DESC");
$ventes      = $conn->query("SELECT * FROM ventes ORDER BY date_vente DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Pharmacien - Gestion & Ventes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .carre { height: 180px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:bold; color:white; border-radius:15px; cursor:pointer; box-shadow:0 5px 15px rgba(0,0,0,0.3); transition:0.3s; }
        .carre:hover { transform:scale(1.08); }
        .section { display:none; }
        .active { display:block; }
        #searchInput { font-size: 1.1rem; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="text-center mb-5 fw-bold">💊 Espace Pharmacien</h1>

    <!-- PAGE PRINCIPALE : 2 CARRÉS -->
    <div id="pagePrincipale">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="carre bg-success" onclick="showSection('gestion')">➕ Ajouter / Modifier / Supprimer<br>Médicament</div>
            </div>
            <div class="col-md-6">
                <div class="carre bg-primary" onclick="showSection('vente')">🛒 Espace Vente<br>(Sorties & Mouvements)</div>
            </div>
        </div>
    </div>

    <!-- ====================== SECTION GESTION MÉDICAMENTS ====================== -->
    <div id="gestion" class="section">
        <h2 class="mb-4">Gestion des Médicaments</h2>
        <?= $message ?>
        <!-- Formulaire + Tableau (identique à avant) -->
        <form method="POST" class="row g-3 mb-5 border p-4 rounded bg-white">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_med" id="idMed" value="">
            <div class="col-md-4"><label>Catégorie</label><input type="text" name="cathegorie_med" class="form-control" required></div>
            <div class="col-md-4"><label>Nom du médicament</label><input type="text" name="nom_med" class="form-control" required></div>
            <div class="col-md-4"><label>Quantité</label><input type="number" name="quantite_med" class="form-control" required></div>
            <div class="col-md-4"><label>Prix unitaire</label><input type="number" step="0.01" name="prix_med" class="form-control" required></div>
            <div class="col-md-4"><label>Emplacement</label><input type="text" name="emplacement" class="form-control"></div>
            <div class="col-md-4"><label>Date d'expiration</label><input type="date" name="date_expiration" class="form-control"></div>
            <div class="col-12"><button type="submit" id="btnSubmit" class="btn btn-success btn-lg w-100">➕ Ajouter le médicament</button></div>
        </form>

        <h4>📋 Liste des médicaments</h4>
        <table class="table table-hover table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Catégorie</th><th>Nom</th><th>Stock</th><th>Prix</th><th>Expiration</th><th>Actions</th></tr></thead>
            <tbody>
                <?php while($m = $medicaments->fetch_assoc()): ?>
                <tr>
                    <td><?= $m['id_med'] ?></td>
                    <td><?= htmlspecialchars($m['cathegorie_med']) ?></td>
                    <td><?= htmlspecialchars($m['nom_med']) ?></td>
                    <td><?= $m['quantite_med'] ?></td>
                    <td><?= $m['prix_med'] ?> FCFA</td>
                    <td><?= $m['date_expiration'] ?? '-' ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editMed(<?= $m['id_med'] ?>, '<?= htmlspecialchars($m['cathegorie_med']) ?>', '<?= htmlspecialchars($m['nom_med']) ?>', <?= $m['quantite_med'] ?>, <?= $m['prix_med'] ?>, '<?= $m['emplacement'] ?? '' ?>', '<?= $m['date_expiration'] ?? '' ?>')">Modifier</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_med" value="<?= $m['id_med'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="gestion_medicaments.php" class="btn btn-outline-dark">← Retour</a>
    </div>

    <!-- ====================== SECTION ESPACE VENTE ====================== -->
    <div id="vente" class="section">
        <h2 class="mb-4">🛒 Espace Vente (Sorties de stock)</h2>
        <?= $message ?>

        <form method="POST" class="row g-3 mb-5 border p-4 rounded bg-white">
            <input type="hidden" name="action" value="vente">
            <div class="col-md-5">
                <label>Médicament à vendre</label>
                <select name="id_med_vente" class="form-select" required>
                    <option value="">-- Choisir un médicament --</option>
                    <?php 
                    $medicaments->data_seek(0);
                    while($m = $medicaments->fetch_assoc()): ?>
                    <option value="<?= $m['id_med'] ?>"><?= htmlspecialchars($m['nom_med']) ?> (Stock : <?= $m['quantite_med'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Quantité vendue</label>
                <input type="number" name="quantite_vendue" class="form-control" min="1" required>
            </div>
            <div class="col-md-4 pt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100">Enregistrer la vente</button>
            </div>
        </form>

        <!-- ====================== BAR DE RECHERCHE ====================== -->
        <div class="mb-3">
            <label class="form-label fw-bold">🔎 Rechercher dans les ventes :</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Tape une date (2026-04-02), un nom de médicament ou une quantité..." onkeyup="filterSales()">
        </div>

        <!-- Tableau des ventes avec recherche dynamique -->
        <h4>📋 Ventes enregistrées</h4>
        <table id="salesTable" class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Médicament</th>
                    <th>Qté vendue</th>
                    <th>Prix total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($v = $ventes->fetch_assoc()): ?>
                <tr>
                    <td><?= date('Y-m-d', strtotime($v['date_vente'])) ?></td>
                    <td><?= htmlspecialchars($v['nom_med']) ?></td>
                    <td><?= $v['quantite_vendue'] ?></td>
                    <td><?= number_format($v['prix_total'], 0) ?> FCFA</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="gestion_medicaments.php" class="btn btn-outline-dark">← Retour</a>
    </div>
</div>

<script>
// Fonction de recherche dynamique
function filterSales() {
    let input = document.getElementById('searchInput').value.toLowerCase().trim();
    let rows = document.querySelectorAll('#salesTable tbody tr');

    rows.forEach(row => {
        let dateCell     = row.cells[0].textContent.toLowerCase();
        let medCell      = row.cells[1].textContent.toLowerCase();
        let qtyCell      = row.cells[2].textContent;

        // Si c'est une date (format YYYY-MM-DD)
        if (input.match(/^\d{4}-\d{2}-\d{2}$/)) {
            row.style.display = dateCell.includes(input) ? '' : 'none';
        }
        // Si c'est un nombre (quantité)
        else if (!isNaN(input) && input !== '') {
            row.style.display = qtyCell === input ? '' : 'none';
        }
        // Sinon recherche par nom de médicament
        else {
            row.style.display = medCell.includes(input) ? '' : 'none';
        }
    });
}

function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById('pagePrincipale').style.display = 'none';
    document.getElementById(section).classList.add('active');
}

function editMed(id, cat, nom, qte, prix, emp, dateExp) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('idMed').value = id;
    document.querySelector('input[name="cathegorie_med"]').value = cat;
    document.querySelector('input[name="nom_med"]').value = nom;
    document.querySelector('input[name="quantite_med"]').value = qte;
    document.querySelector('input[name="prix_med"]').value = prix;
    document.querySelector('input[name="emplacement"]').value = emp;
    document.querySelector('input[name="date_expiration"]').value = dateExp;
    document.getElementById('btnSubmit').innerHTML = '💾 Enregistrer les modifications';
    document.getElementById('gestion').scrollIntoView({ behavior: 'smooth' });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>