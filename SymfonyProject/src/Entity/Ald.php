<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Ald
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
     * @return array|false tous les alds possibles ou false en cas d'échec
     */
    public function readAll()
    {
        $query = '
            SELECT pathologie_ou_etat.id_pathologie_ou_etat,
                   pathologie_ou_etat.id_type_pathologie,
                   pathologie_ou_etat.nom as nom_pathologie_ou_etat,
                   tp.nom                 as nom_type_pathologie
            FROM pathologie_ou_etat
                     JOIN type_pathologie tp on pathologie_ou_etat.id_type_pathologie = tp.id_type_pathologie
            WHERE pathologie_ou_etat.id_pathologie_ou_etat > 0
            ORDER BY id_type_pathologie';
        $stmt = $this->pdo->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_patient
     * @return array|false tous les alds d'un patient ou false en cas d'échec
     */
    public function readAllPatient($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT pathologie_ou_etat.id_pathologie_ou_etat,
                   pathologie_ou_etat.id_type_pathologie,
                   pathologie_ou_etat.nom as nom_pathologie_ou_etat,
                   tp.nom                 as nom_type_pathologie
            FROM pathologie_ou_etat
                     JOIN type_pathologie tp on pathologie_ou_etat.id_type_pathologie = tp.id_type_pathologie
                     JOIN souffre_de sd on pathologie_ou_etat.id_pathologie_ou_etat = sd.id_pathologie_ou_etat
            WHERE sd.id_patient = :id_patient
            ORDER BY id_type_pathologie';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_patient
     * @return false|string les noms de toutes alds d'un patient séparées par ", " ou false en cas d'échec
     */
    public function readAllPatientAsString($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT pathologie_ou_etat.nom
            FROM pathologie_ou_etat
                     JOIN souffre_de sd on pathologie_ou_etat.id_pathologie_ou_etat = sd.id_pathologie_ou_etat
            WHERE sd.id_patient = :id_patient
            ORDER BY pathologie_ou_etat.id_pathologie_ou_etat';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);

        $stmt->execute();

        $result = "";
        $alds_array = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if ($alds_array) {
            $result = implode(", ", $alds_array);
        }

        return $result;
    }

    public function update($parameters)
    {
        if (empty($parameters['id_patient']) ||
            is_null($parameters['liste_alds'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // on delete toutes les alds du patient
            $query = '
                DELETE FROM souffre_de
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":id_patient", $parameters['id_patient']);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM souffre_de');
            }

            // on ajoute les nouvelles ald du patient
            if (is_array($parameters['liste_alds'])) {
                foreach ($parameters['liste_alds'] as $id_pathologie_ou_etat) {
                    $query = '
                        INSERT INTO souffre_de (id_patient, id_pathologie_ou_etat)
                        VALUES (:id_patient, :id_pathologie_ou_etat)';
                    $stmt = $this->pdo->prepare($query);

                    $stmt->bindValue(':id_patient', $parameters['id_patient']);
                    $stmt->bindValue(':id_pathologie_ou_etat', $id_pathologie_ou_etat);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO souffre_de');
                    }
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }
}