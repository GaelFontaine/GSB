<?php
/**
 * Script de contrôle et d'affichage du cas d'utilisation "Consulter une fiche de frais"
 * @package default
 * @todo  RAS
 */
$repInclude = '../include/';
require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecté
if (!estVisiteurConnecte()) {
    header("Location: cSeConnecter.php");
}
require($repInclude . "_entete.inc.html");
require($repInclude . "_sommaire.inc.php");

// acquisition des données entrées, ici le numéro de mois et l'étape du traitement
$moisSaisi = lireDonneePost("lstMois", "");
$etape = lireDonneePost("etape", "");
$visiteurChoisi = lireDonneePost("lstVisit", "");

if ($etape != "demanderConsult" && $etape != "validerConsult") {
    // si autre valeur, on considère que c'est le début du traitement
    $etape = "demanderConsult";
}
if ($etape == "validerConsult") { // l'utilisateur valide ses nouvelles données
    // vérification de l'existence de la fiche de frais pour le mois demandé
    $existeFicheFrais = $pdo->getLesInfosFicheFrais($visiteurChoisi, $moisSaisi);
    $recupNomPrenom = $pdo->getLeVisiteur($visiteurChoisi);
    // si elle n'existe pas, on la crée avec les élets frais forfaitisés à 0
    if (!$existeFicheFrais) {
        ajouterErreur($tabErreurs, "Le mois ou le visiteur demandé est invalide.");
    } else {
        // récupération des données sur la fiche de frais demandée
        $tabFicheFrais = $existeFicheFrais;
        $prenomNomVisit = $recupNomPrenom;
    }
}
?>
<script type="text/javascript" charset="utf8" src="../include/js/traitementValidation.js"></script>
<!--<script type="text/javascript" charset="utf8" src="../include/js/refuseLibelle.js"></script>-->

<form action="" method="post">
    <input type="hidden" name="etape" value="validerConsult" />
    <div id="contenu">
        <h1> Validation des frais par visiteur </h1>
        <div class="corpsForm">
            <!-- Liste déroulante affichant tout les visiteurs -->
            <label for="lstVisit">Choisir le visiteur :</label>
            <select id="lstVisit" name="lstVisit" title="Sélectionnez le visiteur souhaité">
                <option>Choisir un visiteur</option>      
                <?php
                // on propose tous les visiteurs possédant une fiche de frais
                $idVisit = $pdo->getLesVisiteurs();
                foreach ($idVisit as $lgVisit) {
                    $visit = $lgVisit["login"];
                    ?>
                    <option value="<?php echo $lgVisit['id']; ?>"><?php echo $visit; ?></option>
                    <?php
                }
                ?>    
            </select>
            <!-- Liste déroulante des mois mise à jour selon le visiteur choisis -->
            <p>
                <label for="lstMois">Mois : </label>
                <select id="lstMois" name="lstMois" title="Sélectionnez le mois ">
                    <option>...</option>
                </select>
            </p>
        </div>
        <div class="piedForm">
            <p>
                <input id="ok" type="submit" value="Valider" size="20"
                       title="Demandez à consulter cette fiche de frais" />
                <input id="annuler" type="reset" value="Effacer" size="20" />
            </p> 
        </div>
</form>

<?php
// demande et affichage des différents éléments (forfaitisés et non forfaitisés)
// de la fiche de frais demandée, uniquement si pas d'erreur détecté au contrôle
if ($etape == "validerConsult") {
    if (nbErreurs($tabErreurs) > 0) {
        echo toStringErreurs($tabErreurs);
    } else {
        ?>
        <em><b><?php echo $prenomNomVisit["prenom"] . " " . $prenomNomVisit["nom"]; ?></b></em> --
        <em><?php echo $tabFicheFrais["libEtat"]; ?> </em>
        depuis le <em><?php echo $tabFicheFrais["dateModif"]; ?></em>


        <div class="encadre">            
        </p>
        <?php
        // demande de la requête pour obtenir la liste des éléments 
        // forfaitisés du visiteur connecté pour le mois demandé
        $idJeuEltsFraisForfait = $pdo->getLesFraisForfait($visiteurChoisi, $moisSaisi);
        foreach ($idJeuEltsFraisForfait as $lgEltForfait) {
            $tabEltsFraisForfait[$lgEltForfait["libelle"]] = $lgEltForfait["quantite"];
        }
        ?>
        <table class="listeLegere">
            <p class="acacher">f</p>
            <caption>Quantités des éléments forfaitisés</caption>
            <tr>
                <?php
                // premier parcours du tableau des frais forfaitisés du visiteur connecté
                // pour afficher la ligne des libellés des frais forfaitisés
                foreach ($tabEltsFraisForfait as $unLibelle => $uneQuantite) {
                    ?>
                    <th><?php echo $unLibelle; ?></th>
                    <?php
                }
                ?>
            </tr>
            <tr>
                <?php
                // second parcours du tableau des frais forfaitisés du visiteur connecté
                // pour afficher la ligne des quantités des frais forfaitisés
                foreach ($tabEltsFraisForfait as $unLibelle => $uneQuantite) {
                    ?>
                    <td class="qteForfait"><input value="<?php echo $uneQuantite; ?>" onchange="modif()"></td>
                    <?php
                }
                ?>
            </tr>
        </table>
        <table class="listeLegere">
            <caption>Descriptif des éléments hors forfait
            </caption>
            <tr>
                <th class="date">Date</th>
                <th class="libelle">Libellé</th>
                <th class="montant">Montant</th>
                <th></th>
            </tr>
            <?php
            // demande de la requête pour obtenir la liste des fiches
            // validées selon l'utilisateur et le mois choisis dans
            // les listes déroulantes
            $idJeuEltsHorsForfait = $pdo->getLesFraisHorsForfait($visiteurChoisi, $moisSaisi);
            foreach ($idJeuEltsHorsForfait as $lgEltHorsForfait) {
                ?>
                <tr>
                    <td><?php echo $lgEltHorsForfait["date"]; ?></td>
                    <td id="libelleFiche"><?php echo filtrerChainePourNavig($lgEltHorsForfait["libelle"]); ?></td>
                    <td><input size="15" value="<?php echo $lgEltHorsForfait["montant"]; ?>"></td>
                    <td><input type="button" value="Refuser" id="btnRefuse"></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <p>Nombre de justificatifs : <?php echo $tabFicheFrais["nbJustificatifs"]; ?></p>
        </div>
        <?php
    }
}
?>    
</div>
<?php
require($repInclude . "_pied.inc.html");
?> 