<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Evaluation
{
    private PDO $pdo;
    private string $errorMessage;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->errorMessage = '';
    }

    /**
     * @return string the error message of the last operation
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Supprime toutes les évaluations d'un patient
     *
     * @param $id_patient
     * @return void
     * @throws Exception
     */
    public function deleteAllEvaluationPatient($id_patient)
    {
        if (empty($id_patient)) {
            throw new Exception('Error missing id_patient');
        }

        $query = '
            SELECT id_evaluation
            FROM evaluations
            WHERE id_patient = :id_patient';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_patient', $id_patient);

        if (!$statement->execute()) {
            throw new Exception('Error SELECT id_evaluation');
        }
        $evaluation_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

        // disable foreign_key_checks if not already disabled
        $query = 'SELECT @@foreign_key_checks';
        $statement = $this->pdo->prepare($query);
        if (!$statement->execute()) {
            throw new Exception('Error SELECT @@foreign_key_checks');
        }
        $foreign_key_checks = $statement->fetch(PDO::FETCH_COLUMN, 0);

        if ($foreign_key_checks == '1') {
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }
        }

        if ($evaluation_ids) {
            foreach ($evaluation_ids as $id_evaluation) {
                $query = '
                    DELETE FROM evaluations
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM evaluations');
                }

                $query = '
                    DELETE FROM eval_apt_aerobie
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_apt_aerobie');
                }

                $query = '
                    DELETE FROM eval_endurance_musc_mb_inf
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_endurance_musc_mb_inf');
                }

                $query = '
                    DELETE FROM eval_eq_stat
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_eq_stat');
                }

                $query = '
                    DELETE FROM eval_force_musc_mb_sup 
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_force_musc_mb_sup');
                }

                $query = '
                    DELETE FROM eval_mobilite_scapulo_humerale 
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_mobilite_scapulo_humerale');
                }

                $query = '
                    DELETE FROM eval_soupl 
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_soupl');
                }

                $query = '
                    DELETE FROM eval_up_and_go  
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM eval_up_and_go');
                }

                $query = '
                    DELETE FROM test_physio  
                    WHERE id_evaluation = :id_evaluation';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_evaluation', $id_evaluation);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM test_physio');
                }
            }
        }

        // re-enable foreign_key_checks if it was enabled at the start of the function
        if ($foreign_key_checks == '1') {
            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }
        }
    }

    /**
     * @param $parameters
     * @return false|string the id of the evaluation or false on failure
     */
    public function create($parameters)
    {
        if (empty($parameters['id_user']) ||
            empty($parameters['id_patient']) ||
            empty($parameters['id_type_eval']) ||
            empty($parameters['date_eval'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $parameters['id_user'] = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['id_patient'] = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['id_type_eval'] = filter_var(
                $parameters['id_type_eval'],
                FILTER_SANITIZE_NUMBER_INT
            ); // eval_etat
            $parameters['date_eval'] = trim($parameters['date_eval']);

            // test physio
            $parameters['auto0'] = trim($parameters['auto0']); // égal à "1" si le test a été réalisé
            $parameters['poids'] = filter_var(
                $parameters['poids'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['taille'] = filter_var(
                $parameters['taille'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['IMC'] = filter_var(
                $parameters['IMC'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['tour_taille'] = filter_var(
                $parameters['tour_taille'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['fcrepos'] = filter_var($parameters['fcrepos'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['satrepos'] = filter_var(
                $parameters['satrepos'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borgrepos'] = filter_var(
                $parameters['borgrepos'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['fcmax'] = filter_var($parameters['fcmax'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_test_physio'] = filter_var($parameters['motif_test_physio'], FILTER_SANITIZE_NUMBER_INT);

            // test Aptitude aerobie
            $parameters['auto1'] = trim($parameters['auto1']); // égal à "1" si le test a été réalisé
            $parameters['dp'] = filter_var($parameters['dp'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc1'] = filter_var($parameters['fc1'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc2'] = filter_var($parameters['fc2'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc3'] = filter_var($parameters['fc3'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc4'] = filter_var($parameters['fc4'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc5'] = filter_var($parameters['fc5'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc6'] = filter_var($parameters['fc6'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc7'] = filter_var($parameters['fc7'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc8'] = filter_var($parameters['fc8'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc9'] = filter_var($parameters['fc9'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['sat1'] = filter_var(
                $parameters['sat1'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat2'] = filter_var(
                $parameters['sat2'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat3'] = filter_var(
                $parameters['sat3'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat4'] = filter_var(
                $parameters['sat4'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat5'] = filter_var(
                $parameters['sat5'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat6'] = filter_var(
                $parameters['sat6'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat7'] = filter_var(
                $parameters['sat7'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat8'] = filter_var(
                $parameters['sat8'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat9'] = filter_var(
                $parameters['sat9'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg1'] = filter_var(
                $parameters['borg1'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg2'] = filter_var(
                $parameters['borg2'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg3'] = filter_var(
                $parameters['borg3'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg4'] = filter_var(
                $parameters['borg4'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg5'] = filter_var(
                $parameters['borg5'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg6'] = filter_var(
                $parameters['borg6'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg7'] = filter_var(
                $parameters['borg7'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg8'] = filter_var(
                $parameters['borg8'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg9'] = filter_var(
                $parameters['borg9'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['com_aa'] = trim($parameters['com_aa']);
            $parameters['motif_apt_aerobie'] = filter_var($parameters['motif_apt_aerobie'], FILTER_SANITIZE_NUMBER_INT);

            // test up and go
            $parameters['auto-up-and-go'] = trim($parameters['auto-up-and-go']); // égal à "1" si le test a été réalisé
            $parameters['com-up-and-go'] = trim($parameters['com-up-and-go']);
            $parameters['duree-up-and-go'] = filter_var($parameters['duree-up-and-go'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif-up-and-go'] = filter_var($parameters['motif-up-and-go'], FILTER_SANITIZE_NUMBER_INT);

            // test Force musculaire membres supérieurs
            $parameters['auto2'] = trim($parameters['auto2']); // égal à "1" si le test a été réalisé
            $parameters['com_fmms'] = trim($parameters['com_fmms']);
            $parameters['main_forte'] = trim($parameters['main_forte']);
            $parameters['mg'] = filter_var($parameters['mg'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $parameters['md'] = filter_var($parameters['md'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $parameters['motif_fmms'] = filter_var($parameters['motif_fmms'], FILTER_SANITIZE_NUMBER_INT);

            // test Equilibre statique
            $parameters['auto3'] = trim($parameters['auto3']); // égal à "1" si le test a été réalisé
            $parameters['com_eq'] = trim($parameters['com_eq']);
            $parameters['pied-dominant'] = trim($parameters['pied-dominant']);
            $parameters['pg'] = filter_var($parameters['pg'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['pd'] = filter_var($parameters['pd'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_eq_stat'] = filter_var($parameters['motif_eq_stat'], FILTER_SANITIZE_NUMBER_INT);

            // test Souplesse
            $parameters['auto4'] = trim($parameters['auto4']); // égal à "1" si le test a été réalisé
            $parameters['com_soupl'] = trim($parameters['com_soupl']);
            $parameters['membre'] = "Majeur au sol";
            $parameters['distance'] = filter_var($parameters['distance'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_soupl'] = filter_var($parameters['motif_soupl'], FILTER_SANITIZE_NUMBER_INT);

            // test Mobilité Scapulo-Humérale
            $parameters['auto5'] = trim($parameters['auto5']); // égal à "1" si le test a été réalisé
            $parameters['com_msh'] = trim($parameters['com_msh']);
            $parameters['mgh'] = filter_var($parameters['mgh'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['mdh'] = filter_var($parameters['mdh'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_mobilite_scapulo_humerale'] = filter_var(
                $parameters['motif_mobilite_scapulo_humerale'],
                FILTER_SANITIZE_NUMBER_INT
            );

            // test Endurance musculaire membres inférieurs
            $parameters['auto6'] = trim($parameters['auto6']); // égal à "1" si le test a été réalisé
            $parameters['com_emmi'] = trim($parameters['com_emmi']);
            $parameters['nb_lever'] = filter_var($parameters['nb_lever'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc30'] = filter_var($parameters['fc30'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['sat30'] = filter_var(
                $parameters['sat30'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg30'] = filter_var(
                $parameters['borg30'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['motif_end_musc_mb_inf'] = filter_var(
                $parameters['motif_end_musc_mb_inf'],
                FILTER_SANITIZE_NUMBER_INT
            );

            $query = '
                INSERT into evaluations (date_eval, id_type_eval, id_patient, id_user)
                VALUES (:date_eval, :id_type_eval, :id_patient, :id_user)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':date_eval', $parameters['date_eval']);
            $stmt->bindValue(':id_type_eval', $parameters['id_type_eval']);
            $stmt->bindValue(':id_patient', $parameters['id_patient']);
            $stmt->bindValue(':id_user', $parameters['id_user']);
            $this->pdo->prepare($query);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into evaluations');
            }
            $id_evaluation = $this->pdo->lastInsertId();

            //Insertion des informations concernant le test physiologique
            if ($parameters['auto0'] == "1") {
                $patient = new Patient($this->pdo);
                $age = $patient->getAge($parameters['id_patient']);
                $fc_max_theo = null;
                if (is_int($age)) {
                    $fc_max_theo = 220 - $age;
                }

                $query = '
                    INSERT into test_physio 
                        (id_evaluation, poids, taille, IMC, tour_taille, fc_repos, saturation_repos, borg_repos,
                         fc_max_mesuree, fc_max_theo) VALUES 
                        (:id_evaluation, :poids, :taille, :IMC, :tour_taille, :fc_repos, :saturation_repos, :borg_repos,
                         :fc_max_mesuree, :fc_max_theo)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':poids', $parameters['poids']);
                $this->bind_value($stmt, ':taille', $parameters['taille']);
                $this->bind_value($stmt, ':IMC', $parameters['IMC']);
                $this->bind_value($stmt, ':tour_taille', $parameters['tour_taille']);
                $this->bind_value($stmt, ':fc_repos', $parameters['fcrepos']);
                $this->bind_value($stmt, ':saturation_repos', $parameters['satrepos']);
                $this->bind_value($stmt, ':borg_repos', $parameters['borgrepos']);
                $this->bind_value($stmt, ':fc_max_mesuree', $parameters['fcmax']);
                $this->bind_value($stmt, ':fc_max_theo', $fc_max_theo);
            } else {
                $query = '
                    INSERT into test_physio (id_evaluation, motif_test_physio)
                    VALUES (:id_evaluation, :motif_test_physio)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':motif_test_physio', $parameters['motif_test_physio']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into test_physio');
            }
            $id_test_physio = $this->pdo->lastInsertId();

            // Insertion des données dans la table - eval_aa
            if ($parameters['auto1'] == "1") {
                $query = '
                    INSERT into eval_apt_aerobie 
                        (id_evaluation, distance_parcourue, fc1, fc2, fc3, fc4, fc5, fc6, fc7, fc8, fc9, sat1, sat2,
                         sat3, sat4, sat5, sat6, sat7, sat8, sat9, borg1, borg2, borg3, borg4, borg5, borg6, borg7,
                         borg8, borg9, com_apt_aerobie)
                    VALUES (:id_evaluation, :distance_parcourue, :fc1, :fc2, :fc3, :fc4, :fc5, :fc6, :fc7, :fc8, :fc9,
                            :sat1, :sat2, :sat3, :sat4, :sat5, :sat6, :sat7, :sat8, :sat9, :borg1, :borg2, :borg3,
                            :borg4, :borg5, :borg6, :borg7, :borg8, :borg9, :com_apt_aerobie)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':distance_parcourue', $parameters['dp']);
                $this->bind_value($stmt, ':com_apt_aerobie', $parameters['com_aa']);

                $this->bind_value($stmt, ':fc1', $parameters['fc1']);
                $this->bind_value($stmt, ':fc2', $parameters['fc2']);
                $this->bind_value($stmt, ':fc3', $parameters['fc3']);
                $this->bind_value($stmt, ':fc4', $parameters['fc4']);
                $this->bind_value($stmt, ':fc5', $parameters['fc5']);
                $this->bind_value($stmt, ':fc6', $parameters['fc6']);
                $this->bind_value($stmt, ':fc7', $parameters['fc7']);
                $this->bind_value($stmt, ':fc8', $parameters['fc8']);
                $this->bind_value($stmt, ':fc9', $parameters['fc9']);

                $this->bind_value($stmt, ':sat1', $parameters['sat1']);
                $this->bind_value($stmt, ':sat2', $parameters['sat2']);
                $this->bind_value($stmt, ':sat3', $parameters['sat3']);
                $this->bind_value($stmt, ':sat4', $parameters['sat4']);
                $this->bind_value($stmt, ':sat5', $parameters['sat5']);
                $this->bind_value($stmt, ':sat6', $parameters['sat6']);
                $this->bind_value($stmt, ':sat7', $parameters['sat7']);
                $this->bind_value($stmt, ':sat8', $parameters['sat8']);
                $this->bind_value($stmt, ':sat9', $parameters['sat9']);

                $this->bind_value($stmt, ':borg1', $parameters['borg1']);
                $this->bind_value($stmt, ':borg2', $parameters['borg2']);
                $this->bind_value($stmt, ':borg3', $parameters['borg3']);
                $this->bind_value($stmt, ':borg4', $parameters['borg4']);
                $this->bind_value($stmt, ':borg5', $parameters['borg5']);
                $this->bind_value($stmt, ':borg6', $parameters['borg6']);
                $this->bind_value($stmt, ':borg7', $parameters['borg7']);
                $this->bind_value($stmt, ':borg8', $parameters['borg8']);
                $this->bind_value($stmt, ':borg9', $parameters['borg9']);
            } else {
                $query = '
                    INSERT into eval_apt_aerobie (id_evaluation, motif_apt_aerobie)
                    VALUES (:id_evaluation, :motif_apt_aerobie)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':motif_apt_aerobie', $parameters['motif_apt_aerobie']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into eval_apt_aerobie');
            }
            $id_eval_apt_aerobie = $this->pdo->lastInsertId();

            // Insertion des données dans la table - eval_up_and_go
            $id_eval_up_and_go = null;
            if ($parameters['auto-up-and-go'] == "1" && $parameters['auto1'] != "1") {
                $query = '
                    INSERT INTO eval_up_and_go (id_evaluation, duree, commentaire)
                    VALUES (:id_evaluation, :duree, :commentaire)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':duree', $parameters['duree-up-and-go']);
                $this->bind_value($stmt, ':commentaire', $parameters['com-up-and-go']);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT into eval_up_and_go (eval faite)');
                }
                $id_eval_up_and_go = $this->pdo->lastInsertId();
            } elseif ($parameters['auto-up-and-go'] != "1" && $parameters['auto1'] != "1") {
                $query = '
                    INSERT INTO eval_up_and_go (id_evaluation, id_motif)
                    VALUES (:id_evaluation, :id_motif)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':id_motif', $parameters['motif-up-and-go']);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT into eval_up_and_go (eval non faite)');
                }
                $id_eval_up_and_go = $this->pdo->lastInsertId();
            }

            // Insertion des données dans la table - eval_fmms
            if ($parameters['auto2'] == "1") {
                $query = '
                    INSERT into eval_force_musc_mb_sup (id_evaluation, mg, md, com_fmms, main_forte)
                    VALUES (:id_evaluation, :mg, :md, :com_fmms, :main_forte)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':com_fmms', $parameters['com_fmms']);
                $this->bind_value($stmt, ':main_forte', $parameters['main_forte']);
                $this->bind_value($stmt, ':mg', $parameters['mg']);
                $this->bind_value($stmt, ':md', $parameters['md']);
            } else {
                $query = '
                    INSERT into eval_force_musc_mb_sup (id_evaluation, motif_fmms)
                    VALUES (:id_evaluation, :motif_fmms)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':motif_fmms', $parameters['motif_fmms']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into eval_force_musc_mb_sup');
            }
            $id_eval_force_musc_mb_sup = $this->pdo->lastInsertId();

            // Insertion des données dans la table - eval_eq
            if ($parameters['auto3'] == "1") {
                $query = '
                    INSERT into eval_eq_stat (id_evaluation, pied_gauche_sol, pied_droit_sol, com_eq_stat,
                                              pied_dominant)
                    VALUES (:id_evaluation,:pied_gauche_sol, :pied_droit_sol, :com_eq_stat, :pied_dominant)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':pied_gauche_sol', $parameters['pg']);
                $this->bind_value($stmt, ':pied_droit_sol', $parameters['pd']);
                $this->bind_value($stmt, ':pied_dominant', $parameters['pied-dominant']);
                $this->bind_value($stmt, ':com_eq_stat', $parameters['com_eq']);
            } else {
                $query = '
                    INSERT into eval_eq_stat (id_evaluation, motif_eq_stat)
                    VALUES (:id_evaluation, :motif_eq_stat)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':motif_eq_stat', $parameters['motif_eq_stat']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into eval_eq_stat');
            }
            $id_eval_eq_stat = $this->pdo->lastInsertId();

            //Insertion des données dans la table - eval_soupl
            if ($parameters['auto4'] == "1") {
                $query = '
                    INSERT into eval_soupl (id_evaluation, distance, membre, com_soupl)
                    VALUES (:id_evaluation, :distance, :membre, :com_soupl)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':distance', $parameters['distance']);
                $this->bind_value($stmt, ':membre', $parameters['membre']);
                $this->bind_value($stmt, ':com_soupl', $parameters['com_soupl']);
            } else {
                $query = '
                    INSERT into eval_soupl (id_evaluation, motif_soupl)
                    VALUES (:id_evaluation, :motif_soupl)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':motif_soupl', $parameters['motif_soupl']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into eval_soupl');
            }
            $id_eval_soupl = $this->pdo->lastInsertId();


            // Insertion des données dans la table - eval_msh
            if ($parameters['auto5'] == "1") {
                $query = '
                INSERT into eval_mobilite_scapulo_humerale (id_evaluation, main_gauche_haut, main_droite_haut,
                                                            com_mobilite_scapulo_humerale)
                VALUES (:id_evaluation, :main_gauche_haut, :main_droite_haut, :com_mobilite_scapulo_humerale)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':main_gauche_haut', $parameters['mgh']);
                $this->bind_value($stmt, ':main_droite_haut', $parameters['mdh']);
                $this->bind_value($stmt, ':com_mobilite_scapulo_humerale', $parameters['com_msh']);
            } else {
                $query = '
                    INSERT into eval_mobilite_scapulo_humerale (id_evaluation, motif_mobilite_scapulo_humerale)
                    VALUES (:id_evaluation, :motif_mobilite_scapulo_humerale) ';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value(
                    $stmt,
                    ':motif_mobilite_scapulo_humerale',
                    $parameters['motif_mobilite_scapulo_humerale']
                );
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into eval_mobilite_scapulo_humerale');
            }
            $id_eval_mobilite_scapulo_humerale = $this->pdo->lastInsertId();

            // Insertion des données dans la table - eval_emmi
            if ($parameters['auto6'] == "1") {
                $query = '
                    INSERT into eval_endurance_musc_mb_inf (id_evaluation, nb_lever, fc30, sat30, borg30,
                                                            com_end_musc_mb_inf)
                    VALUES (:id_evaluation, :nb_lever, :fc30, :sat30, :borg30, :com_end_musc_mb_inf)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':nb_lever', $parameters['nb_lever']);
                $this->bind_value($stmt, ':fc30', $parameters['fc30']);
                $this->bind_value($stmt, ':sat30', $parameters['sat30']);
                $this->bind_value($stmt, ':borg30', $parameters['borg30']);
                $this->bind_value($stmt, ':com_end_musc_mb_inf', $parameters['com_emmi']);
            } else {
                $query = '
                    INSERT into eval_endurance_musc_mb_inf (id_evaluation, motif_end_musc_mb_inf)
                    VALUES (:id_evaluation, :motif_end_musc_mb_inf)';
                $stmt = $this->pdo->prepare($query);

                $this->bind_value($stmt, ':id_evaluation', $id_evaluation);
                $this->bind_value($stmt, ':motif_end_musc_mb_inf', $parameters['motif_end_musc_mb_inf']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT into eval_endurance_musc_mb_inf');
            }
            $id_eval_end_musc_mb_inf = $this->pdo->lastInsertId();

            // update evaluations
            $query = '
                UPDATE evaluations
                SET id_test_physio = :id_test_physio,
                    id_evaluation_apt_aerobie = :id_evaluation_apt_aerobie,
                    id_evaluation_end_musc_mb_inf = :id_evaluation_end_musc_mb_inf,
                    id_evaluation_eq_stat = :id_evaluation_eq_stat,
                    id_evaluation_force_musc_mb_sup = :id_evaluation_force_musc_mb_sup,
                    id_evaluation_mobilite_scapulo_humerale = :id_evaluation_mobilite_scapulo_humerale,
                    id_evaluation_soupl = :id_evaluation_soupl,
                    id_eval_up_and_go = :id_eval_up_and_go
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_evaluation', $id_evaluation);
            $stmt->bindValue(':id_test_physio', $id_test_physio);
            $stmt->bindValue(':id_evaluation_apt_aerobie', $id_eval_apt_aerobie);
            $stmt->bindValue(':id_evaluation_end_musc_mb_inf', $id_eval_end_musc_mb_inf);
            $stmt->bindValue(':id_evaluation_eq_stat', $id_eval_eq_stat);
            $stmt->bindValue(':id_evaluation_force_musc_mb_sup', $id_eval_force_musc_mb_sup);
            $stmt->bindValue(':id_evaluation_mobilite_scapulo_humerale', $id_eval_mobilite_scapulo_humerale);
            $stmt->bindValue(':id_evaluation_soupl', $id_eval_soupl);
            $this->bind_value($stmt, ':id_eval_up_and_go', $id_eval_up_and_go);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE evaluations');
            }

            $this->pdo->commit();
            return $id_evaluation;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $parameters
     * @return bool if the update was sucessful
     */
    function update($parameters): bool
    {
        if (empty($parameters['id_evaluation']) ||
            empty($parameters['id_type_eval']) ||
            empty($parameters['date_eval'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $parameters['id_evaluation'] = filter_var($parameters['id_evaluation'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['date_eval'] = trim($parameters['date_eval']);

            // test physio
            $parameters['auto0'] = trim($parameters['auto0']); // égal à "1" si le test a été réalisé
            $parameters['poids'] = filter_var(
                $parameters['poids'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['taille'] = filter_var(
                $parameters['taille'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['IMC'] = filter_var(
                $parameters['IMC'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['tour_taille'] = filter_var(
                $parameters['tour_taille'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['fcrepos'] = filter_var($parameters['fcrepos'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['satrepos'] = filter_var(
                $parameters['satrepos'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borgrepos'] = filter_var(
                $parameters['borgrepos'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['fcmax'] = filter_var($parameters['fcmax'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_test_physio'] = filter_var($parameters['motif_test_physio'], FILTER_SANITIZE_NUMBER_INT);

            // test Aptitude aerobie
            $parameters['auto1'] = trim($parameters['auto1']); // égal à "1" si le test a été réalisé
            $parameters['dp'] = filter_var($parameters['dp'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc1'] = filter_var($parameters['fc1'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc2'] = filter_var($parameters['fc2'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc3'] = filter_var($parameters['fc3'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc4'] = filter_var($parameters['fc4'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc5'] = filter_var($parameters['fc5'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc6'] = filter_var($parameters['fc6'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc7'] = filter_var($parameters['fc7'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc8'] = filter_var($parameters['fc8'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc9'] = filter_var($parameters['fc9'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['sat1'] = filter_var(
                $parameters['sat1'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat2'] = filter_var(
                $parameters['sat2'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat3'] = filter_var(
                $parameters['sat3'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat4'] = filter_var(
                $parameters['sat4'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat5'] = filter_var(
                $parameters['sat5'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat6'] = filter_var(
                $parameters['sat6'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat7'] = filter_var(
                $parameters['sat7'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat8'] = filter_var(
                $parameters['sat8'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['sat9'] = filter_var(
                $parameters['sat9'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg1'] = filter_var(
                $parameters['borg1'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg2'] = filter_var(
                $parameters['borg2'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg3'] = filter_var(
                $parameters['borg3'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg4'] = filter_var(
                $parameters['borg4'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg5'] = filter_var(
                $parameters['borg5'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg6'] = filter_var(
                $parameters['borg6'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg7'] = filter_var(
                $parameters['borg7'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg8'] = filter_var(
                $parameters['borg8'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg9'] = filter_var(
                $parameters['borg9'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['com_aa'] = trim($parameters['com_aa']);
            $parameters['motif_apt_aerobie'] = filter_var($parameters['motif_apt_aerobie'], FILTER_SANITIZE_NUMBER_INT);

            // test up and go
            $parameters['auto-up-and-go'] = trim($parameters['auto-up-and-go']); // égal à "1" si le test a été réalisé
            $parameters['com-up-and-go'] = trim($parameters['com-up-and-go']);
            $parameters['duree-up-and-go'] = filter_var($parameters['duree-up-and-go'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif-up-and-go'] = filter_var($parameters['motif-up-and-go'], FILTER_SANITIZE_NUMBER_INT);

            // test Force musculaire membres supérieurs
            $parameters['auto2'] = trim($parameters['auto2']); // égal à "1" si le test a été réalisé
            $parameters['com_fmms'] = trim($parameters['com_fmms']);
            $parameters['main_forte'] = trim($parameters['main_forte']);
            $parameters['mg'] = filter_var($parameters['mg'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $parameters['md'] = filter_var($parameters['md'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $parameters['motif_fmms'] = filter_var($parameters['motif_fmms'], FILTER_SANITIZE_NUMBER_INT);

            // test Equilibre statique
            $parameters['auto3'] = trim($parameters['auto3']); // égal à "1" si le test a été réalisé
            $parameters['com_eq'] = trim($parameters['com_eq']);
            $parameters['pied-dominant'] = trim($parameters['pied-dominant']);
            $parameters['pg'] = filter_var($parameters['pg'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['pd'] = filter_var($parameters['pd'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_eq_stat'] = filter_var($parameters['motif_eq_stat'], FILTER_SANITIZE_NUMBER_INT);

            // test Souplesse
            $parameters['auto4'] = trim($parameters['auto4']); // égal à "1" si le test a été réalisé
            $parameters['com_soupl'] = trim($parameters['com_soupl']);
            $parameters['membre'] = "Majeur au sol";
            $parameters['distance'] = filter_var($parameters['distance'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_soupl'] = filter_var($parameters['motif_soupl'], FILTER_SANITIZE_NUMBER_INT);

            // test Mobilité Scapulo-Humérale
            $parameters['auto5'] = trim($parameters['auto5']); // égal à "1" si le test a été réalisé
            $parameters['com_msh'] = trim($parameters['com_msh']);
            $parameters['mgh'] = filter_var($parameters['mgh'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['mdh'] = filter_var($parameters['mdh'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['motif_mobilite_scapulo_humerale'] = filter_var(
                $parameters['motif_mobilite_scapulo_humerale'],
                FILTER_SANITIZE_NUMBER_INT
            );

            // test Endurance musculaire membres inférieurs
            $parameters['auto6'] = trim($parameters['auto6']); // égal à "1" si le test a été réalisé
            $parameters['com_emmi'] = trim($parameters['com_emmi']);
            $parameters['nb_lever'] = filter_var($parameters['nb_lever'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['fc30'] = filter_var($parameters['fc30'], FILTER_SANITIZE_NUMBER_INT);
            $parameters['sat30'] = filter_var(
                $parameters['sat30'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['borg30'] = filter_var(
                $parameters['borg30'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
            $parameters['motif_end_musc_mb_inf'] = filter_var(
                $parameters['motif_end_musc_mb_inf'],
                FILTER_SANITIZE_NUMBER_INT
            );

            //UPDATE des informations générales
            $query = '
                UPDATE evaluations
                SET date_eval    = :date_eval,
                    id_type_eval = :id_type_eval
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
            $stmt->bindValue(':date_eval', $parameters['date_eval']);
            $stmt->bindValue(':id_type_eval', $parameters['id_type_eval']);
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE evaluations SET date_eval = :date_eval');
            }

            $query = '
                SELECT id_eval_up_and_go
                FROM evaluations
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_eval_up_and_go');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_eval_up_and_go = null;
            if ($data) {
                $id_eval_up_and_go = $data['id_eval_up_and_go'];
            }

            // Insertion ou update des données dans la table - eval_up_and_go
            if (!empty($id_eval_up_and_go)) {
                //Update des informations concernant le test UP and GO
                if ($parameters['auto1'] != "1") { // MAJ du test déja réalisé
                    $query = '
                        UPDATE eval_up_and_go
                        SET id_motif    = :id_motif,
                            duree       = :duree,
                            commentaire = :commentaire
                        WHERE id_evaluation = :id_evaluation';
                    $stmt = $this->pdo->prepare($query);

                    if ($parameters['auto-up-and-go'] == "1") {
                        $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                        $this->bind_value($stmt, ':duree', $parameters['duree-up-and-go']);
                        $this->bind_value($stmt, ':commentaire', $parameters['com-up-and-go']);
                        $this->bind_value($stmt, ':id_motif', null);
                    } elseif ($parameters['auto-up-and-go'] != "1") {
                        $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                        $this->bind_value($stmt, ':duree', null);
                        $this->bind_value($stmt, ':commentaire', null);
                        $this->bind_value($stmt, ':id_motif', $parameters['motif-up-and-go']);
                    }
                    if (!$stmt->execute()) {
                        throw new Exception('Error UPDATE eval_up_and_go');
                    }
                } else { // changement: test réalisé -> test non réalisé
                    $query = '
                        UPDATE evaluations
                        SET id_eval_up_and_go = NULL
                        WHERE id_evaluation = :id_evaluation';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                    if (!$stmt->execute()) {
                        throw new Exception('Error UPDATE evaluations SET id_eval_up_and_go = NULL');
                    }

                    $query = '
                        DELETE
                        FROM eval_up_and_go
                        WHERE id_eval_up_and_go = :id_eval_up_and_go';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_eval_up_and_go', $id_eval_up_and_go);
                    if (!$stmt->execute()) {
                        throw new Exception('Error DELETE FROM eval_up_and_go');
                    }
                }
            } else { // test jamais fait
                if ($parameters['auto1'] != "1") {
                    // Insertion des données dans la table - eval_up_and_go
                    $id_eval_up_and_go = null;
                    if ($parameters['auto-up-and-go'] == "1") {
                        $query = '
                            INSERT INTO eval_up_and_go (id_evaluation, duree, commentaire)
                            VALUES (:id_evaluation, :duree, :commentaire)';
                        $stmt = $this->pdo->prepare($query);
                        $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                        $this->bind_value($stmt, ':duree', $parameters['duree-up-and-go']);
                        $this->bind_value($stmt, ':commentaire', $parameters['com-up-and-go']);
                        if (!$stmt->execute()) {
                            throw new Exception('Error INSERT INTO eval_up_and_go');
                        }
                        $id_eval_up_and_go = $this->pdo->lastInsertId();
                    } elseif ($parameters['auto-up-and-go'] != "1") {
                        $query = '
                            INSERT INTO eval_up_and_go (id_evaluation, id_motif)
                            VALUES (:id_evaluation, :id_motif)';
                        $stmt = $this->pdo->prepare($query);
                        $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                        $this->bind_value($stmt, ':id_motif', $parameters['motif-up-and-go']);
                        if (!$stmt->execute()) {
                            throw new Exception('Error INSERT INTO eval_up_and_go');
                        }
                        $id_eval_up_and_go = $this->pdo->lastInsertId();
                    }

                    if ($id_eval_up_and_go) {
                        // Ajout dans evaluations de l'id eval_up_and_go
                        $query = '
                            UPDATE evaluations
                            SET id_eval_up_and_go = :id_eval_up_and_go
                            WHERE id_evaluation = :id_evaluation';
                        $stmt = $this->pdo->prepare($query);
                        $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                        $stmt->bindValue(':id_eval_up_and_go', $id_eval_up_and_go);
                        if (!$stmt->execute()) {
                            throw new Exception('Error UPDATE evaluations SET id_eval_up_and_go = :id_eval_up_and_go');
                        }
                    }
                }
            }

            //UPDATE des informations concernant le test physiologique
            $query = '
                UPDATE test_physio
                SET poids             = :poids,
                    taille            = :taille,
                    IMC               = :IMC,
                    tour_taille       = :tour_taille,
                    fc_repos          = :fc_repos,
                    saturation_repos  = :saturation_repos,
                    borg_repos        = :borg_repos,
                    fc_max_mesuree    = :fc_max_mesuree,
                    motif_test_physio = :motif_test_physio
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto0'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                $this->bind_value($stmt, ':poids', $parameters['poids']);
                $this->bind_value($stmt, ':taille', $parameters['taille']);
                $this->bind_value($stmt, ':IMC', $parameters['IMC']);
                $this->bind_value($stmt, ':tour_taille', $parameters['tour_taille']);
                $this->bind_value($stmt, ':fc_repos', $parameters['fcrepos']);
                $this->bind_value($stmt, ':saturation_repos', $parameters['satrepos']);
                $this->bind_value($stmt, ':borg_repos', $parameters['borgrepos']);
                $this->bind_value($stmt, ':fc_max_mesuree', $parameters['fcmax']);
                $this->bind_value($stmt, ':motif_test_physio', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                $this->bind_value($stmt, ':poids', null);
                $this->bind_value($stmt, ':taille', null);
                $this->bind_value($stmt, ':IMC', null);
                $this->bind_value($stmt, ':tour_taille', null);
                $this->bind_value($stmt, ':fc_repos', null);
                $this->bind_value($stmt, ':saturation_repos', null);
                $this->bind_value($stmt, ':borg_repos', null);
                $this->bind_value($stmt, ':fc_max_mesuree', null);
                $this->bind_value($stmt, ':motif_test_physio', $parameters['motif_test_physio']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE test_physio');
            }

            // UPDATE des données dans la table - eval_aa
            $query = '
                UPDATE eval_apt_aerobie
                SET distance_parcourue = :distance_parcourue,
                    fc1                = :fc1,
                    fc2                = :fc2,
                    fc3                = :fc3,
                    fc4                = :fc4,
                    fc5                = :fc5,
                    fc6                = :fc6,
                    fc7                = :fc7,
                    fc8                = :fc8,
                    fc9                = :fc9,
                    sat1               = :sat1,
                    sat2               = :sat2,
                    sat3               = :sat3,
                    sat4               = :sat4,
                    sat5               = :sat5,
                    sat6               = :sat6,
                    sat7               = :sat7,
                    sat8               = :sat8,
                    sat9               = :sat9,
                    borg1              = :borg1,
                    borg2              = :borg2,
                    borg3              = :borg3,
                    borg4              = :borg4,
                    borg5              = :borg5,
                    borg6              = :borg6,
                    borg7              = :borg7,
                    borg8              = :borg8,
                    borg9              = :borg9,
                    com_apt_aerobie    = :com_apt_aerobie,
                    motif_apt_aerobie  = :motif_apt_aerobie
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto1'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                $this->bind_value($stmt, ':distance_parcourue', $parameters['dp']);
                $this->bind_value($stmt, ':com_apt_aerobie', $parameters['com_aa']);

                $this->bind_value($stmt, ':fc1', $parameters['fc1']);
                $this->bind_value($stmt, ':fc2', $parameters['fc2']);
                $this->bind_value($stmt, ':fc3', $parameters['fc3']);
                $this->bind_value($stmt, ':fc4', $parameters['fc4']);
                $this->bind_value($stmt, ':fc5', $parameters['fc5']);
                $this->bind_value($stmt, ':fc6', $parameters['fc6']);
                $this->bind_value($stmt, ':fc7', $parameters['fc7']);
                $this->bind_value($stmt, ':fc8', $parameters['fc8']);
                $this->bind_value($stmt, ':fc9', $parameters['fc9']);

                $this->bind_value($stmt, ':sat1', $parameters['sat1']);
                $this->bind_value($stmt, ':sat2', $parameters['sat2']);
                $this->bind_value($stmt, ':sat3', $parameters['sat3']);
                $this->bind_value($stmt, ':sat4', $parameters['sat4']);
                $this->bind_value($stmt, ':sat5', $parameters['sat5']);
                $this->bind_value($stmt, ':sat6', $parameters['sat6']);
                $this->bind_value($stmt, ':sat7', $parameters['sat7']);
                $this->bind_value($stmt, ':sat8', $parameters['sat8']);
                $this->bind_value($stmt, ':sat9', $parameters['sat9']);

                $this->bind_value($stmt, ':borg1', $parameters['borg1']);
                $this->bind_value($stmt, ':borg2', $parameters['borg2']);
                $this->bind_value($stmt, ':borg3', $parameters['borg3']);
                $this->bind_value($stmt, ':borg4', $parameters['borg4']);
                $this->bind_value($stmt, ':borg5', $parameters['borg5']);
                $this->bind_value($stmt, ':borg6', $parameters['borg6']);
                $this->bind_value($stmt, ':borg7', $parameters['borg7']);
                $this->bind_value($stmt, ':borg8', $parameters['borg8']);
                $this->bind_value($stmt, ':borg9', $parameters['borg9']);

                $this->bind_value($stmt, ':motif_apt_aerobie', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);
                $this->bind_value($stmt, ':distance_parcourue', null);
                $this->bind_value($stmt, ':com_apt_aerobie', null);

                $this->bind_value($stmt, ':fc1', null);
                $this->bind_value($stmt, ':fc2', null);
                $this->bind_value($stmt, ':fc3', null);
                $this->bind_value($stmt, ':fc4', null);
                $this->bind_value($stmt, ':fc5', null);
                $this->bind_value($stmt, ':fc6', null);
                $this->bind_value($stmt, ':fc7', null);
                $this->bind_value($stmt, ':fc8', null);
                $this->bind_value($stmt, ':fc9', null);

                $this->bind_value($stmt, ':sat1', null);
                $this->bind_value($stmt, ':sat2', null);
                $this->bind_value($stmt, ':sat3', null);
                $this->bind_value($stmt, ':sat4', null);
                $this->bind_value($stmt, ':sat5', null);
                $this->bind_value($stmt, ':sat6', null);
                $this->bind_value($stmt, ':sat7', null);
                $this->bind_value($stmt, ':sat8', null);
                $this->bind_value($stmt, ':sat9', null);

                $this->bind_value($stmt, ':borg1', null);
                $this->bind_value($stmt, ':borg2', null);
                $this->bind_value($stmt, ':borg3', null);
                $this->bind_value($stmt, ':borg4', null);
                $this->bind_value($stmt, ':borg5', null);
                $this->bind_value($stmt, ':borg6', null);
                $this->bind_value($stmt, ':borg7', null);
                $this->bind_value($stmt, ':borg8', null);
                $this->bind_value($stmt, ':borg9', null);

                $this->bind_value($stmt, ':motif_apt_aerobie', $parameters['motif_apt_aerobie']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE eval_apt_aerobie');
            }

            // UPDATE des données dans la table - eval_force_musc_mb_sup
            $query = '
                UPDATE eval_force_musc_mb_sup
                SET mg         = :mg,
                    md         = :md,
                    com_fmms   = :com_fmms,
                    main_forte = :main_forte,
                    motif_fmms = :motif_fmms
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto2'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':mg', $parameters['mg']);
                $this->bind_value($stmt, ':md', $parameters['md']);
                $this->bind_value($stmt, ':com_fmms', $parameters['com_fmms']);
                $this->bind_value($stmt, ':main_forte', $parameters['main_forte']);
                $this->bind_value($stmt, ':motif_fmms', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':mg', null);
                $this->bind_value($stmt, ':md', null);
                $this->bind_value($stmt, ':com_fmms', null);
                $this->bind_value($stmt, ':main_forte', null);
                $this->bind_value($stmt, ':motif_fmms', $parameters['motif_fmms']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE eval_force_musc_mb_sup');
            }

            // UPDATE des données dans la table - eval_eq_stat
            $query = '
                UPDATE eval_eq_stat
                SET pied_dominant   = :pied_dominant,
                    com_eq_stat     = :com_eq_stat,
                    pied_droit_sol  = :pied_droit_sol,
                    pied_gauche_sol = :pied_gauche_sol,
                    motif_eq_stat   = :motif_eq_stat
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto3'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':com_eq_stat', $parameters['com_eq']);
                $this->bind_value($stmt, ':pied_dominant', $parameters['pied-dominant']);
                $this->bind_value($stmt, ':pied_droit_sol', $parameters['pd']);
                $this->bind_value($stmt, ':pied_gauche_sol', $parameters['pg']);
                $this->bind_value($stmt, ':motif_eq_stat', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':com_eq_stat', null);
                $this->bind_value($stmt, ':pied_dominant', null);
                $this->bind_value($stmt, ':pied_droit_sol', null);
                $this->bind_value($stmt, ':pied_gauche_sol', null);
                $this->bind_value($stmt, ':motif_eq_stat', $parameters['motif_eq_stat']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE eval_eq_stat');
            }

            // UPDATE des données dans la table - eval_soupl
            $query = '
                UPDATE eval_soupl
                SET distance    = :distance,
                    com_soupl   = :com_soupl,
                    motif_soupl = :motif_soupl
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto4'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':distance', $parameters['distance']);
                $this->bind_value($stmt, ':com_soupl', $parameters['com_soupl']);
                $this->bind_value($stmt, ':motif_soupl', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':distance', null);
                $this->bind_value($stmt, ':com_soupl', null);
                $this->bind_value($stmt, ':motif_soupl', $parameters['motif_soupl']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE eval_soupl');
            }

            // UPDATE des données dans la table - eval_mobilite_scapulo_humerale
            $query = '
                UPDATE eval_mobilite_scapulo_humerale
                SET main_gauche_haut                = :main_gauche_haut,
                    main_droite_haut                = :main_droite_haut,
                    com_mobilite_scapulo_humerale   = :com_mobilite_scapulo_humerale,
                    motif_mobilite_scapulo_humerale = :motif_mobilite_scapulo_humerale
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto5'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':main_gauche_haut', $parameters['mgh']);
                $this->bind_value($stmt, ':main_droite_haut', $parameters['mdh']);
                $this->bind_value($stmt, ':com_mobilite_scapulo_humerale', $parameters['com_msh']);
                $this->bind_value($stmt, ':motif_mobilite_scapulo_humerale', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':main_gauche_haut', null);
                $this->bind_value($stmt, ':main_droite_haut', null);
                $this->bind_value($stmt, ':com_mobilite_scapulo_humerale', null);
                $this->bind_value(
                    $stmt,
                    ':motif_mobilite_scapulo_humerale',
                    $parameters['motif_mobilite_scapulo_humerale']
                );
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE eval_mobilite_scapulo_humerale');
            }

            // UPDATE des données dans la table - eval_emmi
            $query = '
                UPDATE eval_endurance_musc_mb_inf
                SET nb_lever              = :nb_lever,
                    fc30                  = :fc30,
                    sat30                 = :sat30,
                    borg30                = :borg30,
                    com_end_musc_mb_inf   = :com_end_musc_mb_inf,
                    motif_end_musc_mb_inf = :motif_end_musc_mb_inf
                WHERE id_evaluation = :id_evaluation';
            $stmt = $this->pdo->prepare($query);

            if ($parameters['auto6'] == "1") {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':nb_lever', $parameters['nb_lever']);
                $this->bind_value($stmt, ':fc30', $parameters['fc30']);
                $this->bind_value($stmt, ':sat30', $parameters['sat30']);
                $this->bind_value($stmt, ':borg30', $parameters['borg30']);
                $this->bind_value($stmt, ':com_end_musc_mb_inf', $parameters['com_emmi']);
                $this->bind_value($stmt, ':motif_end_musc_mb_inf', null);
            } else {
                $stmt->bindValue(':id_evaluation', $parameters['id_evaluation']);

                $this->bind_value($stmt, ':nb_lever', null);
                $this->bind_value($stmt, ':fc30', null);
                $this->bind_value($stmt, ':sat30', null);
                $this->bind_value($stmt, ':borg30', null);
                $this->bind_value($stmt, ':com_end_musc_mb_inf', null);
                $this->bind_value($stmt, ':motif_end_musc_mb_inf', $parameters['motif_end_musc_mb_inf']);
            }
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE eval_endurance_musc_mb_inf');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    private function bind_value($stmt, $param, $value)
    {
        if ($value == null || $value == '') {
            $stmt->bindValue($param, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($param, $value);
        }
    }
}