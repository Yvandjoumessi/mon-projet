<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de la Pharmacie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(2, 250px);
            gap: 30px;
        }
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h2 {
            margin-bottom: 15px;
        }
        .card p {
            margin-bottom: 20px;
            color: #555;
        }
        .card a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        .card a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Gestion des Médicaments -->
        <div class="card">
            <h2>Gestion des Médicaments</h2>
            <p>Ajouter, modifier ou supprimer des médicaments.</p>
            <a href="mdmt.php">Aller</a>
        </div>

        <!-- Gestion des Ventes -->
        <div class="card">
            <h2>Gestion des Ventes</h2>
            <p>Enregistrer et suivre les ventes réalisées.</p>
            <a href="ventes.php">Aller</a>
        </div>

        <!-- Gestion des Stocks -->
        <div class="card">
            <h2>Gestion des Stocks</h2>
            <p>Consulter et mettre à jour les stocks disponibles.</p>
            <a href="stc.php">Aller</a>
        </div>

        <!-- Factures -->
        <div class="card">
            <h2>Factures</h2>
            <p>Générer et consulter les factures.</p>
            <a href="fact.php">Aller</a>
        </div>
    </div>
</body>
</html>