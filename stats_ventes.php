<?php
$conn = new mysqli("localhost", "root", "", "cesman");

// TOTAL VENTES
$total = $conn->query("SELECT COUNT(*) as total FROM ventes")->fetch_assoc()['total'];

// MONTANT TOTAL
$montant = $conn->query("SELECT SUM(montant) as total FROM ventes")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Statistiques Ventes</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial;
            background: #2c3e50;
            color: white;
            text-align: center;
        }

        .box {
            margin: 20px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
    </style>
</head>

<body>

<h2>📊 Statistiques des ventes</h2>

<div class="box">Total ventes : <?php echo $total; ?></div>
<div class="box">Montant total : <?php echo $montant; ?> FCFA</div>

<canvas id="chart" width="400" height="200"></canvas>

<script>
const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Ventes'],
        datasets: [{
            label: 'Total',
            data: [<?php echo $total; ?>]
        }]
    }
});
</script>

</body>
</html>