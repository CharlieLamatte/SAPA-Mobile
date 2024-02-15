<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Pathologie
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
     * @return false|array
     */
    public function readOne($id_patient)
    {
        if (empty($id_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = '
            SELECT id_pathologie,
                   cardio,
                   respiratoire,
                   metabolique,
                   osteo_articulaire,
                   psycho_social,
                   neuro,
                   cancero,
                   circulatoire,
                   autre,
                   a_patho_cardio,
                   a_patho_respiratoire,
                   a_patho_metabolique,
                   a_patho_osteo_articulaire,
                   a_patho_psycho_social,
                   a_patho_neuro,
                   a_patho_cancero,
                   a_patho_circulatoire,
                   a_patho_autre
            FROM pathologies
            WHERE id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * required parameters:
     * [
     *     'id_patient' => string,
     *     'a_patho_cardio' => string, ("0" or "1")
     *     'a_patho_respiratoire' => string, ("0" or "1")
     *     'a_patho_metabolique' => string, ("0" or "1")
     *     'a_patho_osteo_articulaire' => string, ("0" or "1")
     *     'a_patho_psycho_social' => string, ("0" or "1")
     *     'a_patho_neuro' => string, ("0" or "1")
     *     'a_patho_cancero' => string, ("0" or "1")
     *     'a_patho_circulatoire' => string, ("0" or "1")
     *     'a_patho_autre' => string, ("0" or "1")
     * ]
     *
     * optional parameters:
     * [
     *     'cardio' => string,
     *     'respiratoire' => string,
     *     'metabolique' => string,
     *     'osteo_articulaire' => string,
     *     'psycho_social' => string,
     *     'neuro' => string,
     *     'cancero' => string,
     *     'circulatoire' => string,
     *     'autre' => string,
     * ]
     *
     * @param array $parameters
     * @return false|string the id or false on failure
     */
    public function create(array $parameters)
    {
        if (empty($parameters['id_patient'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!($parameters['a_patho_cardio'] == "0" || $parameters['a_patho_cardio'] == "1") ||
            !($parameters['a_patho_respiratoire'] == "0" || $parameters['a_patho_respiratoire'] == "1") ||
            !($parameters['a_patho_metabolique'] == "0" || $parameters['a_patho_metabolique'] == "1") ||
            !($parameters['a_patho_osteo_articulaire'] == "0" || $parameters['a_patho_osteo_articulaire'] == "1") ||
            !($parameters['a_patho_psycho_social'] == "0" || $parameters['a_patho_psycho_social'] == "1") ||
            !($parameters['a_patho_neuro'] == "0" || $parameters['a_patho_neuro'] == "1") ||
            !($parameters['a_patho_cancero'] == "0" || $parameters['a_patho_cancero'] == "1") ||
            !($parameters['a_patho_circulatoire'] == "0" || $parameters['a_patho_circulatoire'] == "1") ||
            !($parameters['a_patho_autre'] == "0" || $parameters['a_patho_autre'] == "1")) {
            $this->errorMessage = "Il y a au moins un des paramètres qui est invalide";
            return false;
        }

        try {
            // si le patient n'a pas la pathologie le texte est par défaut un string vide
            if ($parameters['a_patho_cardio'] == "0") {
                $parameters['cardio'] = "";
            }
            if ($parameters['a_patho_respiratoire'] == "0") {
                $parameters['respiratoire'] = "";
            }
            if ($parameters['a_patho_metabolique'] == "0") {
                $parameters['metabolique'] = "";
            }
            if ($parameters['a_patho_osteo_articulaire'] == "0") {
                $parameters['osteo_articulaire'] = "";
            }
            if ($parameters['a_patho_psycho_social'] == "0") {
                $parameters['psycho_social'] = "";
            }
            if ($parameters['a_patho_neuro'] == "0") {
                $parameters['neuro'] = "";
            }
            if ($parameters['a_patho_cancero'] == "0") {
                $parameters['cancero'] = "";
            }
            if ($parameters['a_patho_circulatoire'] == "0") {
                $parameters['circulatoire'] = "";
            }
            if ($parameters['a_patho_autre'] == "0") {
                $parameters['autre'] = "";
            }

            $parameters['cardio'] = empty($parameters['cardio']) ? "" : trim($parameters['cardio']);
            $parameters['respiratoire'] = empty($parameters['respiratoire']) ? "" : trim($parameters['respiratoire']);
            $parameters['metabolique'] = empty($parameters['metabolique']) ? "" : trim($parameters['metabolique']);
            $parameters['osteo_articulaire'] = empty($parameters['osteo_articulaire']) ? "" : trim(
                $parameters['osteo_articulaire']
            );
            $parameters['psycho_social'] = empty($parameters['psycho_social']) ? "" : trim(
                $parameters['psycho_social']
            );
            $parameters['neuro'] = empty($parameters['neuro']) ? "" : trim($parameters['neuro']);
            $parameters['cancero'] = empty($parameters['cancero']) ? "" : trim($parameters['cancero']);
            $parameters['circulatoire'] = empty($parameters['circulatoire']) ? "" : trim($parameters['circulatoire']);
            $parameters['autre'] = empty($parameters['autre']) ? "" : trim($parameters['autre']);

            $this->pdo->beginTransaction();

            $query = '
                INSERT INTO pathologies (cardio, respiratoire, metabolique, osteo_articulaire, psycho_social, neuro,
                                         cancero, circulatoire, autre, a_patho_cardio, a_patho_respiratoire,
                                         a_patho_metabolique, a_patho_osteo_articulaire, a_patho_psycho_social,
                                         a_patho_neuro, a_patho_cancero, a_patho_circulatoire, a_patho_autre, id_patient)
                VALUES (:cardio, :respiratoire, :metabolique, :osteo_articulaire, :psycho_social, :neuro, :cancero, 
                        :circulatoire, :autre, :a_patho_cardio, :a_patho_respiratoire, :a_patho_metabolique,
                        :a_patho_osteo_articulaire, :a_patho_psycho_social, :a_patho_neuro, :a_patho_cancero,
                        :a_patho_circulatoire, :a_patho_autre, :id_patient)';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":cardio", $parameters['cardio']);
            $stmt->bindValue(":respiratoire", $parameters['respiratoire']);
            $stmt->bindValue(":metabolique", $parameters['metabolique']);
            $stmt->bindValue(":osteo_articulaire", $parameters['osteo_articulaire']);
            $stmt->bindValue(":psycho_social", $parameters['psycho_social']);
            $stmt->bindValue(":neuro", $parameters['neuro']);
            $stmt->bindValue(":cancero", $parameters['cancero']);
            $stmt->bindValue(":circulatoire", $parameters['circulatoire']);
            $stmt->bindValue(":autre", $parameters['autre']);

            $stmt->bindValue(":a_patho_cardio", $parameters['a_patho_cardio']);
            $stmt->bindValue(":a_patho_respiratoire", $parameters['a_patho_respiratoire']);
            $stmt->bindValue(":a_patho_metabolique", $parameters['a_patho_metabolique']);
            $stmt->bindValue(":a_patho_osteo_articulaire", $parameters['a_patho_osteo_articulaire']);
            $stmt->bindValue(":a_patho_psycho_social", $parameters['a_patho_psycho_social']);
            $stmt->bindValue(":a_patho_neuro", $parameters['a_patho_neuro']);
            $stmt->bindValue(":a_patho_cancero", $parameters['a_patho_cancero']);
            $stmt->bindValue(":a_patho_circulatoire", $parameters['a_patho_circulatoire']);
            $stmt->bindValue(":a_patho_autre", $parameters['a_patho_autre']);

            $stmt->bindValue(":id_patient", $parameters['id_patient']);

            $stmt->execute();
            $lastInsertId = $this->pdo->lastInsertId();
            $this->pdo->commit();

            return $lastInsertId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage() . $e->getLine() . $e->getCode();
            return false;
        }
    }

    /**
     * required parameters:
     * [
     *     'id_pathologie' => string,
     *     'a_patho_cardio' => string, ("0" or "1")
     *     'a_patho_respiratoire' => string, ("0" or "1")
     *     'a_patho_metabolique' => string, ("0" or "1")
     *     'a_patho_osteo_articulaire' => string, ("0" or "1")
     *     'a_patho_psycho_social' => string, ("0" or "1")
     *     'a_patho_neuro' => string, ("0" or "1")
     *     'a_patho_cancero' => string, ("0" or "1")
     *     'a_patho_circulatoire' => string, ("0" or "1")
     *     'a_patho_autre' => string, ("0" or "1")
     * ]
     *
     * optional parameters:
     * [
     *     'cardio' => string,
     *     'respiratoire' => string,
     *     'metabolique' => string,
     *     'osteo_articulaire' => string,
     *     'psycho_social' => string,
     *     'neuro' => string,
     *     'cancero' => string,
     *     'circulatoire' => string,
     *     'autre' => string,
     * ]
     *
     * @param array $parameters
     * @return bool if the update was successful
     */
    public function update(array $parameters): bool
    {
        if (empty($parameters['id_pathologie'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!($parameters['a_patho_cardio'] == "0" || $parameters['a_patho_cardio'] == "1") ||
            !($parameters['a_patho_respiratoire'] == "0" || $parameters['a_patho_respiratoire'] == "1") ||
            !($parameters['a_patho_metabolique'] == "0" || $parameters['a_patho_metabolique'] == "1") ||
            !($parameters['a_patho_osteo_articulaire'] == "0" || $parameters['a_patho_osteo_articulaire'] == "1") ||
            !($parameters['a_patho_psycho_social'] == "0" || $parameters['a_patho_psycho_social'] == "1") ||
            !($parameters['a_patho_neuro'] == "0" || $parameters['a_patho_neuro'] == "1") ||
            !($parameters['a_patho_cancero'] == "0" || $parameters['a_patho_cancero'] == "1") ||
            !($parameters['a_patho_circulatoire'] == "0" || $parameters['a_patho_circulatoire'] == "1") ||
            !($parameters['a_patho_autre'] == "0" || $parameters['a_patho_autre'] == "1")) {
            $this->errorMessage = "Il y a au moins un des paramètres qui est invalide";
            return false;
        }

        try {
            // si le patient n'a pas la pathologie le texte est par défaut un string vide
            if ($parameters['a_patho_cardio'] == "0") {
                $parameters['cardio'] = "";
            }
            if ($parameters['a_patho_respiratoire'] == "0") {
                $parameters['respiratoire'] = "";
            }
            if ($parameters['a_patho_metabolique'] == "0") {
                $parameters['metabolique'] = "";
            }
            if ($parameters['a_patho_osteo_articulaire'] == "0") {
                $parameters['osteo_articulaire'] = "";
            }
            if ($parameters['a_patho_psycho_social'] == "0") {
                $parameters['psycho_social'] = "";
            }
            if ($parameters['a_patho_neuro'] == "0") {
                $parameters['neuro'] = "";
            }
            if ($parameters['a_patho_cancero'] == "0") {
                $parameters['cancero'] = "";
            }
            if ($parameters['a_patho_circulatoire'] == "0") {
                $parameters['circulatoire'] = "";
            }
            if ($parameters['a_patho_autre'] == "0") {
                $parameters['autre'] = "";
            }

            $parameters['cardio'] = empty($parameters['cardio']) ? "" : trim($parameters['cardio']);
            $parameters['respiratoire'] = empty($parameters['respiratoire']) ? "" : trim($parameters['respiratoire']);
            $parameters['metabolique'] = empty($parameters['metabolique']) ? "" : trim($parameters['metabolique']);
            $parameters['osteo_articulaire'] = empty($parameters['osteo_articulaire']) ? "" : trim(
                $parameters['osteo_articulaire']
            );
            $parameters['psycho_social'] = empty($parameters['psycho_social']) ? "" : trim(
                $parameters['psycho_social']
            );
            $parameters['neuro'] = empty($parameters['neuro']) ? "" : trim($parameters['neuro']);
            $parameters['cancero'] = empty($parameters['cancero']) ? "" : trim($parameters['cancero']);
            $parameters['circulatoire'] = empty($parameters['circulatoire']) ? "" : trim($parameters['circulatoire']);
            $parameters['autre'] = empty($parameters['autre']) ? "" : trim($parameters['autre']);

            $this->pdo->beginTransaction();

            $query = '
                UPDATE pathologies
                SET cardio                    = :cardio,
                    cancero                   = :cancero,
                    respiratoire              = :respiratoire,
                    metabolique               = :metabolique,
                    osteo_articulaire         = :osteo_articulaire,
                    psycho_social             = :psycho_social,
                    neuro                     = :neuro,
                    circulatoire              = :circulatoire,
                    autre                     = :autre,
                    a_patho_cardio            = :a_patho_cardio,
                    a_patho_cancero           = :a_patho_cancero,
                    a_patho_respiratoire      = :a_patho_respiratoire,
                    a_patho_metabolique       = :a_patho_metabolique,
                    a_patho_osteo_articulaire = :a_patho_osteo_articulaire,
                    a_patho_psycho_social     = :a_patho_psycho_social,
                    a_patho_neuro             = :a_patho_neuro,
                    a_patho_circulatoire      = :a_patho_circulatoire,
                    a_patho_autre             = :a_patho_autre
                WHERE id_pathologie = :id_pathologie';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":cardio", $parameters['cardio']);
            $stmt->bindValue(":respiratoire", $parameters['respiratoire']);
            $stmt->bindValue(":metabolique", $parameters['metabolique']);
            $stmt->bindValue(":osteo_articulaire", $parameters['osteo_articulaire']);
            $stmt->bindValue(":psycho_social", $parameters['psycho_social']);
            $stmt->bindValue(":neuro", $parameters['neuro']);
            $stmt->bindValue(":cancero", $parameters['cancero']);
            $stmt->bindValue(":circulatoire", $parameters['circulatoire']);
            $stmt->bindValue(":autre", $parameters['autre']);

            $stmt->bindValue(":a_patho_cardio", $parameters['a_patho_cardio']);
            $stmt->bindValue(":a_patho_respiratoire", $parameters['a_patho_respiratoire']);
            $stmt->bindValue(":a_patho_metabolique", $parameters['a_patho_metabolique']);
            $stmt->bindValue(":a_patho_osteo_articulaire", $parameters['a_patho_osteo_articulaire']);
            $stmt->bindValue(":a_patho_psycho_social", $parameters['a_patho_psycho_social']);
            $stmt->bindValue(":a_patho_neuro", $parameters['a_patho_neuro']);
            $stmt->bindValue(":a_patho_cancero", $parameters['a_patho_cancero']);
            $stmt->bindValue(":a_patho_circulatoire", $parameters['a_patho_circulatoire']);
            $stmt->bindValue(":a_patho_autre", $parameters['a_patho_autre']);

            $stmt->bindValue(":id_pathologie", $parameters['id_pathologie']);

            $stmt->execute();
            $this->pdo->commit();

            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }
}