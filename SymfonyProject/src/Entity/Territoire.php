<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use Sportsante86\Sapa\Outils\Permissions;

class Territoire
{
    private PDO $pdo;
    private string $errorMessage = "";

    public const TYPE_TERRITOIRE_DEPARTEMENT = 1;
    public const TYPE_TERRITOIRE_REGION = 2;

    /**
     * Les types de territoires possibles
     */
    private const TYPES_TERRITOIRE = [
        self::TYPE_TERRITOIRE_DEPARTEMENT,
        self::TYPE_TERRITOIRE_REGION,
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
     * Return un array contenant tous les id_user des coordinateurs PEPS du territoire donné
     *
     * @param $id_territoire
     * @return false|array Return an array of ids or false on failure
     */
    public function getCoordinateurPeps($id_territoire)
    {
        if (empty($id_territoire)) {
            return false;
        }

        $query = '
            SELECT DISTINCT u.id_user
            FROM territoire
                     JOIN users u on territoire.id_territoire = u.id_territoire
                     JOIN a_role ar on u.id_user = ar.id_user
                     JOIN coordonnees c on u.id_user = c.id_user
            WHERE u.id_territoire = :id_territoire
              AND ar.id_role_user = 2
              AND u.est_coordinateur_peps = 1';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_territoire', $id_territoire);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * @param $id_territoire
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_territoire)
    {
        if (empty($id_territoire)) {
            return false;
        }

        $query = '
            SELECT id_territoire, nom_territoire, lien_ref_territoire
            FROM territoire
            WHERE id_territoire = :id_territoire';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_territoire', $id_territoire);
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
     *     'id_statut_structure' => string,
     *     'id_territoire' => string,
     * ]
     *
     * @param array  $session
     * @param string $id_type_territoire filtre selon le type de territoire
     * @param string $id_region filtre selon la region (dispo seulement pour l'admin)
     * @return array|false Return an associative array or false on failure
     */
    public function readAll($session, $id_type_territoire = null, $id_region = null)
    {
        if (!empty($id_type_territoire) && !in_array($id_type_territoire, self::TYPES_TERRITOIRE)) {
            $this->errorMessage = "Le type de territoire est invalide";
            return false;
        }

        try {
            $permission = new Permissions($session);
        } catch (Exception $e) {
            return false;
        }

        $query = '
            SELECT id_territoire,
                   nom_territoire,
                   lien_ref_territoire,
                   id_region,
                   id_type_territoire
            FROM territoire
            WHERE 1=1 ';

        if (!empty($id_type_territoire)) {
            $query .= ' AND id_type_territoire = :id_type_territoire ';
        }

        // seul le SUPER_ADMIN peut voir les territoires de toutes les régions
        if (!$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND id_region = (SELECT id_region
                                         FROM territoire
                                         WHERE id_territoire = :id_territoire) ';
        }

        if (!empty($id_region) && $permission->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND id_region = :id_region ';
        }

        $query .= ' ORDER BY nom_territoire ';

        $stmt = $this->pdo->prepare($query);
        if (!empty($id_type_territoire)) {
            $stmt->bindValue(':id_type_territoire', $id_type_territoire);
        }
        if (!$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt->bindValue(':id_territoire', $session['id_territoire']);
        }
        if (!empty($id_region) && $permission->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt->bindValue(':id_region', $id_region);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param string $id_type_territoire filtre selon le type de territoire
     * @return array|false Return an associative array or false on failure
     */
    public function readAllUnfiltered($id_type_territoire = null)
    {
        if (!empty($id_type_territoire) && !in_array($id_type_territoire, self::TYPES_TERRITOIRE)) {
            $this->errorMessage = "Le type de territoire est invalide";
            return false;
        }

        $query = '
            SELECT id_territoire, nom_territoire, lien_ref_territoire
            FROM territoire
            WHERE 1=1 ';

        if (!empty($id_type_territoire)) {
            $query .= ' AND id_type_territoire = :id_type_territoire ';
        }

        $query .= ' ORDER BY nom_territoire ';

        $stmt = $this->pdo->prepare($query);
        if (!empty($id_type_territoire)) {
            $stmt->bindValue(':id_type_territoire', $id_type_territoire);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}