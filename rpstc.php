<?php
// Connexion à la base de données
$host = "localhost";
$user = "root"; // adapter si nécessaire
$password = ""; // adapter si nécessaire
$database = "cesman";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération des catégories distinctes pour le filtre
$categories = [];
$cat_result = $conn->query("SELECT DISTINCT cathegorie_med FROM medicaments ORDER BY cathegorie_med ASC");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['cathegorie_med'];
}

// Initialisation
$filtre_categorie = "";
$filtre_quantite = "";
$stocks = [];
$total_valeur_stock = 0;

// Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filtre_categorie = $_POST['categorie'];
    $filtre_quantite = $_POST['quantite_min'];

    $sql = "SELECT *, quantite_med*prix_med AS valeur_stock FROM medicaments WHERE 1=1";
    $params = [];
    $types = "";

    if ($filtre_categorie != "") {
        $sql .= " AND cathegorie_med = ?";
        $params[] = $filtre_categorie;
        $types .= "s";
    }

    if ($filtre_quantite != "") {
        $sql .= " AND quantite_med >= ?";
        $params[] = $filtre_quantite;
        $types .= "i";
    }

    $sql .= " ORDER BY nom_med ASC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
        $total_valeur_stock += $row['valeur_stock'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Stocks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0fff0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #28a745;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 450px;
            margin: 20px auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        form select, form input[type="number"], form input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        table th {
            background-color: #28a745;
            color: white;
        }
        table tr:hover {
            background-color: #c3e6cb;
        }
        tfoot td {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Rapport des Stocks</h1>

<!-- Formulaire de filtre -->
<form method="POST" action="">
    <label>Filtrer par catégorie :</label>
    <select name="categorie">
        <option value="">-- Toutes les catégories --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($filtre_categorie == $cat) echo "selected"; ?>>
                <?php echo htmlspecialchars($cat); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Quantité minimale :</label>
    <input type="number" name="quantite_min" min="0" value="<?php echo htmlspecialchars($filtre_quantite); ?>">

    <input type="submit" value="Générer le rapport">
</form>

<?php if (!empty($stocks)): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Catégorie</th>
            <th>Nom Médicament</th>
            <th>Quantité</th>
            <th>Prix Unitaire</th>
            <th>Valeur Stock</th>
            <th>Emplacement</th>
        </tr>
        <?php foreach ($stocks as $stock): ?>
            <tr>
                <td><?php echo $stock['id_med']; ?></td>
                <td><?php echo htmlspecialchars($stock['cathegorie_med']); ?></td>
                <td><?php echo htmlspecialchars($stock['nom_med']); ?></td>
                <td><?php echo $stock['quantite_med']; ?></td>
                <td><?php echo number_format($stock['prix_med'],2,',',' '); ?> €</td>
                <td><?php echo number_format($stock['valeur_stock'],2,',',' '); ?> €</td>
                <td><?php echo htmlspecialchars($stock['emplacement']); ?></td>
            </tr>
        <?php endforeach; ?>
        <tfoot>
            <tr>
                <td colspan="5">Total valeur du stock</td>
                <td colspan="2"><?php echo number_format($total_valeur_stock,2,',',' '); ?> Fcfa</td>
            </tr>
        </tfoot>
    </table>
<?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <p style="text-align:center; color:red;">Aucun stock trouvé pour ce filtre.</p>
<?php endif; ?>

</body>
</html>