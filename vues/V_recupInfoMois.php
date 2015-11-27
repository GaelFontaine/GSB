<?php
require("../include/_init.inc.php");

header('Content-Type: application/json');

$utilisateur = $_POST['idUtilisateur'];

$idJeuEltsMois = $pdo->getLesMoisValidation($utilisateur);
$i=0;
foreach($idJeuEltsMois as $unMois)
{
    $tab[$i]['mois'] = $unMois['mois'];
    $tab[$i]['libelle'] = obtenirLibelleMois(intval($unMois['numMois']));
    $tab[$i]['numAnnee'] = $unMois['numAnnee'];
    $i++;
}

if($i === 0){
    $tab[$i]['libelle'] = "Pas de fiche de frais pour ce visiteur ce mois";
    $tab[$i]['numAnnee'] = "";
}

print json_encode($tab);