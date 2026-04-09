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

// Ajouter un patient si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_patient = $_POST['nom_patient'];
    $telephone = $_POST['tel_patient'];
    $adresse = $_POST['adresse_patients'];

    $stmt = $conn->prepare("INSERT INTO patients (nom_patient, tel_patient, adresse_patient) VALUES (?, ?, ?)");
    $stmt->bind_param("ssssss", $nom_patient, $tel_patient, $adresse_patient);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Patients</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6f7f7;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #17a2b8;
        }
        form {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            width: 500px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        form input[type="submit"] {
            background-color: #17a2b8;
            color: white;
            border: none;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #138496;
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
            background-color: #17a2b8;
            color: white;
        }
        table tr:hover {
            background-color: #b8e0e0;
        }
    </style>
</head>
<body>

<h1>Gestion des Patients</h1>

<!-- Formulaire d'ajout de patient -->
<form method="POST" action="">
    <label>Nom :</label>
    <input type="text" name="nom_patient" required>

    <label>Prénom :</label>
    <input type="text" name="prenom_patient" required>

    <label>Date de naissance :</label>
    <input type="date" name="date_naissance" required>

    <label>Sexe :</label>
    <select name="sexe" required>
        <option value="">-- Sélectionnez --</option>
        <option value="Masculin">Masculin</option>
        <option value="Féminin">Féminin</option>
    </select>

    <label>Téléphone :</label>
    <input type="text" name="telephone" required>

    <label>Adresse :</label>
    <input type="text" name="adresse" required>

    <input type="submit" value="Ajouter le patient">
</form>

<!-- Tableau dynamique des patients -->
<table>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Date de naissance</th>
        <th>Sexe</th>
        <th>Téléphone</th>
        <th>Adresse</th>
    </tr>

    <?php
    $result = $conn->query("SELECT * FROM patients ORDER BY nom_patient ASC");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>{$row['id_patient']}</td>
                <td>".htmlspecialchars($row['nom_patient'])."</td>
                <td>".htmlspecialchars($row['tel_patient'])."</td>
                <td>".htmlspecialchars($row['adresse_patient'])."</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>Aucun patient enregistré.</td></tr>";
    }

    $conn->close();
    ?>
</table>

</body>
</html>