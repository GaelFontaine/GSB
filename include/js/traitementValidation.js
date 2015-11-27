// Chargement des listes déroulantes liées
$(document).ready(function () {
    var lesVisit = $('#lstVisit');
    var lesMois = $('#lstMois');
    // A la sélection d'un visiteur dans la liste lstVisit
    lesVisit.on('change', function () {
        //On récupère les mois où le visiteur selectionné possède une fiche de frais
        var val = $(this).val();
        if (val !== '') {
            lesMois.empty();
            //On charge la liste des mois
            $.ajax({
                // Appel de la vue V_recupInfoMois.php
                url: '../vues/V_recupInfoMois.php',
                type: 'post',
                data: 'idUtilisateur=' + val,
                success: function (json) {
                    $.each(json, function (index) {
                        lesMois.append('<option value="' + json[index]['mois'] + '">' + json[index]['libelle'] + " " +
                                json[index]['numAnnee'] + '</option>');
                    });
                }
            });
        }
    });
    
    function modif(){
        
    }
});