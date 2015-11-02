<?php
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si visiteur non connecté
if (!estVisiteurConnecte()) {
    header("Location: cSeConnecter.php");
}
require($repInclude . "_entete.inc.html");
require($repInclude . "_sommaire.inc.php");
?>
<script src="include/js/dataTable.js"></script>                                                                                                     
<div id="contenu">
    <form action="" method="post">
        <h1> Fiches de frais à valider et mises en paiement </h1>
        <p class="titre" />
            <table id="listeLegere" class="display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th class="nom">Nom</th>
                        <th class="prenom">Prenom</th>
                        <th class="montantValide">Montant</th>
                        <th class="dateModif">Date de modification</th>
                        <th class="voirDetails"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // demande de la requête pour obtenir la liste des éléments hors
                    // forfait du visiteur connecté pour le mois demandé
                    $idFicheValidees = $pdo->getLesFichesValidees();
                    foreach ($idFicheValidees as $lgEltValides) {
                        ?>
                        <tr id="dessus">
                            <td><?php echo $lgEltValides["nom"]; ?></td>
                            <td><?php echo $lgEltValides["prenom"]; ?></td>
                            <td><?php echo $lgEltValides["montantValide"]; ?></td>
                            <td><?php echo filtrerChainePourNavig($lgEltValides["dateModif"]); ?></td>
                            <td><a href="cDetailsFiche.php" target="_blank"><img src="images/details.png"/></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
</div>