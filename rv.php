<?php
// Connexion à la base de données
$host = "localhost";
$user = "root"; // à adapter
$password = ""; // à adapter
$database = "cesman";

$conn = new mysqli($host, $user, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Initialisation
$date_debut = "";
$date_fin = "";
$ventes = [];
$total_general = 0;

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    $stmt = $conn->prepare("SELECT *, quantite*prix_med AS montant FROM ventes WHERE date_vente BETWEEN ? AND ? ORDER BY date_vente ASC");
    $stmt->bind_param("ss", $date_debut, $date_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $ventes[] = $row;
        $total_general += $row['montant'];
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Ventes</title>
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
            margin-bottom: 30px;
            border-radius: 10px;
            width: 400px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        form input[type="date"], form input[type="submit"] {
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

<h1>Rapport des Ventes</h1>

<!-- Formulaire de sélection de période -->
<form method="POST" action="">
    <label>Date de début :</label>
    <input type="date" name="date_debut" value="<?php echo $date_debut; ?>" required>

    <label>Date de fin :</label>
    <input type="date" name="date_fin" value="<?php echo $date_fin; ?>" required>

    <input type="submit" value="Générer le rapport">
</form>

<?php if (!empty($ventes)): ?>
    <table>
        <tr>
            <th>ID Vente</th>
            <th>Nom Médicament</th>
            <th>Quantité</th>
            <th>Prix Médicament</th>
            <th>Montant</th>
            <th>Date Vente</th>
        </tr>
        <?php foreach ($ventes as $vente): ?>
            <tr>
                <td><?php echo $vente['id_ventes']; ?></td>
                <td><?php echo htmlspecialchars($vente['nom_med']); ?></td>
                <td><?php echo $vente['quantite']; ?></td>
                <td><?php echo number_format($vente['prix_med'], 2, ',', ' '); ?> €</td>
                <td><?php echo number_format($vente['montant'], 2, ',', ' '); ?> €</td>
                <td><?php echo $vente['date_vente']; ?></td>
            </tr>
        <?php endforeach; ?>
        <tfoot>
            <tr>
                <td colspan="4">Total Général</td>
                <td colspan="2"><?php echo number_format($total_general, 2, ',', ' '); ?> €</td>
            </tr>
        </tfoot>
    </table>
<?php elseif($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <p style="text-align:center; color:red;">Aucune vente trouvée pour cette période.</p>
<?php endif; ?>

</body>
</html>