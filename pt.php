<?php
// ======================== CONNEXION MySQLi ========================
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "cesman";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('<div class="alert alert-danger text-center mx-4">❌ Erreur de connexion : ' . $conn->connect_error . '</div>');
}

// ======================== TRAITEMENT ========================

// 1. Suppression
if (isset($_GET['delete'])) {
    $id_patient = intval($_GET['delete']);
    $sql = "DELETE FROM patients WHERE id_patient = $id_patient";
    if ($conn->query($sql) === TRUE) {
        $message = '<div class="alert alert-success mx-4">✅ Patient supprimé avec succès !</div>';
    } else {
        $message = '<div class="alert alert-danger mx-4">❌ Erreur lors de la suppression.</div>';
    }
}

// 2. Ajout
if (isset($_POST['ajouter'])) {
    $nom_patient = $conn->real_escape_string($_POST['nom_patient']);
    $tel_patient = $conn->real_escape_string($_POST['tel_patient']);
    $adresse_patient = $conn->real_escape_string($_POST['adresse_patient']);
    $cni = $conn->real_escape_string($_POST['CNI']);

    if (empty($nom_patient) || empty($tel_patient)) {
        $message = '<div class="alert alert-danger mx-4">❌ Le nom et le téléphone sont obligatoires !</div>';
    } else {
        $sql = "INSERT INTO patients (nom_patient, tel_patient, adresse_patient, CNI) 
                VALUES ('$nom_patient', '$tel_patient', '$adresse_patient', '$cni')";
        if ($conn->query($sql) === TRUE) {
            $message = '<div class="alert alert-success mx-4">✅ Nouveau patient ajouté avec succès !</div>';
        } else {
            $message = '<div class="alert alert-danger mx-4">❌ Erreur lors de l’ajout.</div>';
        }
    }
}

