<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use Sportsante86\Sapa\Outils\Permissions;

class StatistiquesTerritoire
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
     * Return la repartition par age des patients d'un territoire
     *
     * @param $id_territoire
     * @param $year
     * @param $id_structure
     * @return array[]
     */
    public function getRepartitionAge($id_territoire, $year = null, $id_structure = null)
    {
        $query = '
            SELECT sum(IF(age < 18, 1, 0))                count_inf_18,
                   sum(IF(age >= 18 AND age <= 25, 1, 0)) count_18_25,
                   sum(IF(age >= 26 AND age <= 40, 1, 0)) count_26_40,
                   sum(IF(age >= 41 AND age <= 55, 1, 0)) count_41_55,
                   sum(IF(age >= 56 AND age <= 70, 1, 0)) count_56_70,
                   sum(IF(age > 70, 1, 0))                count_sup_70
            FROM (SELECT YEAR(NOW()) - YEAR(date_naissance) as age
                  FROM patients
                           JOIN antenne a on patients.id_antenne = a.id_antenne
                  WHERE patients.id_territoire = :id_territoire ';

        if ($year != null && $year != '-1') {
            $query .= ' AND YEAR(date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        $query .= ' ) as py';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
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
     * Return pour chaque territoire (de type departement) de la region de l'utilisateur (sauf pour l'admin qui a
     * tout): nb_patient, nb_creneau, nb_intervenan, nb_medecin, nb_coordinateur, nb_structure
     *
     * @param $session
     * @return array[]|false
     */
    public function getNombreEntitites($session)
    {
        try {
            $permission = new Permissions($session);
        } catch (Exception $e) {
            return false;
        }

        $query = "
            SELECT id_territoire, nom_territoire, territoire.id_region, nom_region
            FROM territoire
            JOIN region r on territoire.id_region = r.id_region
            WHERE id_type_territoire = 1 ";

        // seul le SUPER_ADMIN peut voir les territoires de toutes les régions
        if (!$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND territoire.id_region = (SELECT id_region
                                         FROM territoire
                                         WHERE id_territoire = :id_territoire) ';
        }

        $query .= ' ORDER BY nom_territoire ';

        $stmt_terr = $this->pdo->prepare($query);
        if (!$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt_terr->bindValue(':id_territoire', $session['id_territoire']);
        }
        $stmt_terr->execute();

        $total_all_regions = [
            'id_territoire' => null,
            'nom_territoire' => "Total",
            'id_region' => null,
            'nom_region' => null,
            'is_total' => true,

            'nb_patient' => 0,
            'nb_creneau' => 0,
            'nb_intervenant' => 0,
            'nb_medecin' => 0,
            'nb_coordinateur' => 0,
            'nb_structure' => 0,
        ];
        $total_regions = [];
        $territoires = [];

        while ($territoire_item = $stmt_terr->fetch(PDO::FETCH_ASSOC)) {
            if (!array_key_exists($territoire_item['id_region'], $total_regions)) {
                $total_regions[$territoire_item['id_region']] = [
                    'id_territoire' => null,
                    'nom_territoire' => "Total " . $territoire_item['nom_region'],
                    'id_region' => $territoire_item['id_region'],
                    'nom_region' => $territoire_item['nom_region'],
                    'is_total' => true,

                    'nb_patient' => 0,
                    'nb_creneau' => 0,
                    'nb_intervenant' => 0,
                    'nb_medecin' => 0,
                    'nb_coordinateur' => 0,
                    'nb_structure' => 0,
                ];
            }

            $territoire_item['is_total'] = false;

            // Nombre de patients
            $query = '
                SELECT COUNT(id_patient) AS nb_patient
                FROM patients
                WHERE id_territoire = :id_territoire';
            $stmt_num = $this->pdo->prepare($query);
            $stmt_num->bindValue(":id_territoire", $territoire_item['id_territoire']);
            $stmt_num->execute();
            $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
            $territoire_item["nb_patient"] = intval($data['nb_patient']);
            $total_regions[$territoire_item['id_region']]['nb_patient'] += intval($data['nb_patient']);
            $total_all_regions['nb_patient'] += intval($data['nb_patient']);

            // Nombre de créneaux
            $query = '
                SELECT COUNT(id_creneau) AS nb_creneau
                FROM creneaux
                JOIN structure ON structure.id_structure = creneaux.id_structure 
                WHERE id_territoire  =:id_territoire';
            $stmt_num = $this->pdo->prepare($query);
            $stmt_num->bindValue(":id_territoire", $territoire_item['id_territoire']);
            $stmt_num->execute();
            $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
            $territoire_item["nb_creneau"] = intval($data['nb_creneau']);
            $total_regions[$territoire_item['id_region']]['nb_creneau'] += intval($data['nb_creneau']);
            $total_all_regions['nb_creneau'] += intval($data['nb_creneau']);

            // Nombre d'intervenants
            $query = '
                SELECT COUNT(id_intervenant) AS nb_intervenant
                FROM intervenants
                WHERE id_territoire = :id_territoire';
            $stmt_num = $this->pdo->prepare($query);
            $stmt_num->bindValue(":id_territoire", $territoire_item['id_territoire']);
            $stmt_num->execute();
            $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
            $territoire_item["nb_intervenant"] = intval($data['nb_intervenant']);
            $total_regions[$territoire_item['id_region']]['nb_intervenant'] += intval($data['nb_intervenant']);
            $total_all_regions['nb_intervenant'] += intval($data['nb_intervenant']);

            // Nombre de medecins
            $query = '
                SELECT COUNT(id_medecin) AS nb_medecin
                FROM medecins
                WHERE id_territoire = :id_territoire';
            $stmt_num = $this->pdo->prepare($query);
            $stmt_num->bindValue(":id_territoire", $territoire_item['id_territoire']);
            $stmt_num->execute();
            $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
            $territoire_item["nb_medecin"] = intval($data['nb_medecin']);
            $total_regions[$territoire_item['id_region']]['nb_medecin'] += intval($data['nb_medecin']);
            $total_all_regions['nb_medecin'] += intval($data['nb_medecin']);

            // Nombre de coordinateurs
            $query = '
                SELECT COUNT(u.id_user) AS nb_coordinateur
                FROM users u
                JOIN a_role ar on u.id_user = ar.id_user
                WHERE ar.id_role_user = 2
                 AND id_territoire = :id_territoire';
            $stmt_num = $this->pdo->prepare($query);
            $stmt_num->bindValue(":id_territoire", $territoire_item['id_territoire']);
            $stmt_num->execute();
            $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
            $territoire_item["nb_coordinateur"] = intval($data['nb_coordinateur']);
            $total_regions[$territoire_item['id_region']]['nb_coordinateur'] += intval($data['nb_coordinateur']);
            $total_all_regions['nb_coordinateur'] += intval($data['nb_coordinateur']);

            // Nombre de structures
            $query = '
                SELECT COUNT(id_structure) AS nb_structure
                FROM structure
                WHERE id_territoire = :id_territoire';
            $stmt_num = $this->pdo->prepare($query);
            $stmt_num->bindValue(":id_territoire", $territoire_item['id_territoire']);
            $stmt_num->execute();
            $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
            $territoire_item["nb_structure"] = intval($data['nb_structure']);
            $total_regions[$territoire_item['id_region']]['nb_structure'] += intval($data['nb_structure']);
            $total_all_regions['nb_structure'] += intval($data['nb_structure']);

            $territoires[] = $territoire_item;
        }

        foreach ($total_regions as $total) {
            $territoires[] = $total;
        }

        // s'il y a plusieurs regions on ajoute le total de toutes les régions
        if (count($total_regions) > 1) {
            $territoires[] = $total_all_regions;
        }

        return $territoires;
    }

    /**
     * Return pour le territoire donné le nombre d'orientations par structure (qui ont eu au moins une orientation)
     * et le total
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission du patient
     * @param string $id_structure La structure à laquelle est rattachée l'antenne du patient
     * @return array|array[]|false
     */
    public function getNombreOrientation($id_territoire, $year = null, $id_structure = null)
    {
        if (empty($id_territoire)) {
            return false;
        }

        $query = '
            SELECT s.nom_structure,
                   count(*) as orientation_count
            FROM activite_choisie
                     JOIN orientation o on activite_choisie.id_orientation = o.id_orientation
                     JOIN creneaux c on activite_choisie.id_creneau = c.id_creneau
                     JOIN structure s on s.id_structure = c.id_structure
                     JOIN patients p on p.id_patient = o.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire ';

        if ($year != null && $year != '-1') {
            $query .= ' AND YEAR(date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        $query .= '
            GROUP BY s.nom_structure
            ORDER BY orientation_count DESC';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }

        $stmt->execute();

        $result = [
            'labels' => [],
            'values' => []
        ];
        $total = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total += intval($row["orientation_count"]);
            $result['values'][] = intval($row["orientation_count"]);
            $result['labels'][] = $row["nom_structure"];
        }

        // ajout du total
        $result['total'] = $total;

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'amélioration des patients au test aérobie
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getAmeliorationTestAerobie($id_territoire, $year = null, $id_structure = null, $sexe_patient = null)
    {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_aerobie(evaluations.id_patient)) as moyenne
            FROM evaluations
                     JOIN eval_apt_aerobie eaa on evaluations.id_evaluation = eaa.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'Moyenne évolution distance parcourue', round($row['moyenne'] ?? 0, 2));

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'amélioration des patients au test physio
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getAmeliorationTestPhysio($id_territoire, $year = null, $id_structure = null, $sexe_patient = null)
    {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_phys_poids(evaluations.id_patient))        as moyenne_poids,
                   AVG(evolution_eval_phys_taille(evaluations.id_patient))       as moyenne_taille,
                   AVG(evolution_eval_phys_IMC(evaluations.id_patient))          as moyenne_IMC,
                   AVG(evolution_eval_phys_tourdetaille(evaluations.id_patient)) as moyenne_tourdetaille,
                   AVG(evolution_eval_phys_sat_o2(evaluations.id_patient))       as moyenne_sat_o2,
                   AVG(evolution_eval_phys_fc_repos(evaluations.id_patient))     as moyenne_fc_repos,
                   AVG(evolution_eval_phys_fc_max_mes(evaluations.id_patient))   as moyenne_fc_max_mes,
                   AVG(evolution_eval_phys_fc_max_th(evaluations.id_patient))    as moyenne_fc_max_th,
                   AVG(evolution_eval_phys_borg_repos(evaluations.id_patient))   as moyenne_borg_repos
            FROM evaluations
                     JOIN test_physio tp on evaluations.id_evaluation = tp.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'évolution du poids (kg)', round($row['moyenne_poids'] ?? 0, 2));
        self::add($result, 'évolution de la taille (cm)', round($row['moyenne_taille'] ?? 0, 2));
        self::add($result, 'évolution de l\'IMC', round($row['moyenne_IMC'] ?? 0, 2));
        self::add($result, 'évolution du tour de taille (cm)', round($row['moyenne_tourdetaille'] ?? 0, 2));
        self::add($result, 'évolution de la saturation en O2 (% SpO2)', round($row['moyenne_sat_o2'] ?? 0, 2));
        self::add(
            $result,
            'évolution de la fréquence cardiaque au repos (bpm)',
            round($row['moyenne_fc_repos'] ?? 0, 2)
        );
        self::add(
            $result,
            'évolution de la fréquence cardiaque max mesurée (bpm)',
            round($row['moyenne_fc_max_mes'] ?? 0, 2)
        );
        self::add(
            $result,
            'évolution de la fréquence cardiaque max théorique (bpm)',
            round($row['moyenne_fc_max_th'] ?? 0, 2)
        );
        self::add($result, 'évolution du score de Borg de repos', round($row['moyenne_borg_repos'] ?? 0, 2));

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'amélioration des patients au test de force des Mb Sup
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getAmeliorationTestForceMbSup(
        $id_territoire,
        $year = null,
        $id_structure = null,
        $sexe_patient = null
    ) {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_mb_sup_main_gauche(evaluations.id_patient)) as moyenne_main_gauche,
                   AVG(evolution_eval_mb_sup_main_droite(evaluations.id_patient)) as moyenne_main_droite
            FROM evaluations
                     JOIN eval_apt_aerobie eaa on evaluations.id_evaluation = eaa.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'Moyenne force main gauche', round($row['moyenne_main_gauche'] ?? 0, 2));
        self::add($result, 'Moyenne force main droite', round($row['moyenne_main_droite'] ?? 0, 2));

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'amélioration des patients au test équilibre statique
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getAmeliorationTestEquilibreStatique(
        $id_territoire,
        $year = null,
        $id_structure = null,
        $sexe_patient = null
    ) {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_equilibre_statique_pied_gauche(evaluations.id_patient)) as moyenne_pied_gauche,
                   AVG(evolution_eval_equilibre_statique_pied_droit(evaluations.id_patient))  as moyenne_pied_droit
            FROM evaluations
                     JOIN eval_eq_stat ees on evaluations.id_evaluation = ees.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire ";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }

        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'Moyenne pied gauche', round($row['moyenne_pied_gauche'] ?? 0, 2));
        self::add($result, 'Moyenne pied droit', round($row['moyenne_pied_droit'] ?? 0, 2));

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'amélioration des patients au test souplesse
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getAmeliorationTestSouplesse(
        $id_territoire,
        $year = null,
        $id_structure = null,
        $sexe_patient = null
    ) {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_souplesse(evaluations.id_patient)) as moyenne_distance
            FROM evaluations
                     JOIN eval_soupl es on evaluations.id_evaluation = es.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'Moyenne distance au sol', round($row['moyenne_distance'] ?? 0, 2));

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'amélioration des patients au test mobilité scapulo humérale
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getEvolutionTestMobiliteScapuloHumerale(
        $id_territoire,
        $year = null,
        $id_structure = null,
        $sexe_patient = null
    ) {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_mobilite_scapulo_humerale_main_gauche(evaluations.id_patient)) as moyenne_main_gauche,
                   AVG(evolution_eval_mobilite_scapulo_humerale_main_droite(evaluations.id_patient)) as moyenne_main_droite
            FROM evaluations
                     JOIN eval_mobilite_scapulo_humerale emsh on evaluations.id_evaluation = emsh.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'Moyenne distance main gauche', round($row['moyenne_main_gauche'] ?? 0, 2));
        self::add($result, 'Moyenne distance main droite', round($row['moyenne_main_droite'] ?? 0, 2));

        return $result;
    }

    /**
     * Return pour le territoire donné la moyenne d'évolution des patients au test endurance des membres inférieurs
     *
     * @param string $id_territoire Le territoire du patient
     * @param string $year L'année d'admission des patients
     * @param string $id_structure La structure à laquelle est rattachée l'antenne des patients
     * @param string $sexe_patient Le sexe des patients ("F" ou "M")
     * @return array[]|false
     */
    public function getEvolutionTestEnduranceMbInf(
        $id_territoire,
        $year = null,
        $id_structure = null,
        $sexe_patient = null
    ) {
        if (empty($id_territoire)) {
            return false;
        }

        $query = "
            SELECT AVG(evolution_eval_endurance_mb_inf_nombre_levers(evaluations.id_patient)) as moyenne_nombre_levers,
                   AVG(evolution_eval_endurance_mb_inf_fc30(evaluations.id_patient))          as moyenne_fc30,
                   AVG(evolution_eval_endurance_mb_inf_sat30(evaluations.id_patient))         as moyenne_sat30,
                   AVG(evolution_eval_endurance_mb_inf_borg30(evaluations.id_patient))        as moyenne_borg30
            FROM evaluations
                     JOIN eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
                     JOIN patients p on evaluations.id_patient = p.id_patient
                     JOIN antenne a on p.id_antenne = a.id_antenne
            WHERE p.id_territoire = :id_territoire ";

        if ($year != null && $year != '-1') {
            $query .= ' AND year(p.date_admission) = :year ';
        }
        if ($id_structure != null && $id_structure != '-1') {
            $query .= ' AND a.id_structure = :id_structure ';
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $query .= ' AND sexe = :sexe_patient';
        }

        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(':id_territoire', $id_territoire);
        if ($year != null && $year != '-1') {
            $stmt->bindValue(':year', $year);
        }
        if ($id_structure != null && $id_structure != '-1') {
            $stmt->bindValue(':id_structure', $id_structure);
        }
        if ($sexe_patient != null && $sexe_patient != '-1') {
            $stmt->bindValue(':sexe_patient', $sexe_patient);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return false;
        }

        $result = [
            'labels' => [],
            'values' => []
        ];
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        self::add($result, 'Moyenne nombre de levers', round($row['moyenne_nombre_levers'] ?? 0, 2));
        self::add($result, 'Moyenne FC à 30s (bpm)', round($row['moyenne_fc30'] ?? 0, 2));
        self::add($result, 'Moyenne sat à 30s (en %)', round($row['moyenne_sat30'] ?? 0, 2));
        self::add($result, 'Moyenne borg à 30s', round($row['moyenne_borg30'] ?? 0, 2));

        return $result;
    }

    private static function add(&$array, $label, $value)
    {
        $array['values'][] = $value;
        $array['labels'][] = $label;
    }
}