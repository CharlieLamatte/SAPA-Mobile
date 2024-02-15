<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class SettingsSynthese
{
    private PDO $pdo;
    private string $errorMessage;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->errorMessage = '';
    }

    /**
     * Creates settings
     *
     * required parameters :
     * [
     *     'id_structure' => string,
     * ]
     *
     * optional parameters :
     * [
     *     'introduction_medecin' => string,
     *     'introduction_beneficiaire' => string,
     *     'remerciements_medecin' => string,
     *     'remerciements_beneficiaire' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the settings or false on failure
     */
    public function create($parameters)
    {
        if (empty($parameters['id_structure'])) {
            $this->errorMessage = "Error: Missing required parameters";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_structure = filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $introduction_medecin = isset($parameters['introduction_medecin']) ?
                trim($parameters['introduction_medecin']) :
                null;
            $introduction_beneficiaire = isset($parameters['introduction_beneficiaire']) ?
                trim($parameters['introduction_beneficiaire']) :
                null;
            $remerciements_medecin = isset($parameters['remerciements_medecin']) ?
                trim($parameters['remerciements_medecin']) :
                null;
            $remerciements_beneficiaire = isset($parameters['remerciements_beneficiaire']) ?
                trim($parameters['remerciements_beneficiaire']) :
                null;

            // check if settings already exists for the structure
            $query = '
                SELECT count(*) as settings_count
                FROM settings_synthese
                WHERE id_structure = :id_structure';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure', $id_structure);

            if (!$statement->execute()) {
                throw new Exception('Error: SELECT count(*) as settings_count');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (intval($data['settings_count'] > 0)) {
                throw new Exception('Error: The structure id=' . $id_structure . ' already have settigns');
            }

            $query = '
                INSERT INTO settings_synthese (introduction_medecin, introduction_beneficiaire, remerciements_medecin,
                                               remerciements_beneficiaire, id_structure)
                VALUES (:introduction_medecin, :introduction_beneficiaire, :remerciements_medecin, :remerciements_beneficiaire,
                        :id_structure)';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure', $id_structure);
            if (is_null($introduction_medecin)) {
                $statement->bindValue(':introduction_medecin', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':introduction_medecin', $introduction_medecin);
            }
            if (is_null($introduction_beneficiaire)) {
                $statement->bindValue(':introduction_beneficiaire', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':introduction_beneficiaire', $introduction_beneficiaire);
            }
            if (is_null($remerciements_medecin)) {
                $statement->bindValue(':remerciements_medecin', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':remerciements_medecin', $remerciements_medecin);
            }
            if (is_null($remerciements_beneficiaire)) {
                $statement->bindValue(':remerciements_beneficiaire', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':remerciements_beneficiaire', $remerciements_beneficiaire);
            }

            if (!$statement->execute()) {
                throw new Exception('Error: INSERT INTO settings_synthese');
            }
            $id_settings_synthese = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $id_settings_synthese;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates settings
     *
     * required parameters :
     * [
     *     'id_settings_synthese' => string,
     * ]
     *
     * optional parameters :
     * [
     *     'introduction_medecin' => string,
     *     'introduction_beneficiaire' => string,
     *     'remerciements_medecin' => string,
     *     'remerciements_beneficiaire' => string,
     * ]
     *
     * @param $parameters
     * @return bool if the settings were successfully updated
     */
    public function update($parameters): bool
    {
        if (empty($parameters['id_settings_synthese'])) {
            $this->errorMessage = "Error: Missing required parameters";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_settings_synthese = filter_var($parameters['id_settings_synthese'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $introduction_medecin = isset($parameters['introduction_medecin']) ?
                trim($parameters['introduction_medecin']) :
                null;
            $introduction_beneficiaire = isset($parameters['introduction_beneficiaire']) ?
                trim($parameters['introduction_beneficiaire']) :
                null;
            $remerciements_medecin = isset($parameters['remerciements_medecin']) ?
                trim($parameters['remerciements_medecin']) :
                null;
            $remerciements_beneficiaire = isset($parameters['remerciements_beneficiaire']) ?
                trim($parameters['remerciements_beneficiaire']) :
                null;

            $query = '
                    UPDATE settings_synthese
                    SET introduction_medecin       = :introduction_medecin,
                        introduction_beneficiaire  = :introduction_beneficiaire,
                        remerciements_medecin      = :remerciements_medecin,
                        remerciements_beneficiaire = :remerciements_beneficiaire
                    WHERE id_settings_synthese = :id_settings_synthese';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_settings_synthese', $id_settings_synthese);
            if (is_null($introduction_medecin)) {
                $statement->bindValue(':introduction_medecin', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':introduction_medecin', $introduction_medecin);
            }
            if (is_null($introduction_beneficiaire)) {
                $statement->bindValue(':introduction_beneficiaire', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':introduction_beneficiaire', $introduction_beneficiaire);
            }
            if (is_null($remerciements_medecin)) {
                $statement->bindValue(':remerciements_medecin', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':remerciements_medecin', $remerciements_medecin);
            }
            if (is_null($remerciements_beneficiaire)) {
                $statement->bindValue(':remerciements_beneficiaire', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':remerciements_beneficiaire', $remerciements_beneficiaire);
            }

            if (!$statement->execute()) {
                throw new Exception('Error: UPDATE settings_synthese');
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
     * @return string the error message of the last operation
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param $id_settings_synthese
     * @return false|array
     */
    public function readOne($id_settings_synthese)
    {
        if (empty($id_settings_synthese)) {
            return false;
        }

        $query = '
            SELECT id_settings_synthese,
                   introduction_medecin,
                   introduction_beneficiaire,
                   remerciements_medecin,
                   remerciements_beneficiaire,
                   id_structure
            FROM settings_synthese
            WHERE id_settings_synthese = :id_settings_synthese';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_settings_synthese', $id_settings_synthese);
        if (!$statement->execute()) {
            return false;
        }
        if ($statement->rowCount() == 0) {
            return false;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param $id_structure
     * @return null|string
     */
    public function getIdSettingsSynthese($id_structure)
    {
        if (empty($id_structure)) {
            return null;
        }

        $query = '
            SELECT id_settings_synthese
            FROM settings_synthese
            WHERE id_structure = :id_structure';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_structure', $id_structure);
        if (!$statement->execute()) {
            return null;
        }
        if ($statement->rowCount() == 0) {
            return null;
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row['id_settings_synthese'] ?? null;
    }
}