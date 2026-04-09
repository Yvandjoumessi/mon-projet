<?php
// Connexion à la base de données
$host = "localhost";
$user = "root"; // à adapter si nécessaire
$password = ""; // à adapter si nécessaire
$database = "cesman";

$conn = new mysqli($host, $user, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Supprimer un médicament si demandé
if (isset($_GET['supprimer'])) {
    $id_supprimer = intval($_GET['supprimer']);
    $stmt = $conn->prepare("DELETE FROM medicaments WHERE id_med = ?");
    $stmt->bind_param("i", $id_supprimer);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Charger les données pour modification
$modification = false;
$mod_id = 0;
$mod_categorie = '';
$mod_nom_med = '';
$mod_quantite_med = '';
$mod_prix_med = '';
$mod_emplacement = '';
$mod_date_expiration = '';

if (isset($_GET['modifier'])) {
    $mod_id = intval($_GET['modifier']);
    $stmt = $conn->prepare("SELECT * FROM medicaments WHERE id_med = ?");
    $stmt->bind_param("i", $mod_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $modification = true;
        $mod_categorie = $row['categorie_med'];
        $mod_nom_med = $row['nom_med'];
        $mod_quantite_med = $row['quantite_med'];
        $mod_prix_med = $row['prix_med'];
        $mod_emplacement = $row['emplacement'];
        $mod_date_expiration = $row['date_expiration'];
    }
    $stmt->close();
}

// Ajouter ou modifier un médicament si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cathegorie_med = $_POST['cathegorie_med'];
    $nom_med = $_POST['nom_med'];
    $quantite_med = $_POST['quantite_med'];
    $prix_med = $_POST['prix_med'];
    $emplacement = $_POST['emplacement'];
    $date_expiration = $_POST['date_expiration'];

    if (isset($_POST['id_med']) && !empty($_POST['id_med'])) {
        // Modification
        $id_med = intval($_POST['id_med']);
        $stmt = $conn->prepare("UPDATE medicaments SET categorie_med = ?, nom_med = ?, quantite_med = ?, prix_med = ?, emplacement = ?, date_expiration = ? WHERE id_med = ?");
        $stmt->bind_param("ssidsi", $categorie_med, $nom_med, $quantite_med, $prix_med, $emplacement, $date_expiration, $id_med);
        $stmt->execute();
        $stmt->close();
    } else {
        // Ajout
        $stmt = $conn->prepare("INSERT INTO medicaments (cathegorie_med, nom_med, quantite_med, prix_med, emplacement, date_expiration) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $cathegorie_med, $nom_med, $quantite_med, $prix_med, $emplacement, $date_expiration);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Médicaments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #28a745;
            padding: 20px;
            transition: background-color 0.5s ease;
        }
        h1 {
            text-align: center;
            color: white;
            animation: fadeIn 1s ease;
        }
        form {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 500px;
            margin: auto;
            animation: slideDown 0.7s ease;
        }
        form input[type="text"], form input[type="number"], form input[type="date"], form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: border 0.3s;
        }
        form input[type="text"]:focus,
        form input[type="number"]:focus,
        form input[type="date"]:focus,
        form select:focus {
            border-color: #28a745;
            box-shadow: 0 0 5px rgba(40,167,69,0.5);
        }
        form input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        form input[type="submit"]:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: fadeIn 1s ease;
        }
        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            transition: background-color 0.3s;
        }
        table th {
            background-color: #218838;
            color: white;
        }
        table tr:hover {
            background-color: #c3e6cb;
        }
        a {
            text-decoration: none;
            color: #28a745;
            transition: color 0.3s;
        }
        a:hover {
            color: #155724;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<h1>Gestion des Médicaments</h1>

<!-- Formulaire d'ajout ou modification -->
<form method="POST" action="">
    <input type="hidden" name="id_med" value="<?php echo $modification ? $mod_id : ''; ?>">

    <!-- Champ catégorie en combo box -->
    <label>Catégorie :</label>
    <select name="cathegorie_med" required>
        <option value="">-- Sélectionner une catégorie --</option>
        <option value="Antibiotiques" <?php if($mod_categorie=="Antibiotiques") echo "selected"; ?>>Antibiotiques</option>
        <option value="Analgésiques" <?php if($mod_categorie=="Analgésiques") echo "selected"; ?>>Analgésiques</option>
        <option value="Vitamines" <?php if($mod_categorie=="Vitamines") echo "selected"; ?>>Vitamines</option>
        <option value="Vaccins" <?php if($mod_categorie=="Vaccins") echo "selected"; ?>>Vaccins</option>
        <option value="Autres" <?php if($mod_categorie=="Autres") echo "selected"; ?>>Autres</option>
    </select>

    <label>Nom du médicament :</label>
    <input type="text" name="nom_med" value="<?php echo htmlspecialchars($mod_nom_med); ?>" required>

    <label>Quantité :</label>
    <input type="number" name="quantite_med" min="1" value="<?php echo htmlspecialchars($mod_quantite_med); ?>" required>

    <label>Prix :</label>
    <input type="number" step="0.01" name="prix_med" value="<?php echo htmlspecialchars($mod_prix_med); ?>" required>

    <label>Emplacement :</label>
    <input type="text" name="emplacement" value="<?php echo htmlspecialchars($mod_emplacement); ?>" required>

    <label>Date d'expiration :</label>
    <input type="date" name="date_expiration" value="<?php echo htmlspecialchars($mod_date_expiration); ?>" required>

    <input type="submit" value="<?php echo $modification ? 'Modifier le médicament' : 'Ajouter le médicament'; ?>">
</form>

<!-- Tableau dynamique des médicaments -->
<table>
    <tr>
        <th>ID</th>
        <th>Catégorie</th>
        <th>Nom</th>
        <th>Quantité</th>
        <th>Prix</th>
        <th>Emplacement</th>
        <th>Date d'expiration</th>
        <th>Actions</th>
    </tr>

    <?php
    $result = $conn->query("SELECT * FROM medicaments ORDER BY date_expiration ASC");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id_med']}</td>
                <td>".htmlspecialchars($row['cathegorie_med'])."</td>
                <td>".htmlspecialchars($row['nom_med'])."</td>
                <td>{$row['quantite_med']}</td>
                <td>{$row['prix_med']}</td>
                <td>".htmlspecialchars($row['emplacement'])."</td>
                <td>{$row['date_expiration']}</td>
                <td>
                    <a href='?modifier={$row['id_med']}'>Modifier</a> | 
                    <a href='?supprimer={$row['id_med']}' onclick='return confirm(\"Voulez-vous vraiment supprimer ce médicament ?\")'>Supprimer</a>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='8'>Aucun médicament enregistré.</td></tr>";
    }

    $conn->close();
    ?>
</table>

</body>
</html>