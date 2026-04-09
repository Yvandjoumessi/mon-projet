<?php
// rapports.php - Interface des Rapports (Thème Vert Foncé)
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface des Rapports</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0a3d2a, #0f5132);
            color: #e0f2e9;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .dashboard-header {
            background: rgba(25, 135, 84, 0.95);
            backdrop-filter: blur(10px);
            padding: 3rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .report-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
        }

        .report-card:hover {
            transform: translateY(-15px) scale(1.03);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(25, 135, 84, 0.4);
            border-color: #28a745;
        }

        .card-icon {
            font-size: 3.5rem;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .report-card:hover .card-icon {
            transform: scale(1.15) rotate(8deg);
            color: #28a745;
        }

        .card-title {
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(40, 167, 69, 0.08) 0%, transparent 70%);
            pointer-events: none;
            z-index: -1;
            animation: pulse 15s infinite alternate ease-in-out;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .footer-link {
            color: #a3d9b8;
            text-decoration: none;
        }
        .footer-link:hover {
            color: #28a745;
        }
    </style>
</head>
<body>

    <!-- Fond animé subtil -->
    <div class="bg-overlay"></div>

    <!-- Header -->
    <div class="dashboard-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-2">
                <i class="fas fa-chart-pie text-white me-3"></i> 
                Rapports & Statistiques
            </h1>
            <p class="lead mb-0">Sélectionnez le rapport que vous souhaitez consulter</p>
        </div>
    </div>

    <div class="container py-5">

        <div class="row g-4">

            <!-- Rapport de Vente -->
            <div class="col-lg-3 col-md-6">
                <a href="rv.php" class="text-decoration-none">
                    <div class="card report-card text-center p-4">
                        <div class="card-body">
                            <div class="card-icon text-success">
                                <i class="fas fa-money-bill-trend-up"></i>
                            </div>
                            <h5 class="card-title text-white">Rapport des Ventes</h5>
                            <p class="text-light opacity-75 small">Chiffre d'affaires, tendances, top produits vendus</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-success fw-bold">
                            Accéder <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Rapport des Patients -->
            <div class="col-lg-3 col-md-6">
                <a href="rpts.php" class="text-decoration-none">
                    <div class="card report-card text-center p-4">
                        <div class="card-body">
                            <div class="card-icon text-info">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h5 class="card-title text-white">Rapport des Patients</h5>
                            <p class="text-light opacity-75 small">Statistiques patients, consultations, suivi</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-info fw-bold">
                            Accéder <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Rapport des Stocks -->
            <div class="col-lg-3 col-md-6">
                <a href="rpstc.php" class="text-decoration-none">
                    <div class="card report-card text-center p-4">
                        <div class="card-body">
                            <div class="card-icon text-warning">
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                            <h5 class="card-title text-white">Rapport des Stocks</h5>
                            <p class="text-light opacity-75 small">Niveau des stocks, alertes rupture, inventaire</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-warning fw-bold">
                            Accéder <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Rapport des Médicaments -->
            <div class="col-lg-3 col-md-6">
                <a href="rpm.php" class="text-decoration-none">
                    <div class="card report-card text-center p-4">
                        <div class="card-body">
                            <div class="card-icon text-danger">
                                <i class="fas fa-pills"></i>
                            </div>
                            <h5 class="card-title text-white">Rapport des Médicaments</h5>
                            <p class="text-light opacity-75 small">Consommation, dates d'expiration, statistiques</p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-danger fw-bold">
                            Accéder <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </div>

    <!-- Footer optionnel -->
    <footer class="text-center py-4 opacity-75">
        <small>© Système de Gestion Pharmacie - Rapports</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>