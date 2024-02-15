<?php

namespace Sportsante86\Sapa\Model;

 ;

class Ville
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
     * @return false|array Returns an array
     */
    public function readAll()
    {
        $query = '
            SELECT id_ville, nom_ville, code_postal
            FROM villes';
        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}