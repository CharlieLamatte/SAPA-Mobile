<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use Sportsante86\Sapa\Outils\ChaineCharactere;
use Sportsante86\Sapa\Outils\EncryptionManager;

class Synthese
{
    private PDO $pdo;
    private string $errorMessage = '';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return string the error message of the last operation
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string      $id_patient
     * @param string|null $id_structure La structure de l'utilisateur connecté
     * @return array|false Returns la synthèse du patient $id_patient, false en cas d'erreur
     */
    public function read($id_patient, $id_structure)
    {
        if (empty($id_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        // recup des données patient
        $p = new Patient($this->pdo);
        $synthese = $p->readOne($id_patient);

        $id_territoire = $synthese['id_territoire'];

        // recup de la personne qui suit le patient
        $query_suivi = '
            SELECT nom_coordonnees    as nom_suivi,
                   prenom_coordonnees as prenom_suivi,
                   patients.id_user   as id_user_suivi,
                   mail_coordonnees   as mail_suivi
            FROM patients
                     JOIN users u on patients.id_user = u.id_user
                     JOIN coordonnees c on u.id_coordonnees = c.id_coordonnees
            WHERE patients.id_patient = :id_patient';
        $stmt_suivi = $this->pdo->prepare($query_suivi);
        $stmt_suivi->bindValue(':id_patient', $id_patient);
        $stmt_suivi->execute();

        $row_suivi = $stmt_suivi->fetch(PDO::FETCH_ASSOC);
        $synthese = array_merge($synthese, $row_suivi);

        $u = new User($this->pdo);
        $synthese['fonction_suivi'] = $u->getRoles($synthese['id_user_suivi']);

        // recup du coordinateur territorial PEPS
        $query_coordo = "
            SELECT nom_coordonnees    as nom_coordinateur,
                   prenom_coordonnees as prenom_coordinateur,
                   u.id_user          as id_user_coordinateur,
                   mail_coordonnees   as mail_coordinateur,
                   CASE
                       WHEN tel_fixe_coordonnees IS NOT NULL AND tel_fixe_coordonnees != '' THEN tel_fixe_coordonnees
                       WHEN tel_portable_coordonnees IS NOT NULL AND tel_portable_coordonnees != '' THEN tel_portable_coordonnees
                       ELSE ''
                       END            as telephone_coordinateur
            FROM users u
                     JOIN coordonnees c on c.id_coordonnees = u.id_coordonnees
                     JOIN a_role ar on u.id_user = ar.id_user
            WHERE ar.id_role_user = 2
              AND u.est_coordinateur_peps = 1
              AND u.id_territoire = :id_territoire";
        $stmt_coordo = $this->pdo->prepare($query_coordo);
        $stmt_coordo->bindValue(':id_territoire', $id_territoire);
        $stmt_coordo->execute();

        $row_coordo = $stmt_coordo->fetch(PDO::FETCH_ASSOC);
        $synthese = array_merge($synthese, $row_coordo);

        $synthese['fonction_coordinateur'] = $u->getRoles($synthese['id_user_coordinateur']);

        // recup de la dernière évaluation
        $query_eval = '
            SELECT evaluations.id_evaluation,
                   evaluations.date_eval,
                   type_eval,
                   c.prenom_coordonnees,
                   c.nom_coordonnees,
                   c.mail_coordonnees,
                   u.id_user as id_user_evaluateur,
                   tel_fixe_coordonnees,
                   tel_portable_coordonnees
            FROM evaluations
                     JOIN users u on evaluations.id_user = u.id_user
                     JOIN coordonnees c on c.id_coordonnees = u.id_coordonnees
                     JOIN type_eval on evaluations.id_type_eval = type_eval.id_type_eval
            WHERE evaluations.id_patient = :id_patient
            ORDER BY evaluations.id_type_eval DESC
            LIMIT 1';
        $stmt_eval = $this->pdo->prepare($query_eval);
        $stmt_eval->bindValue(':id_patient', $id_patient);
        $stmt_eval->execute();

        $row_eval = $stmt_eval->fetch(PDO::FETCH_ASSOC);

        $id_evaluation = $row_eval['id_evaluation'] ?? null;
        $synthese['id_evaluation'] = $row_eval['id_evaluation'] ?? null;
        $synthese['date_eval'] = $row_eval['date_eval'] ?? null;
        $synthese['type_eval'] = $row_eval['type_eval'] ?? null;
        $synthese['nom_evaluateur'] = $row_eval['nom_coordonnees'] ?? null;
        $synthese['prenom_evaluateur'] = $row_eval['prenom_coordonnees'] ?? null;
        $synthese['mail_evaluateur'] = $row_eval['mail_coordonnees'] ?? null;
        $synthese['telephone_evaluateur'] = !empty($row_eval['tel_fixe_coordonnees']) ? $row_eval['tel_fixe_coordonnees'] : (!empty($row_eval['tel_portable_coordonnees']) ? $row_eval['tel_portable_coordonnees'] : "");
        $synthese['id_user_evaluateur'] = $row_eval['id_user_evaluateur'] ?? null;
        $synthese['fonction_evaluateur'] = $u->getRoles($synthese['id_user_evaluateur']) ?: null;

        // recup des evals précedentes
        $synthese['evaluations_precedentes'] = [];
        if ($id_evaluation != null) {
            $query_eval_prec = "
                SELECT id_evaluation,
                       type_eval,
                       DATE_FORMAT(date_eval, '%d/%m/%Y') as date_eval
                FROM evaluations
                         JOIN type_eval te on evaluations.id_type_eval = te.id_type_eval
                WHERE id_patient = :id_patient
                  AND id_evaluation <> :id_evaluation
                ORDER BY type_eval";

            $stmt_eval_prec = $this->pdo->prepare($query_eval_prec);
            $stmt_eval_prec->bindValue(':id_patient', $id_patient);
            $stmt_eval_prec->bindValue(':id_evaluation', $id_evaluation);
            $stmt_eval_prec->execute();

            while ($row_eval_prec = $stmt_eval_prec->fetch(PDO::FETCH_ASSOC)) {
                $eval_prec_item = [
                    'id' => $row_eval_prec['id_evaluation'],
                    'nom' => $row_eval_prec['type_eval'] . ' du ' . $row_eval_prec['date_eval'],
                ];

                $synthese['evaluations_precedentes'][] = $eval_prec_item;
            }
        }

        // recup objectifs
        $query_objectifs = '
            SELECT id_obj_patient,
                   id_patient,
                   date_objectif_patient,
                   nom_objectif,
                   desc_objectif,
                   pratique,
                   termine,
                   id_user,
                   type_activite,
                   duree,
                   frequence,
                   infos_complementaires
            FROM objectif_patient
            WHERE id_patient = :id_patient';
        $stmt_objectifs = $this->pdo->prepare($query_objectifs);
        $stmt_objectifs->bindValue(':id_patient', $id_patient);
        $stmt_objectifs->execute();

        $synthese['objectifs'] = [];

        while ($row_objectifs = $stmt_objectifs->fetch(PDO::FETCH_ASSOC)) {
            // recupération des avancement de l'objectif
            $query_avanc = '
                SELECT id_avancement_obj,
                       date_avancement,
                       atteinte,
                       commentaires,
                       id_obj_patient
                FROM avancement_obj
                WHERE id_obj_patient = :id_obj_patient
                ORDER BY date_avancement DESC
                LIMIT 1';
            $stmt_avanc = $this->pdo->prepare($query_avanc);
            $stmt_avanc->bindValue(':id_obj_patient', $row_objectifs['id_obj_patient']);
            $stmt_avanc->execute();

            $row_objectifs['avancement'] = null;
            if ($stmt_avanc->rowCount() != 0) {
                $row_avanc = $stmt_avanc->fetch(PDO::FETCH_ASSOC);
                $row_objectifs['avancement'] = $row_avanc;
            }

            $synthese['objectifs'][] = $row_objectifs;
        }

        // recup de l'orientation du patient
        $query_orientation = '
            SELECT id_orientation,
                   date_orientation,
                   commentaires_general,
                   type_parcours
            FROM orientation
                     JOIN type_parcours tp on orientation.id_type_parcours = tp.id_type_parcours
            WHERE orientation.id_patient = :id_patient';
        $stmt_orientation = $this->pdo->prepare($query_orientation);
        $stmt_orientation->bindValue(':id_patient', $id_patient);
        $stmt_orientation->execute();

        if ($stmt_orientation->rowCount() == 0) {
            $synthese['orientation'] = null;
        } else {
            $row_orientation = $stmt_orientation->fetch(PDO::FETCH_ASSOC);
            $id_orientation = $row_orientation['id_orientation'];
            $synthese['orientation'] = $row_orientation;
        }

        // recup activités (créneaux auquel est inscrit le patient)
        $synthese['activites'] = [];

        if (!empty($id_orientation)) {
            // recuperation de toutes les activites du patient
            $query_liste_act = '
                SELECT id_activite_choisie,
                       statut,
                       commentaire,
                       date_demarrage,
                       id_orientation,
                       id_creneau
                FROM activite_choisie
                WHERE id_orientation = :id_orientation';
            $stmt_liste_act = $this->pdo->prepare($query_liste_act);
            $stmt_liste_act->bindValue(':id_orientation', $id_orientation);
            $stmt_liste_act->execute();
            if ($stmt_liste_act->rowCount() > 0) {
                while ($row_act = $stmt_liste_act->fetch(PDO::FETCH_ASSOC)) {
                    //recup infos sur le creneau
                    $query_creneau = '
                        SELECT creneaux.id_creneau          as id_creneau,
                               creneaux.nom_creneau         as nom_creneau,
                               creneaux.nombre_participants as nb_participant,
                               creneaux.prix_creneau        as tarif,
                               creneaux.public_vise         as public_vise,
                               creneaux.description_creneau as description_creneau,
                               creneaux.type_seance         as type_seance,
                               creneaux.pathologie_creneau  as pathologie_creneau,
                               jours.nom_jour               as jour,
                               heureCommence.heure          as heure_debut,
                               heureTermine.heure           as heure_fin,
                               structure.nom_structure      as nom_structure,
                               creneaux.id_structure,
                               type_parcours
                        FROM creneaux
                                 JOIN structure USING (id_structure)
                                 JOIN jours USING (id_jour)
                                 JOIN commence_a USING (id_creneau)
                                 JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                                 JOIN se_termine_a USING (id_creneau)
                                 JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                                 JOIN type_parcours tp on creneaux.id_type_parcours = tp.id_type_parcours
                        WHERE creneaux.id_creneau = :id_creneau';
                    $stmt_creneau = $this->pdo->prepare($query_creneau);
                    $stmt_creneau->bindValue(':id_creneau', $row_act['id_creneau']);
                    $stmt_creneau->execute();
                    $data_creneau = $stmt_creneau->fetch(PDO::FETCH_ASSOC);

                    $row_act = array_merge($row_act, $data_creneau);

                    $synthese['activites'][] = $row_act;
                }
            }
        }

        // recup du logo de la structure de l'utilisateur connecté
        $synthese['logo_fichier'] = null;
        if (!empty($id_structure)) {
            $query = '
                SELECT logo_fichier
                FROM structure
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $synthese['logo_fichier'] = $data["logo_fichier"] ?? null;
        }

        return $synthese;
    }

    /**
     * @param $id_synthese
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_synthese)
    {
        if (empty($id_synthese)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = "
            SELECT id_synthese, synthese, id_patient, id_user, date_synthese
            FROM synthese
            WHERE id_synthese = :id_synthese ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id_synthese", $id_synthese);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Fonction sauvegardant une synthèse dans la table "synthese"
     *
     * @param $synthese string Nom de la synthèse à sauvegarder
     * @param $id_patient int Id du patient
     * @param $id_user int Id de l'utilisateur sauvegardant la synthèse
     * @return string|false Returns l'id de la synthèse créée si réussi, false sinon
     */

    public function saveSynthese($synthese, $id_patient, $id_user)
    {
        if (!isset($synthese) || !isset($id_patient) || !isset($id_user)) {
            return false;
        }
        try {
            $this->pdo->beginTransaction();
            //on vérifie si la synthèse n'existe pas déjà
            $query_check = 'SELECT synthese FROM synthese WHERE synthese = :synthese';
            $stmt_check = $this->pdo->prepare($query_check);
            $stmt_check->bindValue(":synthese", $synthese);
            $stmt_check->execute();
            if ($stmt_check->rowCount() != 0) {
                throw new Exception('Error synthese existe déjà');
            }
            //insert dans la bdd
            $query_save = 'INSERT INTO synthese (synthese,id_patient,id_user,date_synthese) VALUES (:synthese, :id_patient, :id_user, :date_synthese)';
            $stmt_save = $this->pdo->prepare($query_save);
            $stmt_save->bindValue(":synthese", $synthese);
            $stmt_save->bindValue(":id_patient", $id_patient);
            $stmt_save->bindValue(":id_user", $id_user);
            $stmt_save->bindValue(":date_synthese", date("Y-m-d H:m:s"));
            if (!$stmt_save->execute()) {
                throw new Exception('Error INSERT INTO synthese');
            }
            //on return l'id synthese
            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /** Fonction récupérant toutes les synthèses enregistrées d'un bénéficiaire
     *
     * @param $id_patient int Identifiant du bénéficiaire
     * @param $id_user int Identifiant de l'utilisateur
     * @param $filter_id_user bool True = seulement celles créées par l'utilisateur connecté, false = toutes les
     *     synthèses
     * @return array|false Retourne l'ensemble des synthèses sous forme de tableau associatif si réussi (peut-être un
     *     tableau vide), false sinon
     */
    public function fetchSyntheses($id_patient, $id_user, $filter_id_user)
    {
        if (!isset($id_patient)) {
            return false;
        }
        $query_fetch = "SELECT id_synthese,
                    synthese,
                    id_patient,
                    id_user,
                    date_synthese
                    FROM synthese
                    WHERE id_patient = :id_patient";
        if ($filter_id_user) {
            $query_fetch = $query_fetch . " AND id_user = :id_user";
        }
        $query_fetch .= " ORDER BY date_synthese";
        $stmt_fetch = $this->pdo->prepare($query_fetch);
        $stmt_fetch->bindValue(":id_patient", $id_patient);
        if ($filter_id_user) {
            $stmt_fetch->bindValue(":id_user", $id_user);
        }
        if (!$stmt_fetch->execute()) {
            $this->errorMessage = "Error SELECT FROM synthese";
            return false;
        }
        return $stmt_fetch->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Fonction supprimant une synthèse de la base de données
     *
     * @param $id_synthese int identifiant de la synthèse à supprimer
     * @returns true|false true si réussi, false sinon
     */
    public function deleteSynthese($id_synthese)
    {
        if (!isset($id_synthese)) {
            return false;
        }
        $query_supp = "DELETE FROM synthese WHERE id_synthese = :id_synthese";
        $stmt_supp = $this->pdo->prepare($query_supp);
        $stmt_supp->bindValue(":id_synthese", $id_synthese);
        if (!$stmt_supp->execute()) {
            $this->errorMessage = "Error DELETE FROM synthese";
            return false;
        }
        return true;
    }
}