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

// Supprimer un rendez-vous
if (isset($_GET['supprimer'])) {
    $id_sup = intval($_GET['supprimer']);
    $stmt = $conn->prepare("DELETE FROM rendezvous WHERE id_rdv = ?");
    $stmt->bind_param("i", $id_sup);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Ajouter un rendez-vous
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_patient = $_POST['nom_patient'];
    $telephone = $_POST['telephone'];
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $motif = $_POST['motif'];

    $stmt = $conn->prepare("INSERT INTO rendezvous (nom_patient, telephone, date_rdv, heure_rdv, motif) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nom_patient, $telephone, $date_rdv, $heure_rdv, $motif);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Rendez-Vous</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 400px;
            margin: 20px auto;
        }
        form input[type="text"], form input[type="tel"], form input[type="date"], form input[type="time"], form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
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
        a {
            text-decoration: none;
            color: #28a745;
        }
        a:hover {
            color: #155724;
        }
    </style>
</head>
<body>

<h1>Gestion des Rendez-Vous</h1>

<!-- Formulaire d'ajout -->
<form method="POST" action="">
    <label>Nom du patient :</label>
    <input type="text" name="nom_patient" required>

    <label>Téléphone :</label>
    <input type="tel" name="telephone" required>

    <label>Date du rendez-vous :</label>
    <input type="date" name="date_rdv" required>

    <label>Heure du rendez-vous :</label>
    <input type="time" name="heure_rdv" required>

    <label>Motif :</label>
    <textarea name="motif" rows="2" required></textarea>

    <input type="submit" value="Ajouter le rendez-vous">
</form>

<!-- Tableau dynamique des rendez-vous -->
<table>
    <tr>
        <th>ID</th>
        <th>Nom Patient</th>
        <th>Téléphone</th>
        <th>Date</th>
        <th>Heure</th>
        <th>Motif</th>
        <th>Actions</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM rendezvous ORDER BY date_rdv, heure_rdv ASC");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id_rdv']}</td>
                <td>".htmlspecialchars($row['nom_patient'])."</td>
                <td>{$row['telephone']}</td>
                <td>{$row['date_rdv']}</td>
                <td>{$row['heure_rdv']}</td>
                <td>".htmlspecialchars($row['motif'])."</td>
                <td><a href='?supprimer={$row['id_rdv']}' onclick='return confirm(\"Voulez-vous vraiment supprimer ce rendez-vous ?\")'>Supprimer</a></td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>Aucun rendez-vous enregistré.</td></tr>";
    }
    $conn->close();
    ?>
</table>

</body>
</html>