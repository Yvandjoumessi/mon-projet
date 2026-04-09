<?php
// Connexion
$host = "localhost";
$user = "root";
$password = "";
$dbname = "cesman";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

// Création table
$conn->query("CREATE TABLE IF NOT EXISTS rendezvous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_rdv DATE NOT NULL,
    nom_patient VARCHAR(100) NOT NULL,
    heure TIME NOT NULL
)");

// 🔴 SUPPRESSION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM rendezvous WHERE id=$id");
    header("Location: rendezvous.php");
    exit();
}

// 🟢 MODE EDIT
$editMode = false;
$id = "";
$date = "";
$nom = "";
$heure = "";

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM rendezvous WHERE id=$id");

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        $editMode = true;
        $date = $data['date_rdv'];
        $nom = $data['nom_patient'];
        $heure = $data['heure'];
    }
}

// 💾 INSERT / UPDATE
$message = "";

if (isset($_POST['submit'])) {
    $id_post = $_POST['id'];
    $date = $_POST['date'];
    $nom = $_POST['nom_patient'];
    $heure = $_POST['heure'];

    if (!empty($id_post)) {
        // UPDATE
        $conn->query("UPDATE rendezvous 
                      SET date_rdv='$date', nom_patient='$nom', heure='$heure'
                      WHERE id=$id_post");
        $message = "✏️ Rendez-vous modifié avec succès";
    } else {
        // INSERT
        $conn->query("INSERT INTO rendezvous (date_rdv, nom_patient, heure)
                      VALUES ('$date','$nom','$heure')");
        $message = "✅ Rendez-vous ajouté avec succès";
    }
}

// 🔔 ALERTES
$today = date("Y-m-d");
$alertes = $conn->query("SELECT * FROM rendezvous 
                        WHERE date_rdv >= '$today'
                        ORDER BY date_rdv, heure LIMIT 5");

// 📋 LISTE
$result = $conn->query("SELECT * FROM rendezvous ORDER BY date_rdv, heure");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion Rendez-vous - CESMAN</title>

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            margin: 0;
        }

        .container {
            width: 420px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
        }

        h2 {
            text-align: center;
            color: #2E7D32;
        }

        input, button {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            background: #4CAF50;
            color: white;
            border: none;
        }

        button:hover {
            background: #2E7D32;
        }

        .message {
            text-align: center;
            font-weight: bold;
        }

        table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #4CAF50;
            color: white;
            padding: 10px;
        }

        td {
            padding: 10px;
            text-align: center;
        }

        .edit-btn {
            background: #2196F3;
            color: white;
            padding: 5px 8px;
            text-decoration: none;
            border-radius: 5px;
        }

        .delete-btn {
            background: red;
            color: white;
            padding: 5px 8px;
            text-decoration: none;
            border-radius: 5px;
        }

        .alert-box {
            width: 90%;
            margin: 20px auto;
            background: #fff3cd;
            padding: 10px;
            border-left: 5px solid orange;
        }

        .today {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>

<!-- 🔔 ALERTES -->
<div class="alert-box">
    <strong>🔔 Prochains rendez-vous :</strong><br>
    <?php
    if ($alertes->num_rows > 0) {
        while ($a = $alertes->fetch_assoc()) {
            if ($a['date_rdv'] == $today) {
                echo "<div class='today'>Aujourd'hui : ".$a['nom_patient']." à ".$a['heure']."</div>";
            } else {
                echo "<div>".$a['date_rdv']." - ".$a['nom_patient']." à ".$a['heure']."</div>";
            }
        }
    } else {
        echo "Aucun rendez-vous à venir";
    }
    ?>
</div>

<!-- 📅 FORMULAIRE -->
<div class="container">
    <h2><?php echo $editMode ? "Modifier" : "Ajouter"; ?> un Rendez-vous</h2>

    <div class="message"><?php echo $message; ?></div>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <label>Date :</label>
        <input type="date" name="date" value="<?php echo $date; ?>" required>

        <label>Nom patient :</label>
        <input type="text" name="nom_patient" value="<?php echo $nom; ?>" required>

        <label>Heure :</label>
        <input type="time" name="heure" value="<?php echo $heure; ?>" required>

        <button type="submit" name="submit">
            <?php echo $editMode ? "Modifier" : "Enregistrer"; ?>
        </button>
    </form>
</div>

<!-- 📋 TABLEAU -->
<h2 style="text-align:center;color:white;">📋 Liste des Rendez-vous</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Nom</th>
        <th>Heure</th>
        <th>Actions</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>".$row['id']."</td>
                <td>".$row['date_rdv']."</td>
                <td>".$row['nom_patient']."</td>
                <td>".$row['heure']."</td>
                <td>
                    <a class='edit-btn' href='?edit=".$row['id']."'>Modifier</a>
                    <a class='delete-btn' href='?delete=".$row['id']."' onclick=\"return confirm('Supprimer ce rendez-vous ?')\">Supprimer</a>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Aucun rendez-vous</td></tr>";
    }
    ?>
</table>

</body>
</html>