<?php

/**
 * Classe d'accès aux données. 

 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe

 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */
class PdoGsb {

    private static $serveur = 'mysql:host=localhost';
    private static $bdd = 'dbname=gsb_frais';
    private static $user = 'root';
    private static $mdp = '';
    private static $monPdo;
    private static $monPdoGsb = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct() {
        PdoGsb::$monPdo = new PDO(PdoGsb::$serveur . ';' . PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp);
        PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
    }

    public function _destruct() {
        PdoGsb::$monPdo = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe

     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();

     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb() {
        if (PdoGsb::$monPdoGsb == null) {
            PdoGsb::$monPdoGsb = new PdoGsb();
        }
        return PdoGsb::$monPdoGsb;
    }

    /**
     * Retourne les informations d'un utilisateur

     * @param $id
     * @return le login, le nom et le prénom sous la forme d'un tableau associatif 
     */
    public function getInfosVisiteur($id) {
        $query = "select utilisateur.login as login, utilisateur.nom as nom, utilisateur.prenom as prenom, utilisateur.role as role from utilisateur 
		where utilisateur.id=:id";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':id', $id, PDO::PARAM_INT);
        $prep->execute();
        $ligne = $prep->fetch(PDO::FETCH_ASSOC);
        return $ligne;
    }

    /**
     * Retourne les informations d'un utilisateur selon s'il est visiteur ou comptable

     * @param $login 
     * @param $mdp
     * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
     */
    public function estUnVisiteurRole($login, $mdp, $role) {
        $query = "select utilisateur.id as id, utilisateur.nom as nom, utilisateur.prenom as prenom, utilisateur.login as login from utilisateur 
		where utilisateur.login=:login and utilisateur.mdp=:mdp and utilisateur.role=:role";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':login', $login, PDO::PARAM_STR);
        $prep->bindValue(':mdp', $mdp, PDO::PARAM_STR);
        $prep->bindValue(':role', $role, PDO::PARAM_INT);
        $prep->execute();
        $ligne = $prep->fetch();
        return $ligne;
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
     * concernées par les deux arguments

     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-

     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
     */
    public function getLesFraisHorsForfait($idVisiteur, $mois) {
        $query = "select * from lignefraishorsforfait where lignefraishorsforfait.idvisiteur =:idVisiteur
		and lignefraishorsforfait.mois = :mois ";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
        $lesLignes = $prep->fetchAll(PDO::FETCH_ASSOC);
        $nbLignes = count($lesLignes);
        for ($i = 0; $i < $nbLignes; $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = convertirDateAnglaisVersFrancais($date);
        }
        return $lesLignes;
    }

    /**
     * Retourne les informations des visiteurs possédant une fiche de frais à l'état "Validée"
     */
    public function getLesFichesValidees() {
        $req = "select id, mois, nom, prenom, montantValide, dateModif from fichefrais inner join utilisateur on fichefrais.idVisiteur = utilisateur.id where idEtat='VA'";
        $res = PdoGsb::$monPdo->query($req);
        $lignes = $res->fetchAll();
        return $lignes;
    }

    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @return le nombre entier de justificatifs 
     */
    public function getNbjustificatifs($idVisiteur, $mois) {
        $query = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur =:idVisiteur and fichefrais.mois = :mois";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
        $laLigne = $prep->fetch();
        return $laLigne['nb'];
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
     * concernées par les deux arguments

     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
     */
    public function getLesFraisForfait($idVisiteur, $mois) {
        $query = "select fraisforfait.id as idFrais, fraisforfait.libelle as libelle, 
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur =:idVisiteur and lignefraisforfait.mois=:mois 
		order by lignefraisforfait.idfraisforfait";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
        $lesLignes = $prep->fetchAll();
        return $lesLignes;
    }

    /**
     * Retourne tous les id de la table FraisForfait

     * @return un tableau associatif 
     */
    public function getLesIdFrais() {
        $req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
        $res = PdoGsb::$monPdo->query($req);
        $lesLignes = $res->fetchAll();
        return $lesLignes;
    }

    /**
     * Met à jour la table ligneFraisForfait

     * Met à jour la table ligneFraisForfait pour un visiteur et
     * un mois donné en enregistrant les nouveaux montants

     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
     * @return un tableau associatif 
     */
    public function majFraisForfait($idVisiteur, $mois, $lesFrais) {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $query = "update lignefraisforfait set lignefraisforfait.quantite = :qte
			where lignefraisforfait.idvisiteur = :idVisiteur and lignefraisforfait.mois = :mois
			and lignefraisforfait.idfraisforfait = :unIdFrais";
            $prep = PdoGsb::$monPdo->prepare($query);
            $prep->bindValue(':qte', $qte, PDO::PARAM_INT);
            $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
            $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
            $prep->bindValue(':unIdFrais', $unIdFrais, PDO::PARAM_STR);
            $prep->execute();
        }
    }

    /**
     * Met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné

     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     */
    public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs) {
        $query = "update fichefrais set nbjustificatifs = :nbJustificatifs 
		where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':nbJustificatifs', $nbJustificatifs, PDO::PARAM_INT);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
    }

    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @return vrai ou faux 
     */
    public function estPremierFraisMois($idVisiteur, $mois) {
        $ok = false;
        $query = "select count(*) as nblignesfrais from fichefrais 
		where fichefrais.mois = :mois and fichefrais.idvisiteur = :idVisiteur";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
        $laLigne = $prep->fetch();
        if ($laLigne['nblignesfrais'] == 0) {
            $ok = true;
        }
        return $ok;
    }