// 3. Modification
if (isset($_POST['modifier'])) {
    $id_patient = intval($_POST['id_patient']);
    $nom_patient = $conn->real_escape_string($_POST['nom_patient']);
    $tel_patient = $conn->real_escape_string($_POST['tel_patient']);
    $adresse_patient = $conn->real_escape_string($_POST['adresse_patient']);
    $cni = $conn->real_escape_string($_POST['CNI']);

    $sql = "UPDATE patients 
            SET nom_patient='$nom_patient', tel_patient='$tel_patient', adresse_patient='$adresse_patient', CNI='$cni' 
            WHERE id_patient='$id_patient'";

    if ($conn->query($sql) === TRUE) {
        $message = '<div class="alert alert-success mx-4">✅ Patient modifié avec succès !</div>';
    } else {
        $message = '<div class="alert alert-danger mx-4">❌ Erreur lors de la modification.</div>';
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// 4. Préparation pour édition
$edit_patient = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM patients WHERE id_patient = $edit_id");
    $edit_patient = $res->fetch_assoc();
}

// 5. Recherche
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
}
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
<title>📋 Gestion des Patients - du cesman</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
:root { --primary: #95ec31; }
body { background: linear-gradient(135deg, #08c092 0%, #047c4e 100%); font-family: 'Segoe UI', sans-serif; }
.header-logo { font-size: 2.5rem; animation: pulse 2s infinite; }
.card { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; }
.card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }
.table { animation: fadeInUp 0.8s ease; }
.btn-action { transition: all 0.3s ease; }
.btn-action:hover { transform: scale(1.1); }
.form-control:focus { border-color: #cc430d; box-shadow: 0 0 0 0.25rem rgba(245, 104, 38, 0.25); }
.success-alert { animation: fadeInDown 0.5s ease; }
</style>
</head>
<body class="pt-4">
<div class="container">

    <!-- HEADER -->
    <div class="text-center mb-5">
        <h1 class="header-logo text-primary"><i class="fas fa-hospital-user"></i> Gestion des Patients</h1>
    </div>

    <!-- MESSAGE -->
    <?php if (isset($message)) echo $message; ?>

    <div class="row">

        <!-- ======================== FORMULAIRE ======================== -->
        <div class="col-lg-5">
            <div class="card h-100 animate__animated animate__fadeInLeft">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person-plus-fill"></i> 
                    <?php echo $edit_patient ? 'Modifier le patient' : 'Ajouter un nouveau patient'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="patientForm">
                        <?php if ($edit_patient): ?>
                            <input type="hidden" name="id_patient" value="<?php echo $edit_patient['id_patient']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-person"></i> Nom du patient</label>
                            <input type="text" class="form-control" name="nom_patient" 
                                   value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['nom_patient']) : ''; ?>" 
                                   placeholder="Entrez le nom complet" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-telephone"></i> Téléphone du patient</label>
                            <input type="tel" class="form-control" name="tel_patient" 
                                   value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['tel_patient']) : ''; ?>" 
                                   placeholder="+237 6XX XX XX XX" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold"><i class="bi bi-geo-alt"></i> Adresse du patient</label>
                            <textarea class="form-control" name="adresse_patient" rows="3" 
                                      placeholder="Douala, Littoral, Cameroun..."><?php echo $edit_patient ? htmlspecialchars($edit_patient['adresse_patient']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="bi bi-person"></i> CNI du patient</label>
                            <input type="text" class="form-control" name="CNI" 
                                   value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['CNI']) : ''; ?>" 
                                   placeholder="Entrez le Num de CNI" required>
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($edit_patient): ?>
                                <button type="submit" name="modifier" class="btn btn-warning btn-lg fw-bold"
                                    onclick="return confirm('Voulez-vous vraiment modifier ce patient ?');">
                                    <i class="bi bi-pencil-square"></i> Modifier
                                </button>
                                <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-x-circle"></i> Annuler
                                </a>
                            <?php else: ?>
                                <button type="submit" name="ajouter" class="btn btn-success btn-lg fw-bold">
                                    <i class="bi bi-save"></i> Ajouter le patient
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ======================== TABLEAU + RECHERCHE ======================== -->
        <div class="col-lg-7">
            <div class="card h-100 animate__animated animate__fadeInRight">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Liste des patients</h5>
                    <span class="badge bg-light text-dark" id="totalPatients">0 patient(s)</span>
                </div>
                <div class="card-body">
                    <!-- Recherche -->
                    <form method="GET" class="mb-3 d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un patient" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="patientsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="80"><i class="bi bi-hash"></i> ID</th>
                                    <th><i class="bi bi-person"></i> Nom</th>
                                    <th><i class="bi bi-telephone"></i> Téléphone</th>
                                    <th><i class="bi bi-geo-alt"></i> Adresse</th>
                                    <th width="160" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $query = "SELECT * FROM patients";
                                if (!empty($search_query)) {
                                    $query .= " WHERE nom_patient LIKE '%$search_query%' OR tel_patient LIKE '%$search_query%'";
                                }
                                $query .= " ORDER BY id_patient DESC";
                                $result = $conn->query($query);

                                if ($result && $result->num_rows > 0) {
                                    $count = 0;
                                    while ($row = $result->fetch_assoc()) {
                                        $count++;
                                        echo "<tr>";
                                        echo "<td>#".$row['id_patient']."</td>";
                                        echo "<td>".htmlspecialchars($row['nom_patient'])."</td>";
                                        echo "<td>".htmlspecialchars($row['tel_patient'])."</td>";
                                        echo "<td>".htmlspecialchars($row['adresse_patient'])."</td>";
                                        echo "<td class='text-center'>";
                                        echo "<a href='?edit=".$row['id_patient']."' class='btn btn-primary btn-sm mx-1'><i class='bi bi-pencil-square'></i></a>";
                                        echo "<a href='?delete=".$row['id_patient']."' onclick='return confirm(\"Voulez-vous vraiment supprimer ce patient ?\")' class='btn btn-danger btn-sm mx-1'><i class='bi bi-trash'></i></a>";
                                        echo "</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Aucun patient trouvé.</td></tr>";
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end text-muted">
                    Total : <strong id="footerCount"><?php echo $count ?? 0; ?></strong> patient(s)
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>