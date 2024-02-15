<?php

/**
 * Cette permet d'enregistrer les activités qui ont lieu et
 * la récupération de toutes les activités
 */

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class JournalAcces
{
    private PDO $pdo;
    private string $errorMessage = '';

    /**
     * @var array|string[] Les types d'action possibles
     */
    private array $type_actions_possibles = [
        'create',
        'read',
        'modify',
        'delete'
    ];

    /**
     * @var array|string[] Les types de cible possibles
     */
    private array $type_cible_possibles = [
        'ald',
        'creneau',
        'intervenant',
        'medecin',
        'patient',
        'structure',
        'user',
        'synthese',
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
     * Creates a journal_activite
     *
     * required parameters :
     * [
     *     'nom_acteur' => string,
     *     'type_action' => string,
     *     'type_cible' => string,
     *     'nom_cible' => string,
     * ]
     *
     * optional parameters :
     * [
     *     'id_user_acteur' => string,
     *     'id_cible' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the journal_activite or false on failure
     */
    public function create($parameters)
    {
        // check required parameters
        if (empty($parameters['nom_acteur']) ||
            empty($parameters['type_action']) ||
            empty($parameters['type_cible']) ||
            empty($parameters['nom_cible'])) {
            return false;
        }

        // check type_action
        $parameters['type_action'] = strtolower($parameters['type_action']);
        if (!in_array($parameters['type_action'], $this->type_actions_possibles)) {
            return false;
        }

        // check type_cible
        $parameters['type_cible'] = strtolower($parameters['type_cible']);
        if (!in_array($parameters['type_cible'], $this->type_cible_possibles)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                INSERT INTO journal_activite (id_user_acteur, nom_acteur, type_action, type_cible, nom_cible,
                                              id_cible, date_action)
                VALUES (:id_user_acteur, :nom_acteur, :type_action, :type_cible, :nom_cible, :id_cible, NOW())';

            $stmt = $this->pdo->prepare($query);
            if (!empty($parameters['id_user_acteur'])) {
                $stmt->bindValue(':id_user_acteur', $parameters['id_user_acteur']);
            } else {
                $stmt->bindValue(':id_user_acteur', null, PDO::PARAM_NULL);
            }
            if (!empty($parameters['id_cible'])) {
                $stmt->bindValue(':id_cible', $parameters['id_cible']);
            } else {
                $stmt->bindValue(':id_cible', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(':nom_acteur', $parameters['nom_acteur']);
            $stmt->bindValue(':type_action', $parameters['type_action']);
            $stmt->bindValue(':type_cible', $parameters['type_cible']);
            $stmt->bindValue(':nom_cible', $parameters['nom_cible']);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO journal_activite');
            }

            $id_journal_activite = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id_journal_activite;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @return array|false Returns an array of associative arrays or false on failure
     */
    public function readAll()
    {
        $query = '
            SELECT id_journal_activite,
                   id_user_acteur,
                   nom_acteur,
                   type_action,
                   type_cible,
                   nom_cible,
                   id_cible,
                   date_action
            FROM journal_activite';
        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute()) {
            return false;
        }

        $all_activities = [];
        while ($activity = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $all_activities[] = $activity;
        }

        return $all_activities;
    }
}