    /**
     * Retourne le dernier mois en cours d'un visiteur
     * @param $idVisiteur 
     * @return le mois sous la forme aaaamm
     */
    public function dernierMoisSaisi($idVisiteur) {
        $query = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = :idVisiteur";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->execute();
        $laLigne = $prep->fetch();
        $dernierMois = $laLigne['dernierMois'];
        return $dernierMois;
    }

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
     * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
     * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     */
    public function creeNouvellesLignesFrais($idVisiteur, $mois) {
        $dernierMois = $this->dernierMoisSaisi($idVisiteur);
        $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur, $dernierMois);
        if ($laDerniereFiche['idEtat'] == 'CR') {
            $this->majEtatFicheFrais($idVisiteur, $dernierMois, 'CL');
        }
        $query = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values(:idVisiteur,:mois,0,0,now(),'CR')";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
        $lesIdFrais = $this->getLesIdFrais();
        foreach ($lesIdFrais as $uneLigneIdFrais) {
            $unIdFrais = $uneLigneIdFrais['idfrais'];
            $query = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values(:idVisiteur,:mois,:unIdFrais,0)";
            $prep = PdoGsb::$monPdo->prepare($query);
            $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
            $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
            $prep->bindValue(':unIdFrais', $unIdFrais, PDO::PARAM_STR);
            $prep->execute();
        }
    }

    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @param $libelle : le libelle du frais
     * @param $date : la date du frais au format français jj//mm/aaaa
     * @param $montant : le montant
     */
    public function creeNouveauFraisHorsForfait($idVisiteur, $mois, $libelle, $date, $montant) {
        $dateFr = convertirDateFrancaisVersAnglais($date);
        $query = "insert into lignefraishorsforfait 
		values('',:idVisiteur,:mois,:libelle,:dateFr,:montant)";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->bindValue(':libelle', $libelle, PDO::PARAM_STR);
        $prep->bindValue(':dateFr', $dateFr, PDO::PARAM_STR);
        $prep->bindValue(':montant', $montant, PDO::PARAM_INT);
        $prep->execute();
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     * @param $idFrais 
     */
    public function supprimerFraisHorsForfait($idFrais) {
        $query = "delete from lignefraishorsforfait where lignefraishorsforfait.id =:idFrais ";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idFrais', $idFrais, PDO::PARAM_INT);
        $prep->execute();
    }

    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais
     * @param $idVisiteur 
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
     */
    public function getLesMoisDisponibles($idVisiteur) {
        $query = "select fichefrais.mois as mois from fichefrais where fichefrais.idvisiteur = :idVisiteur 
		order by fichefrais.mois desc ";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->execute();
        $lesMois = array();
        $laLigne = $prep->fetch();
        while ($laLigne != null) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois["$mois"] = array(
                "mois" => "$mois",
                "numAnnee" => "$numAnnee",
                "numMois" => "$numMois"
            );
            $laLigne = $prep->fetch();
        }
        return $lesMois;
    }

    /**
     * Retourne les id et login des utilisateurs ayant pour role '0' donc étant visiteur médicaux
     * @param null 
     * @return un tableau associatif de clé un visteur -login-
     */
    public function getLesVisiteurs() {
        $req = "select id, login from utilisateur where role = 0 order by login";
        $res = PdoGsb::$monPdo->query($req);
        $lesVisit = array();
        $lesVisit = $res->fetchAll();
        return $lesVisit;
    }

    /**
     * Retourne les nom et prenom des utilisateurs ayant pour id : idVisiteur
     * @param idVisiteur
     * @return un tableau associatif de clé un visteur nom
     */
    public function getLeVisiteur($idVisiteur) {
        $query = "select nom, prenom from utilisateur where id= :idVisiteur";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->execute();
        $leVisiteur = $prep->fetch();
        return $leVisiteur;
    }

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
     */
    public function getLesInfosFicheFrais($idVisiteur, $mois) {
        $query = "select ficheFrais.idEtat as idEtat, ficheFrais.dateModif as dateModif, ficheFrais.nbJustificatifs as nbJustificatifs, 
			ficheFrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join Etat on ficheFrais.idEtat = Etat.id 
			where fichefrais.idvisiteur =:idVisiteur and fichefrais.mois = :mois";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
        $laLigne = $prep->fetch();
        return $laLigne;
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais

     * Modifie le champ idEtat et met la date de modif à aujourd'hui
     * @param $idVisiteur 
     * @param $mois sous la forme aaaamm
     */
    public function majEtatFicheFrais($idVisiteur, $mois, $etat) {
        $query = "update ficheFrais set idEtat = :etat, dateModif = now() 
		where fichefrais.idvisiteur =:idVisiteur and fichefrais.mois = :mois";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':mois', $etat, PDO::PARAM_STR);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
    }

    /**
     * Permet de mettre à jour la base de donnée en modifiant l'état
     * de la la fiche en "Mise en paiement".
     * @param $idUtilisateur
     * @param $mois sous la forme aaaamm
     */
    public function miseEnPaiement($idUtilisateur, $mois) {
        $query = "update ficheFrais set idEtat = 'MP', dateModif = now() 
		where fichefrais.idvisiteur =:idUtilisateur and fichefrais.mois = :mois";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idUtilisateur', $idUtilisateur, PDO::PARAM_STR);
        $prep->bindValue(':mois', $mois, PDO::PARAM_STR);
        $prep->execute();
    }

    /**
     * Retourne un tableau des mois pour lequel le visiteur a une fiche de frais
     * clôturée
     * @param type $idVisiteur
     * @return type
     */
    public function getLesMoisValidation($idVisiteur) {
        $query = "select fichefrais.mois as mois from fichefrais where fichefrais.idvisiteur ='$idVisiteur' 
		and idEtat = 'CL' order by fichefrais.mois desc ";
        $prep = PdoGsb::$monPdo->prepare($query);
        $prep->bindValue(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $prep->execute();
        $lesMois = array();
        $laLigne = $prep->fetch();
        while ($laLigne != null) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois["$mois"] = array(
                "mois" => "$mois",
                "numAnnee" => "$numAnnee",
                "numMois" => "$numMois"
            );
            $laLigne = $prep->fetch();
        }
        return $lesMois;
    }

}
