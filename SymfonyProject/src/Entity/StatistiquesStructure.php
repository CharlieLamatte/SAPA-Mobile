<?php

/**
 * Cette classe sert à récupérer les statistiques d'une structure
 */

namespace Sportsante86\Sapa\Model;

 ;

class StatistiquesStructure
{
    private PDO $pdo;
    private string $errorMessage = "";

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
     * Return la repartition par age des patients d'une structure
     *
     * @param $id_structure
     * @return array[]
     */
    public function getRepartitionAge($id_structure)
    {
        $query = '
            SELECT sum(IF(age < 18, 1, 0)) count_inf_18,
                   sum(IF(age >= 18 AND age <= 25, 1, 0)) count_18_25,
                   sum(IF(age >= 26 AND age <= 40, 1, 0)) count_26_40,
                   sum(IF(age >= 41 AND age <= 55, 1, 0)) count_41_55,
                   sum(IF(age >= 56 AND age <= 70, 1, 0)) count_56_70,
                   sum(IF(age > 70, 1, 0)) count_sup_70
            FROM (SELECT YEAR(NOW()) - YEAR(date_naissance) as age
                  FROM patients
                           JOIN antenne a on patients.id_antenne = a.id_antenne
                  WHERE a.id_structure = :id_structure) as py';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $rows_cols = [
            'count_inf_18' => 'moins de 18 ans',
            'count_18_25' => '18 à 25 ans',
            'count_26_40' => '25 à 40 ans',
            'count_41_55' => '40 à 55 ans',
            'count_56_70' => '55 à 70 ans',
            'count_sup_70' => 'plus de 70 ans'
        ];

        $result = [
            'labels' => [],
            'values' => []
        ];
        foreach ($rows_cols as $col => $label) {
            $result['values'][] = intval($row[$col]);
            $result['labels'][] = $label;
        }

