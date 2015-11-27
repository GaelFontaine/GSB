<?php

require("../include/_init.inc.php");
header('Content-Type: application/json');

$utilisateur = $_POST['idUtilisateur'];
$mois = $_POST['mois'];

// Récupération des frais au forfait selon l'utilisateur et le mois 
$idJeuEltsFraisForfait = $pdo->getLesFraisForfait($utilisateur,$mois);
print json_encode($idJeuEltsFraisForfait);
