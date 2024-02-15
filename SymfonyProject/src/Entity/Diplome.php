<?php

namespace Sportsante86\Sapa\Model;

 ;

class Diplome
{
    private PDO $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAll()
    {
        $query = '
            SELECT id_diplome, nom_diplome
            FROM diplome';
        $statement = $this->pdo->prepare($query);

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}