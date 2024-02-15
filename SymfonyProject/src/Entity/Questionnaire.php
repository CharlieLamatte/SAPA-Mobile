<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Questionnaire
{
    private PDO $pdo;
    private string $errorMessage = '';

    /**
     * @param $pdo
     */
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
     * Creates a questionnaire instance
     *
     * required parameters :
     * [
     *     'id_patient' => string,
     *     'id_questionnaire' => string,
     *     'id_user' => string,
     *     'date' => string, // format "YYYY-MM-DD"
     *     'reponses' => array,
     * ]
     *
     * @param array $parameters
     * @return false|string the id of the questionnaire_instance or false on failure
     */
    public function create(array $parameters)
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['id_questionnaire']) ||
            empty($parameters['id_user']) ||
            empty($parameters['date']) ||
            !is_array($parameters['reponses']) || empty($parameters['reponses'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Insertion dans questionnaires
            $query = '
                INSERT INTO questionnaire_instance
                    (id_patient, id_questionnaire, id_user, date)
                VALUES (:id_patient, :id_questionnaire, :id_user, :date)';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_patient', $parameters['id_patient']);
            $stmt->bindValue(':date', $parameters['date']);
            $stmt->bindValue(':id_questionnaire', $parameters['id_questionnaire']);
            $stmt->bindValue(':id_user', $parameters['id_user']);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO questionnaire_instance');
            }
            $id_questionnaire_instance = $this->pdo->lastInsertId();

            // Insertion des reponses
            $this->insertReponses($parameters['reponses'], $id_questionnaire_instance);

            $this->pdo->commit();
            return $id_questionnaire_instance;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a questionnaire instance
     *
     * required parameters :
     * [
     *     'id_patient' => string,
     *     'id_questionnaire' => string,
     *     'id_user' => string,
     *     'id_questionnaire_instance' => string,
     *     'date' => string, // format "YYYY-MM-DD"
     *     'reponses' => array,
     * ]
     *
     * @param array $parameters
     * @return bool if the uodate was successful
     */
    public function update(array $parameters): bool
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['id_questionnaire']) ||
            empty($parameters['id_questionnaire_instance']) ||
            empty($parameters['id_user']) ||
            empty($parameters['date']) ||
            !is_array($parameters['reponses']) || empty($parameters['reponses'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Suppression des anciennes reponses
            $this->deleteReponses($parameters['id_questionnaire_instance']);

            // Insertions des nouvelles réponses
            $this->insertReponses($parameters['reponses'], $parameters['id_questionnaire_instance']);

            // update questionnaire_instance
            $query = '
                UPDATE questionnaire_instance
                SET id_patient       = :id_patient,
                    date             = :date,
                    id_user          = :id_user,
                    id_questionnaire = :id_questionnaire
                WHERE id_questionnaire_instance = :id_questionnaire_instance';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $parameters['id_patient']);
            $stmt->bindValue(':date', $parameters['date']);
            $stmt->bindValue(':id_questionnaire', $parameters['id_questionnaire']);
            $stmt->bindValue(':id_user', $parameters['id_user']);
            $stmt->bindValue(':id_questionnaire_instance', $parameters['id_questionnaire_instance']);
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE questionnaire_instance');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id_questionnaire
     * @return array|false Returns an array containing all the questions of a questionnaire, false on failure
     */
    public function read($id_questionnaire)
    {
        if (empty($id_questionnaire)) {
            return false;
        }

        ////////////////////////////////////////////////////
        // Recuperation du nom
        ////////////////////////////////////////////////////
        $query = '
            SELECT id_questionnaire,
                   nom_questionnaire
            FROM questionnaire q
            WHERE q.id_questionnaire = :id_questionnaire';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire', $id_questionnaire);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        $questionnaire = [
            'id_questionnaire' => $data['id_questionnaire'],
            'nom_questionnaire' => $data['nom_questionnaire'],
            'questions' => []
        ];

        ////////////////////////////////////////////////////
        // Recuperation des  questions
        ////////////////////////////////////////////////////
        $query_questions = '
            SELECT q.id_question,
                   q.que_id_question,
                   rp.id_type_reponse,
                   rp.valeur_min,
                   rp.valeur_max,
                   tr.nom_type_reponse,
                   q.enonce,
                   q.ordre
            FROM question q
                     JOIN reponse_possible rp on rp.id_reponse_possible = q.id_reponse_possible
                     JOIN type_reponse tr on tr.id_type_reponse = rp.id_type_reponse
            WHERE q.id_questionnaire = :id_questionnaire
            ORDER BY ordre';
        $stmt_questions = $this->pdo->prepare($query_questions);
        $stmt_questions->bindValue(':id_questionnaire', $id_questionnaire);
        $stmt_questions->execute();

        while ($row = $stmt_questions->fetch(PDO::FETCH_ASSOC)) {
            $question_item = [
                'id_question' => $row['id_question'],
                'id_type_reponse' => $row['id_type_reponse'],
                'valeur_min' => $row['valeur_min'],
                'valeur_max' => $row['valeur_max'],
                'nom_type_reponse' => $row['nom_type_reponse'],
                'enonce' => $row['enonce'],
                'que_id_question' => $row['que_id_question'],
                'ordre' => $row['ordre'],
                'sous_questions' => null,
                'qcu' => null,
                'qcm' => null,
            ];

            if ($row['nom_type_reponse'] == 'qcu') {
                $query_qcu = '
                    SELECT qcu.valeur,
                           qcu.enonce
                    FROM qcu
                             JOIN reponse_possible rp on qcu.id_reponse_possible = rp.id_reponse_possible
                             JOIN question q on rp.id_reponse_possible = q.id_reponse_possible
                    WHERE q.id_question = :id_question
                    ORDER BY qcu.valeur';

                $stmt_qcu = $this->pdo->prepare($query_qcu);
                $stmt_qcu->bindValue(':id_question', $row['id_question']);
                $stmt_qcu->execute();

                $question_item['qcu'] = [];
                while ($row_qcu = $stmt_qcu->fetch(PDO::FETCH_ASSOC)) {
                    $qcu_item = [
                        'valeur' => $row_qcu['valeur'],
                        'enonce' => $row_qcu['enonce']
                    ];

                    $question_item['qcu'][] = $qcu_item;
                }
            } elseif ($row['nom_type_reponse'] == 'qcm') {
                $query_qcu = '
                    SELECT qcu.valeur,
                           qcu.enonce
                    FROM qcu
                             JOIN reponse_possible rp on qcu.id_reponse_possible = rp.id_reponse_possible
                             JOIN question q on rp.id_reponse_possible = q.id_reponse_possible
                    WHERE q.id_question = :id_question
                    ORDER BY qcu.valeur';

                $stmt_qcu = $this->pdo->prepare($query_qcu);
                $stmt_qcu->bindValue(':id_question', $row['id_question']);
                $stmt_qcu->execute();

                if ($stmt_qcu->rowCount() > 0) {
                    $question_item['qcm'] = [];
                    while ($row_qcu = $stmt_qcu->fetch(PDO::FETCH_ASSOC)) {
                        $qcu_item = [
                            'valeur' => $row_qcu['valeur'],
                            'enonce' => $row_qcu['enonce']
                        ];

                        $question_item['qcm'][] = $qcu_item;
                    }
                }
            }

            if ($row['que_id_question'] == null) {
                $questionnaire['questions'][] = $question_item;
            } else {
                $this->insert_sous_question($questionnaire['questions'], $question_item, $row['que_id_question']);
            }
        }

        return $questionnaire;
    }

    /**
     * @param $id_questionnaire_instance
     * @return array|false Returns an array containing all the answers of a questionnaire_instance, false on failure
     */
    public function readReponses($id_questionnaire_instance)
    {
        if (empty($id_questionnaire_instance)) {
            return false;
        }

        ////////////////////////////////////////////////////
        // Recuperation des infos du questionnaire d'instance
        ////////////////////////////////////////////////////
        $query = "
            SELECT id_questionnaire_instance,
                   questionnaire_instance.id_patient,
                   DATE_FORMAT(date, '%d/%m/%Y') as date,
                   nom_questionnaire,
                   q.id_questionnaire,
                   nom_coordonnees,
                   prenom_coordonnees
            FROM questionnaire_instance
                     JOIN questionnaire q on questionnaire_instance.id_questionnaire = q.id_questionnaire
                     JOIN coordonnees c on questionnaire_instance.id_user = c.id_user
            WHERE questionnaire_instance.id_questionnaire_instance = :id_questionnaire_instance";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        $questionnaire = [
            'id_questionnaire_instance' => $data['id_questionnaire_instance'],
            'id_patient' => $data['id_patient'],
            'date' => $data['date'],
            'id_questionnaire' => $data['id_questionnaire'],
            'nom_questionnaire' => $data['nom_questionnaire'],
            'nom' => $data['nom_coordonnees'],
            'prenom' => $data['prenom_coordonnees'],
        ];

        $questionnaire['reponses'] = [];

        ////////////////////////////////////////////////////
        // Recuperation des reponses du questionnaire
        ////////////////////////////////////////////////////
        $query_reponses = '
            SELECT ri.valeur_bool,
                   ri.valeur_int,
                   ri.valeur_string,
                   ri.id_reponse_instance,
                   tr.nom_type_reponse,
                   tr.id_type_reponse,
                   q.ordre,
                   q.id_question
            FROM reponse_questionnaire
                     JOIN question q on reponse_questionnaire.id_question = q.id_question
                     JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                     JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                     JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
            WHERE reponse_questionnaire.id_questionnaire_instance = :id_questionnaire_instance
            ORDER BY q.ordre';
        $stmt_reponses = $this->pdo->prepare($query_reponses);
        $stmt_reponses->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);

        $stmt_reponses->execute();
        while ($row_reponses = $stmt_reponses->fetch(PDO::FETCH_ASSOC)) {
            $liste = null;
            $valeur = null;
            if ($row_reponses['id_type_reponse'] == '1') {
                $valeur = $row_reponses['valeur_int'];
            } elseif ($row_reponses['id_type_reponse'] == '2') {
                $valeur = $row_reponses['valeur_bool'];
            } elseif ($row_reponses['id_type_reponse'] == '3') {
                $valeur = $row_reponses['valeur_string'];
            } elseif ($row_reponses['id_type_reponse'] == '5') {
                $valeur = $row_reponses['valeur_int']; // les qcu sont stocké sous forme d'int
            } elseif ($row_reponses['nom_type_reponse'] == 'qcm_liste') {
                $id_qcm = $row_reponses['valeur_int'];
                $valeur = $row_reponses['valeur_bool']; // les listes

                $liste = [];
                $query_liste = '
                    SELECT valeur_int, nom_structure
                    FROM liste_reponse_instance
                             JOIN structure on valeur_int = structure.id_structure
                    WHERE id_reponse_instance = :id_reponse_instance';
                $stmt_liste = $this->pdo->prepare($query_liste);

                $stmt_liste->bindValue(':id_reponse_instance', $row_reponses['id_reponse_instance']);

                $stmt_liste->execute();
                if ($stmt_liste->rowCount() > 0) {
                    while ($row_liste = $stmt_liste->fetch(PDO::FETCH_ASSOC)) {
                        $liste[] = [
                            'id_structure' => $row_liste['valeur_int'],
                            'nom_structure' => $row_liste['nom_structure']
                        ];
                    }
                }
            } elseif ($row_reponses['id_type_reponse'] == '6') {
                $id_qcm = $row_reponses['valeur_int'];
                $valeur = $row_reponses['valeur_bool']; // les qcm
            }

            $reponse_item = [
                'reponse' => $valeur,
                'id_qcm' => $id_qcm ?? null,
                'id_question' => $row_reponses['id_question'],
                'id_type_reponse' => $row_reponses['id_type_reponse'],
                'nom_type_reponse' => $row_reponses['nom_type_reponse'],
                'ordre' => $row_reponses['ordre'],
                'liste' => $liste,
                'id_reponse_instance' => $row_reponses['id_reponse_instance']
            ];

            $questionnaire['reponses'][] = $reponse_item;
        }

        return $questionnaire;
    }

    /**
     * Return all id_questionnaire_instance for the given $id_patient and $id_questionnaire
     *
     * @param $id_patient
     * @param $id_questionnaire
     * @return array|false Returns an array containing id_questionnaire_instance, false on failure
     */
    public function getQuestionnairesInstancesPatient($id_patient, $id_questionnaire)
    {
        if (empty($id_patient) || empty($id_questionnaire)) {
            return false;
        }

        $query = '
            SELECT id_questionnaire_instance
            FROM questionnaire_instance
            WHERE id_patient = :id_patient
              AND id_questionnaire = :id_questionnaire
            ORDER BY id_questionnaire_instance DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->bindValue(':id_questionnaire', $id_questionnaire);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * @param $id_questionnaire_instance
     * @return bool if the deletion was successful
     */
    public function delete($id_questionnaire_instance): bool
    {
        if (empty($id_questionnaire_instance)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $this->deleteReponses($id_questionnaire_instance);

            $query_delete = '
                DELETE
                FROM questionnaire_instance
                WHERE id_questionnaire_instance = :id_questionnaire_instance';
            $stmt_delete = $this->pdo->prepare($query_delete);
            $stmt_delete->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
            if (!$stmt_delete->execute()) {
                throw new Exception('Error DELETE FROM questionnaire_instance');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id_questionnaire_instance
     * @return array les scores opaq dans les clés "niveau_activite_physique_minutes", "niveau_activite_physique_mets",
     *     "niveau_sendentarite" et "niveau_sendentarite_semaine"
     */
    public function getScoreOpaq($id_questionnaire_instance): array
    {
        if (empty($id_questionnaire_instance)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            $scores['niveau_activite_physique_minutes'] = null;
            $scores['niveau_activite_physique_mets'] = null;
            $scores['niveau_sendentarite'] = null;
            $scores['niveau_sendentarite_semaine'] = null;

            return $scores;
        }

        ////////////////////////////////////////////////////
        // Recuperation des réponses du questionnaire
        ////////////////////////////////////////////////////
        $query = '
            SELECT ri.valeur_int,
                   ri.valeur_bool,
                   tr.nom_type_reponse,
                   tr.id_type_reponse,
                   q.ordre,
                   q.id_question
            FROM reponse_questionnaire
                     JOIN question q on reponse_questionnaire.id_question = q.id_question
                     JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                     JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                     JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
            WHERE id_questionnaire_instance = :id_questionnaire_instance';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->errorMessage = "Le paramètre id_questionnaire_instance est invalide";
            $scores['niveau_activite_physique_minutes'] = null;
            $scores['niveau_activite_physique_mets'] = null;
            $scores['niveau_sendentarite'] = null;
            $scores['niveau_sendentarite_semaine'] = null;

            return $scores;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Bloc A
            if ($row['id_question'] == '2') {
                $a1 = $row['valeur_bool'];
                $a4 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '7') {
                $a2 = $row['valeur_int'];
            } elseif ($row['id_question'] == '10') {
                $a3 = $row['valeur_int'];
            } elseif ($row['id_question'] == '6') {
                $a5 = $row['valeur_int'];
            } elseif ($row['id_question'] == '9') {
                $a6 = $row['valeur_int'];
            } elseif ($row['id_question'] == '11') {
                $a7 = $row['valeur_int'];
            } // Bloc B
            elseif ($row['id_question'] == '13') {
                $b8 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '17') {
                $b9 = $row['valeur_int'];
            } elseif ($row['id_question'] == '21') {
                $b10 = $row['valeur_int'];
            } elseif ($row['id_question'] == '14') {
                $b11 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '18') {
                $b12 = $row['valeur_int'];
            } elseif ($row['id_question'] == '22') {
                $b13 = $row['valeur_int'];
            } elseif ($row['id_question'] == '15') {
                $b14 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '19') {
                $b15 = $row['valeur_int'];
            } elseif ($row['id_question'] == '23') {
                $b16 = $row['valeur_int'];
            } elseif ($row['id_question'] == '24') {
                $b17 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '25') {
                $b18 = $row['valeur_int'];
            } elseif ($row['id_question'] == '26') {
                $b19 = $row['valeur_int'];
            } // Bloc C
            elseif ($row['id_question'] == '27') {
                $c20 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '28') {
                $c21 = $row['valeur_int'];
            } elseif ($row['id_question'] == '29') {
                $c22 = $row['valeur_int'];
            } elseif ($row['id_question'] == '40') {
                $c23 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '43') {
                $c24 = $row['valeur_int'];
            } elseif ($row['id_question'] == '46') {
                $c25 = $row['valeur_int'];
            } elseif ($row['id_question'] == '41') {
                $c26 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '44') {
                $c27 = $row['valeur_int'];
            } elseif ($row['id_question'] == '47') {
                $c28 = $row['valeur_int'];
            } elseif ($row['id_question'] == '32') {
                $c29 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '35') {
                $c30 = $row['valeur_int'];
            } elseif ($row['id_question'] == '38') {
                $c31 = $row['valeur_int'];
            } elseif ($row['id_question'] == '31') {
                $c32 = $row['valeur_bool'];
            } elseif ($row['id_question'] == '34') {
                $c33 = $row['valeur_int'];
            } elseif ($row['id_question'] == '37') {
                $c34 = $row['valeur_int'];
            }
        }

        ////////////////////////////////////////////////////
        // Calcul du niveau d'activité physique (en minutes et en mets)
        ////////////////////////////////////////////////////

        // verification que toutes les réponses nécessaires au calcul du MVPA et du APtot
        $rep_act_phys = [
            $a2 ?? null,
            $a3 ?? null,
            $a5 ?? null,
            $a6 ?? null,
            $b9 ?? null,
            $b10 ?? null,
            $b12 ?? null,
            $b13 ?? null,
            $c30 ?? null,
            $c31 ?? null,
            $c33 ?? null,
            $c34 ?? null
        ];
        $all_rep_act_phys_renseignes = true;

        foreach ($rep_act_phys as $val) {
            if (is_null($val)) {
                $all_rep_act_phys_renseignes = false;
                break;
            }
        }

        $mvpa = null;
        $ap_tot = null;
        if ($all_rep_act_phys_renseignes) {
            // VPA = (A2 x A3) + (D30 x D31)
            $vpa = ($a2 * $a3) + ($c30 * $c31);

            // MPA = (A5 x A6) + (B9 x B10) + (B12 x B13) + (C33 x C34)
            $mpa = ($a5 * $a6) + ($b9 * $b10) + ($b12 * $b13) + ($c33 * $c34);

            // MVPA = MPA + VPA
            $mvpa = round($mpa + $vpa, 1);

            // MPAMET = 4 [(A5 x A6) + (C33 x C34)] + 3,3(B9 x B10) + 6(B12 x B13)
            $mpa_met = 4 * (($a5 * $a6) + ($c33 * $c34)) + 3.3 * ($b9 * $b10) + 6 * ($b12 * $b13);

            // VPAMET = 8 [(A2 x A3) + (D30 x D31)]
            $vpa_met = 8 * (($a2 * $a3) + ($c30 * $c31));

            // APtot = MPAMET + VPAMET
            $ap_tot = round($mpa_met + $vpa_met, 1);
        }

        $scores['niveau_activite_physique_minutes'] = $mvpa;
        $scores['niveau_activite_physique_mets'] = $ap_tot;

        ////////////////////////////////////////////////////
        // Calcul du niveau de sédentarité en min/j
        ////////////////////////////////////////////////////

        // verification que toutes les réponses nécessaires au calcul du SB
        $rep_sedentarite = [
            $a7 ?? null,
            $b18 ?? null,
            $b19 ?? null,
            $c24 ?? null,
            $c25 ?? null,
            $c27 ?? null,
            $c28 ?? null
        ];
        $all_rep_sedentarite_renseignes = true;

        foreach ($rep_sedentarite as $val) {
            if (is_null($val)) {
                $all_rep_sedentarite_renseignes = false;
                break;
            }
        }

        $sb = null;
        if ($all_rep_sedentarite_renseignes) {
            // SB = A7 + [(B18 x B19) + (C24 x C25) + (C27 x C28)] / 7
            $sb = $a7 + (($b18 * $b19) + ($c24 * $c25) + ($c27 * $c28)) / 7;
            $sb = round($sb ?? null, 1);
        }

        $scores['niveau_sendentarite'] = $sb;

        ////////////////////////////////////////////////////
        // Calcul du niveau de sédentarité en min/semaine
        ////////////////////////////////////////////////////

        // verification que toutes les réponses nécessaires au calcul du SB
        $rep_sedentarite_sem = [
            $a7 ?? null,
            $b18 ?? null,
            $b19 ?? null,
            $c24 ?? null,
            $c25 ?? null,
            $c27 ?? null,
            $c28 ?? null
        ];
        $all_rep_sedentarite_sem_renseignes = true;

        foreach ($rep_sedentarite_sem as $val) {
            if (is_null($val)) {
                $all_rep_sedentarite_sem_renseignes = false;
                break;
            }
        }

        $sb = null;
        if ($all_rep_sedentarite_sem_renseignes) {
            // SB = A7 + [(B18 x B19) + (C24 x C25) + (C27 x C28)] / 7
            $sb = ($a7 * 7) + (($b18 * $b19) + ($c24 * $c25) + ($c27 * $c28));
            $sb = round($sb, 1);
        }

        $scores['niveau_sendentarite_semaine'] = $sb;

        return $scores;
    }

    /**
     * @param $id_questionnaire_instance
     * @return array le score garnier dans la clé 'perception_sante'
     */
    public function getScoreGarnier($id_questionnaire_instance): array
    {
        if (empty($id_questionnaire_instance)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            $scores['perception_sante'] = null;

            return $scores;
        }

        ////////////////////////////////////////////////////
        // Recuperation des réponses du questionnaire
        ////////////////////////////////////////////////////
        $query = '
            SELECT ri.valeur_int,
                   ri.valeur_bool,
                   tr.nom_type_reponse,
                   tr.id_type_reponse,
                   q.ordre,
                   q.id_question
            FROM reponse_questionnaire
                     JOIN question q on reponse_questionnaire.id_question = q.id_question
                     JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                     JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                     JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
            WHERE id_questionnaire_instance = :id_questionnaire_instance';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->errorMessage = "Le paramètre id_questionnaire_instance est invalide";
            $scores['perception_sante'] = null;

            return $scores;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Bloc A
            if ($row['id_question'] == '48') {
                $a1 = $row['valeur_int'];
            } elseif ($row['id_question'] == '49') {
                $a2 = $row['valeur_int'];
            } elseif ($row['id_question'] == '50') {
                $a3 = $row['valeur_int'];
            } elseif ($row['id_question'] == '51') {
                $a4 = $row['valeur_int'];
            } elseif ($row['id_question'] == '52') {
                $a5 = $row['valeur_int'];
            } elseif ($row['id_question'] == '53') {
                $a6 = $row['valeur_int'];
            } elseif ($row['id_question'] == '54') {
                $a7 = $row['valeur_int'];
            } elseif ($row['id_question'] == '55') {
                $a8 = $row['valeur_int'];
            }
        }

        ////////////////////////////////////////////////////
        // Calcul du score Perception de la santé
        ////////////////////////////////////////////////////

        // verification que toutes les réponses nécessaires au calcul de la perception de la santé sont renseignées
        $rep_questionnaire2 = [
            $a1 ?? null,
            $a2 ?? null,
            $a3 ?? null,
            $a4 ?? null,
            $a5 ?? null,
            $a6 ?? null,
            $a7 ?? null,
            $a8 ?? null
        ];
        $all_rep_perception_sante_renseignes = true;

        foreach ($rep_questionnaire2 as $val) {
            if (is_null($val)) {
                $all_rep_perception_sante_renseignes = false;
                break;
            }
        }

        $perception_sante = null;
        if ($all_rep_perception_sante_renseignes) {
            $perception_sante = array_sum($rep_questionnaire2) / count($rep_questionnaire2);
            $perception_sante = round($perception_sante, 1);
        }

        $scores['perception_sante'] = $perception_sante;

        return $scores;
    }

    /**
     * @param $id_questionnaire_instance
     * @return array le scores épices dans la clé "epices"
     */
    public function getScoreEpices($id_questionnaire_instance): array
    {
        if (empty($id_questionnaire_instance)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            $scores['epices'] = null;

            return $scores;
        }

        ////////////////////////////////////////////////////
        // Recuperation des réponses du questionnaire
        ////////////////////////////////////////////////////
        $query = '
            SELECT ri.valeur_bool,
                   tr.nom_type_reponse,
                   tr.id_type_reponse,
                   q.ordre,
                   q.id_question
            FROM reponse_questionnaire
                     JOIN question q on reponse_questionnaire.id_question = q.id_question
                     JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                     JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                     JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
            WHERE id_questionnaire_instance = :id_questionnaire_instance';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->errorMessage = "Le paramètre id_questionnaire_instance est invalide";
            $scores['epices'] = null;

            return $scores;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['ordre'] == '1' && $row['valeur_bool'] == '1') {
                $q1 = 10.06;
            } elseif ($row['ordre'] == '2' && $row['valeur_bool'] == '1') {
                $q2 = -11.83;
            } elseif ($row['ordre'] == '3' && $row['valeur_bool'] == '1') {
                $q3 = -8.28;
            } elseif ($row['ordre'] == '4' && $row['valeur_bool'] == '1') {
                $q4 = -8.28;
            } elseif ($row['ordre'] == '5' && $row['valeur_bool'] == '1') {
                $q5 = 14.80;
            } elseif ($row['ordre'] == '6' && $row['valeur_bool'] == '1') {
                $q6 = -6.51;
            } elseif ($row['ordre'] == '7' && $row['valeur_bool'] == '1') {
                $q7 = -7.10;
            } elseif ($row['ordre'] == '8' && $row['valeur_bool'] == '1') {
                $q8 = -7.10;
            } elseif ($row['ordre'] == '9' && $row['valeur_bool'] == '1') {
                $q9 = -9.47;
            } elseif ($row['ordre'] == '10' && $row['valeur_bool'] == '1') {
                $q10 = -9.47;
            } elseif ($row['ordre'] == '11' && $row['valeur_bool'] == '1') {
                $q11 = -7.10;
            }
        }

        ////////////////////////////////////////////////////
        // Calcul du score EPÏCES
        ////////////////////////////////////////////////////
        $reps = [
            $q1 ?? null,
            $q2 ?? null,
            $q3 ?? null,
            $q4 ?? null,
            $q5 ?? null,
            $q6 ?? null,
            $q7 ?? null,
            $q8 ?? null,
            $q9 ?? null,
            $q10 ?? null,
            $q11 ?? null
        ];

        $sum = 75.14;
        foreach ($reps as $val) {
            if (!is_null($val)) {
                $sum += $val;
            }
        }

        $scores['epices'] = round($sum, 2);

        return $scores;
    }

    /**
     * @param $id_questionnaire_instance
     * @return array le scores proshenska dans la clé "proshenska"
     */
    public function getScoreProcheska($id_questionnaire_instance): array
    {
        if (empty($id_questionnaire_instance)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            $scores['proshenska'] = null;

            return $scores;
        }

        ////////////////////////////////////////////////////
        // Recuperation des réponses du questionnaire
        ////////////////////////////////////////////////////
        $query = '
            SELECT ri.valeur_int,
                   tr.nom_type_reponse,
                   tr.id_type_reponse,
                   q.ordre,
                   q.id_question
            FROM reponse_questionnaire
                     JOIN question q on reponse_questionnaire.id_question = q.id_question
                     JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                     JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                     JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
            WHERE id_questionnaire_instance = :id_questionnaire_instance';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $this->errorMessage = "Le paramètre id_questionnaire_instance est invalide";
            $scores['proshenska'] = null;

            return $scores;
        }

        ////////////////////////////////////////////////////
        // Calcul du score
        ////////////////////////////////////////////////////
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $scores['proshenska'] = $row['valeur_int'];

        return $scores;
    }

    /**
     * Insertion des réponses d'un questionnaire_instance
     *
     * @param array $reponses
     * @param       $id_questionnaire_instance
     * @return void
     * @throws Exception
     */
    private function insertReponses($reponses, $id_questionnaire_instance)
    {
        foreach ($reponses as $value) {
            ////////////////////////////////////////////////////
            // Insertion dans reponse_instance
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO reponse_instance
                    (valeur_string, valeur_int, valeur_bool)
                VALUES (:valeur_string, :valeur_int, :valeur_bool)';
            $stmt = $this->pdo->prepare($query);

            $type_qcm_normal = $value['nom_type_reponse'] == 'qcm';

            if ($value['nom_type_reponse'] == 'bool' || $type_qcm_normal) {
                $stmt->bindValue(':valeur_bool', $value['reponse']);
            } else {
                $stmt->bindValue(':valeur_bool', null, PDO::PARAM_NULL);
            }
            if ($value['nom_type_reponse'] == 'string') {
                $stmt->bindValue(':valeur_string', $value['reponse']);
            } else {
                $stmt->bindValue(':valeur_string', null, PDO::PARAM_NULL);
            }
            if ($value['nom_type_reponse'] == 'int' || $type_qcm_normal) {
                if ($type_qcm_normal) {
                    $stmt->bindValue(':valeur_int', $value['id_qcm']);
                } else {
                    $stmt->bindValue(':valeur_int', $value['reponse']);
                }
            } else {
                $stmt->bindValue(':valeur_int', null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO reponse_instance');
            }
            $id_reponse_instance = $this->pdo->lastInsertId();

            if ($value['nom_type_reponse'] == 'qcm_liste') {
                ////////////////////////////////////////////////////
                // Insertion dans liste_reponse_instance
                ////////////////////////////////////////////////////

                if (is_array($value['reponses'])) {
                    foreach ($value['reponses'] as $id) {
                        $query = '
                            INSERT INTO liste_reponse_instance
                                (id_reponse_instance, valeur_int)
                            VALUES (:id_reponse_instance, :valeur_int)';
                        $stmt = $this->pdo->prepare($query);

                        $stmt->bindValue(':id_reponse_instance', $id_reponse_instance);
                        $stmt->bindValue(':valeur_int', $id);

                        if (!$stmt->execute()) {
                            throw new Exception('Error INSERT INTO liste_reponse_instance');
                        }
                    }
                }
            }

            ////////////////////////////////////////////////////
            // Insertion dans reponse_questionnnaire
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO reponse_questionnaire
                    (id_questionnaire_instance, id_reponse_instance, id_question)
                VALUES (:id_questionnaire_instance, :id_reponse_instance, :id_question)';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
            $stmt->bindValue(':id_reponse_instance', $id_reponse_instance);
            $stmt->bindValue(':id_question', $value['id_question']);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO reponse_questionnnaire');
            }
        }
    }

    /**
     * Suppression des réponses d'un questionnaire_instance
     *
     * @param $id_questionnaire_instance
     * @return void
     * @throws Exception
     */
    private function deleteReponses($id_questionnaire_instance)
    {
        // Recuperation des reponses_instance
        $query = '
            SELECT reponse_instance.id_reponse_instance
            FROM reponse_instance
                     JOIN reponse_questionnaire rq on reponse_instance.id_reponse_instance = rq.id_reponse_instance
            WHERE id_questionnaire_instance = :id_questionnaire_instance';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_questionnaire_instance', $id_questionnaire_instance);
        if (!$stmt->execute()) {
            throw new Exception('Error SELECT id_questionnaire_instance');
        }

        if ($stmt->rowCount() == 0) {
            throw new Exception(
                'No id_reponse_instance for id_questionnaire_instance=' . $id_questionnaire_instance
            );
        }

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($ids as $id_reponse_instance) {
            // Suppression des liste_reponse_instance
            $query_delete = '
                DELETE
                FROM liste_reponse_instance
                WHERE id_reponse_instance = :id_reponse_instance';
            $stmt_delete = $this->pdo->prepare($query_delete);
            $stmt_delete->bindValue(':id_reponse_instance', $id_reponse_instance);
            if (!$stmt_delete->execute()) {
                throw new Exception('Error DELETE FROM liste_reponse_instance');
            }

            // Suppression des reponse_questionnaire
            $query_delete = '
                DELETE
                FROM reponse_questionnaire
                WHERE id_reponse_instance = :id_reponse_instance';
            $stmt_delete = $this->pdo->prepare($query_delete);
            $stmt_delete->bindValue(':id_reponse_instance', $id_reponse_instance);
            if (!$stmt_delete->execute()) {
                throw new Exception('Error DELETE FROM reponse_questionnaire');
            }

            // Suppression des reponse_instance
            $query_delete = '
                DELETE
                FROM reponse_instance
                WHERE id_reponse_instance = :id_reponse_instance';
            $stmt_delete = $this->pdo->prepare($query_delete);
            $stmt_delete->bindValue(':id_reponse_instance', $id_reponse_instance);
            if (!$stmt_delete->execute()) {
                throw new Exception('Error DELETE FROM reponse_instance');
            }
        }
    }

    private function insert_sous_question(&$array, $item, $que_id_question)
    {
        foreach ($array as &$value) {
            if ($value['id_question'] == $que_id_question) {
                if (!is_array($value['sous_questions'])) {
                    $value['sous_questions'] = [];
                }
                array_push($value['sous_questions'], $item);

                return true;
            }
        }

        return false;
    }

    /**
     * Delete all questionnaire instances of a patient
     *
     * @param $id_patient
     * @return bool if the deletion was successful
     */
    public function deleteAllQuestionnairePatient($id_patient)
    {
        if (empty($id_patient)) {
            $this->errorMessage = 'Error: required parameter id_patient missing';
            return false;
        }

        $query = '
            SELECT id_questionnaire_instance
            FROM questionnaire_instance
            WHERE id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        if (!$stmt->execute()) {
            $this->errorMessage = 'Error: required parameter id_patient missing';
            return false;
        }

        $questionnaire_instance_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?? [];
        $all_ok = true;
        foreach ($questionnaire_instance_ids as $id_questionnaire_instance) {
            $all_ok = $all_ok && $this->delete($id_questionnaire_instance);
        }

        return $all_ok;
    }
}