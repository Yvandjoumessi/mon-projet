<?php

$con = mysqli_connect("localhost","root","","cesman");

if (!$con) {
    die("Erreur: ".mysqli_connect_error());
}else {
    echo("felicitation !!");
}
?>             