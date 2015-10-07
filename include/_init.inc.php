<?php

/**
 * Initialise les ressources nécessaires au fonctionnement de l'application
 * @package default
 * @todo  RAS
 */
require("class.pdogsb.inc.php");
require("_gestionSession.lib.php");
require("_utilitairesEtGestionErreurs.lib.php");
// démarrage ou reprise de la session
initSession();
// initialement, aucune erreur ...
$tabErreurs = array();

// Demande-t-on une déconnexion ?
$demandeDeconnexion = lireDonneeUrl("cmdDeconnecter");
if ($demandeDeconnexion == "on") {
    deconnecterVisiteur();
    header("Location: cAccueil.php");
}

try {
    $pdo = PdoGsb::getPdoGsb();
} catch (PDOException $e) {
    ajouterErreur($tabErreurs, 'Erreur num&eacute;ro : ' . $e->getCode());
    ajouterErreur($tabErreurs, 'Détails : ' . $e->getMessage());
}
?>