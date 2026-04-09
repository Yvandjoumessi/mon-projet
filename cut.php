<?php
// Définition des utilisateurs avec leur catégorie et icône Font Awesome
$users = [
    ['categorie' => 'Médecin', 'prenom' => 'Jean', 'nom' => 'Dupont', 'icon' => 'fa-solid fa-user-doctor', 'color' => '#FF8A65'],
    ['categorie' => 'Pharmacien', 'prenom' => 'Claire', 'nom' => 'Martin', 'icon' => 'fa-solid fa-capsules', 'color' => '#4DB6AC'],
    ['categorie' => 'Infirmier', 'prenom' => 'Thi', 'nom' => 'Nguyen', 'icon' => 'fa-solid fa-user-nurse', 'color' => '#BA68C8'],
    ['categorie' => 'Échographe', 'prenom' => 'Paul', 'nom' => 'Kouadio', 'icon' => 'fa-solid fa-stethoscope', 'color' => '#FFD54F']
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cartes Utilisateurs</title>
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Général */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h1 {
            margin-top: 30px;
            margin-bottom: 30px;
            color: #333;
            font-size: 2.2em;
        }

        .cards-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding: 20px;
        }

        /* Carte utilisateur */
        .card {
            background: white;
            border-radius: 20px;
            width: 220px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: transform 0.4s, box-shadow 0.4s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s forwards;
        }

        .card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        /* Icône */
        .card-icon {
            font-size: 60px;
            color: white;
            padding: 30px 0 10px 0;
        }

        .card-info {
            padding: 15px 20px 20px 20px;
        }

        .card-info h2 {
            margin: 10px 0 5px;
            font-size: 1.4em;
            color: #333;
        }

        .card-info p {
            margin: 0;
            font-weight: bold;
            color: #666;
        }

        /* Animation fadeInUp */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animation delay pour chaque carte */
        .cards-container .card:nth-child(1) { animation-delay: 0.1s; }
        .cards-container .card:nth-child(2) { animation-delay: 0.2s; }
        .cards-container .card:nth-child(3) { animation-delay: 0.3s; }
        .cards-container .card:nth-child(4) { animation-delay: 0.4s; }

        /* Couleur de fond dynamique pour l’icône */
        <?php foreach($users as $index => $user): ?>
        .cards-container .card:nth-child(<?= $index + 1 ?>)::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 120px;
            width: 100%;
            background: <?= $user['color'] ?>;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            z-index: 0;
        }

        .cards-container .card:nth-child(<?= $index + 1 ?>) .card-icon {
            position: relative;
            z-index: 1;
        }
        <?php endforeach; ?>

        /* Scrollbar pour horizontal */
        .cards-container::-webkit-scrollbar {
            height: 8px;
        }

        .cards-container::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.2);
            border-radius: 4px;
        }

    </style>
</head>
<body>
    <h1>UTLISATEURS DU SYSTEME</h1>
    <div class="cards-container">
        <?php foreach($users as $user): ?>
            <div class="card">
                <div class="card-icon">
                    <i class="<?= $user['icon'] ?>"></i>
                </div>
                <div class="card-info">
                    <h2><?= $user['prenom'] ?> <?= $user['nom'] ?></h2>
                    <p><?= $user['categorie'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>