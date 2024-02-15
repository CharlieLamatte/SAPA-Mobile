<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Objectif
{
    private PDO $pdo;
    private string $errorMessage = '';

    /**
     * Les types de pratiques possibles pour un objectif
     */
    public const TYPE_PRATIQUE = [
        'Autonome',
        'Encadrée',
    ];

    /**
     * Les types possibles pour l'avancement d'un objectif
     */
    public const TYPE_AVANCEMENT = [
        'Partiellement Atteint',
        'Atteint',
        'Non Atteint',
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
     * Creates an objectif
     *
     * required parameters:
     * [
     *     'id_patient' => string,
     *     'nom_objectif' => string,
     *     'date_objectif_patient' => string,
     *     'desc_objectif' => string,
     *     'pratique' => string,
     *     'id_user' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'type_activite' => string,
     *     'duree' => string,
     *     'frequence' => string,
     *     'infos_complementaires' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id or false on failure
     */
    public function create($parameters)
    {
        if (empty($parameters['date_objectif_patient']) ||
            empty($parameters['nom_objectif']) ||
            empty($parameters['pratique']) ||
            empty($parameters['desc_objectif']) ||
            empty($parameters['id_patient']) ||
            empty($parameters['id_user'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!in_array($parameters['pratique'], self::TYPE_PRATIQUE)) {
            $this->errorMessage = "Le type de pratique est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $nom_objectif = trim($parameters['nom_objectif']);
            $date_objectif_patient = trim($parameters['date_objectif_patient']);
            $desc_objectif = trim($parameters['desc_objectif']);
            $pratique = trim($parameters['pratique']);
            $id_user = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);;

            // obligatoire si $parameters['pratique'] == 'Autonome'
            $type_activite = empty($parameters['type_activite']) ? null : trim($parameters['type_activite']);
            $duree = empty($parameters['duree']) ? null : trim($parameters['duree']);
            $frequence = empty($parameters['frequence']) ? null : trim($parameters['frequence']);
            $infos_complementaires = empty($parameters['infos_complementaires']) ? null : trim(
                $parameters['infos_complementaires']
            );

            $query = '
                INSERT INTO objectif_patient(id_patient, date_objectif_patient, nom_objectif, desc_objectif, pratique, 
                                             id_user, type_activite, duree, frequence, infos_complementaires)
                VALUES (:id_patient, :date_objectif_patient, :nom_objectif, :desc_objectif, :pratique, :id_user, 
                        :type_activite, :duree, :frequence, :infos_complementaires)';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_patient', $id_patient);
            $stmt->bindValue(':nom_objectif', $nom_objectif);
            $stmt->bindValue(':date_objectif_patient', $date_objectif_patient);
            $stmt->bindValue(':desc_objectif', $desc_objectif);
            $stmt->bindValue(':pratique', $pratique);
            $stmt->bindValue(':id_user', $id_user);

            if ($pratique == 'Autonome') {
                $stmt->bindValue(':type_activite', $type_activite);
                $stmt->bindValue(':duree', $duree);
                $stmt->bindValue(':frequence', $frequence);
                $stmt->bindValue(':infos_complementaires', $infos_complementaires);
            } else {
                $stmt->bindValue(':type_activite', null, PDO::PARAM_NULL);
                $stmt->bindValue(':duree', null, PDO::PARAM_NULL);
                $stmt->bindValue(':frequence', null, PDO::PARAM_NULL);
                $stmt->bindValue(':infos_complementaires', null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO objectif_patient');
            }

            $id_obj_patient = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $id_obj_patient;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates an objectif
     *
     * required parameters:
     * [
     *     'id_obj_patient' => string,
     *     'nom_objectif' => string,
     *     'date_objectif_patient' => string,
     *     'desc_objectif' => string,
     *     'pratique' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'type_activite' => string,
     *     'duree' => string,
     *     'frequence' => string,
     *     'infos_complementaires' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id or false on failure
     */
    public function update($parameters)
    {
        if (empty($parameters['id_obj_patient']) ||
            empty($parameters['date_objectif_patient']) ||
            empty($parameters['nom_objectif']) ||
            empty($parameters['pratique']) ||
            empty($parameters['desc_objectif'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!in_array($parameters['pratique'], self::TYPE_PRATIQUE)) {
            $this->errorMessage = "Le type de pratique est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_obj_patient = filter_var($parameters['id_obj_patient'], FILTER_SANITIZE_NUMBER_INT);
            $nom_objectif = trim($parameters['nom_objectif']);
            $date_objectif_patient = trim($parameters['date_objectif_patient']);
            $desc_objectif = trim($parameters['desc_objectif']);
            $pratique = trim($parameters['pratique']);

            // obligatoire si $parameters['pratique'] == 'Autonome'
            $type_activite = empty($parameters['type_activite']) ? null : trim($parameters['type_activite']);
            $duree = empty($parameters['duree']) ? null : trim($parameters['duree']);
            $frequence = empty($parameters['frequence']) ? null : trim($parameters['frequence']);
            $infos_complementaires = empty($parameters['infos_complementaires']) ? null : trim(
                $parameters['infos_complementaires']
            );

            $query = '
                UPDATE objectif_patient
                SET nom_objectif          = :nom_objectif,
                    date_objectif_patient = :date_objectif_patient,
                    desc_objectif         = :desc_objectif,
                    pratique              = :pratique,
                    type_activite         = :type_activite,
                    duree                 = :duree,
                    frequence             = :frequence,
                    infos_complementaires = :infos_complementaires
                WHERE id_obj_patient = :id_obj_patient';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_obj_patient', $id_obj_patient);
            $stmt->bindValue(':nom_objectif', $nom_objectif);
            $stmt->bindValue(':date_objectif_patient', $date_objectif_patient);
            $stmt->bindValue(':desc_objectif', $desc_objectif);
            $stmt->bindValue(':pratique', $pratique);

            if ($pratique == 'Autonome') {
                $stmt->bindValue(':type_activite', $type_activite);
                $stmt->bindValue(':duree', $duree);
                $stmt->bindValue(':frequence', $frequence);
                $stmt->bindValue(':infos_complementaires', $infos_complementaires);
            } else {
                $stmt->bindValue(':type_activite', null, PDO::PARAM_NULL);
                $stmt->bindValue(':duree', null, PDO::PARAM_NULL);
                $stmt->bindValue(':frequence', null, PDO::PARAM_NULL);
                $stmt->bindValue(':infos_complementaires', null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE fin_orientation');
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
     * Creates an avancement
     *
     * required parameters:
     * [
     *     'date_avancement' => string,
     *     'atteinte' => string,
     *     'id_obj_patient' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'commentaires' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id or false on failure
     */
    public function createAvancement($parameters)
    {
        if (empty($parameters['date_avancement']) ||
            empty($parameters['atteinte']) ||
            empty($parameters['id_obj_patient'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!in_array($parameters['atteinte'], self::TYPE_AVANCEMENT)) {
            $this->errorMessage = "Le type d'avancement est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_obj_patient = filter_var($parameters['id_obj_patient'], FILTER_SANITIZE_NUMBER_INT);
            $atteinte = trim($parameters['atteinte']);
            $date_avancement = trim($parameters['date_avancement']);

            // obligatoire
            $commentaires = empty($parameters['commentaires']) ? "" : trim($parameters['commentaires']);

            // insertion dans avancement_obj
            $query = '
                INSERT INTO avancement_obj (date_avancement, atteinte, commentaires, id_obj_patient)
                VALUES (:date_avancement, :atteinte, :commentaires, :id_obj_patient)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':date_avancement', $date_avancement);
            $stmt->bindValue(':atteinte', $atteinte);
            $stmt->bindValue(':commentaires', $commentaires);
            $stmt->bindValue(':id_obj_patient', $id_obj_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO avancement_obj');
            }
            $id_avancement_obj = $this->pdo->lastInsertId();

            // si l'avancement de l'objectif est atteint, l'objectif est terminé
            if ($atteinte == 'Atteint') {
                $query = '
                    UPDATE objectif_patient
                    SET termine = :termine
                    WHERE id_obj_patient = :id_obj_patient';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_obj_patient', $id_obj_patient);
                $stmt->bindValue(':termine', '1');

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE objectif_patient');
                }
            }

            $this->pdo->commit();
            return $id_avancement_obj;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id_obj_patient
     * @return bool if the deletion was successful
     */
    public function delete($id_obj_patient)
    {
        if (empty($id_obj_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // suppression de tous les avancements de l'objectif
            $query = '
                DELETE
                FROM avancement_obj
                WHERE id_obj_patient = :id_obj_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_obj_patient', $id_obj_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM avancement_obj');
            }

            // suppression de l'objectif
            $query = '
                DELETE
                FROM objectif_patient
                WHERE id_obj_patient = :id_obj_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_obj_patient', $id_obj_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM objectif_patient');
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
     * @param $id_avancement_obj
     * @return bool if the deletion was successful
     */
    function deleteAvancement($id_avancement_obj)
    {
        if (empty($id_avancement_obj)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // on récupère le statut de l'avancement que l'on veut supprimer et l'id de l'objectif concerné
            $query = '
                SELECT atteinte, id_obj_patient 
                FROM avancement_obj
                WHERE id_avancement_obj = :id_avancement_obj';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_avancement_obj', $id_avancement_obj);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT atteinte');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($data)) {
                $this->errorMessage = "L'avancement n'existe pas";
                return false;
            }

            $atteinte = $data['atteinte'];
            $id_obj_patient = $data['id_obj_patient'];

            // suppression de l'avancement
            $query = '
                DELETE
                FROM avancement_obj
                WHERE id_avancement_obj = :id_avancement_obj';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_avancement_obj', $id_avancement_obj);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM avancement_obj');
            }

            if ($atteinte == 'Atteint') {
                // si l'on supprime l'avancement atteint, l'objectif n'est plus terminé
                $query = '
                    UPDATE objectif_patient
                    SET termine = :termine
                    WHERE id_obj_patient = :id_obj_patient';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_obj_patient', $id_obj_patient);
                $stmt->bindValue(':termine', '0');

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE objectif_patient');
                }
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
     * @param $id_obj_patient
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_obj_patient)
    {
        if (empty($id_obj_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = "
            SELECT id_obj_patient,
                   id_patient,
                   date_objectif_patient,
                   DATE_FORMAT(date_objectif_patient, '%d/%m/%Y') as date_objectif_patient_formated,
                   nom_objectif,
                   desc_objectif,
                   pratique,
                   termine,
                   id_user,
                   type_activite,
                   duree,
                   frequence,
                   infos_complementaires
            FROM objectif_patient
            WHERE id_obj_patient = :id_obj_patient";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_obj_patient', $id_obj_patient);

        if (!$stmt->execute()) {
            $this->errorMessage = "Erreur lors de l'exécution de la requête";
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_avancement_obj
     * @return false|array Return an associative array or false on failure
     */
    public function readOneAvancement($id_avancement_obj)
    {
        if (empty($id_avancement_obj)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = "
            SELECT id_avancement_obj,
                   DATE_FORMAT(date_avancement, '%d/%m/%Y') as date_avancement,
                   atteinte,
                   commentaires,
                   id_obj_patient
            FROM avancement_obj
            WHERE id_avancement_obj = :id_avancement_obj";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_avancement_obj', $id_avancement_obj);

        if (!$stmt->execute()) {
            $this->errorMessage = "Erreur lors de l'exécution de la requête";
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Return tous les objectifs d'un patient
     *
     * @param $id_patient
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAll($id_patient, $filter_pratique = null)
    {
        if (empty($id_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!empty($filter_pratique) && !in_array($filter_pratique, self::TYPE_PRATIQUE)) {
            $this->errorMessage = "Le type de pratique est invalide";
            return false;
        }

        $query = "
            SELECT id_obj_patient,
                   id_patient,
                   date_objectif_patient,
                   DATE_FORMAT(date_objectif_patient, '%d/%m/%Y') as date_objectif_patient_formated,
                   nom_objectif,
                   desc_objectif,
                   pratique,
                   termine,
                   id_user,
                   type_activite,
                   duree,
                   frequence,
                   infos_complementaires
            FROM objectif_patient
            WHERE id_patient = :id_patient ";

        if (!empty($filter_pratique)) {
            $query .= " AND pratique LIKE :pratique ";
        }

        $query .= " ORDER BY objectif_patient.date_objectif_patient";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        if (!empty($filter_pratique)) {
            $stmt->bindValue(':pratique', $filter_pratique);
        }
        if (!$stmt->execute()) {
            $this->errorMessage = "Erreur lors de l'exécution de la requête";
            return false;
        }

        $objectifs = [];
        while ($objectif_item = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // recupération des avancements de l'objectif
            $query_avanc = "
                SELECT id_avancement_obj,
                       DATE_FORMAT(date_avancement, '%d/%m/%Y') as date_avancement,
                       atteinte,
                       commentaires,
                       id_obj_patient
                FROM avancement_obj
                WHERE id_obj_patient = :id_obj_patient
                ORDER BY date_avancement";
            $stmt_avanc = $this->pdo->prepare($query_avanc);
            $stmt_avanc->bindValue(':id_obj_patient', $objectif_item['id_obj_patient']);
            if (!$stmt_avanc->execute()) {
                $this->errorMessage = "Erreur lors de l'exécution de la requête";
                return false;
            }

            $objectif_item['avancements'] = [];
            while ($row_avanc = $stmt_avanc->fetch(PDO::FETCH_ASSOC)) {
                $objectif_item['avancements'][] = $row_avanc;
            }

            $objectifs[] = $objectif_item;
        }

        return $objectifs;
    }

    /**
     * Return tous les avancements d'un objectif
     *
     * @param $id_obj_patient
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAllAvancement($id_obj_patient)
    {
        if (empty($id_obj_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = "
            SELECT id_avancement_obj,
                   DATE_FORMAT(date_avancement, '%d/%m/%Y') as date_avancement,
                   atteinte,
                   commentaires,
                   id_obj_patient
            FROM avancement_obj
            WHERE id_obj_patient = :id_obj_patient";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_obj_patient', $id_obj_patient);
        if (!$stmt->execute()) {
            $this->errorMessage = "Erreur lors de l'exécution de la requête";
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}