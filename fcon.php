<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacie du Cesman - Connexion</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f5132, #1a7d4f);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            width: 95%;
            max-width: 1200px;
            height: 90vh;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            overflow: hidden;
        }

        /* GAUCHE - LOGO */
        .left-panel {
            flex: 1;
            background: linear-gradient(to bottom, #0f5132, #1a7d4f);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
        }

        .logo {
            width: 180px;
            height: 180px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 72px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-bottom: 25px;
            animation: logoPulse 2s infinite ease-in-out;
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        .pharmacy-name {
            font-size: 2.3rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 1.15rem;
            opacity: 0.95;
        }

        .consult-link {
            margin-top: 60px;
            padding: 16px 35px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.5);
            color: white;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.4s;
            text-decoration: none;
        }
        .consult-link:hover {
            background: white;
            color: #0f5132;
            transform: translateY(-4px);
        }

        /* DROITE - FORMULAIRE */
        .right-panel {
            flex: 1;
            padding: 55px 65px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h2 { 
            color: #0f5132; 
            text-align: center; 
            margin-bottom: 35px; 
            font-size: 1.8rem;
        }

        .form-group { margin-bottom: 22px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input, select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        input:focus, select:focus {
            border-color: #1a7d4f;
            box-shadow: 0 0 0 4px rgba(26,125,79,0.15);
            outline: none;
        }

        .btn-connect {
            width: 100%;
            padding: 17px;
            font-size: 1.15rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 25px;
            transition: all 0.4s ease;
            color: white;
        }

        .btn-connect.red    { background: #f44336; }
        .btn-connect.yellow { background: #ff9800; }
        .btn-connect.green  { background: #4caf50; }

        .btn-connect.loading { animation: pulse 1.2s infinite; }

        @keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.04); } }

        .message {
            margin-top: 18px;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }
        .error   { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>

<div class="container">

    <!-- PARTIE GAUCHE -->
    <div class="left-panel">
        <div class="logo">💊</div>
        <div class="pharmacy-name">PHARMACIE DU CESMAN</div>
        <div class="subtitle">"Votre santé, notre engagement"</div>
        
        <!-- Redirection vers le nouvel espace médicaments -->
        <a href="med.php" class="consult-link">
            Consulter l’espace des médicaments
        </a>
    </div>

    <!-- PARTIE DROITE - FORMULAIRE DE CONNEXION -->
    <div class="right-panel">
        <h2>Connexion au système</h2>

        <form id="loginForm">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" id="nom" placeholder="Votre nom d'utilisateur" required>
            </div>

            <div class="form-group">
                <label>Adresse e-mail</label>
                <input type="email" id="email" placeholder="exemple@cesseman.cm" required>
            </div>

            <div class="form-group">
                <label>Mot de passe du système</label>
                <input type="password" id="password" placeholder="Mot de passe configuré par l'administrateur" required>
            </div>

            <div class="form-group">
                <label>Numéro de téléphone</label>
                <input type="tel" id="telephone" placeholder="+237 6XX XXX XXX" required>
            </div>

            <div class="form-group">
                <label>Rôle</label>
                <select id="role" required>
                    <option value="">Sélectionnez votre rôle</option>
                    <option value="administrateur">Administrateur</option>
                    <option value="pharmacien">Pharmacien</option>
                    <option value="infirmier">Infirmier</option>
                    <option value="echographe">Échographe</option>
                </select>
            </div>

            <button type="button" id="btnConnect" class="btn-connect">Se connecter</button>
        </form>

        <div id="message" class="message"></div>
    </div>
</div>

<script>
// === GESTION DU BOUTON CONNEXION (inchangé) ===
const btn = document.getElementById('btnConnect');
const messageDiv = document.getElementById('message');

btn.addEventListener('click', async function() {
    const nom = document.getElementById('nom').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const tel = document.getElementById('telephone').value.trim();
    const role = document.getElementById('role').value;

    messageDiv.innerHTML = '';
    btn.classList.remove('red', 'yellow', 'green', 'loading');
    btn.textContent = "Se connecter";

    if (!nom || !email || !password || !tel || !role) {
        btn.classList.add('red');
        messageDiv.innerHTML = `<div class="error">❌ Veuillez remplir tous les champs.</div>`;
        setTimeout(() => btn.classList.remove('red'), 2500);
        return;
    }

    btn.textContent = "Vérification en cours...";
    btn.classList.add('loading');

    // Simulation de vérification du mot de passe système
    setTimeout(() => {
        btn.classList.remove('loading');

        const passwordCorrect = password.length >= 4; // Remplace par vraie vérification (config.json ou base de données)

        if (!passwordCorrect) {
            btn.classList.add('yellow');
            btn.textContent = "Réessayer";
            messageDiv.innerHTML = `<div class="error">❌ Mot de passe du système incorrect.</div>`;
            return;
        }

        btn.classList.add('green');
        btn.textContent = "✅ Connexion réussie";
        messageDiv.innerHTML = `<div class="success">🎉 Connexion réussie !<br>Bienvenue \( {nom} ( \){role})</div>`;

        // Redirection vers le tableau de bord après succès
        setTimeout(() => {
            window.location.href = "db.php";
        }, 1800);
    }, 1600);
});
</script>
</body>
</html>