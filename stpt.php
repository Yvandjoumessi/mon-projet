<?php
$conn = new mysqli("localhost", "root", "", "cesman");

// TOTAL PATIENTS
if($conn->connect_error) {

die("Erreur de connexion : " . $conn->connect_error);

}

// PATIENTS PAR MOIS (évolution)
$mois = [];
$valeurs = [];




    $sql = "SELECT MONTH(d_a) as mois, COUNT(*) as total
    FROM patients
    GROUP BY mois";
    $result = $conn->query($sql);
    
    if (!$result){

die("Erreur SQL : " . $conn->error);

    }

while ($row = $result->fetch_assoc()) {
    $mois[] = $row['mois'];
    $valeurs[] = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>

<!-- BOUTON RETOUR -->
<div style="margin-bottom: 20px; text-align: left;">
    <button onclick="window.history.back();" 
            style="padding: 10px 20px; background:#3498db; color:white; border:none; border-radius:5px; cursor:pointer; transition:0.3s;">
        <i class="fas fa-arrow-left"></i> Retour
    </button>
</div>


    <title>Statistiques Patients</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(120deg, #27ae60, #2ecc71);
            color: white;
            text-align: center;
        }

        h2 {
            margin-top: 20px;
        }

        .box {
            margin: 20px auto;
            padding: 20px;
            width: 300px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            font-size: 20px;
        }

        canvas {
            background: white;
            border-radius: 10px;
            margin: 20px;
            padding: 10px;
        }
    </style>
</head>

<body>

<h2>📊 Statistiques des patients</h2>

<!-- TOTAL -->
<div class="box">
    👥 Nombre total de patients : <strong><?php echo $total; ?></strong>
</div>

<!-- DIAGRAMME BARRES -->
<h3>📈 Évolution mensuelle</h3>
<canvas id="barChart" width="400" height="200"></canvas>

<!-- DIAGRAMME CIRCULAIRE -->
<h3>🥧 Répartition</h3>
<canvas id="pieChart" width="400" height="200"></canvas>

<script>

// DONNÉES PHP → JS
const labels = <?php echo json_encode($mois); ?>;
const dataValues = <?php echo json_encode($valeurs); ?>;

// 📊 DIAGRAMME BARRES
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Patients par mois',
            data: dataValues
        }]
    }
});

// 🥧 DIAGRAMME CIRCULAIRE
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: dataValues
        }]
    }
});

</script>




</body>
</html>