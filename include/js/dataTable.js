// Cette fonction permet de mettre sous forme HTML le contenu des child row
function format() {
    return '<span style="color:black;"><b><u>Elements forfaitisés</u></b>' +
            '<br>Forfait étape: ' +
            '<b id="ETP"></b>' +
            '<br>Frais Kilométrique: ' +
            '<b id="KM"></b>' +
            '<br>Nuitée Hôtel: ' +
            '<b id="NUI"></b>' +
            '<br>Repas restaurant : ' +
            '<b id="REP"></b>' +
            '<br><b><u>Frais hors forfait</u></b>' +
            '<p id="lsFraisHForfait"></p>' +
            '</span>';
}

// Options de la datatable
$(document).ready(function () {
    
    var table = $('#listeLegere').DataTable({
        "aLengthMenu": [[25, 50, 100], [25, 50, 100]],
        "iDisplayLength": 25,
        "columns": [
            {
                "className": 'details-control',
                "orderable": false,
                "data": null,
                "defaultContent": ''
            },
            {"data": "nom"},
            {"data": "prenom"},
            {"data": "montant"},
            {"data": "date"},
            {"data": "bouton"}
        ],
        // Applique le langage français à la datatable
        "language":
                {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
                },
        "order": [[1, 'asc']]
    });

    $('#listeLegere tbody').on('click', 'td.details-control', function () {
        var tr = $(this).parents('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            // Cette partie est déjà ouverte -- la fermer
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            parametre = $(this).parents("tr").attr('id');
            paramSplit = parametre.split('_');
            idUtilisateur = paramSplit[0];
            idMois = paramSplit[1];
            
            // Récupère les informations des frais au forfait
            $.ajax({
                url: '../vues/V_recupInfoFiche.php',
                type: 'POST',
                data: 'idUtilisateur=' + idUtilisateur + '&mois=' + idMois,
                success: function (data) { 
                    $('#ETP').empty();
                    $('#KM').empty();
                    $('#NUI').empty();
                    $('#REP').empty();
                    // Pour chaque data trouvée, envoyer le frais concerné et sa quantité
                    $.each('data', function (key) {
                        $('#' + data[key]['idFrais']).append(data[key]['quantite']);
                    });
                }
            });
            
            // Récupère les informations des frais hors forfait
            $.ajax({
                url: '../vues/V_recupInfoHorsForfait.php',
                type: 'POST',
                data: 'idUtilisateur=' + idUtilisateur + '&mois=' + idMois,
                success: function (data) { 
                    $('#lsFraisHForfait').empty();
                    // Pour chaque data trouvée, envoyer la date, le libelle et le montant du frais hors forfait
                    for(i = 0;i<data.length;i++)
                    {
                        $('#lsFraisHForfait').append(data[i]['date'] + ' ' + data[i]['libelle'] + ' ' + '<b>' + data[i]['montant'] + '€' + '</b></br>');
                    }
                }
            });
            
            //Fin AJAX

            if (table.row('.shown').length) {
                $('td.details-control', table.row('.shown').node()).click();
            }
            // Ouvre la partie demandée
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });
    
$('.btnMP').click(function(){
    parametre = $(this).parents("tr").attr('id');
            paramSplit = parametre.split('_');
            idUtilisateur = paramSplit[0];
            idMois = paramSplit[1];
    $.ajax({
        
                url: '../vues/V_majEtat.php',
                type: 'POST',
                data: 'idUtilisateur=' + idUtilisateur + '&mois=' + idMois,
                success:function(){
                    window.location.reload(false);
                }
            });
    });
});