        return $result;
    }

    /**
     * Return la repartition par status (actif ou non) des patients d'une structure
     *
     * @param $id_structure
     * @return array[]
     */
    public function getRepartitionStatusBeneficiaire($id_structure)
    {
        $query = '
            SELECT sum(IF(est_archive = 0, 1, 0)) as actif_count,
                   sum(IF(est_archive = 1, 1, 0)) as inactif_count
            FROM (SELECT est_archive
                  FROM patients
                           JOIN antenne a on patients.id_antenne = a.id_antenne
                  WHERE a.id_structure = :id_structure) as py';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $rows_cols = [
            'actif_count' => 'Actif',
            'inactif_count' => 'Non actif',
        ];

        $result = [
            'labels' => [],
            'values' => []
        ];
        foreach ($rows_cols as $col => $label) {
            $result['values'][] = intval($row[$col]);
            $result['labels'][] = $label;
        }

        return $result;
    }

    /**
     * Return la repartition par role des utilisateurs d'une structure
     *
     * @param $id_structure
     * @return array[]
     */
    public function getRepartitionRole($id_structure)
    {
        $query = '
            SELECT sum(IF(id_role_user = 3, 1, 0)) as intervenant_count,
                   sum(IF(id_role_user = 5, 1, 0)) as evaluateur_count,
                   sum(IF(id_role_user = 6, 1, 0)) as responsable_count
            FROM (SELECT id_role_user
                  FROM users
                  JOIN a_role ar on users.id_user = ar.id_user
                  WHERE users.id_structure = :id_structure) as py';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $rows_cols = [
            'intervenant_count' => 'Intervenant sportif',
            'evaluateur_count' => 'Évaluateur PEPS',
            'responsable_count' => 'Responsable Structure',
        ];

        $result = [
            'labels' => [],
            'values' => []
        ];
        foreach ($rows_cols as $col => $label) {
            $result['values'][] = intval($row[$col]);
            $result['labels'][] = $label;
        }

        return $result;
    }

    /**
     * Récupère le pourcentage d'assiduité d'une structure pour la semaine de $today si calculable,
     * le pourcentage d'assiduité pour la semaine précédente de $today si calculable,
     * le taux de variation si calculable
     *
     * Seules les séances validées sont prisent en compte
     *
     * @param        $id_structure
     * @param string $today date au format 'YYYY-MM-DD'
     * @return array[]
     */
    public function getAssiduite($id_structure, $today)
    {
        $query = "
            SELECT SUM(IF(presence = 1 AND seance_week = current_week, 1, 0))  as present_current_week,
                   SUM(IF(presence = 0 AND seance_week = current_week, 1, 0))  as absent_current_week,
                   SUM(IF(presence = 1 AND seance_week = previous_week, 1, 0)) as present_previous_week,
                   SUM(IF(presence = 0 AND seance_week = previous_week, 1, 0)) as absent_previous_week
            FROM (SELECT presence,
                         YEARWEEK(:today_1, 1)                  as current_week,
                         YEARWEEK(:today_2 - interval 7 day, 1) as previous_week,
                         YEARWEEK(date_seance, 1)             as seance_week
                  FROM seance
                           join creneaux on creneaux.id_creneau = seance.id_creneau
                           join a_participe_a apa on seance.id_seance = apa.id_seance
                  WHERE creneaux.id_structure = :id_structure
                    AND seance.validation_seance = 1
                    AND (YEARWEEK(seance.date_seance, 1) = YEARWEEK(:today_3, 1)
                      OR YEARWEEK(date_seance, 1) = YEARWEEK(:today_4 - interval 7 day, 1))) as py";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->bindValue(':today_1', $today);
        $stmt->bindValue(':today_2', $today);
        $stmt->bindValue(':today_3', $today);
        $stmt->bindValue(':today_4', $today);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $present_current_week = intval($row['present_current_week']);
        $absent_current_week = intval($row['absent_current_week']);
        $total_current_week = $present_current_week + $absent_current_week;

        $present_previous_week = intval($row['present_previous_week']);
        $absent_previous_week = intval($row['absent_previous_week']);
        $total_previous_week = $present_previous_week + $absent_previous_week;

        $percent_present_current_week = null;
        if ($total_current_week > 0) {
            $percent_present_current_week = intval(round($present_current_week / $total_current_week * 100));
        }

        $percent_present_previous_week = null;
        if ($total_previous_week > 0) {
            $percent_present_previous_week = intval(round($present_previous_week / $total_previous_week * 100));
        }

        $variation = null;
        if (!is_null($percent_present_current_week) && !is_null($percent_present_previous_week)) {
            $variation = $percent_present_current_week - $percent_present_previous_week;
        }

        $result = [
            'labels' => [],
            'values' => [],
            'percent_present_previous_week' => null,
            'percent_present_current_week' => null,
            'variation' => null,
        ];

        if (!is_null($percent_present_previous_week)) {
            $result['values'][] = $percent_present_previous_week;
            $result['labels'][] = "Pourcentage de présence la semaine dernière";
            $result['percent_present_previous_week'] = $percent_present_previous_week;
        }
        if (!is_null($percent_present_current_week)) {
            $result['values'][] = $percent_present_current_week;
            $result['labels'][] = "Pourcentage de présence cette semaine";
            $result['percent_present_current_week'] = $percent_present_current_week;
        }
        if (!is_null($variation)) {
            $result['values'][] = $variation;
            $result['labels'][] = "Taux de variation de présence";
            $result['variation'] = $variation;
        }

        return $result;
    }

    /**
     * Calcul l'assiduité de toutes les séances validées du créneau
     *
     * @param $id_creneau
     * @return false|int l'assiduité en %
     */
    public function getAssiduiteCreneau($id_creneau)
    {
        if (empty($id_creneau)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        // assiduite globale du créneau
        $query = "
            SELECT SUM(IF(presence = 1, 1, 0))                 as present_count,
                   SUM(IF(presence = 0 OR presence = 1, 1, 0)) as total
            FROM seance
                     join creneaux on creneaux.id_creneau = seance.id_creneau
                     join a_participe_a apa on seance.id_seance = apa.id_seance
            WHERE creneaux.id_creneau = :id_creneau
               AND seance.validation_seance = 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_creneau', $id_creneau);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_null($row['present_count']) || is_null($row['total'])) {
            return false;
        }

        if ($row['total'] == 0) {
            return false;
        }

        return intval(round(($row['present_count'] / $row['total']) * 100));
    }

    /**
     * Return un array qui peut contenir des arrays associatifs au format suivant:
     * [
     *      "id_creneau" => int,
     *      "nom_creneau" => string,,
     *      "heure_commence" => string,
     *      "heure_fin" =>string,
     *      "heure_fin" => string,
     *      "nom_jour" => string,
     *      "assiduite" => int,
     * }
     *
     * @param $id_structure
     * @return array|false
     */
    public function getAssiduiteAllCreaneaux($id_structure)
    {
        if (empty($id_structure)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = "
            SELECT id_creneau, nom_creneau, heureCommence.heure as heure_commence, heureTermine.heure as heure_fin, jours.nom_jour
            FROM creneaux
                     JOIN jours USING (id_jour)
                     JOIN commence_a USING (id_creneau)
                     JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     JOIN se_termine_a USING (id_creneau)
                     JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
            WHERE creneaux.id_structure = :id_structure";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->execute();
        $creneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!is_array($creneaux)) {
            return false;
        }

        foreach ($creneaux as &$creneau) {
            $creneau['assiduite'] = $this->getAssiduiteCreneau($creneau['id_creneau']);
        }

        return $creneaux;
    }
}

