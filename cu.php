<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix de Compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
            margin: 0;
        }
        .container {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;           /* Permet de passer à la ligne si l'écran est petit */
            justify-content: center;
        }
        .card {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-align: center;
            width: 200px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h2 {
            margin-bottom: 20px;
        }
        .card a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        .card a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Carte Médecin -->
        <div class="card">
            <h2>Compte Médecin</h2>
            <p>Espace Médecin.</p>
            <a href="ordo.php" onclick="return checkPassword('medecin')">Aller au formulaire</a>
        </div>

        <!-- Carte Pharmacien -->
        <div class="card">
            <h2>Compte Pharmacien</h2>
            <p>Espace Pharmacien.</p>
            <a href="gs.php" onclick="return checkPassword('pharmacien')">Aller au formulaire</a>
        </div>

        <!-- Nouvelle Carte : Comptes Fournisseurs -->
        <div class="card">
            <h2>Comptes Fournisseurs</h2>
            <p>Espace Fournisseurs.</p>
            <a href="bdc.php" onclick="return checkPassword('fournisseur')">Aller au formulaire</a>
        </div>

    </div>

    <script>
        function checkPassword(compte) {
            let mdp = prompt("Entrez le mot de passe pour accéder à ce compte :");
            
            if (compte === 'medecin' && mdp === '12345') {
                return true;
            } 
            else if (compte === 'pharmacien' && mdp === '12213') {
                return true;
            } 
            else if (compte === 'fournisseur' && mdp === 'fournisseur123') {   // Tu peux changer ce mot de passe
                return true;
            } 
            else {
                alert("Mot de passe incorrect !");
                return false;
            }
        }
    </script>
</body>
</html>