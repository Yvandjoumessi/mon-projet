
<?php
$admin = "Jumi Patric";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs & Sécurité - CESMAN</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0a3d2a 0%, #0f5132 100%);
            color: #e0f2e9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animation de fond */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at 30% 40%, rgba(40, 167, 69, 0.25) 0%, transparent 60%);
            animation: pulseBg 18s ease-in-out infinite alternate;
            z-index: -1;
            pointer-events: none;
        }

        @keyframes pulseBg {
            0% { opacity: 0.7; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.15); }
        }

        .header {
            background: rgba(255,255,255,0.95);
            padding: 25px;
            text-align: center;
            color: #0f5132;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        }

        .container {
            padding: 60px 20px;
            display: flex;
            justify-content: center;
            gap: 60px;
            flex-wrap: wrap;
        }

        .card {
            background: rgba(255,255,255,0.95);
            width: 320px;
            padding: 50px 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            transition: all 0.4s ease;
            color: #0f5132;
        }

        .card:hover {
            transform: translateY(-20px) scale(1.05);
            box-shadow: 0 25px 50px rgba(40, 167, 69, 0.45);
        }

        .icon {
            font-size: 5.5rem;
            margin-bottom: 25px;
            transition: all 0.5s ease;
            color: #28a745;
        }

        .card:hover .icon {
            transform: scale(1.2) rotate(10deg);
            color: #1e7e3e;
        }

        .card h2 {
            font-size: 1.8rem;
            margin: 15px 0 10px 0;
            font-weight: 800;
        }

        .card p {
            opacity: 0.85;
            margin-bottom: 30px;
        }

        .card a {
            display: inline-block;
            padding: 14px 40px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .card a:hover {
            background: #218838;
            transform: scale(1.08);
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-shield-alt"></i> CESMAN - Gestion & Sécurité</h1>
        <p>Bienvenue, <?php echo $admin; ?></p>
    </div>

    <div class="container">

        <!-- Carte 1 : Ajout des Utilisateurs -->
        <div class="card">
            <div class="icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Ajout des Utilisateurs</h2>
            <p>Créer un nouveau compte utilisateur</p>
            <a href="ajtuu.php" onclick="return checkPassword('utilisateur')">
                Accéder au formulaire
            </a>
        </div>

        <!-- Carte 2 : Sécurité -->
        <div class="card">
            <div class="icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Sécurité du Système</h2>
            <p>Gestion des mots de passe et permissions</p>
            <a href="securite.php" onclick="return checkPassword('securite')">
                Accéder au formulaire
            </a>
        </div>

    </div>

    <script>
        function checkPassword(type) {
            let password = prompt("Entrez le mot de passe pour accéder :");
            
            // Mot de passe unique pour les deux cartes : 1234
            if (password === "1234") {
                return true;   // Autorise la redirection
            } else {
                alert("❌ Mot de passe incorrect !\nLe mot de passe est : 1234");
                return false;  // Bloque la redirection
            }
        }
    </script>

</body>
</html>