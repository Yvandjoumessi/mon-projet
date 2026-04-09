<?php
// gestion_personnel.php
$admin = "Patrick DJOUMESSI";

// ====================== CONNEXION À LA BASE DE DONNÉES ======================
$host = 'localhost';
$dbname = 'cesman';     // Change si ton nom de base est différent
$username = 'root';        // Ton utilisateur MySQL
$password = '';            // Ton mot de passe MySQL (souvent vide en local)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ====================== CRÉATION AUTOMATIQUE DES TABLES ======================
    // Table infirmier
    $pdo->exec("CREATE TABLE IF NOT EXISTS infirmier (
        id_inf INT AUTO_INCREMENT PRIMARY KEY,
        nom_inf VARCHAR(100) NOT NULL,
        tel_inf VARCHAR(20) NOT NULL,
        adresse_inf VARCHAR(255) NOT NULL,
        CNI VARCHAR(30) NOT NULL,
        date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Table medecin
    $pdo->exec("CREATE TABLE IF NOT EXISTS medecin (
        id_MEDECIN INT AUTO_INCREMENT PRIMARY KEY,
        nom_MEDECIN VARCHAR(150) NOT NULL,
        tel_MEDECIN VARCHAR(20) NOT NULL,
        adresse_MEDECIN VARCHAR(255) NOT NULL,
        CNI VARCHAR(30) NOT NULL,
        date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Table pharmacien
    $pdo->exec("CREATE TABLE IF NOT EXISTS pharmacien (
        id_pharmacien INT AUTO_INCREMENT PRIMARY KEY,
        nom_pharmacien VARCHAR(150) NOT NULL,
        tel_pharmacien VARCHAR(20) NOT NULL,
        adresse_pharmacien VARCHAR(255) NOT NULL,
        CNI VARCHAR(30) NOT NULL,
        date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage() . "<br>Vérifiez que la base 'cesman' existe.");
}

// ====================== TRAITEMENT DES FORMULAIRES ======================
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? null;
    $nom = trim($_POST['nom'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $cni = trim($_POST['cni'] ?? '');

    try {
        if ($type === 'infirmier') {
            if ($id) {
                // Modification
                $stmt = $pdo->prepare("UPDATE infirmier SET nom_inf=?, tel_inf=?, adresse_inf=?, CNI=? WHERE id_inf=?");
                $stmt->execute([$nom, $tel, $adresse, $cni, $id]);
                $success = "Infirmier modifié avec succès !";
            } else {
                // Ajout
                $stmt = $pdo->prepare("INSERT INTO infirmier (nom_inf, tel_inf, adresse_inf, CNI) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $tel, $adresse, $cni]);
                $success = "Infirmier ajouté avec succès !";
            }
        }
        elseif ($type === 'medecin') {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE medecin SET nom_MEDECIN=?, tel_MEDECIN=?, adresse_MEDECIN=?, CNI=? WHERE id_MEDECIN=?");
                $stmt->execute([$nom, $tel, $adresse, $cni, $id]);
                $success = "Médecin modifié avec succès !";
            } else {
                $stmt = $pdo->prepare("INSERT INTO medecin (nom_MEDECIN, tel_MEDECIN, adresse_MEDECIN, CNI) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $tel, $adresse, $cni]);
                $success = "Médecin ajouté avec succès !";
            }
        }
        elseif ($type === 'pharmacien') {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE pharmacien SET nom_pharmacien=?, tel_pharmacien=?, adresse_pharmacien=?, CNI=? WHERE id_pharmacien=?");
                $stmt->execute([$nom, $tel, $adresse, $cni, $id]);
                $success = "Pharmacien modifié avec succès !";
            } else {
                $stmt = $pdo->prepare("INSERT INTO pharmacien (nom_pharmacien, tel_pharmacien, adresse_pharmacien, CNI) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $tel, $adresse, $cni]);
                $success = "Pharmacien ajouté avec succès !";
            }
        }
    } catch(PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// ====================== SUPPRESSION ======================
if (isset($_GET['action']) && $_GET['action'] === 'supprimer') {
    $type = $_GET['type'];
    $id = (int)$_GET['id'];

    try {
        if ($type === 'infirmier') {
            $pdo->prepare("DELETE FROM infirmier WHERE id_inf = ?")->execute([$id]);
            $success = "Infirmier supprimé !";
        } elseif ($type === 'medecin') {
            $pdo->prepare("DELETE FROM medecin WHERE id_MEDECIN = ?")->execute([$id]);
            $success = "Médecin supprimé !";
        } elseif ($type === 'pharmacien') {
            $pdo->prepare("DELETE FROM pharmacien WHERE id_pharmacien = ?")->execute([$id]);
            $success = "Pharmacien supprimé !";
        }
    } catch(PDOException $e) {
        $error = "Erreur de suppression";
    }
    header("Location: gestion_personnel.php");
    exit;
}

// ====================== RÉCUPÉRATION DES DONNÉES ======================
$infirmiers = $pdo->query("SELECT * FROM infirmier ORDER BY id_inf DESC")->fetchAll(PDO::FETCH_ASSOC);
$medecins   = $pdo->query("SELECT * FROM medecin ORDER BY id_MEDECIN DESC")->fetchAll(PDO::FETCH_ASSOC);
$pharmaciens = $pdo->query("SELECT * FROM pharmacien ORDER BY id_pharmacien DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Personnel - CESMAN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { margin:0; font-family:'Segoe UI',Arial,sans-serif; background: linear-gradient(135deg,#0a3d2a,#0f5132); color:#e0f2e9; min-height:100vh; }
        .header { background:rgba(255,255,255,0.95); padding:20px; text-align:center; color:#0f5132; box-shadow:0 4px 15px rgba(0,0,0,0.3); }
        .container { padding:40px 20px; display:flex; justify-content:center; gap:40px; flex-wrap:wrap; }
        .card { background:rgba(255,255,255,0.95); width:320px; padding:45px 30px; border-radius:20px; text-align:center; box-shadow:0 15px 35px rgba(0,0,0,0.3); transition:all 0.4s; color:#0f5132; cursor:pointer; }
        .card:hover { transform:translateY(-15px) scale(1.06); box-shadow:0 25px 50px rgba(40,167,69,0.5); }
        .card i { font-size:4.8rem; margin-bottom:20px; color:#28a745; transition:all 0.4s; }
        .card:hover i { transform:scale(1.2) rotate(12deg); }
        .section { display:none; max-width:1200px; margin:30px auto; background:rgba(255,255,255,0.95); padding:35px; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.3); color:#0f5132; }
        .section.active { display:block; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:12px; border:1px solid #ddd; text-align:left; }
        th { background:#28a745; color:white; }
        .btn { padding:8px 16px; border:none; border-radius:6px; cursor:pointer; margin:3px; }
        .btn-edit { background:#ffc107; color:#000; }
        .btn-delete { background:#dc3545; color:white; }
        .form-group { margin-bottom:18px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:600; }
        .form-group input { width:100%; padding:12px; border:1px solid #ccc; border-radius:6px; font-size:1.05rem; }
        .success { background:#28a745; color:white; padding:15px; border-radius:8px; text-align:center; margin:15px 0; }
        .error { background:#dc3545; color:white; padding:15px; border-radius:8px; text-align:center; margin:15px 0; }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-users"></i> Gestion du Personnel - CESMAN</h1>
    <p>Bienvenue, <?= htmlspecialchars($admin) ?></p>
</div>

<div class="container">
    <div class="card" onclick="showSection('infirmier')"><i class="fas fa-user-nurse"></i><h3>Ajouter Infirmier</h3></div>
    <div class="card" onclick="showSection('medecin')"><i class="fas fa-user-md"></i><h3>Ajouter Médecin</h3></div>
    <div class="card" onclick="showSection('pharmacien')"><i class="fas fa-user-tie"></i><h3>Ajouter Pharmacien</h3></div>
</div>

<?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

<!-- SECTION INFIRMIER -->
<div id="section_infirmier" class="section">
    <h2>Ajout / Liste des Infirmiers</h2>
    <form method="POST">
        <input type="hidden" name="type" value="infirmier">
        <input type="hidden" name="id" id="inf_id" value="">
        <div class="form-group"><label>Nom complet</label><input type="text" name="nom" id="inf_nom" required></div>
        <div class="form-group"><label>Téléphone</label><input type="text" name="tel" id="inf_tel" required></div>
        <div class="form-group"><label>Adresse</label><input type="text" name="adresse" id="inf_adresse" required></div>
        <div class="form-group"><label>CNI</label><input type="text" name="cni" id="inf_cni" required></div>
        <button type="submit" style="background:#28a745;color:white;padding:14px 40px;border:none;border-radius:8px;font-size:1.1rem;cursor:pointer;">Enregistrer Infirmier</button>
    </form>

    <h3>Liste des Infirmiers</h3>
    <table>
        <tr><th>ID</th><th>Nom</th><th>Tél</th><th>Adresse</th><th>CNI</th><th>Actions</th></tr>
        <?php foreach ($infirmiers as $row): ?>
        <tr>
            <td><?= $row['id_inf'] ?></td>
            <td><?= htmlspecialchars($row['nom_inf']) ?></td>
            <td><?= htmlspecialchars($row['tel_inf']) ?></td>
            <td><?= htmlspecialchars($row['adresse_inf']) ?></td>
            <td><?= htmlspecialchars($row['CNI']) ?></td>
            <td>
                <button class="btn btn-edit" onclick='editPerson("infirmier",<?= json_encode($row) ?>)'>Modifier</button>
                <a href="?action=supprimer&type=infirmier&id=<?= $row['id_inf'] ?>" class="btn btn-delete" onclick="return confirm('Supprimer cet infirmier ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- SECTION MEDECIN -->
<div id="section_medecin" class="section">
    <h2>Ajout / Liste des Médecins</h2>
    <form method="POST">
        <input type="hidden" name="type" value="medecin">
        <input type="hidden" name="id" id="med_id" value="">
        <div class="form-group"><label>Nom complet</label><input type="text" name="nom" id="med_nom" required></div>
        <div class="form-group"><label>Téléphone</label><input type="text" name="tel" id="med_tel" required></div>
        <div class="form-group"><label>Adresse</label><input type="text" name="adresse" id="med_adresse" required></div>
        <div class="form-group"><label>CNI</label><input type="text" name="cni" id="med_cni" required></div>
        <button type="submit" style="background:#28a745;color:white;padding:14px 40px;border:none;border-radius:8px;font-size:1.1rem;cursor:pointer;">Enregistrer Médecin</button>
    </form>

    <h3>Liste des Médecins</h3>
    <table>
        <tr><th>ID</th><th>Nom</th><th>Tél</th><th>Adresse</th><th>CNI</th><th>Actions</th></tr>
        <?php foreach ($medecins as $row): ?>
        <tr>
            <td><?= $row['id_MEDECIN'] ?></td>
            <td><?= htmlspecialchars($row['nom_MEDECIN']) ?></td>
            <td><?= htmlspecialchars($row['tel_MEDECIN']) ?></td>
            <td><?= htmlspecialchars($row['adresse_MEDECIN']) ?></td>
            <td><?= htmlspecialchars($row['CNI']) ?></td>
            <td>
                <button class="btn btn-edit" onclick='editPerson("medecin",<?= json_encode($row) ?>)'>Modifier</button>
                <a href="?action=supprimer&type=medecin&id=<?= $row['id_MEDECIN'] ?>" class="btn btn-delete" onclick="return confirm('Supprimer ce médecin ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- SECTION PHARMACIEN -->
<div id="section_pharmacien" class="section">
    <h2>Ajout / Liste des Pharmaciens</h2>
    <form method="POST">
        <input type="hidden" name="type" value="pharmacien">
        <input type="hidden" name="id" id="pha_id" value="">
        <div class="form-group"><label>Nom complet</label><input type="text" name="nom" id="pha_nom" required></div>
        <div class="form-group"><label>Téléphone</label><input type="text" name="tel" id="pha_tel" required></div>
        <div class="form-group"><label>Adresse</label><input type="text" name="adresse" id="pha_adresse" required></div>
        <div class="form-group"><label>CNI</label><input type="text" name="cni" id="pha_cni" required></div>
        <button type="submit" style="background:#28a745;color:white;padding:14px 40px;border:none;border-radius:8px;font-size:1.1rem;cursor:pointer;">Enregistrer Pharmacien</button>
    </form>

    <h3>Liste des Pharmaciens</h3>
    <table>
        <tr><th>ID</th><th>Nom</th><th>Tél</th><th>Adresse</th><th>CNI</th><th>Actions</th></tr>
        <?php foreach ($pharmaciens as $row): ?>
        <tr>
            <td><?= $row['id_pharmacien'] ?></td>
            <td><?= htmlspecialchars($row['nom_pharmacien']) ?></td>
            <td><?= htmlspecialchars($row['tel_pharmacien']) ?></td>
            <td><?= htmlspecialchars($row['adresse_pharmacien']) ?></td>
            <td><?= htmlspecialchars($row['CNI']) ?></td>
            <td>
                <button class="btn btn-edit" onclick='editPerson("pharmacien",<?= json_encode($row) ?>)'>Modifier</button>
                <a href="?action=supprimer&type=pharmacien&id=<?= $row['id_pharmacien'] ?>" class="btn btn-delete" onclick="return confirm('Supprimer ce pharmacien ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById('section_' + section).classList.add('active');
}

function editPerson(type, data) {
    showSection(type);
    if (type === 'medecin') {
        document.getElementById('med_id').value = data.id_MEDECIN;
        document.getElementById('med_nom').value = data.nom_MEDECIN;
        document.getElementById('med_tel').value = data.tel_MEDECIN;
        document.getElementById('med_adresse').value = data.adresse_MEDECIN;
        document.getElementById('med_cni').value = data.CNI;
    } else if (type === 'infirmier') {
        document.getElementById('inf_id').value = data.id_inf;
        document.getElementById('inf_nom').value = data.nom_inf;
        document.getElementById('inf_tel').value = data.tel_inf;
        document.getElementById('inf_adresse').value = data.adresse_inf;
        document.getElementById('inf_cni').value = data.CNI;
    } else if (type === 'pharmacien') {
        document.getElementById('pha_id').value = data.id_pharmacien;
        document.getElementById('pha_nom').value = data.nom_pharmacien;
        document.getElementById('pha_tel').value = data.tel_pharmacien;
        document.getElementById('pha_adresse').value = data.adresse_pharmacien;
        document.getElementById('pha_cni').value = data.CNI;
    }
}
</script>

</body>
</html>