<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Observation
{
    private PDO $pdo;
    private string $errorMessage = "";

    public const TYPE_OBSERVATION_SANTE = 1;
    public const TYPE_OBSERVATION_PROGRESSION = 2;

    /**
     * Les types d'observations possibles
     */
    private const TYPES_OBSERVATION = [
        self::TYPE_OBSERVATION_SANTE,
        self::TYPE_OBSERVATION_PROGRESSION,
    ];

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
     * @param $parameters
     * @return false|string
     */
    public function create($parameters)
    {
        if (!$this->checkParameters($parameters)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!in_array($parameters['id_type_observation'], self::TYPES_OBSERVATION)) {
            $this->errorMessage = "Le type d'observation est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                INSERT INTO observation
                    (observation, date_observation, id_patient, id_user, id_type_observation)
                VALUES (:observation, :date_observation, :id_patient, :id_user, :id_type_observation)';
            $statement = $this->pdo->prepare($query);

            $date_observation = date('y-m-d H:i:s');

            $statement->bindValue(':observation', trim($parameters['observation']));
            $statement->bindValue(':date_observation', $date_observation);
            $statement->bindValue(':id_patient', $parameters['id_patient']);
            $statement->bindValue(':id_user', $parameters['id_user']);
            $statement->bindValue(':id_type_observation', $parameters['id_type_observation']);

            if (!$statement->execute()) {
                throw new Exception("Error: INSERT INTO observation");
            }

            $lastInsertId = $this->pdo->lastInsertId();
            $this->pdo->commit();

            return $lastInsertId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id
     * @return bool if the deletion was successful
     */
    public function delete($id): bool
    {
        if (empty($id)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                DELETE FROM observation
                WHERE id_observation = :id_observation';
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id_observation', $id);

            if (!$statement->execute()) {
                throw new Exception("Error: DELETE FROM observation");
            }

            $this->pdo->commit();

            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function update($parameters)
    {
        // TODO
    }

    /**
     * @param $id_observation
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_observation)
    {
        if (empty($id_observation)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = '
            SELECT o.id_observation,
                   o.observation,
                   DATE_FORMAT(o.date_observation, \'%d/%m/%Y\') as date_observation,
                   o.id_patient,
                   o.id_user,
                   o.id_type_observation,
                   t.nom,
                   c.nom_coordonnees,
                   c.prenom_coordonnees
            FROM observation o
            JOIN type_observation t on t.id_type_observation = o.id_type_observation
            JOIN coordonnees c on o.id_user = c.id_user
            WHERE id_observation = :id_observation';
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id_observation', $id_observation);

        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_patient
     * @param $id_type_observation
     * @return array|false Returns an array of associative arrays or false on failure
     */
    public function readAll($id_patient, $id_type_observation = null)
    {
        if (empty($id_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $is_id_type_observation_present = !empty($id_type_observation);

        $query = "
            SELECT o.id_observation,
                   o.observation,
                   DATE_FORMAT(o.date_observation, '%d/%m/%Y') as date_observation,
                   o.date_observation as complete_date_observation,
                   o.id_patient,
                   o.id_user,
                   o.id_type_observation,
                   t.nom,
                   c.nom_coordonnees,
                   c.prenom_coordonnees
            FROM observation o
                     JOIN type_observation t on t.id_type_observation = o.id_type_observation
                     JOIN coordonnees c on o.id_user = c.id_user
            WHERE o.id_patient = :id_patient ";
        if ($is_id_type_observation_present) {
            $query .= ' AND o.id_type_observation = :id_type_observation ';
        }
        $query .= ' ORDER BY complete_date_observation';
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id_patient', $id_patient);
        if ($is_id_type_observation_present) {
            $statement->bindValue(':id_type_observation', $id_type_observation);
        }

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function checkParameters($parameters)
    {
        return !empty($parameters['observation']) &&
            !empty($parameters['id_patient']) &&
            !empty($parameters['id_user']) &&
            !empty($parameters['id_type_observation']);
    }
}