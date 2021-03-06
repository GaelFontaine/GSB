<?php
/**
 * Contient la division pour le sommaire, sujet à des variations suivant la 
 * connexion ou non d'un utilisateur, et dans l'avenir, suivant le type de cet utilisateur 
 * @todo  RAS
 */
?>
<!-- Division pour le sommaire -->
<?php
if (estVisiteurConnecte()) {
    $idUser = obtenirIdUserConnecte();
    $lgUser = $pdo->getInfosVisiteur($idUser);
    $nom = $lgUser['nom'];
    $prenom = $lgUser['prenom'];
    $role = $lgUser['role'];
    ?>
    <!-- Division pour le sommaire -->
    <div id="menuGauche">
        <div id="infosUtil">

            <h2>
                <?php
                echo $nom . " " . $prenom;
                ?>
            </h2>
                <?php
                if ($role == 1) {
                    ?>
                <h3>Comptable</h3>
                <?php
            } else {
                ?>
                <h3>Visiteur médical</h3>        
                <?php }
            ?>
        </div>  
            <?php
            if (estVisiteurConnecte()) {
                if ($role == 1) {
                    ?>
                <ul id="menuList">
                    <li class="smenu">
                        <a href="../pages/cAccueil.php" title="Page d'accueil">Accueil</a>
                    </li>
                    <li class="smenu">
                        <a href="../pages/cSeDeconnecter.php" title="Se déconnecter">Se déconnecter</a>
                    </li>
                    <li class="smenu">
                        <a href="../pages/cValidationFicheVisit.php" title="Validation des fiches de frais">Validation des fiches de frais</a>
                    </li>
                    <li class="smenu">
                        <a href="../pages/cSuiviPaiementFiche.php" title="Suivi du paiement des fiches de frais">Suivi du paiement des fiches de frais</a>
                    </li>
                </ul>
            <?php
        } else {
            ?>
                <ul id="menuList">
                    <li class="smenu">
                        <a href="../pages/cAccueil.php" title="Page d'accueil">Accueil</a>
                    </li>
                    <li class="smenu">
                        <a href="../pages/cSeDeconnecter.php" title="Se déconnecter">Se déconnecter</a>
                    </li>
                        <h2>Outils</h2>
                        <ul>
                            <li class="smenu"><a href="../pages/cSaisieFicheFrais.php" >Nouvelle fiche de frais</a></li>
                            <li class="smenu"><a href="../pages/cConsultFichesFrais.php">Consulter mes fiches de frais</a></li>
                        </ul>
                </ul>
            <?php
        }
    }
    // affichage des éventuelles erreurs déjà détectées
    if (nbErreurs($tabErreurs) > 0) {
        echo toStringErreurs($tabErreurs);
    }
}
?>
</div>

