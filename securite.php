<?php
// Connexion à la base de données
$host = "localhost";
$dbname = "cesman";
$username = "root"; // adapter selon ta config
$password = "";     // adapter selon ta config

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Messages pour les opérations
$message = "";

// Ajouter ou modifier un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_User = $_POST['id_User'] ?? '';
    $nomUser = trim($_POST['nomUser']);
    $adresseUser = trim($_POST['adresseUser']);
    $telUser = trim($_POST['telUser']);
    $roleUser = trim($_POST['roleUser']);
    $miseUser = trim($_POST['miseUser']);

    if (empty($nomUser) || empty($miseUser)) {
        $message = "Nom d'utilisateur et mot de passe obligatoires.";
    } else {
        $hashedPassword = password_hash($miseUser, PASSWORD_DEFAULT);
        if (empty($id_User)) {
            // Ajouter
            $stmt = $pdo->prepare("INSERT INTO login (nomUser, adresseUser, telUser, roleUser, miseUser) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nomUser, $adresseUser, $telUser, $roleUser, $hashedPassword]);
            $message = "Utilisateur ajouté avec succès.";
        } else {
            // Modifier
            $stmt = $pdo->prepare("UPDATE login SET nomUser=?, adresseUser=?, telUser=?, roleUser=?, miseUser=? WHERE idUser=?");
            $stmt->execute([$nomUser, $adresseUser, $telUser, $roleUser, $hashedPassword, $id_User]);
            $message = "Utilisateur modifié avec succès.";
        }

        // Rediriger pour réinitialiser le formulaire en mode "Ajouter"
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=" . urlencode($message));
        exit;
    }
}

// Supprimer un utilisateur
if (isset($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM login WHERE idUser=?");
    $stmt->execute([$idToDelete]);
    $message = "Utilisateur supprimé avec succès.";

    // Rediriger pour nettoyer l'URL
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=" . urlencode($message));
    exit;
}

// Pré-remplir le formulaire pour modification
$editUser = null;
if (isset($_GET['edit'])) {
    $idToEdit = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM login WHERE idUser=?");
    $stmt->execute([$idToEdit]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Récupération de tous les utilisateurs
$stmt = $pdo->query("SELECT idUser, nomUser, adresseUser, telUser, roleUser FROM login");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer message de succès depuis GET après redirection
if (isset($_GET['success'])) {
    $message = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion Utilisateurs CRUD</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0; padding: 0;
    display: flex;
    justify-content: center;
    background-color: #04633e;
}
.container {
    display: flex;
    margin-top: 50px;
    width: 90%;
    max-width: 1200px;
    gap: 50px;
}
.form-container, .table-container {
    background-color: #22e7a6;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.form-container {
    flex: 1;
}
.form-container h2 {
    margin-top: 0; color: #333;
}
.form-container input[type="text"],
.form-container input[type="password"] {
    width: 100%; padding: 10px; margin-bottom: 15px;
    border-radius: 4px; border: 1px solid #ccc;
}
.form-container input[type="submit"] {
    padding: 10px 20px; background-color: #007bff;
    border: none; color: white; border-radius: 4px;
    cursor: pointer;
}
.form-container .message {
    margin-bottom: 15px; color: green;
}
.table-container {
    flex: 2; overflow-x: auto;
}
table {
    width: 100%; border-collapse: collapse;
}
table, th, td {
    border: 1px solid #ddd;
}
th, td {
    padding: 12px; text-align: left;
}
th {
    background-color: #007bff; color: white;
}
tr:nth-child(even) {
    background-color: #f9f9f9;
}
.action-buttons a {
    margin-right: 5px; text-decoration: none; padding: 5px 10px;
    border-radius: 4px; color: white;
}
.edit-btn { background-color: #28a745; }
.delete-btn { background-color: #dc3545; }
</style>
</head>
<body>
<div class="container">
    <!-- Formulaire -->
    <div class="form-container">
        <h2><?= $editUser ? "Modifier Utilisateur" : "Ajouter Utilisateur" ?></h2>
        <?php if (!empty($message)) echo "<div class='message'>" . htmlspecialchars($message) . "</div>"; ?>
        <form method="post" action="">
            <input type="hidden" name="id_User" value="<?= $editUser['idUser'] ?? '' ?>">
            <input type="text" name="nomUser" placeholder="Nom d'utilisateur" required value="<?= $editUser['nomUser'] ?? '' ?>">
            <input type="text" name="adresseUser" placeholder="Adresse" value="<?= $editUser['adresseUser'] ?? '' ?>">
            <input type="text" name="telUser" placeholder="Téléphone" value="<?= $editUser['telUser'] ?? '' ?>">
            <input type="text" name="roleUser" placeholder="Rôle" value="<?= $editUser['roleUser'] ?? '' ?>">
            <input type="password" name="miseUser" placeholder="Mot de passe" required>
            <input type="submit" value="<?= $editUser ? "Modifier" : "Ajouter" ?>">
        </form>
    </div>

    <!-- Tableau -->
    <div class="table-container">
        <h2>Liste des utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['idUser']) ?></td>
                    <td><?= htmlspecialchars($user['nomUser']) ?></td>
                    <td><?= htmlspecialchars($user['adresseUser']) ?></td>
                    <td><?= htmlspecialchars($user['telUser']) ?></td>
                    <td><?= htmlspecialchars($user['roleUser']) ?></td>
                    <td class="action-buttons">
                        <a href="?edit=<?= $user['idUser'] ?>" class="edit-btn">Modifier</a>
                        <a href="?delete=<?= $user['idUser'] ?>" class="delete-btn" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)) echo "<tr><td colspan='6'>Aucun utilisateur trouvé.</td></tr>"; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>