<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class SuiviDossier
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

    /**Ajoute le suivi d'un dossier de bénéficiaire pour l'utilisateur
     * @param int $id_user L'utilisateur connecté
     * @param int $id_patient Le bénéficiaire que l'utilisateur veut suivre
     * @return bool true si réussi, false sinon
     */
    public function createSuiviDossier($id_user, $id_patient)
    {
        if (empty($id_user) || empty($id_patient)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = 'INSERT INTO dossiers_suivi VALUES (:id_user, :id_patient)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_user', $id_user);
            $stmt->bindValue(':id_patient', $id_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO dossiers_suivi');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**Supprime le suivi d'un bénéficiaire par l'utilisateur connecté
     * @param int $id_user L'utilisateur connecté
     * @param int $id_patient Le bénéficiaire que l'utilisateur ne veut plus suivre
     * @return bool true si réussi, false sinon
     */
    public function deleteSuiviDossier($id_user, $id_patient)
    {
        if (empty($id_user) || empty($id_patient)) {
            return false;
        }
        try {
            $this->pdo->beginTransaction();

            $query = 'DELETE FROM dossiers_suivi WHERE id_user = :id_user AND id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_user', $id_user);
            $stmt->bindValue(':id_patient', $id_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM dossiers_suivi');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**Supprime tous les suivis associés à un bénéficiaire
     * @param int $id_patient Le bénéficiaire que l'utilisateur ne veut plus suivre
     * @return bool true si réussi, false sinon
     */
    public function deleteAllSuiviDossier($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }
        try {
            $this->pdo->beginTransaction();

            $query = 'DELETE FROM dossiers_suivi WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM dossiers_suivi');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**Détermine si le patient est suivi par l'utilisateur connecté
     * @param int $id_patient Le patient dont on vérifie le suivi
     * @param int $id_user L'utilisateur connecté
     * @return bool True si le patient est trouvé, false sinon
     */
    public function checkSuiviDossier($id_user, $id_patient)
    {
        if (empty($id_user) || empty($id_patient)) {
            return false;
        }

        $query = 'SELECT id_patient FROM dossiers_suivi 
              WHERE id_user = :id_user AND id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_user', $id_user);
        $stmt->bindValue(':id_patient', $id_patient);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }
}