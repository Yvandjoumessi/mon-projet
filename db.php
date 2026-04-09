<?php
$admin = "Dr MBOUGO";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Tableau de bord CESMAN</title>

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{
    margin:0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #0a3d2a 0%, #0f5132 100%);
    color: #e0f2e9;
    min-height: 100vh;
    overflow-x: hidden;
    position: relative;
}

/* Animation de fond sur TOUT l'arrière-plan */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 30%, rgba(40, 167, 69, 0.25) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(40, 167, 69, 0.18) 0%, transparent 60%);
    animation: backgroundGlow 25s ease-in-out infinite alternate;
    z-index: -1;
    pointer-events: none;
}

@keyframes backgroundGlow {
    0% {
        opacity: 0.65;
        transform: scale(1) rotate(0deg);
    }
    100% {
        opacity: 0.95;
        transform: scale(1.12) rotate(3deg);
    }
}

/* SIDEBAR */
.sidebar{
    width:250px;
    height:100vh;
    background: rgba(15, 81, 50, 0.95);
    color:white;
    position:fixed;
    backdrop-filter: blur(8px);
    border-right: 1px solid rgba(40, 167, 69, 0.3);
    box-shadow: 4px 0 15px rgba(0,0,0,0.3);
    z-index: 1000;
}

.sidebar h2{
    text-align:center;
    padding:20px 15px;
    background:#0a3d2a;
    font-weight: 800;
    letter-spacing: 1px;
    margin:0;
    border-bottom: 3px solid #28a745;
}

.sidebar a{
    display:block;
    padding:14px 20px;
    color:white;
    text-decoration:none;
    border-bottom:1px solid rgba(255,255,255,0.08);
    font-weight: 600;
    transition: all 0.3s ease;
}

.sidebar a:hover{
    background:#28a745;
    padding-left:30px;
    color: white;
    box-shadow: inset 4px 0 0 #1e7e3e;
}

/* Bouton Déconnexion */
.logout-btn {
    display:block;
    padding:14px 20px;
    color:white;
    text-decoration:none;
    background:#c82333;
    font-weight: 700;
    margin-top: 20px;
    border-bottom: none;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background:#a71d2a;
    padding-left:30px;
    color: white;
    box-shadow: inset 4px 0 0 #72141f;
}

/* HEADER */
.header{
    margin-left:250px;
    padding:18px 25px;
    background: rgba(255,255,255,0.95);
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0px 3px 10px rgba(0,0,0,0.15);
    color: #0f5132;
    font-weight: 700;
}

/* CONTENU */
.content{
    margin-left:250px;
    padding:40px 25px;
    text-align: center;
}

/* Style pour le logo - Image occupe tout l'espace */
.logo-container {
    margin: 30px auto;
    width: 100%;
    max-width: 1800px;           /* Tu peux augmenter cette valeur si tu veux plus grand */
    padding: 20px;
    background: rgba(255,255,255,0.08);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.logo-container img {
    width: 100%;                 /* Occupe TOUTE la largeur disponible */
    height: auto;                /* Garde les proportions naturelles */
    display: block;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    transition: all 0.4s ease;
}

.logo-container img:hover {
    transform: scale(1.02);
}

.h3{
    text-align: center;
}
</style>

</head>

<body>

<!-- MENU GAUCHE -->
<div class="sidebar">
    <h2><i class="fas fa-hospital-user me-2"></i> CESMAN</h2>

    <a href="cut.php"><i class="fas fa-users me-2"></i> Comptes utilisateurs</a>
    <a href="pt.php"><i class="fas fa-user-injured me-2"></i> Comptes patients</a>
    <a href="MEDOC.php"><i class="fas fa-pills me-2"></i> Comptes médicaments</a>
    <!-- Ligne supprimée : Comptes ordonnances -->
    <a href="vt.php"><i class="fas fa-money-bill-wave me-2"></i> Comptes ventes</a>
    <a href="stc.php"><i class="fas fa-boxes-stacked me-2"></i> Comptes stocks</a>
    <a href="rp.php"><i class="fas fa-chart-bar me-2"></i> Comptes rapports</a>
    <a href="rdv.php"><i class="fas fa-calendar-check me-2"></i> Comptes rendez-vous</a>
    <a href="stats.php"><i class="fas fa-chart-pie me-2"></i> Comptes statistiques</a>
    <a href="cu.php"><i class="fas fa-user-shield me-2"></i> Comptes uniques</a>
    <a href="pmt.php"><i class="fas fa-cog me-2"></i> Comptes paramètres</a>
    
    <!-- Bouton Déconnexion ajouté en bas -->
    <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
    </a>
</div>

<!-- HEADER -->
<div class="header">
    <h3><i class="fas fa-tachometer-alt me-2"></i> Bienvenue dans le tableau de bord</h3>
    <h4><?php echo $admin; ?></h4>
</div>

<!-- CONTENU - Zone pour ton logo/image -->
<div class="content">
    <div class="logo-container">
        <!-- Remplace le chemin par celui de ton logo -->
        <img src="mm.png" alt="Logo CESMAN">
    </div>
</div>

</body>
</html>