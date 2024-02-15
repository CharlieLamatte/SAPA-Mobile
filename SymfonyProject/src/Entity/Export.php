<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

use Sportsante86\Sapa\Outils\EncryptionManager;
use Sportsante86\Sapa\Outils\Permissions;

use function Sportsante86\Sapa\Outils\age;
use function Sportsante86\Sapa\Outils\{duree_semaines, duree_minutes};

class Export
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
     * Return un array qui contient les données ONAPS de santé, formaté pour l'export csv (le premier élément est un array qui contient les headers)
     *
     * @param $session array la session de l'utilisateur
     * @param $year int l'année d'arrivée des patients
     * @return array Return un array qui contient les données ONAPS de santé
     */
    public function readOnapsData($session, $year = null)
    {
        if (empty($session['role_user_ids']) ||
            !isset($session['est_coordinateur_peps']) ||
            empty($session['id_structure']) ||
            empty($session['id_territoire']) ||
            empty($session['id_statut_structure'])) {
            return [];
        }

        try {
            $permissions = new Permissions($session);
        } catch (Exception $e) {
            return [];
        }

        $query = '
            SELECT patients.id_patient,
                   sexe as sexe_patient,
                   date_naissance,
                   est_pris_en_charge_financierement,
                   hauteur_prise_en_charge_financierement,
                   est_dans_qpv,
                   est_dans_zrr,
                   id_evaluation,
                   DATE_FORMAT(date_eval, \'%d/%m/%Y\') as date_eval,
                   date_eval as date_not_formated,
                   id_type_eval,
                   id_type_eval,
                   code_onaps,
                   consentement
            FROM patients
                     JOIN antenne a on patients.id_antenne = a.id_antenne
                     JOIN structure s on a.id_structure = s.id_structure
                     JOIN evaluations e on patients.id_patient = e.id_patient
            WHERE 1 = 1 ';

        if ($year != null) {
            $query .= ' AND YEAR(patients.date_admission) = :year ';
        }

        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $query .= ' AND s.id_structure = :id_structure ';
        } elseif ($permissions->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            $query .= ' AND patients.id_territoire = :id_territoire ';
        } elseif ($permissions->hasRole(Permissions::EVALUATEUR)) {
            $query .= ' AND patients.id_user = :id_user ';
        }

        $stmt = $this->pdo->prepare($query);
        if ($year != null) {
            $stmt->bindValue(':year', $year);
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
        } elseif ($permissions->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            $stmt->bindValue(':id_territoire', $session['id_territoire']);
        } elseif ($permissions->hasRole(Permissions::EVALUATEUR)) {
            $stmt->bindValue(':id_user', $session['id_user']);
        }
        $stmt->execute();

        $patients = [];
        $patient_header = [
            "id_beneficiaire",
            "date",
            "rep_mesure",
            'age',
            'sexe',
            'prescription',
            'raison', // en attente
            'raison_detail', // en attente
            'ald1',
            'ald1_detail',
            'ald2',
            'ald2_detail',
            'ald3',
            'ald3_detail',
            'ald4',
            'ald4_detail',
            'poids',
            'taille',
            'perimetre',
            "qpv",
            "zrr",
            "heures",
            "mode",
            "tm6",
            "tm6_spo2_pre",
            "tm6_spo2_post0min",
            "tm6_spo2_post1min",
            "tm6_spo2_post2min",
            "tm6_fc_pre",
            "tm6_fc_post0min",
            "tm6_fc_post1min",
            "tm6_fc_post2min",
            "tm6_rpe",
            "assis_debout",
            "tupandgo",
            "handgrip_md",
            "handgrip_mnd",
            "equilibre_pd",
            "equilibre_pnd",
            "flexion_tronc",
            "q0",
            "q1_fort",
            "q1_modere",
            "q2_fort",
            "q2_modere",
            "q3_fort",
            "q3_modere",
            "q4",
            "q5_pied",
            "q5_velo",
            "q5_autre",
            "q6_pied",
            "q6_velo",
            "q6_autre",
            "q7_pied",
            "q7_velo",
            "q7_autre",
            "q8",
            "q9",
            "q10",
            "q11",
            "q12",
            "q13",
            "q14_fort",
            "q14_modere",
            "q15_fort",
            "q15_modere",
            "q16_fort",
            "q16_modere",
            "q17_ecran",
            "q17_autre",
            "q18_ecran",
            "q18_autre",
            "q19_ecran",
            "q19_autre",
            "garnier_q1",
            "garnier_q2",
            "garnier_q3",
            "garnier_q4",
            "garnier_q5",
            "garnier_q6",
            "garnier_q7",
            "garnier_q8",
            "nap_min_score",
            "nap_mets_score",
            "sed_score",
            "ps_score",
        ];

        $questionnaire = new Questionnaire($this->pdo);

        $patients[] = $patient_header;
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $row['code_onaps'] . '_BF';
                $id .= str_repeat('0', 5 - strlen($row['id_patient']));
                $id .= $row['id_patient'];

                $patient_item = [
                    $id,
                    $row['date_eval'],
                    $row['id_type_eval'],
                    age($row['date_naissance']),
                    $row['sexe_patient'] == 'F' ? '2' : '1' // dans le fichier excel, h (=1) / f (=2) / autre (=3)
                ];

                // recup si le patitent a une prescription
                $query_presc = '
                    SELECT prescription_ap
                    FROM prescription
                    WHERE id_patient = :id_patient
                      AND prescription_ap LIKE \'Oui\'
                    limit 1';
                $stmt_presc = $this->pdo->prepare($query_presc);
                $stmt_presc->bindValue(':id_patient', $row['id_patient']);
                $stmt_presc->execute();

                $a_prescription = '0';
                if ($stmt_presc->rowCount() > 0) {
                    $a_prescription = '1';
                }
                $patient_item[] = $a_prescription;

                // TODO 'raison' et 'raison_detail' en attente
                $a = array_fill(1, 2, null);
                foreach ($a as $value) {
                    $patient_item[] = $value;
                }

                // select les ald du patient
                // les 4 premières
                $query_ald = '
                    SELECT pathologie_ou_etat.id_pathologie_ou_etat,
                           pathologie_ou_etat.id_type_pathologie,
                           pathologie_ou_etat.nom as nom_pathologie_ou_etat,
                           tp.nom                 as nom_type_pathologie
                    FROM pathologie_ou_etat
                             JOIN type_pathologie tp on pathologie_ou_etat.id_type_pathologie = tp.id_type_pathologie
                             JOIN souffre_de sd on pathologie_ou_etat.id_pathologie_ou_etat = sd.id_pathologie_ou_etat
                    WHERE sd.id_patient = :id_patient
                    ORDER BY id_type_pathologie
                    LIMIT 4';
                $stmt_ald = $this->pdo->prepare($query_ald);
                $stmt_ald->bindValue(':id_patient', $row['id_patient']);
                $stmt_ald->execute();

                $row_count = $stmt_ald->rowCount();

                while ($row_ald = $stmt_ald->fetch(PDO::FETCH_ASSOC)) {
                    $patient_item[] = $row_ald['nom_type_pathologie'];
                    $patient_item[] = $row_ald['nom_pathologie_ou_etat'];
                }

                if ($row_count < 4) {
                    $a = array_fill(1, (4 - $row_count) * 2, null);
                    foreach ($a as $value) {
                        $patient_item[] = $value;
                    }
                }

                // recup du test physio
                $query_physio = '
                    SELECT fc_repos, saturation_repos, poids, taille, tour_taille
                    FROM test_physio
                    WHERE id_evaluation = :id_evaluation';
                $stmt_physio = $this->pdo->prepare($query_physio);
                $stmt_physio->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_physio->execute();

                $data_physio = $stmt_physio->fetch(PDO::FETCH_ASSOC);

                if (!$data_physio) {
                    $patient_item[] = null;
                    $patient_item[] = null;
                    $patient_item[] = null;
                } else {
                    $patient_item[] = str_replace('.', ',', $data_physio['poids']);
                    $patient_item[] = str_replace('.', ',', $data_physio['taille']);
                    $patient_item[] = str_replace('.', ',', $data_physio['tour_taille']);
                }

                $patient_item[] = $row['est_dans_qpv'];
                $patient_item[] = $row['est_dans_zrr'];

                //recup des moyennes d'heures par semaine
                if ($row['id_type_eval'] != 1) {
                    $patient_item[] = $this->moyenneHeuresSeanceParSemaine($row['id_patient']);
                } else {
                    $patient_item[] = "0";
                }

                //recup du mode
                $query_mode = "
                        SELECT c.id_type_parcours
                        FROM activite_choisie
                        JOIN orientation o on o.id_orientation = activite_choisie.id_orientation
                        JOIN creneaux c on c.id_creneau = activite_choisie.id_creneau
                        JOIN type_parcours tp on c.id_type_parcours = tp.id_type_parcours
                        WHERE o.id_patient = :id_patient
                          AND activite_choisie.statut LIKE 'En cours'";
                $stmt_mode = $this->pdo->prepare($query_mode);
                $stmt_mode->bindValue(':id_patient', $row['id_patient']);
                $stmt_mode->execute();
                $data_mode = $stmt_mode->fetch();
                $id_type_parcours = $data_mode['id_type_parcours'] ?? null;

                if ($id_type_parcours == '1') {
                    $patient_item[] = '1';
                } elseif ($id_type_parcours == '2') {
                    $patient_item[] = '2';
                } elseif ($id_type_parcours == '3') {
                    $patient_item[] = '2';
                } else {
                    $patient_item[] = null;
                }

                // recup du test 6 mins
                $query_tm6 = '
                    SELECT *
                    FROM eval_apt_aerobie
                    WHERE id_evaluation = :id_evaluation';
                $stmt_tm6 = $this->pdo->prepare($query_tm6);
                $stmt_tm6->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_tm6->execute();

                $data_tm6 = $stmt_tm6->fetch(PDO::FETCH_ASSOC);

                if (!$data_tm6) {
                    for ($i = 0; $i < 10; $i++) {
                        $patient_item[] = null;
                    }
                } else {
                    $patient_item[] = $data_tm6['distance_parcourue'];
                    $patient_item[] = $data_physio['fc_repos'];
                    $patient_item[] = str_replace('.', ',', $data_tm6['sat6']);
                    $patient_item[] = str_replace('.', ',', $data_tm6['sat7']);
                    $patient_item[] = str_replace('.', ',', $data_tm6['sat8']);
                    $patient_item[] = str_replace('.', ',', $data_physio['saturation_repos']);
                    $patient_item[] = $data_tm6['fc6'];
                    $patient_item[] = $data_tm6['fc7'];
                    $patient_item[] = $data_tm6['fc8'];

                    if ($data_tm6['borg6'] == 0 || $data_tm6['borg6'] == 0.5) {
                        $data_tm6['borg6'] = 1;
                    }
                    $patient_item[] = str_replace('.', ',', $data_tm6['borg6']);
                }

                // recup du test assisdebout
                $query_assisdebout = '
                    SELECT nb_lever
                    FROM eval_endurance_musc_mb_inf
                    WHERE id_evaluation = :id_evaluation';
                $stmt_assisdebout = $this->pdo->prepare($query_assisdebout);
                $stmt_assisdebout->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_assisdebout->execute();

                $data_assisdebout = $stmt_assisdebout->fetch(PDO::FETCH_ASSOC);
                $patient_item[] = $data_assisdebout['nb_lever'] ?? null;

                // recup du test up and go
                $query_up_and_go = '
                    SELECT  *
                    FROM eval_up_and_go
                    WHERE id_evaluation = :id_evaluation';
                $stmt_up_and_go = $this->pdo->prepare($query_up_and_go);
                $stmt_up_and_go->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_up_and_go->execute();

                $data_up_and_go = $stmt_up_and_go->fetch(PDO::FETCH_ASSOC);

                // le test test up and go peut être NULL
                // si le test aptititude aerobie a été fait
                if ($data_up_and_go == false) {
                    $patient_item[] = null;
                } else {
                    $patient_item[] = $data_up_and_go['duree'];
                }

                // recup du test force mb sub
                $query_force_musc_mb_sup = '
                    SELECT *
                    FROM eval_force_musc_mb_sup
                    WHERE id_evaluation = :id_evaluation';
                $stmt_force_musc_mb_sup = $this->pdo->prepare($query_force_musc_mb_sup);
                $stmt_force_musc_mb_sup->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_force_musc_mb_sup->execute();

                $data_force_musc_mb_sup = $stmt_force_musc_mb_sup->fetch(PDO::FETCH_ASSOC);

                if (!$data_force_musc_mb_sup) {
                    $patient_item[] = null;
                    $patient_item[] = null;
                } else {
                    if ($data_force_musc_mb_sup['main_forte'] == 'droitier') {
                        $patient_item[] = str_replace('.', ',', $data_force_musc_mb_sup['md']);
                        $patient_item[] = str_replace('.', ',', $data_force_musc_mb_sup['mg']);
                    } elseif ($data_force_musc_mb_sup['main_forte'] == 'gaucher') {
                        $patient_item[] = str_replace('.', ',', $data_force_musc_mb_sup['mg']);
                        $patient_item[] = str_replace('.', ',', $data_force_musc_mb_sup['md']);
                    } else {
                        $max = max([$data_force_musc_mb_sup['md'], $data_force_musc_mb_sup['mg']]);
                        $min = min([$data_force_musc_mb_sup['md'], $data_force_musc_mb_sup['mg']]);
                        $patient_item[] = str_replace('.', ',', $max);
                        $patient_item[] = str_replace('.', ',', $min);
                    }
                }

                // recup du test equilibre
                $query_equilibre = '
                    SELECT *
                    FROM eval_eq_stat
                    WHERE id_evaluation = :id_evaluation';
                $stmt_equilibre = $this->pdo->prepare($query_equilibre);
                $stmt_equilibre->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_equilibre->execute();

                $data_equilibre = $stmt_equilibre->fetch(PDO::FETCH_ASSOC);
                $data_equilibre['pied_droit_sol'] = $data_equilibre['pied_droit_sol'] ?? null;
                $data_equilibre['pied_gauche_sol'] = $data_equilibre['pied_gauche_sol'] ?? null;
                $data_equilibre['pied_dominant'] = $data_equilibre['pied_dominant'] ?? null;

                if ($data_equilibre['pied_dominant'] == 'droit') {
                    $patient_item[] = $data_equilibre['pied_droit_sol'];
                    $patient_item[] = $data_equilibre['pied_gauche_sol'];
                } elseif ($data_equilibre['pied_dominant'] == 'gauche') {
                    $patient_item[] = $data_equilibre['pied_gauche_sol'];
                    $patient_item[] = $data_equilibre['pied_droit_sol'];
                } else {
                    $max = max([$data_equilibre['pied_droit_sol'], $data_equilibre['pied_gauche_sol']]);
                    $min = min([$data_equilibre['pied_droit_sol'], $data_equilibre['pied_gauche_sol']]);
                    $patient_item[] = $max;
                    $patient_item[] = $min;
                }

                // recup du test souplesse
                $query_equilibre = '
                    SELECT distance
                    FROM eval_soupl
                    WHERE id_evaluation = :id_evaluation';
                $stmt_equilibre = $this->pdo->prepare($query_equilibre);
                $stmt_equilibre->bindValue(':id_evaluation', $row['id_evaluation']);
                $stmt_equilibre->execute();

                $data_equilibre = $stmt_equilibre->fetch(PDO::FETCH_ASSOC);

                $patient_item[] = $data_equilibre['distance'] ?? null;

                // recup de l'id du questionnaire OPAQ qui a lieu le même jour que l'évaluation
                $query_questionnaires = '
                    SELECT id_questionnaire_instance,
                           date
                    FROM questionnaire_instance
                    WHERE id_patient = :id_patient
                      AND id_questionnaire = 1
                      AND date = :date_questionnaire
                    GROUP BY id_questionnaire_instance
                    ORDER BY date DESC
                    limit 1';
                $stmt_questionnaires = $this->pdo->prepare($query_questionnaires);
                $stmt_questionnaires->bindValue(':id_patient', $row['id_patient']);
                $stmt_questionnaires->bindValue(':date_questionnaire', $row['date_not_formated']);

                $stmt_questionnaires->execute();
                $data_questionnaires = $stmt_questionnaires->fetch(PDO::FETCH_ASSOC);
                $id_questionnaire_instance_1 = $data_questionnaires['id_questionnaire_instance'] ?? null;

                // recup de l'id du questionnaire GARNIER qui a lieu le même jour que l'évaluation
                $query_questionnaires = '
                    SELECT id_questionnaire_instance,
                           date
                    FROM questionnaire_instance
                    WHERE id_patient = :id_patient
                      AND id_questionnaire = 4
                      AND date = :date_questionnaire
                    GROUP BY id_questionnaire_instance
                    ORDER BY date DESC
                    limit 1';
                $stmt_questionnaires = $this->pdo->prepare($query_questionnaires);
                $stmt_questionnaires->bindValue(':id_patient', $row['id_patient']);
                $stmt_questionnaires->bindValue(':date_questionnaire', $row['date_not_formated']);

                $stmt_questionnaires->execute();
                $data_questionnaires = $stmt_questionnaires->fetch(PDO::FETCH_ASSOC);
                $id_questionnaire_instance_2 = $data_questionnaires['id_questionnaire_instance'] ?? null;

                ////////////////////////////////////////////////////
                // questionnaire 1 (OPAQ)
                ////////////////////////////////////////////////////
                $questionnaire_1_present = !empty($id_questionnaire_instance_1);
                if ($questionnaire_1_present) {
                    // recup du questionnaire 1
                    $query_questionnaire_1 = '
                        SELECT ri.valeur_bool,
                               ri.valeur_int,
                               ri.valeur_string,
                               tr.nom_type_reponse,
                               tr.id_type_reponse,
                               q.ordre,
                               q.id_question
                        FROM reponse_questionnaire
                                 JOIN question q on reponse_questionnaire.id_question = q.id_question
                                 JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                                 JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                                 JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
                        WHERE id_questionnaire_instance = :id_questionnaire_instance
                            AND q.id_question != 2
                        ORDER BY q.ordre'; // on ne récupère plus directement la question 2
                    $stmt_questionnaire_1 = $this->pdo->prepare($query_questionnaire_1);
                    $stmt_questionnaire_1->bindValue(':id_questionnaire_instance', $id_questionnaire_instance_1);
                    $stmt_questionnaire_1->execute();

                    $questionnaire_1 = [];

                    while ($row_questionnaire_1 = $stmt_questionnaire_1->fetch(PDO::FETCH_ASSOC)) {
                        if ($row_questionnaire_1['id_type_reponse'] == '1') {
                            $valeur = $row_questionnaire_1['valeur_int'];
                        } elseif ($row_questionnaire_1['id_type_reponse'] == '2') {
                            $valeur = $row_questionnaire_1['valeur_bool'];
                        } elseif ($row_questionnaire_1['id_type_reponse'] == '3') {
                            $valeur = $row_questionnaire_1['valeur_string'];
                        }

                        $questionnaire_1[] = $valeur;
                    }

                    /**
                     * Prise en compte de la modification de la question 2 (q1_fort et q1_modere) (qu'on ne récupère plus directement),
                     * qui a été séparé en 2 (forte et moyenne intensité)
                     * on regarde la question 3 et pour savoir si on met 0 ou 1 pour q1_fort et q1_modere
                     */
                    $inserted = [];
                    $inserted[] = $questionnaire_1[4] == '0' ? '0' : '1';
                    $inserted[] = $questionnaire_1[3] == '0' ? '0' : '1';
                    array_splice($questionnaire_1, 1, 0, $inserted);

                    /**
                     * Inversion des questions avec 'forte intensité' et 'intensité modérée'
                     */
                    $this->swapArrayValues($questionnaire_1, 3, 4);
                    $this->swapArrayValues($questionnaire_1, 5, 6);
                    $this->swapArrayValues($questionnaire_1, 23, 24);
                    $this->swapArrayValues($questionnaire_1, 25, 26);
                    $this->swapArrayValues($questionnaire_1, 27, 28);

                    for ($i = 0; $i < count($questionnaire_1); $i++) {
                        $patient_item[] = $questionnaire_1[$i];
                    }
                } else {
                    for ($i = 0; $i < 35; $i++) {
                        $patient_item[] = null;
                    }
                }

                ////////////////////////////////////////////////////
                // questionnaire 2 (Garnier)
                ////////////////////////////////////////////////////
                if (!empty($id_questionnaire_instance_2)) {
                    // recup du questionnaire 2
                    $query_questionnaire_2 = '
                        SELECT ri.valeur_bool,
                               ri.valeur_int,
                               ri.valeur_string,
                               tr.nom_type_reponse,
                               tr.id_type_reponse,
                               q.ordre,
                               q.id_question
                        FROM reponse_questionnaire
                                 JOIN question q on reponse_questionnaire.id_question = q.id_question
                                 JOIN reponse_possible rp on q.id_reponse_possible = rp.id_reponse_possible
                                 JOIN type_reponse tr on rp.id_type_reponse = tr.id_type_reponse
                                 JOIN reponse_instance ri on reponse_questionnaire.id_reponse_instance = ri.id_reponse_instance
                        WHERE id_questionnaire_instance = :id_questionnaire_instance
                        ORDER BY q.ordre';
                    $stmt_questionnaire_2 = $this->pdo->prepare($query_questionnaire_2);
                    $stmt_questionnaire_2->bindValue(':id_questionnaire_instance', $id_questionnaire_instance_2);
                    $stmt_questionnaire_2->execute();

                    while ($row_questionnaire_2 = $stmt_questionnaire_2->fetch(PDO::FETCH_ASSOC)) {
                        $valeur = "";
                        if ($row_questionnaire_2['id_type_reponse'] == '1') {
                            $valeur = $row_questionnaire_2['valeur_int'];
                        } elseif ($row_questionnaire_2['id_type_reponse'] == '2') {
                            $valeur = $row_questionnaire_2['valeur_bool'];
                        } elseif ($row_questionnaire_2['id_type_reponse'] == '3') {
                            $valeur = $row_questionnaire_2['valeur_string'];
                        }

                        $patient_item[] = $valeur;
                    }
                } else {
                    for ($i = 0; $i < 8; $i++) {
                        $patient_item[] = null;
                    }
                }

                ////////////////////////////////////////////////////
                // Ajout des scores du questionnaire 1 (OPAQ)
                ////////////////////////////////////////////////////
                if ($questionnaire_1_present) {
                    $score_opnaps = $questionnaire->getScoreOpaq($id_questionnaire_instance_1);
                    $patient_item[] = $score_opnaps['niveau_activite_physique_minutes'];
                    $patient_item[] = $score_opnaps['niveau_activite_physique_mets'];
                    $patient_item[] = $score_opnaps['niveau_sendentarite'];
                } else {
                    for ($i = 0; $i < 3; $i++) {
                        $patient_item[] = null;
                    }
                }

                ////////////////////////////////////////////////////
                // Ajout des scores du questionnaire 2 (Garnier)
                ////////////////////////////////////////////////////
                if (!empty($id_questionnaire_instance_2)) {
                    $score_garnier = $questionnaire->getScoreGarnier($id_questionnaire_instance_2);
                    $patient_item[] = $score_garnier['perception_sante'];
                } else {
                    $patient_item[] = null;
                }

                $patients[] = $patient_item;
            }
        }

        return $patients;
    }

    /**
     * @param $session array la session de l'utilisateur
     * @param $year int l'année d'arrivée des patients
     * @return array Return un array qui contient les données ONAPS de santé, formaté pour api thalamus
     */
    public function readOnapsDataForThalamus($session, $year = null)
    {
        $data = $this->readOnapsData($session, $year);

        if (empty($data) || count($data) <= 1) {
            return [];
        }

        $result = [];

        for ($i = 1; $i < count($data); $i++) {
            $row = [];

            $row["id_beneficiaire"] = $data[$i][0];
            $row["date"] = $data[$i][1];
            $row["rep_mesure"] = $data[$i][2];
            $row["age"] = $data[$i][3];
            $row["sexe"] = $data[$i][4];
            $row["prescription"] = $data[$i][5];
            $row["raison"] = $data[$i][6];
            $row["raison_detail"] = $data[$i][7];
            $row["ald1"] = $data[$i][8];
            $row["ald1_detail"] = $data[$i][9];
            $row["ald2"] = $data[$i][10];
            $row["ald2_detail"] = $data[$i][11];
            $row["ald3"] = $data[$i][12];
            $row["ald3_detail"] = $data[$i][13];
            $row["ald4"] = $data[$i][14];
            $row["ald4_detail"] = $data[$i][15];
            $row["poids"] = $data[$i][16];
            $row["taille"] = $data[$i][17];
            $row["perimetre"] = $data[$i][18];
            $row["qpv"] = $data[$i][19];
            $row["zrr"] = $data[$i][20];
            $row["heures"] = $data[$i][21];
            $row["mode"] = $data[$i][22];
            $row["tm6"] = $data[$i][23];
            $row["tm6_spo2_pre"] = $data[$i][24];
            $row["tm6_spo2_post0min"] = $data[$i][25];
            $row["tm6_spo2_post1min"] = $data[$i][26];
            $row["tm6_spo2_post2min"] = $data[$i][27];
            $row["tm6_fc_pre"] = $data[$i][28];
            $row["tm6_fc_post0min"] = $data[$i][29];
            $row["tm6_fc_post1min"] = $data[$i][30];
            $row["tm6_fc_post2min"] = $data[$i][31];
            $row["tm6_rpe"] = $data[$i][32];
            $row["assis_debout"] = $data[$i][33];
            $row["tupandgo"] = $data[$i][34];
            $row["handgrip_md"] = $data[$i][35];
            $row["handgrip_mnd"] = $data[$i][36];
            $row["equilibre_pd"] = $data[$i][37];
            $row["equilibre_pnd"] = $data[$i][38];
            $row["flexion_tronc"] = $data[$i][39];
            $row["q0"] = $data[$i][40];
            $row["q1_fort"] = $data[$i][41];
            $row["q1_modere"] = $data[$i][42];
            $row["q2_fort"] = $data[$i][43];
            $row["q2_modere"] = $data[$i][44];
            $row["q3_fort"] = $data[$i][45];
            $row["q3_modere"] = $data[$i][46];
            $row["q4"] = $data[$i][47];
            $row["q5_pied"] = $data[$i][48];
            $row["q5_velo"] = $data[$i][49];
            $row["q5_autre"] = $data[$i][50];
            $row["q6_pied"] = $data[$i][51];
            $row["q6_velo"] = $data[$i][52];
            $row["q6_autre"] = $data[$i][53];
            $row["q7_pied"] = $data[$i][54];
            $row["q7_velo"] = $data[$i][55];
            $row["q7_autre"] = $data[$i][56];
            $row["q8"] = $data[$i][57];
            $row["q9"] = $data[$i][58];
            $row["q10"] = $data[$i][59];
            $row["q11"] = $data[$i][60];
            $row["q12"] = $data[$i][61];
            $row["q13"] = $data[$i][62];
            $row["q14_fort"] = $data[$i][63];
            $row["q14_modere"] = $data[$i][64];
            $row["q15_fort"] = $data[$i][65];
            $row["q15_modere"] = $data[$i][66];
            $row["q16_fort"] = $data[$i][67];
            $row["q16_modere"] = $data[$i][68];
            $row["q17_ecran"] = $data[$i][69];
            $row["q17_autre"] = $data[$i][70];
            $row["q18_ecran"] = $data[$i][71];
            $row["q18_autre"] = $data[$i][72];
            $row["q19_ecran"] = $data[$i][73];
            $row["q19_autre"] = $data[$i][74];
            $row["garnier_q1"] = $data[$i][75];
            $row["garnier_q2"] = $data[$i][76];
            $row["garnier_q3"] = $data[$i][77];
            $row["garnier_q4"] = $data[$i][78];
            $row["garnier_q5"] = $data[$i][79];
            $row["garnier_q6"] = $data[$i][80];
            $row["garnier_q7"] = $data[$i][81];
            $row["garnier_q8"] = $data[$i][82];
            $row["nap_min_score"] = $data[$i][83];
            $row["nap_mets_score"] = $data[$i][84];
            $row["sed_score"] = $data[$i][85];
            $row["ps_score"] = $data[$i][86];

            $result[] = $row;
        }

        return $result;
    }

    private function swapArrayValues(&$array, $id1, $id2)
    {
        $temp = $array[$id1];
        $array[$id1] = $array[$id2];
        $array[$id2] = $temp;
    }

    /**
     * @param $id_patient
     * @return float
     */
    public function moyenneHeuresSeanceParSemaine($id_patient)
    {
        $query = "
            SELECT date_seance,
                   presence,
                   hd.heure as heure_debut,
                   hf.heure as heure_fin
            FROM a_participe_a
                     JOIN seance s on a_participe_a.id_seance = s.id_seance
                     JOIN heures hd on s.heure_debut = hd.id_heure
                     JOIN heures hf on s.heure_fin = hf.id_heure
            WHERE a_participe_a.id_patient = :id_patient
              AND a_participe_a.presence = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();

        $total_minutes = 0;
        $date_list = [];

        while ($data_moy = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $duree_seance = duree_minutes($data_moy['heure_debut'], $data_moy['heure_fin']);
            $total_minutes += $duree_seance;

            $date_list[] = $data_moy['date_seance'];
        }

        $moy = 0;
        if (count($date_list) == 1) {
            $moy = $total_minutes / 60;
        } elseif (count($date_list) > 1) {
            $date_min = min($date_list);
            $date_max = max($date_list);

            $nb_semaines = duree_semaines($date_min, $date_max);
            if ($nb_semaines == 0) {
                $nb_semaines = 1;
            }

            $moy = round($total_minutes / 60) / $nb_semaines;
        }

        return round($moy);
    }

    /**
     * @param $session array la session de l'utilisateur
     * @return array|false Return un array qui contient les données des patients ou un false en cas d'erreur
     */
    public function readPatientData(array $session)
    {
        if (empty($session['id_territoire'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            // roles qui ont accès à l'export des patients
            $authorized_roles = [
                Permissions::COORDONNATEUR_MSS,
                Permissions::COORDONNATEUR_PEPS,
                Permissions::COORDONNATEUR_NON_MSS,
                Permissions::SUPER_ADMIN,
                Permissions::EVALUATEUR,
            ];

            $permissions = new Permissions($session);
            $roles_user = $permissions->getRolesUser();

            if (array_intersect($authorized_roles, $roles_user) == []) {
                $this->errorMessage = "L'utilisateur n'a pas la permission d'accès";
                return false;
            }
        } catch (Exception $e) {
            $this->errorMessage = "Erreur lors de la vérification des permissions";
            return false;
        }

        $query = "
            SELECT patients.id_patient,
                   date_naissance,
                   DATE_FORMAT(date_admission, '%d/%m/%Y') as date_admission,
                   IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)                     as nom,
                   IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise, premier_prenom_naissance) as prenom,
                   c.tel_fixe_coordonnees as tel_fixe,
                   c.tel_portable_coordonnees as tel_portable,
                   c.mail_coordonnees as email,
                   v.nom_ville,
                   v.code_postal,
                   a.nom_adresse,
                   c_prescripteur.nom_coordonnees as nom_prescripteur,
                   c_prescripteur.prenom_coordonnees as prenom_prescripteur,
                   c_traitant.nom_coordonnees as nom_traitant,
                   c_traitant.prenom_coordonnees as prenom_traitant,
                   tp.type_parcours,
                   antenne.nom_antenne
            FROM patients
                    JOIN coordonnees c on patients.id_coordonnee = c.id_coordonnees
                    JOIN adresse a on patients.id_adresse = a.id_adresse
                    JOIN antenne on patients.id_antenne = antenne.id_antenne
                    JOIN structure s on antenne.id_structure = s.id_structure
                    JOIN users u on patients.id_user = u.id_user
                    LEFT JOIN se_localise_a sla on a.id_adresse = sla.id_adresse
                    LEFT JOIN villes v on sla.id_ville = v.id_ville
                    LEFT JOIN prescrit p on patients.id_patient = p.id_patient
                    LEFT JOIN medecins m_prescripteur on p.id_medecin = m_prescripteur.id_medecin
                    LEFT JOIN coordonnees c_prescripteur on m_prescripteur.id_coordonnees = c_prescripteur.id_coordonnees
                    LEFT JOIN traite t on patients.id_patient = t.id_patient
                    LEFT JOIN medecins m_traitant on t.id_medecin = m_traitant.id_medecin
                    LEFT JOIN coordonnees c_traitant on m_traitant.id_coordonnees = c_traitant.id_coordonnees
                    LEFT JOIN orientation o on patients.id_patient = o.id_patient
                    LEFT JOIN type_parcours tp on o.id_type_parcours = tp.id_type_parcours
            WHERE 1=1 ";

        // filtres selon les rôles
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND patients.id_territoire = :id_territoire ';
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS)) {
            $query .= ' AND (s.id_structure = :id_structure
                             OR u.id_user = :id_user) ';
        } elseif ($permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $query .= ' AND (s.id_structure = :id_structure
                             OR u.id_user = :id_user) ';
        } elseif ($permissions->hasRole(Permissions::EVALUATEUR)) {
            $query .= ' AND patients.id_user = :id_user ';
        }

        $stmt = $this->pdo->prepare($query);
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt->bindValue(':id_territoire', $session['id_territoire']);
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
            $stmt->bindValue(':id_user', $session['id_user']);
        } elseif ($permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
            $stmt->bindValue(':id_user', $session['id_user']);
        } elseif ($permissions->hasRole(Permissions::EVALUATEUR)) {
            $stmt->bindValue(':id_user', $session['id_user']);
        }

        if (!$stmt->execute()) {
            $this->errorMessage = "Erreur lors de l'exécution de la requête";
            return false;
        }

        $patients = [];
        $a = new Ald($this->pdo);
        while ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patient['alds'] = $a->readAllPatientAsString($patient['id_patient']) ?? "";

            $patient['nom'] = EncryptionManager::decrypt($patient['nom']);
            $patient['prenom'] = EncryptionManager::decrypt($patient['prenom']);
            $patient['email'] = !empty($patient['email']) ? EncryptionManager::decrypt(
                $patient['email']
            ) : "";
            $patient['tel_fixe'] = !empty($patient['tel_fixe']) ? EncryptionManager::decrypt(
                $patient['tel_fixe']
            ) : "";
            $patient['tel_portable'] = !empty($patient['tel_portable']) ? EncryptionManager::decrypt(
                $patient['tel_portable']
            ) : "";
            $patient['nom_adresse'] = !empty($patient['nom_adresse']) ? EncryptionManager::decrypt(
                $patient['nom_adresse']
            ) : "";

            $patients[] = $patient;
        }

        return $patients;
    }
}