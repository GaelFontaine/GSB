<?php
$repInclude = '../include/';
require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecté
if (!estVisiteurConnecte()) {
    header("Location: cSeConnecter.php");
}

require($repInclude . "_entete.inc.html");
require($repInclude . "_sommaire.inc.php");
?>
<script src="../include/js/dataTable.js"></script>                                                                                                     
<div id="contenu">
    <form action="" method="post">
        <h2> Fiches de frais à valider et mises en paiement </h2>
        <table id="listeLegere" class="display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Détails fiche</th>
                        <th class="nom">Nom</th>
                        <th class="prenom">Prenom</th>
                        <th class="montantValide">Montant total de la fiche</th>
                        <th class="dateModif">Date de modification</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // demande de la requête pour obtenir la liste des éléments hors
                    // forfait du visiteur connecté pour le mois demandé
                    $idFicheValidees = $pdo->getLesFichesValidees();
                    foreach ($idFicheValidees as $lgEltValides) {
                        ?>
                        <tr id="<?php echo $id=$lgEltValides["id"]; ?>_<?php echo $lgEltValides["mois"]; ?>">
                            <td  class="btnAffiche"></td>
                            <td><?php echo $lgEltValides["nom"]; ?></td>
                            <td><?php echo $lgEltValides["prenom"]; ?></td>
                            <td><?php echo $lgEltValides["montantValide"]; ?></td>
                            <td><?php echo filtrerChainePourNavig($lgEltValides["dateModif"]); ?></td>
                            <td><input class ="btnMP" type="button" value="Mettre en paiement"></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
    </form>
</div>
