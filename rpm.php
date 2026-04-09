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
$filtre_date = "";
$medicaments = [];

// Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filtre_categorie = $_POST['categorie'];
    $filtre_date = $_POST['date_expiration'];

    $sql = "SELECT * FROM medicaments WHERE 1=1";
    $params = [];
    $types = "";

    if ($filtre_categorie != "") {
        $sql .= " AND cathegorie_med = ?";
        $params[] = $filtre_categorie;
        $types .= "s";
    }

    if ($filtre_date != "") {
        $sql .= " AND date_expiration <= ?";
        $params[] = $filtre_date;
        $types .= "s";
    }

    $sql .= " ORDER BY date_expiration ASC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $medicaments[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Médicaments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6f7e6;
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
        form select, form input[type="date"], form input[type="submit"] {
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
    </style>
</head>
<body>

<h1>Rapport des Médicaments</h1>

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

    <label>Filtrer par date d'expiration (jusqu'à) :</label>
    <input type="date" name="date_expiration" value="<?php echo $filtre_date; ?>">

    <input type="submit" value="Générer le rapport">
</form>

<?php if (!empty($medicaments)): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Catégorie</th>
            <th>Nom Médicament</th>
            <th>Quantité</th>
            <th>Prix</th>
            <th>Emplacement</th>
            <th>Date Expiration</th>
        </tr>
        <?php foreach ($medicaments as $med): ?>
            <tr>
                <td><?php echo $med['id_med']; ?></td>
                <td><?php echo htmlspecialchars($med['cathegorie_med']); ?></td>
                <td><?php echo htmlspecialchars($med['nom_med']); ?></td>
                <td><?php echo $med['quantite_med']; ?></td>
                <td><?php echo number_format($med['prix_med'], 2, ',', ' '); ?> €</td>
                <td><?php echo htmlspecialchars($med['emplacement']); ?></td>
                <td><?php echo $med['date_expiration']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <p style="text-align:center; color:red;">Aucun médicament trouvé pour ce filtre.</p>
<?php endif; ?>

</body>
</html>