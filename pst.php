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


    <title>Statistiques</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: Arial;
            background: #16a085;
            color: white;
            text-align: center;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
        }

        .card {
            width: 180px;
            height: 120px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            cursor: pointer;
        }

        .card:hover {
            transform: scale(1.05);
        }

        a {
            text-decoration: none;
            color: white;
        }
    </style>
</head>

<body>

<h2>📊 Statistiques</h2>

<div class="cards">

    <a href="stats_ventes.php"><div class="card">Ventes</div></a>
    <a href="stpt.php"><div class="card">Patients</div></a>
    <a href="stats_utilisateurs.php"><div class="card">Utilisateurs</div></a>
    <a href="stats_rdv.php"><div class="card">Rendez-vous</div></a>
    <a href="stats_ordonnances.php"><div class="card">Ordonnances</div></a>
    <a href="stats_medicaments.php"><div class="card">Médicaments</div></a>

</div>



</body>
</html>