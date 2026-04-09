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

// Supprimer une vente si demandé
if (isset($_GET['supprimer'])) {
    $id_supprimer = intval($_GET['supprimer']);
    $stmt = $conn->prepare("DELETE FROM ventes WHERE id_ventes = ?");
    $stmt->bind_param("i", $id_supprimer);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Charger les données pour modification
$modification = false;
$mod_id = 0;
$mod_nom_med = '';
$mod_quantite = '';
$mod_prix_med = '';
$mod_date_vente = '';

if (isset($_GET['modifier'])) {
    $mod_id = intval($_GET['modifier']);
    $stmt = $conn->prepare("SELECT * FROM ventes WHERE id_ventes = ?");
    $stmt->bind_param("i", $mod_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $modification = true;
        $mod_nom_med = $row['nom_med'];
        $mod_quantite = $row['quantite'];
        $mod_prix_med = $row['prix_med'];
        $mod_date_vente = $row['date_vente'];
    }
    $stmt->close();
}

// Ajouter ou modifier une vente si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_med = $_POST['nom_med'];
    $quantite = $_POST['quantite'];
    $prix_med = $_POST['prix_med'];
    $date_vente = $_POST['date_vente'];
    $montant = $quantite * $prix_med; // calcul automatique du montant

    if (isset($_POST['id_ventes']) && !empty($_POST['id_ventes'])) {
        // Modification
        $id_ventes = intval($_POST['id_ventes']);
        $stmt = $conn->prepare("UPDATE ventes SET nom_med = ?, quantite = ?, prix_med = ?, date_vente = ?, montant = ? WHERE id_ventes = ?");
        $stmt->bind_param("sidsdi", $nom_med, $quantite, $prix_med, $date_vente, $montant, $id_ventes);
        $stmt->execute();
        $stmt->close();
    } else {
        // Ajout
        $stmt = $conn->prepare("INSERT INTO ventes (nom_med, quantite, prix_med, date_vente, montant) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsd", $nom_med, $quantite, $prix_med, $date_vente, $montant);
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
    <title>Gestion des Ventes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
           background: linear-gradient(rgba(171, 206, 223, 0.5), rgba(40,167,69,0.5)), 
                url('images/lg.jpeg') no-repeat center center;
            padding: 20px;
            transition: background-color 0.5s ease;
        }
        h1 { 
            text-align: center; 
            color: white;
            animation: fadeIn 1s ease;
        }
        form {
            background-color: #a5a5a3;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 400px;
            margin: auto;
            animation: slideDown 0.7s ease;
        }
        form input[type="text"], form input[type="number"], form input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: border 0.3s;
        }
        form input[type="text"]:focus,
        form input[type="number"]:focus,
        form input[type="date"]:focus {
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
            background-color: #fdab9c;
            transform: scale(1.05);
        }
        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background-color: #e7dbdb;
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
        a { text-decoration: none; color: #28a745; transition: color 0.3s; }
        a:hover { color: #155724; }
        /* Animations */
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

<h1>Gestion des Ventes</h1>

<!-- Formulaire d'ajout ou modification -->
<form method="POST" action="">
    <input type="hidden" name="id_ventes" value="<?php echo $modification ? $mod_id : ''; ?>">
    <label>Nom du médicament :</label>
    <input type="text" name="nom_med" value="<?php echo htmlspecialchars($mod_nom_med); ?>" required>

    <label>Quantité :</label>
    <input type="number" name="quantite" min="1" value="<?php echo htmlspecialchars($mod_quantite); ?>" required>

    <label>Prix du médicament :</label>
    <input type="number" step="0.01" name="prix_med" value="<?php echo htmlspecialchars($mod_prix_med); ?>" required>

    <label>Date de vente :</label>
    <input type="date" name="date_vente" value="<?php echo htmlspecialchars($mod_date_vente); ?>" required>

    <input type="submit" value="<?php echo $modification ? 'Modifier la vente' : 'Ajouter la vente'; ?>">
</form>

<!-- Tableau dynamique des ventes -->
<table>
    <tr>
        <th>ID Vente</th>
        <th>Nom Médicament</th>
        <th>Quantité</th>
        <th>Prix</th>
        <th>Montant</th>
        <th>Date Vente</th>
        <th>Actions</th>
    </tr>

    <?php
    $result = $conn->query("SELECT * FROM ventes ORDER BY date_vente DESC");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $montant = $row['quantite'] * $row['prix_med'];
            echo "<tr>
                <td>{$row['id_ventes']}</td>
                <td>".htmlspecialchars($row['nom_med'])."</td>
                <td>{$row['quantite']}</td>
                <td>{$row['prix_med']}</td>
                <td>".number_format($montant, 2)." </td>
                <td>{$row['date_vente']}</td>
                <td>
                    <a href='?modifier={$row['id_ventes']}'>Modifier</a> | 
                    <a href='?supprimer={$row['id_ventes']}' onclick='return confirm(\"Voulez-vous vraiment supprimer cette vente ?\")'>Supprimer</a>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>Aucune vente enregistrée.</td></tr>";
    }

    $conn->close();
    ?>
</table>

</body>
</html>