<?php

namespace Sportsante86\Sapa\Model;

 ;
use Sportsante86\Sapa\Outils\Permissions;

class Antenne
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
     * @param $id_antenne
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_antenne)
    {
        if (empty($id_antenne)) {
            return false;
        }

        $query = '
            SELECT id_antenne, antenne.id_structure, nom_antenne, nom_structure
            FROM antenne
            JOIN structure s on antenne.id_structure = s.id_structure
            WHERE id_antenne = :id_antenne';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_antenne', $id_antenne);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * session parameters:
     * [
     *     'id_role_user' => string,
     *     'est_coordinateur_peps' => bool,
     *     'id_statut_structure' => string|null, // null si id_role_user=1
     *     'id_territoire' => string,
     *     'id_structure' => string|null, // null si id_role_user=1
     * ]
     *
     * @param $session
     * @return array|false Return an associative array or false on failure
     */
    public function readAll($session)
    {
        if (empty($session['role_user_ids']) ||
            !isset($session['est_coordinateur_peps']) ||
            empty($session['id_territoire']) ||
            (empty($session['id_structure']) && !in_array("1", $session['role_user_ids'])) ||
            (empty($session['id_statut_structure']) && !in_array("1", $session['role_user_ids']))) {
            return false;
        }

        $permission = new Permissions($session);

        $query = '
            SELECT id_antenne, antenne.id_structure, nom_antenne, nom_structure
            FROM antenne
                     JOIN structure using (id_structure)
            WHERE ';

        if ($permission->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' 1=1 ';
        } elseif ($permission->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            $query .= ' structure.id_territoire = :id_territoire ';
        } else {
            $query .= ' structure.id_structure = :id_structure ';
        }
        $query .= ' ORDER BY nom_antenne ';

        $stmt = $this->pdo->prepare($query);
        if ($permission->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            $stmt->bindValue(':id_territoire', $session['id_territoire']);
        } elseif (!$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
        }
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return un array contenant toutes les antennes de la structure donnée
     *
     * @param $id_structure
     * @return false|array Return an associative array or false on failure
     */
    public function readAllStructure($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        $query = '
            SELECT id_antenne, antenne.id_structure, nom_antenne, nom_structure
            FROM antenne
            JOIN structure s on antenne.id_structure = s.id_structure
            WHERE antenne.id_structure = :id_structure';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        if (!$stmt->execute()) {
            return false;
        }

        $antennes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // recup nombre de patients qui sont affectés à chaque antenne
        if (is_array($antennes) && count($antennes) > 0) {
            foreach ($antennes as $key => $antenne) {
                $query = '
                    SELECT COUNT(id_patient) AS nb_patient
                    FROM patients
                    WHERE patients.id_antenne = :id_antenne';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_antenne', $antenne['id_antenne']);
                if (!$stmt->execute()) {
                    return false;
                }

                $data_count = $stmt->fetch(PDO::FETCH_ASSOC);
                $antennes[$key]['nb_patient'] = $data_count['nb_patient'];
            }
        }

        return $antennes;
    }

    /**
     * Return un array contenant tous les id_user des responsables structures
     * qui sont rattachées à l'antenne donnée
     *
     * @param $id_antenne
     * @return false|array Return an array of ids or false on failure
     */
    public function getResponsableAntenne($id_antenne)
    {
        if (empty($id_antenne)) {
            return false;
        }

        $query = '
            SELECT DISTINCT c.id_user
            FROM structure
                     JOIN users u on structure.id_structure = u.id_structure
                     JOIN a_role ar on u.id_user = ar.id_user
                     JOIN coordonnees c on u.id_user = c.id_user
                     JOIN antenne a on structure.id_structure = a.id_structure
            WHERE ar.id_role_user = 6
              AND a.id_antenne = :id_antenne';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_antenne', $id_antenne);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}