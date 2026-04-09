<?php
session_start();

// Connexion BD
$conn = new mysqli("localhost", "root", "", "cesman");

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = $_POST['nomUser'];
    $adresse = $_POST['adresseUser'];
    $password = $_POST['miseUser'];
    $tel = $_POST['telUser'];
    $role = $_POST['roleUser'];

    $sql = "SELECT * FROM login WHERE nomUser='$nom'";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        $message = "❌ Utilisateur inexistant";
    } else {
        $user = $result->fetch_assoc();

        if (
            $user['adresseUser'] == $adresse &&
            $user['miseUser'] == $password &&
            $user['telUser'] == $tel &&
            $user['roleUser'] == $role
        ) {
            $_SESSION['nomUser'] = $nom;
            header("Location: db.php");
        } else {
            $message = "⚠️ Informations incorrectes";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Login Pharmacie</title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background: linear-gradient(135deg, #053010, #90ee90);
    display: flex;
    height: 100vh;
}

/* GAUCHE */
.left {
    width: 50%;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    animation: fadeIn 2s;
}

.left img {
    width: 150px;
}

.left h1 {
    margin-top: 20px;
}

.left p {
    font-style: italic;
}

.left a {
    margin-top: 30px;
    padding: 10px 20px;
    background: white;
    color: green;
    text-decoration: none;
    border-radius: 20px;
    transition: 0.3s;
}

.left a:hover {
    background: #0b6623;
    color: white;
}

/* DROITE */
.right {
    width: 450px;
    background: #d7dbd4;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 50px;
    margin: 20px;
}

.form-box {
    width: 400px;
    
    animation: slideIn 1.5s;
}

.form-box h2 {
    text-align: center;
    color: green;
}

input, select {
    width: 100%;
    padding: 15px;
    margin: 10px 0;
    border: 1px solid #03101b;
    border-radius: 50px;
    text-align: center;
}

button {
    width: 50%;
    padding: 10px;
    margin: 10px;
    background: #0b6623;
    color: white;
    border: none;
    border-radius: 50px;
    cursor: pointer;
}

button:hover {
    background: #90ee90;
    color: black;
}

.message {
    text-align: center;
    color: red;
}

/* Animations */
@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
}

@keyframes slideIn {
    from {transform: translateX(100px); opacity: 0;}
    to {transform: translateX(0); opacity: 1;}
}
</style>

</head>

<body>

<!-- GAUCHE -->
<div class="left">
    <img src="lg.jpeg" alt="Logo pharmacie" style="width: 350px;  ">
    <h1>PHARMACIE DU CESMAN</h1>
    <p>"Votre santé, notre engagement"</p>

    <a href="med.php">Consulter les médicaments</a>
</div>

<div class="form-box">

<!-- DROITE -->
<div class="right">
    <div class="form-box">
        <h2>CONNEXION</h2>

        <form method="POST">
            <input type="text" name="nomUser" placeholder="Nom utilisateur" required>
            <input type="text" name="adresseUser" placeholder="Adresse" required>
            <input type="text" name="telUser" placeholder="Téléphone" required>
<input type="text" name="miseUser" placeholder="Mot de passe" required>
            <select name="roleUser" required>
                <option value="">Choisir rôle</option>
                <option value="administrateur">Administrateur</option>
                <option value="pharmacien">Pharmacien</option>
                <option value="medecin">Médecin</option>
                <option value="echographe">Échographe</option>
            </select>

            <button type="submit">Se connecter</button>
        </form>

        <div class="message"><?php echo $message; ?></div>
    </div>
</div>

</body>
</html>