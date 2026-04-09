<?php
require 'config.php';

if (isset($_POST['Se_Connecter'])) {
    

$nom = mysqli_real_escape_string($con, $_POST['nom']);
$email = mysqli_real_escape_string($con, $_POST['email']);
$mdp = mysqli_real_escape_string($con, $_POST['password']);
$tel = mysqli_real_escape_string($con, $_POST['telephone']);
$role = mysqli_real_escape_string($con, $_POST['role']);

if (mysqli_query($con, "SELECT * FROM UTILISATEURS WHERE  nom = '$nom'  AND email= '$email' AND 
                 mot_de_passe = $mdp AND tel_user = '$tel' AND role = '$role' ")) {
    header("Location:db.php");
}else {
    echo ("Erreur: ".mysqli_error($con));
}
}
?>


<H2>GESTION DES INFIRMIERS</H2>

<form id="formInfirmier">

</form>