<!DOCTYPE html>
<html>
<head>
    <title>Paramètres</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(120deg, #2c3e50, #15af96);
            color: white;
            text-align: center;
        }

        .cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 100px;
        }

        .card {
            width: 200px;
            height: 150px;
            border-radius: 15px;
            background: rgba(255,255,255,0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: 0.3s;
        }

        .card:hover {
            transform: scale(1.1);
        }

        a {
            text-decoration: none;
            color: white;
        }

        i {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<h2>⚙️ Paramètres du système</h2>

<div class="cards">

    <a href="pst.php">
        <div class="card">
            <i class="fas fa-chart-line"></i>
            Statistiques
        </div>
    </a>


    <a href="admin.php">
    
        <div class="card">
            <i class="fas fa-user-shield"></i>
            Administrateur
        </div>
    </a>

</div>

</body>
</html>