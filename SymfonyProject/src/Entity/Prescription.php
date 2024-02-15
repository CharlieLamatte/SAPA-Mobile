<?php

namespace Sportsante86\Sapa\Model;

 ;

class Prescription
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
     * @param $id_patient
     * @return array|false
     */
    public function readAllPatient($id_patient)
    {
        $query = '
            SELECT id_prescription,
                   prescription_ap,
                   prescription_medecin,
                   prescription_date,
                   fc_max,
                   remarque,
                   act_a_privilegier,
                   intensite_recommandee,
                   efforts_non,
                   articulation_non,
                   action_non,
                   arret_si
            FROM prescription
            WHERE id_patient = :id_patient
            ORDER BY prescription_date DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id_patient", $id_patient);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}