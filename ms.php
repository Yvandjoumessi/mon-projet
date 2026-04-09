<?php
// ================================================
// GESTION DU STOCK - PHARMACIE
// ================================================

include 'stc.php';   // Ton fichier de connexion à la base de données

$message = "";

// ====================== TRAITEMENT DES ACTIONS ======================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['id_stock'])) {

    $id_stock = intval($_POST['id_stock']);

    // ====================== SUPPRESSION ======================
    if ($_POST['action'] === 'supprimer') {

        $sql = "DELETE FROM stocks WHERE id_stocks = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $id_stocks);

        if ($stmt->execute()) {
            $message = '<p style="color:green; padding:10px; background:#d4edda; border:1px solid #c3e6cb; border-radius:5px;">
                        ✅ Produit supprimé avec succès !
                       </p>';
        } else {
            $message = '<p style="color:red; padding:10px; background:#f8d7da; border:1px solid #f5c6cb; border-radius:5px;">
                        ❌ Erreur lors de la suppression.
                       </p>';
        }
        $stmt->close();
    }

    // ====================== MODIFICATION ======================
    elseif ($_POST['action'] === 'modifier') {

        $nom_med         = trim($_POST['nom_med'] ?? '');
        $type_med        = trim($_POST['type_med'] ?? '');
        $cathegorie_med   = trim($_POST['cathegorie_med'] ?? '');
        $quantite        = intval($_POST['quantite'] ?? 0);
        $prix_unitaire   = floatval($_POST['prix_unitaire'] ?? 0);
        $date_expiration = $_POST['date_expiration'] ?? '';

        if (empty($nom_med)) {
            $message = '<p style="color:red; padding:10px; background:#f8d7da; border:1px solid #f5c6cb; border-radius:5px;">
                        ❌ Le nom du médicament est obligatoire.
                       </p>';
        } else {
            $sql = "UPDATE stocks SET 
                        nom_med = ?, 
                        type_med = ?, 
                        cathegorie_med = ?, 
                        quantite = ?, 
                        prix_unitaire = ?, 
                        date_expiration = ? 
                    WHERE id_stocks = ?";

            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssidsi", 
                $nom_med, $type_med, $cathegorie_med,
                $quantite, $prix_unitaire, $date_expiration, $id_stocks);

            if ($stmt->execute()) {
                $message = '<p style="color:green; padding:10px; background:#d4edda; border:1px solid #c3e6cb; border-radius:5px;">
                            ✅ Produit modifié avec succès !
                           </p>';
            } else {
                $message = '<p style="color:red; padding:10px; background:#f8d7da; border:1px solid #f5c6cb; border-radius:5px;">
                            ❌ Erreur lors de la modification.
                           </p>';
            }
            $stmt->close();
        }
    }
}

// ====================== RÉCUPÉRATION DES STOCKS ======================
// IMPORTANT : Cette requête doit être faite AVANT toute fermeture de connexion
$sql = "SELECT * FROM stocks ORDER BY nom_med ASC";
$result = $mysqli->query($sql);

if (!$result) {
    $message = '<p style="color:red;">Erreur de récupération des stocks : ' . $mysqli->error . '</p>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Stock - Pharmacie</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background-color: #f0f0f0; 
        }
        .message { 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 5px; 
        }
        .btn { 
            padding: 8px 15px; 
            margin: 5px; 
            cursor: pointer; 
            border: none; 
            border-radius: 4px; 
        }
        .btn-modifier { 
            background-color: #ffc107; 
            color: black; 
        }
        .btn-supprimer { 
            background-color: #dc3545; 
            color: white; 
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
    </style>
</head>
<body>

<h1>Gestion du Stock de la Pharmacie</h1>

<!-- Affichage des messages -->
<?= $message ?>

<h2>Tableau des stocks</h2>

<form method="POST">
    <table>
        <thead>
            <tr>
                <th>Sélection</th>
                <th>Nom du Médicament</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Quantité</th>
                <th>Prix Unitaire (FCFA)</th>
                <th>Date d'expiration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td>
                        <input type="radio" name="id_stocks" value="<?= $row['id_stocks'] ?>" required>
                    </td>
                    <td><?= htmlspecialchars($row['nom_med']) ?></td>
                    <td><?= htmlspecialchars($row['type_med'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['cathegorie_med'] ?? '') ?></td>
                    <td><?= $row['quantite'] ?></td>
                    <td><?= number_format($row['prix_unitaire'], 2) ?></td>
                    <td><?= $row['date_expiration'] ?? 'Non définie' ?></td>
                    <td>
                        <button type="submit" name="action" value="modifier" class="btn btn-modifier">
                            Modifier
                        </button>
                        <button type="submit" name="action" value="supprimer" class="btn btn-supprimer" 
                                onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?')">
                            Supprimer
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</form>

<!-- Formulaire de modification (à améliorer plus tard avec JavaScript si besoin) -->
<?php if (isset($_POST['action']) && $_POST['action'] === 'modifier' && isset($_POST['id_stock'])) : ?>
    <!-- Tu peux ajouter ici un formulaire de modification pré-rempli -->
<?php endif; ?>

<script>
    // Optionnel : confirmation supplémentaire
</script>

</body>
</html>

<?php
// FERMETURE DE LA CONNEXION UNIQUEMENT À LA TOUTE FIN
if (isset($mysqli)) {
    $mysqli->close();
}
?>