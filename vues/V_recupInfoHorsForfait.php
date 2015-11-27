<?php

require("../include/_init.inc.php");

header('Content-Type: application/json');

$utilisateur = $_POST['idUtilisateur'];
$mois = $_POST['mois'];


$idJeuEltsHorsForfait = $pdo->getLesFraisHorsForfait($utilisateur,$mois);


print json_encode($idJeuEltsHorsForfait);
