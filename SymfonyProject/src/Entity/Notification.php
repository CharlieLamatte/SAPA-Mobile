<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Notification
{
    private PDO $pdo;
    private string $errorMessage = '';

    public const TYPE_NOTIFICATION_AJOUT = 1;
    public const TYPE_NOTIFICATION_CHANGEMENT_EVALUATEUR = 2;
    public const TYPE_NOTIFICATION_CHANGEMENT_ANTENNE = 3;
    public const TYPE_NOTIFICATION_AFFECTATION_CRENEAU = 4;
    public const TYPE_NOTIFICATION_EVALUATION = 5;
    public const TYPE_NOTIFICATION_RETARD = 6;
    public const TYPE_NOTIFICATION_ANNULATION = 7;
    public const TYPE_MAJ_SAPA = 8;
    public const TYPE_NOTIFICATION_ARCHIVAGE = 9;
    public const TYPE_NOTIFICATION_PARTAGE = 10;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Creates a notification
     *
     * required parameters :
     * [
     *     'id_envoyeur' => string,
     *     'id_destinataire' => string,
     *     'id_type_notification' => string,
     *     'text_notification' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the notification or false on failure
     */
    public function create($parameters)
    {
        if (empty($parameters['id_envoyeur']) ||
            empty($parameters['id_destinataire']) ||
            empty($parameters['id_type_notification']) ||
            empty($parameters['text_notification'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_envoyeur = filter_var($parameters['id_envoyeur'], FILTER_SANITIZE_NUMBER_INT);
            $id_destinataire = filter_var($parameters['id_destinataire'], FILTER_SANITIZE_NUMBER_INT);
            $id_type_notification = filter_var($parameters['id_type_notification'], FILTER_SANITIZE_NUMBER_INT);
            $text_notification = $parameters['text_notification'];

            $id_notification = $this->insertNotification([
                'id_envoyeur' => $id_envoyeur,
                'id_destinataire' => $id_destinataire,
                'id_type_notification' => $id_type_notification,
                'text_notification' => $text_notification,
            ]);

            $this->pdo->commit();
            return $id_notification;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Creates a MAJ notification
     *
     * required parameters :
     * [
     *     'id_envoyeur' => string,
     *     'text_notification' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the notification or false on failure
     */
    public function createMaj($parameters)
    {
        if (empty($parameters['id_envoyeur']) ||
            empty($parameters['text_notification'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_envoyeur = filter_var($parameters['id_envoyeur'], FILTER_SANITIZE_NUMBER_INT);
            $text_notification = trim($parameters['text_notification']);

            $id_notification = $this->insertNotification([
                'id_envoyeur' => $id_envoyeur,
                'id_destinataire' => $id_envoyeur,
                'id_type_notification' => self::TYPE_MAJ_SAPA,
                'text_notification' => $text_notification,
            ]);

            $this->pdo->commit();
            return $id_notification;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     *
     * required parameters :
     * [
     *     'id_envoyeur' => string,
     *     'destinataires' => array,
     *     'id_type_notification' => string,
     *     'text_notification' => string,
     * ]
     *
     * @param $parameters
     * @return false|array an array containing id of the created notifications or false on failure
     */
    public function createMultiple($parameters)
    {
        if (empty($parameters['id_envoyeur']) ||
            (empty($parameters['destinataires']) || !is_array($parameters['destinataires'])) ||
            empty($parameters['id_type_notification']) ||
            empty($parameters['text_notification'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_envoyeur = filter_var($parameters['id_envoyeur'], FILTER_SANITIZE_NUMBER_INT);
            $destinataires = filter_var_array($parameters['destinataires'], FILTER_SANITIZE_NUMBER_INT);
            $id_type_notification = filter_var($parameters['id_type_notification'], FILTER_SANITIZE_NUMBER_INT);
            $text_notification = $parameters['text_notification'];

            $ids = [];
            foreach ($destinataires as $id_user) {
                $id_notification = $this->insertNotification([
                    'id_envoyeur' => $id_envoyeur,
                    'id_destinataire' => $id_user,
                    'id_type_notification' => $id_type_notification,
                    'text_notification' => $text_notification,
                ]);
                $ids[] = $id_notification;
            }

            $this->pdo->commit();
            return $ids;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * inserts a notification in the database.
     *
     * required parameters :
     * [
     *     'id_envoyeur' => string,
     *     'id_destinataire' => string,
     *     'id_type_notification' => string,
     *     'text_notification' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the notification or false on failure
     * @throws Exception
     */
    private function insertNotification($parameters)
    {
        $query = '
                INSERT INTO notifications
                    (id_envoyeur, id_destinataire, id_type_notification, text_notification)
                VALUES (:id_envoyeur, :id_destinataire, :id_type_notification, :text_notification);';
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id_envoyeur', $parameters['id_envoyeur']);
        $statement->bindValue(':id_destinataire', $parameters['id_destinataire']);
        $statement->bindValue(':id_type_notification', $parameters['id_type_notification']);
        $statement->bindValue(':text_notification', $parameters['text_notification']);

        if (!$statement->execute()) {
            throw new Exception('Error insert notifications');
        }

        return $this->pdo->lastInsertId();
    }

    /**
     * @param $id_notification
     * @return array|false Return an associative array or false on failure
     */
    public function readOne($id_notification)
    {
        if (empty($id_notification)) {
            return false;
        }

        $query = '
            SELECT id_notification,
                   id_envoyeur,
                   id_destinataire,
                   tn.nom as type_notification,
                   text_notification,
                   DATE_FORMAT(date_notification, \'%d/%m/%Y %H:%i\') as date_notification,
                   date_notification as date_default_format,
                   est_vu
            FROM notifications
            JOIN type_notification tn on tn.id_type_notification = notifications.id_type_notification
            WHERE notifications.id_notification = :id_notification
            ORDER BY date_default_format DESC';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_notification', $id_notification);
        if (!$statement->execute()) {
            return false;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_user
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAll($id_user)
    {
        if (empty($id_user)) {
            return false;
        }

        $query = '
            SELECT id_notification,
                   id_envoyeur,
                   id_destinataire,
                   tn.nom as type_notification,
                   text_notification,
                   DATE_FORMAT(date_notification, \'%d/%m/%Y %H:%i\') as date_notification,
                   date_notification as date_default_format,
                   est_vu
            FROM notifications
            JOIN type_notification tn on tn.id_type_notification = notifications.id_type_notification
            WHERE notifications.id_destinataire = :id_user
            ORDER BY date_default_format DESC';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);
        if (!$statement->execute()) {
            return false;
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAllMaj()
    {
        $query = "
            SELECT id_notification,
                   tn.nom as type_notification,
                   text_notification,
                   DATE_FORMAT(date_notification, '%d/%m/%Y %H:%i') as date_notification,
                   date_notification as date_default_format
            FROM notifications
            JOIN type_notification tn on tn.id_type_notification = notifications.id_type_notification
            WHERE notifications.id_type_notification = :id_type_notification
            ORDER BY date_default_format DESC";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_type_notification', self::TYPE_MAJ_SAPA);
        if (!$statement->execute()) {
            return false;
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $id_user L'id du destinataire
     * @return false|int le nombre de nouvelles notifications ou false en cas d'échec
     */
    function readNewCount($id_user)
    {
        if (empty($id_user)) {
            return false;
        }

        $query = '
            SELECT COUNT(*) as not_est_vu_count
            FROM notifications
            WHERE notifications.id_destinataire = :id_user
              AND est_vu = 0';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);
        if (!$statement->execute()) {
            return false;
        }

        return $statement->fetch(PDO::FETCH_COLUMN, 0) ?? false;
    }

    /**
     * Updates a MAJ notification
     *
     * required parameters :
     * [
     *     'id_envoyeur' => string,
     *     'text_notification' => string,
     * ]
     *
     * @param array $parameters
     * @return bool if successfully updated
     */
    public function updateMaj($parameters): bool
    {
        if (empty($parameters['id_notification']) ||
            empty($parameters['text_notification'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $id_notification = filter_var($parameters['id_notification'], FILTER_SANITIZE_NUMBER_INT);
            $text_notification = trim($parameters['text_notification']);

            ////////////////////////////////////////////////////
            // verification si la notification existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) AS notification_count
                FROM notifications
                WHERE id_notification = :id_notification';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_notification', $id_notification);

            if ($statement->execute()) {
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                if (intval($data['notification_count']) == 0) {
                    throw new Exception('Error: Cet notification n\'existe pas');
                }
            } else {
                throw new Exception('Error SELECT count(*) AS notification_count');
            }

            // update
            $query = '
                UPDATE notifications
                SET text_notification = :text_notification
                WHERE id_notification = :id_notification';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_notification', $id_notification);
            $statement->bindValue(':text_notification', $text_notification);

            if (!$statement->execute()) {
                throw new Exception('Error update notifications');
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
     * Sets the est_vu field of a notification
     *
     * @param $id_notification
     * @param $est_vu bool
     * @return bool if successfully set
     */
    public function setEstVu($id_notification, $est_vu): bool
    {
        if (empty($id_notification) ||
            gettype($est_vu) != "boolean") {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $id_notification = filter_var($id_notification, FILTER_SANITIZE_NUMBER_INT);
            $est_vu = $est_vu ? "1" : "0";

            ////////////////////////////////////////////////////
            // verification si la notification existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) AS notification_count
                FROM notifications
                WHERE id_notification = :id_notification';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_notification', $id_notification);

            if ($statement->execute()) {
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                if (intval($data['notification_count']) == 0) {
                    throw new Exception('Error: Cet notification n\'existe pas');
                }
            } else {
                throw new Exception('Error SELECT count(*) AS notification_count');
            }

            // update
            $query = '
                UPDATE notifications
                SET est_vu = :est_vu
                WHERE id_notification = :id_notification';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_notification', $id_notification);
            $statement->bindValue(':est_vu', $est_vu);

            if (!$statement->execute()) {
                throw new Exception('Error update notifications');
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
     * Deletes a notification
     *
     * @param $id_notification
     * @return bool if successfully deleted
     */
    public function delete($id_notification)
    {
        if (empty($id_notification)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $id_notification = filter_var($id_notification, FILTER_SANITIZE_NUMBER_INT);

            $query = '
                DELETE FROM notifications
                WHERE id_notification = :id_notification';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_notification', $id_notification);

            if (!$statement->execute()) {
                throw new Exception('Error delete');
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
     * Deletes all notification received by a user
     *
     * @param $id_user
     * @return bool if successfully deleted
     */
    public function deleteAllUser($id_user)
    {
        if (empty($id_user)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $id_user = filter_var($id_user, FILTER_SANITIZE_NUMBER_INT);

            $query = '
                DELETE FROM notifications
                WHERE id_destinataire = :id_destinataire';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_destinataire', $id_user);

            if (!$statement->execute()) {
                throw new Exception('Error delete');
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
}