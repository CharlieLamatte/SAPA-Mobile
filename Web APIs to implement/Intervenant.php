<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use Sportsante86\Sapa\Outils\ChaineCharactere;
use Sportsante86\Sapa\Outils\Permissions;

class Intervenant
{
    private PDO $pdo;
    private string $errorMessage;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->errorMessage = '';
    }

    /**
     * Creates an intervenant
     *
     * required parameters:
     * [
     *     'nom_intervenant' => string,
     *     'prenom_intervenant' => string,
     *     'id_statut_intervenant' => string,
     *     'id_territoire' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the intervenant or false on failure
     */
    public function create($parameters)
    {
        if (!$this->requiredParametersPresent($parameters)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètre obligatoires
            $nom = $parameters['nom_intervenant'];
            $nom = trim(mb_strtoupper($nom, 'UTF-8'));
            $prenom = $parameters['prenom_intervenant'];
            $prenom = trim(ChaineCharactere::mb_ucfirst($prenom));
            $id_statut = filter_var($parameters['id_statut_intervenant'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $id_territoire = isset($parameters['id_territoire']) ?
                filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $email = isset($parameters['mail_intervenant']) ?
                filter_var($parameters['mail_intervenant'], FILTER_SANITIZE_EMAIL, ['options' => ['default' => ""]]) :
                "";
            $tel_portable = isset($parameters['tel_portable_intervenant']) ?
                filter_var(
                    $parameters['tel_portable_intervenant'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_fixe = isset($parameters['tel_fixe_intervenant']) ?
                filter_var(
                    $parameters['tel_fixe_intervenant'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $numero_carte = $parameters['numero_carte'] ?? "";
            $diplomes = isset($parameters['diplomes']) ?
                filter_var_array($parameters['diplomes'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $structures = isset($parameters['structures']) ?
                filter_var_array($parameters['structures'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $id_api = $parameters['id_api'] ?? null;
            $id_api_structure = $parameters['id_api_structure'] ?? null;

            // verif de longueur du numero carte
            $is_numero_carte_valid = empty($parameters['numero_carte']) ||
                (is_string($parameters['numero_carte']) && strlen($parameters['numero_carte']) <= 11);

            if (!$is_numero_carte_valid) {
                throw new Exception('Error: Le numéro de carte ' . $parameters['numero_carte'] . ' est invalide');
            }

            // si on importe un intervenant de l'API
            if (!empty($id_api)) {
                // verification si l'intervenant à déja été importé
                $query = 'SELECT id_intervenant FROM intervenants WHERE id_api = :id_api';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_api', $id_api);

                if ($statement->execute()) {
                    if ($statement->rowCount() > 0) {
                        throw new Exception('Error: element déja importé');
                    }
                    $statement->closeCursor();
                } else {
                    throw new Exception('Error select id_structure');
                }

                // récupération de la structure auquel appartient l'intervenant
                $query = 'SELECT id_structure FROM structure WHERE id_api = :id_api';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_api', $id_api_structure);

                if ($statement->execute()) {
                    if ($statement->rowCount() == 0) {
                        throw new Exception('Error: La structure de l\'intervenant n\'a pas été importée');
                    }
                    $data = $statement->fetch();
                    $structures = [$data['id_structure']];
                } else {
                    throw new Exception('Error SELECT id_structure FROM structure');
                }

                // Récupération de l'id du territoire s'il n'a pas été fourni à la création
                $query = 'SELECT id_territoire FROM structure WHERE id_structure = :id_structure';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_structure', $structures[0]);

                if ($statement->execute()) {
                    $data = $statement->fetch();
                    $id_territoire = $data['id_territoire'];

                    if (empty($id_territoire)) {
                        throw new Exception(
                            'Error: Il n\'y a pas d\'id_territoire pour l\'id_structure=' . $structures[0]
                        );
                    }
                } else {
                    throw new Exception('Error SELECT id_territoire');
                }
            }

            // Insertion dans coordonnées
            $query = '
                INSERT INTO coordonnees (nom_coordonnees, prenom_coordonnees, tel_fixe_coordonnees, tel_portable_coordonnees,
                                         mail_coordonnees)
                VALUES (:nom_coordonnees, :prenom_coordonnees, :tel_fixe_coordonnees, :tel_portable_coordonnees, :mail_coordonnees)';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':nom_coordonnees', $nom);
            $statement->bindValue(':prenom_coordonnees', $prenom);
            $statement->bindValue(':tel_fixe_coordonnees', $tel_fixe);
            $statement->bindValue(':tel_portable_coordonnees', $tel_portable);
            $statement->bindValue(':mail_coordonnees', $email);

            if ($statement->execute()) {
                $id_coordonnees = $this->pdo->lastInsertId();
            } else {
                throw new Exception('Error INSERT INTO coordonnees');
            }

            // Ajout dans la table intervenant
            $query = '
                INSERT INTO intervenants (id_coordonnees, id_api, numero_carte, id_statut_intervenant, id_territoire)
                VALUES (:id_coordonnees, :id_api, :numero_carte, :id_statut_intervenant, :id_territoire)';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_coordonnees', $id_coordonnees);
            $statement->bindValue(':id_statut_intervenant', $id_statut);
            $statement->bindValue(':id_territoire', $id_territoire);
            if (empty($numero_carte)) {
                $statement->bindValue(':numero_carte', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':numero_carte', $numero_carte);
            }
            if (empty($id_api)) {
                $statement->bindValue(':id_api', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':id_api', $id_api);
            }

            if ($statement->execute()) {
                $id_intervenant = $this->pdo->lastInsertId();
            } else {
                throw new Exception("Error INSERT INTO intervenants");
            }

            // UPDATE de la table coordonnees avec l'id intervenant
            $query = '
                UPDATE coordonnees
                SET id_intervenant = :id_intervenant
                WHERE id_coordonnees = :id_coordonnees';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);
            $statement->bindValue(':id_coordonnees', $id_coordonnees);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            // Ajout dans la table a obtenu
            foreach ($diplomes as $id_diplome) {
                $query = '
                        INSERT INTO a_obtenu (id_diplome, id_intervenant)
                        VALUES (:id_diplome, :id_intervenant)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_diplome', $id_diplome);
                $statement->bindValue(':id_intervenant', $id_intervenant);

                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO a_obtenu');
                }
            }
            // Ajout dans la table intervient_dans
            foreach ($structures as $id_structure) {
                $query = '
                        INSERT INTO intervient_dans (id_intervenant, id_structure)
                        VALUES (:id_intervenant, :id_structure)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $id_intervenant);
                $statement->bindValue(':id_structure', $id_structure);

                if (!$statement->execute()) {
                    throw new Exception('Error INTO intervient_dans');
                }
            }

            $this->pdo->commit();

            return $id_intervenant;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Updates an intervenant
     *
     * required parameters:
     * [
     *     'id_intervenant' => string,
     *     'nom_intervenant' => string,
     *     'prenom_intervenant' => string,
     *     'id_statut_intervenant' => string,
     *     'id_territoire' => string,
     * ]
     *
     * @param $parameters
     * @return bool if the update was successful
     */
    public function update($parameters): bool
    {
        if (!$this->requiredParametersPresent($parameters) && !empty($parameters['id_intervenant'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètre obligatoires
            $id_intervenant = $parameters['id_intervenant'];
            $nom = $parameters['nom_intervenant'];
            $nom = trim(mb_strtoupper($nom, 'UTF-8'));
            $prenom = $parameters['prenom_intervenant'];
            $prenom = trim(ChaineCharactere::mb_ucfirst($prenom));
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $id_statut = filter_var($parameters['id_statut_intervenant'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $email = isset($parameters['mail_intervenant']) ?
                filter_var($parameters['mail_intervenant'], FILTER_SANITIZE_EMAIL, ['options' => ['default' => ""]]) :
                "";
            $tel_portable = isset($parameters['tel_portable_intervenant']) ?
                filter_var(
                    $parameters['tel_portable_intervenant'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_fixe = isset($parameters['tel_fixe_intervenant']) ?
                filter_var(
                    $parameters['tel_fixe_intervenant'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $numero_carte = $parameters['numero_carte'] ?? "";
            $diplomes = isset($parameters['diplomes']) ?
                filter_var_array($parameters['diplomes'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $structures = isset($parameters['structures']) ?
                filter_var_array($parameters['structures'], FILTER_SANITIZE_NUMBER_INT) :
                [];

            // verif de longueur du numero carte
            $is_numero_carte_valid = empty($parameters['numero_carte']) ||
                (is_string($parameters['numero_carte']) && strlen($parameters['numero_carte']) <= 11);

            if (!$is_numero_carte_valid) {
                throw new Exception('Error: Le numéro de carte ' . $parameters['numero_carte'] . ' est invalide');
            }

            // UPDATE l'intervenant
            $query = '
                UPDATE intervenants
                SET id_statut_intervenant = :id_statut_intervenant,
                    numero_carte          = :numero_carte,
                    id_territoire         = :id_territoire
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_statut_intervenant', $id_statut);
            $statement->bindValue(':numero_carte', $numero_carte);
            $statement->bindValue(':id_intervenant', $id_intervenant);
            $statement->bindValue(':id_territoire', $id_territoire);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE intervenants');
            }

            // UPDATE les coordonnées
            $query = '
                UPDATE coordonnees
                SET nom_coordonnees          = :nom_coordonnees,
                    prenom_coordonnees       = :prenom_coordonnees,
                    mail_coordonnees         = :mail_coordonnees,
                    tel_fixe_coordonnees     = :tel_fixe_coordonnees,
                    tel_portable_coordonnees = :tel_portable_coordonnees
                WHERE id_intervenant = :id_intervenant';

            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':id_intervenant', $id_intervenant);
            $statement->bindValue(':nom_coordonnees', $nom);
            $statement->bindValue(':prenom_coordonnees', $prenom);
            $statement->bindValue(':mail_coordonnees', $email);
            $statement->bindValue(':tel_fixe_coordonnees', $tel_fixe);
            $statement->bindValue(':tel_portable_coordonnees', $tel_portable);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            // update des diplômes
            $query = 'DELETE FROM a_obtenu WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM a_obtenu');
            }

            foreach ($diplomes as $id_diplome) {
                $query = '
                        INSERT INTO a_obtenu (id_diplome, id_intervenant)
                        VALUES (:id_diplome, :id_intervenant)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_diplome', $id_diplome);
                $statement->bindValue(':id_intervenant', $id_intervenant);

                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO a_obtenu');
                }
            }

            // update des structures intervient_dans
            $query = 'DELETE FROM intervient_dans WHERE intervient_dans.id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervient_dans');
            }

            foreach ($structures as $id_structure) {
                $query = '
                        INSERT INTO intervient_dans (id_intervenant, id_structure)
                        VALUES (:id_intervenant, :id_structure)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $id_intervenant);
                $statement->bindValue(':id_structure', $id_structure);

                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO intervient_dans');
                }
            }

            $this->pdo->commit();

            return true;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Deletes an intervenant
     *
     * @param $id_intervenant string the id of the intervenant to be deleted
     * @return bool if the deletion was successful
     */
    public function delete($id_intervenant): bool
    {
        if (empty($id_intervenant)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // verification si l'intervenant existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) AS nb_intervenant
                FROM intervenants
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_intervenant']) == 0) {
                    throw new Exception('Error: Cet intervenant n\'existe pas');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_intervenant');
            }

            ////////////////////////////////////////////////////
            // verification si l'intervenant à des créneaux
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) AS nb_creneaux
                FROM creneaux_intervenant
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_creneaux']) > 0) {
                    throw new Exception('Error: Cet intervenant intervient dans des créneaux');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_creneaux');
            }

            ////////////////////////////////////////////////////
            // verification si l'intervenant est un user
            ////////////////////////////////////////////////////
            // TODO autoriser la suppression d'intervenants utilisateur
            $query = '
                SELECT id_user
                FROM coordonnees
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if ($statement->execute()) {
                $data = $statement->fetch();
                if (!empty($data['id_user'])) {
                    throw new Exception('Error: Cet intervenant est un utilisateur');
                }
            } else {
                throw new Exception('Error SELECT id_user');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM coordonnees
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM coordonnees
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            ////////////////////////////////////////////////////
            // DELETE diplomes de l'intervenant
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM a_obtenu
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM a_obtenu');
            }

            ////////////////////////////////////////////////////
            // DELETE intervient_dans
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM intervient_dans
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervient_dans');
            }

            ////////////////////////////////////////////////////
            // DELETE intervenants
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM intervenants
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervenants');
            }

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // $this->pdo->query("SET foreign_key_checks=1"); // TODO is this necessary?
            $this->errorMessage = $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Fuses two intervenants
     *
     * @param $id_intervenant_from
     * @param $id_intervenant_target
     * @return bool if the fusion was successful
     */
    public function fuse($id_intervenant_from, $id_intervenant_target)
    {
        if (empty($id_intervenant_from) || empty($id_intervenant_target)) {
            return false;
        }

        $query = '
            SELECT intervenants.id_intervenant, c.id_user, c.id_coordonnees
            FROM intervenants
                     JOIN coordonnees c on intervenants.id_intervenant = c.id_intervenant
            WHERE intervenants.id_intervenant = :id_intervenant';

        // check that $id_intervenant_from exists
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_intervenant', $id_intervenant_from);

        $statement->execute();
        if ($statement->rowCount() == 0) {
            return false;
        }
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $is_intervenant_from_user = !empty($row['id_user']);
        $id_user_from = $row['id_user'];
        $id_coordonnees_from = $row['id_coordonnees'];

        // check that $id_intervenant_target exists
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_intervenant', $id_intervenant_target);

        $statement->execute();
        if ($statement->rowCount() == 0) {
            return false;
        }
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $is_intervenant_target_user = !empty($row['id_user']);
        $id_user_target = $row['id_user'];
        $id_coordonnees_target = $row['id_coordonnees'];

        try {
            $this->pdo->beginTransaction();

            if ($is_intervenant_from_user && $is_intervenant_target_user) {
                // cas où les deux intervenants sont utilisateur

                // update des seances
                $query = '
                    UPDATE seance
                    SET id_user = :id_user_target
                    WHERE id_user = :id_user_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user_target', $id_user_target);
                $statement->bindValue(':id_user_from', $id_user_from);

                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE seance');
                }

                // fusion intervention

                // 1 recup structures commun à intervenant from et à intervenant target
                $query = '
                    SELECT DISTINCT id_structure
                    FROM intervention
                    WHERE id_user = :id_user_from OR id_user = :id_user_target';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user_target', $id_user_target);
                $statement->bindValue(':id_user_from', $id_user_from);

                if (!$statement->execute()) {
                    throw new Exception('Error SELECT DISTINCT id_structure');
                }
                $structure_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

                // 2 suppression de tous les structures interviens_dans
                $query = '
                    DELETE
                    FROM intervention
                    WHERE id_user = :id_user_from OR id_user = :id_user_target';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user_target', $id_user_target);
                $statement->bindValue(':id_user_from', $id_user_from);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM intervention');
                }

                // 3 ajout des structures en commmun
                foreach ($structure_ids as $id_structure) {
                    // INSERT dans intervention
                    $query = '
                        INSERT INTO intervention (id_user, id_structure)
                        VALUES (:id_user_target, :id_structure)';
                    $statement = $this->pdo->prepare($query);

                    $statement->bindValue(':id_user_target', $id_user_target);
                    $statement->bindValue(':id_structure', $id_structure);

                    if (!$statement->execute()) {
                        throw new Exception('Error insert intervention');
                    }
                }

                if (!$this->pdo->query("SET foreign_key_checks=0")) {
                    throw new Exception('Error disabling foreign key checks');
                }

                // delete de users from
                $query = '
                    DELETE
                    FROM users
                    WHERE id_user = :id_user_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user_from', $id_user_from);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM users');
                }
                if (!$this->pdo->query("SET foreign_key_checks=1")) {
                    throw new Exception('Error re-enabling foreign key checks');
                }
            } elseif ($is_intervenant_from_user || $is_intervenant_target_user) {
                // cas où un des intervenants est utilisateur

                // cas où intervenant_from est utilisateur
                if ($is_intervenant_from_user) {
                    // update coordonnes target avec l'user intervenant_from qui sera gardé
                    $query = '
                        UPDATE coordonnees
                        SET id_user = :id_user_from
                        WHERE id_coordonnees = :id_coordonnees_target';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_coordonnees_target', $id_coordonnees_target);
                    $statement->bindValue(':id_user_from', $id_user_from);

                    if (!$statement->execute()) {
                        throw new Exception('Error UPDATE coordonnees');
                    }
                }
            }

            // fusion creneaux_intervenant

            // 1 recup creneau commun à intervenant from et à intervenant target
            $query = '
                SELECT DISTINCT id_creneau
                FROM creneaux_intervenant
                WHERE id_intervenant = :id_intervenant_from OR id_intervenant = :id_intervenant_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_creneau');
            }
            $creneaux_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // 2 suppression de tous les creneaux_intervenant
            $query = '
                DELETE
                FROM creneaux_intervenant
                WHERE id_intervenant = :id_intervenant_from OR id_intervenant = :id_intervenant_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM creneaux_intervenant');
            }

            // 3 ajout des creneaux en commmun
            foreach ($creneaux_ids as $id_creneau) {
                // INSERT dans intervention
                $query = '
                    INSERT INTO creneaux_intervenant (id_intervenant, id_creneau)
                    VALUES (:id_intervenant_target, :id_creneau)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
                $statement->bindValue(':id_creneau', $id_creneau);

                if (!$statement->execute()) {
                    throw new Exception('Error insert creneaux_intervenant');
                }
            }

            // fusion diplomes

            // 1 recup diplomes commun à intervenant from et à intervenant target
            $query = '
                SELECT DISTINCT id_diplome
                FROM a_obtenu
                WHERE id_intervenant = :id_intervenant_from OR id_intervenant = :id_intervenant_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_diplome');
            }
            $diplomes_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // 2 suppression de tous les diplômes
            $query = '
                DELETE
                FROM a_obtenu
                WHERE id_intervenant = :id_intervenant_from OR id_intervenant = :id_intervenant_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM a_obtenu');
            }

            // 3 ajout des diplomes en commmun
            foreach ($diplomes_ids as $id_diplome) {
                // INSERT dans intervention
                $query = '
                    INSERT INTO a_obtenu (id_intervenant, id_diplome)
                    VALUES (:id_intervenant_target, :id_diplome)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
                $statement->bindValue(':id_diplome', $id_diplome);

                if (!$statement->execute()) {
                    throw new Exception('Error insert a_obtenu');
                }
            }

            // fusion intervient_dans

            // 1 recup structures commun à intervenant from et à intervenant target
            $query = '
                SELECT DISTINCT id_structure
                FROM intervient_dans
                WHERE id_intervenant = :id_intervenant_from OR id_intervenant = :id_intervenant_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_structure');
            }
            $structure_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // 2 suppression de tous les structures interviens_dans
            $query = '
                DELETE
                FROM intervient_dans
                WHERE id_intervenant = :id_intervenant_from OR id_intervenant = :id_intervenant_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervient_dans');
            }

            // 3 ajout des structures en commmun
            foreach ($structure_ids as $id_structure) {
                // INSERT dans intervention
                $query = '
                    INSERT INTO intervient_dans (id_intervenant, id_structure)
                    VALUES (:id_intervenant_target, :id_structure)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant_target', $id_intervenant_target);
                $statement->bindValue(':id_structure', $id_structure);

                if (!$statement->execute()) {
                    throw new Exception('Error insert intervient_dans');
                }
            }

            // suppressions
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            // delete de coordonnees from
            $query = '
                DELETE
                FROM coordonnees
                WHERE id_coordonnees = :id_coordonnees_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_coordonnees_from', $id_coordonnees_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            // suppression intervenant from
            $query = '
                DELETE
                FROM intervenants
                WHERE id_intervenant = :id_intervenant_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant_from', $id_intervenant_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervenants');
            }

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage() . $e->getLine();
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * @param $permissions Permissions
     * @param $id_territoire string id_territoire of the user trying to access
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAll(Permissions $permissions, $id_territoire)
    {
        if (empty($permissions) || empty($id_territoire)) {
            return false;
        }

        $query = '
            SELECT intervenants.id_intervenant as id_intervenant,
                   numero_carte,
                   nom_coordonnees             as nom_intervenant,
                   prenom_coordonnees          as prenom_intervenant,
                   mail_coordonnees            as mail_intervenant,
                   tel_fixe_coordonnees        as tel_fixe_intervenant,
                   tel_portable_coordonnees    as tel_portable_intervenant,
                   id_statut_intervenant       as id_statut_intervenant,
                   nom_statut_intervenant,
                   id_territoire,
                   nom_territoire
            FROM intervenants
                     JOIN coordonnees USING (id_coordonnees)
                     JOIN territoire USING (id_territoire)
                     LEFT JOIN statuts_intervenant USING (id_statut_intervenant) ';

        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' WHERE intervenants.id_territoire = :id_territoire';
        }

        $statement = $this->pdo->prepare($query);
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $statement->bindValue(':id_territoire', $id_territoire);
        }
        $statement->execute();

        $intervenants = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($intervenants)) {
            foreach ($intervenants as &$intervenant) {
                // recup si l'intervenant est un utilisateur
                $query = '
                SELECT COUNT(*) as user_count
                FROM intervenants i
                    JOIN users u ON u.id_coordonnees = i.id_coordonnees
                WHERE id_intervenant = :id_intervenant';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_intervenant", $intervenant['id_intervenant']);
                if (!$statement->execute()) {
                    return false;
                }
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                $intervenant['is_user'] = intval($data['user_count']) > 0;

                // recup des structures de l'id_intervenant
                $query = '
                SELECT id_intervenant, intervient_dans.id_structure, nom_structure
                FROM intervient_dans
                         JOIN structure USING (id_structure)
                WHERE intervient_dans.id_intervenant = :id_intervenant';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $intervenant['id_intervenant']);
                if (!$statement->execute()) {
                    return false;
                }

                $intervenant['structures'] = [];
                if ($statement->rowCount() > 0) {
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        $structure_item = [
                            "id_structure" => $row['id_structure'],
                            "nom_structure" => $row['nom_structure']
                        ];

                        // recup le nombre de creneaux dont est en charge l'intervenant dans la structure
                        $query = '
                        SELECT COUNT(*) AS intervenant_count
                        FROM creneaux
                        JOIN creneaux_intervenant ON creneaux.id_creneau = creneaux_intervenant.id_creneau
                        WHERE creneaux_intervenant.id_intervenant = :id_intervenant
                          AND creneaux.id_structure = :id_structure';
                        $statement_count = $this->pdo->prepare($query);
                        $statement_count->bindValue(':id_intervenant', $intervenant['id_intervenant']);
                        $statement_count->bindValue(':id_structure', $row['id_structure']);
                        if (!$statement_count->execute()) {
                            return false;
                        }

                        $data_count = $statement_count->fetch();
                        $structure_item['is_intervenant'] = intval($data_count['intervenant_count']) > 0;

                        $intervenant['structures'][] = $structure_item;
                    }
                }
            }
        }

        return $intervenants;
    }

    /**
     * Return un array contenant tous les intervenants de la structure donnée
     *
     * @param $id_structure string id_structure
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAllStructure($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        $query = '
            SELECT intervenants.id_intervenant as id_intervenant,
                   numero_carte,
                   nom_coordonnees             as nom_intervenant,
                   prenom_coordonnees          as prenom_intervenant,
                   mail_coordonnees            as mail_intervenant,
                   tel_fixe_coordonnees        as tel_fixe_intervenant,
                   tel_portable_coordonnees    as tel_portable_intervenant,
                   id_statut_intervenant       as id_statut_intervenant,
                   nom_statut_intervenant,
                   id_territoire,
                   nom_territoire
            FROM intervenants
                     JOIN coordonnees USING (id_coordonnees)
                     JOIN territoire USING (id_territoire)
                     JOIN intervient_dans id on intervenants.id_intervenant = id.id_intervenant
                     LEFT JOIN statuts_intervenant USING (id_statut_intervenant) 
            WHERE id.id_structure = :id_structure
            ORDER BY nom_intervenant';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_structure', $id_structure);
        if (!$statement->execute()) {
            return false;
        }

        $intervenants = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($intervenants)) {
            foreach ($intervenants as &$intervenant) {
                // recup si l'intervenant est un utilisateur
                $query = '
                    SELECT COUNT(*) as user_count
                    FROM intervenants i
                        JOIN users u ON u.id_coordonnees = i.id_coordonnees
                    WHERE id_intervenant = :id_intervenant';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_intervenant", $intervenant['id_intervenant']);
                if (!$statement->execute()) {
                    return false;
                }
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                $intervenant['is_user'] = intval($data['user_count']) > 0;

                // recup des structures de l'id_intervenant
                $query = '
                    SELECT id_intervenant, intervient_dans.id_structure, nom_structure
                    FROM intervient_dans
                             JOIN structure USING (id_structure)
                    WHERE intervient_dans.id_intervenant = :id_intervenant';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $intervenant['id_intervenant']);
                if (!$statement->execute()) {
                    return false;
                }

                // recup le nombre de creneaux dont est en charge l'intervenant dans la structure
                $query = '
                    SELECT COUNT(*) AS nb_creneau
                    FROM creneaux
                    JOIN creneaux_intervenant on creneaux.id_creneau = creneaux_intervenant.id_creneau
                    WHERE creneaux_intervenant.id_intervenant = :id_intervenant
                      AND creneaux.id_structure = :id_structure';
                $statement_count = $this->pdo->prepare($query);
                $statement_count->bindValue(':id_intervenant', $intervenant['id_intervenant']);
                $statement_count->bindValue(':id_structure', $id_structure);
                if (!$statement_count->execute()) {
                    return false;
                }

                $data_count = $statement_count->fetch();
                $intervenant['nb_creneau'] = $data_count['nb_creneau'];

                $intervenant['structures'] = [];
                if ($statement->rowCount() > 0) {
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        $structure_item = [
                            "id_structure" => $row['id_structure'],
                            "nom_structure" => $row['nom_structure']
                        ];

                        // recup le nombre de creneaux dont est en charge l'intervenant dans la structure
                        $query = '
                            SELECT COUNT(*) AS intervenant_count
                            FROM creneaux
                            JOIN creneaux_intervenant on creneaux.id_creneau = creneaux_intervenant.id_creneau
                            WHERE creneaux_intervenant.id_intervenant = :id_intervenant
                              AND creneaux.id_structure = :id_structure';
                        $statement_count = $this->pdo->prepare($query);
                        $statement_count->bindValue(':id_intervenant', $intervenant['id_intervenant']);
                        $statement_count->bindValue(':id_structure', $row['id_structure']);
                        if (!$statement_count->execute()) {
                            return false;
                        }

                        $data_count = $statement_count->fetch();
                        $structure_item['is_intervenant'] = intval($data_count['intervenant_count']) > 0;

                        $intervenant['structures'][] = $structure_item;
                    }
                }
            }
        }

        return $intervenants;
    }

    /**
     * @param $id_intervenant
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_intervenant)
    {
        if (empty($id_intervenant)) {
            return false;
        }

        $query = '
            SELECT intervenants.id_intervenant as id_intervenant,
                   numero_carte,
                   nom_coordonnees             as nom_intervenant,
                   prenom_coordonnees          as prenom_intervenant,
                   mail_coordonnees            as mail_intervenant,
                   tel_fixe_coordonnees        as tel_fixe_intervenant,
                   tel_portable_coordonnees    as tel_portable_intervenant,
                   id_statut_intervenant,
                   nom_statut_intervenant,
                   id_territoire,
                   nom_territoire
            FROM intervenants
                     JOIN coordonnees USING (id_coordonnees)
                     JOIN territoire USING (id_territoire)
                     LEFT JOIN statuts_intervenant USING (id_statut_intervenant)
            WHERE intervenants.id_intervenant = :id_intervenant';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_intervenant', $id_intervenant);

        if (!$statement->execute()) {
            return false;
        }

        if ($statement->rowCount() > 0) {
            $intervenant = $statement->fetch(PDO::FETCH_ASSOC);

            if (empty($intervenant)) {
                return false;
            }

            // recup si l'intervenant est un utilisateur
            $query = '
                SELECT COUNT(*) as user_count
                FROM intervenants i
                    JOIN users u ON u.id_coordonnees = i.id_coordonnees
                WHERE id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_intervenant", $id_intervenant);
            if (!$statement->execute()) {
                return false;
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $intervenant['is_user'] = intval($data['user_count']) > 0;

            // recup des structures de l'id_intervenant
            $query = '
                SELECT id_intervenant, intervient_dans.id_structure, nom_structure
                FROM intervient_dans
                         JOIN structure USING (id_structure)
                WHERE intervient_dans.id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);
            if (!$statement->execute()) {
                return false;
            }

            $intervenant['structures'] = [];
            if ($statement->rowCount() > 0) {
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $structure_item = [
                        "id_structure" => $row['id_structure'],
                        "nom_structure" => $row['nom_structure']
                    ];

                    // recup le nombre de creneaux dont est en charge l'intervenant dans la structure
                    $query = '
                        SELECT COUNT(*) AS intervenant_count
                        FROM creneaux
                        JOIN creneaux_intervenant ON creneaux.id_creneau = creneaux_intervenant.id_creneau
                        WHERE creneaux_intervenant.id_intervenant = :id_intervenant
                          AND creneaux.id_structure = :id_structure';
                    $statement_count = $this->pdo->prepare($query);
                    $statement_count->bindValue(':id_intervenant', $id_intervenant);
                    $statement_count->bindValue(':id_structure', $row['id_structure']);
                    if (!$statement_count->execute()) {
                        return false;
                    }

                    $data_count = $statement_count->fetch();
                    $structure_item['is_intervenant'] = intval($data_count['intervenant_count']) > 0;

                    $intervenant['structures'][] = $structure_item;
                }
            }

            // recup des diplomes
            $query = '
                SELECT d.id_diplome, d.nom_diplome
                FROM a_obtenu 
                     JOIN diplome d on a_obtenu.id_diplome = d.id_diplome
                WHERE a_obtenu.id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $id_intervenant);
            if (!$statement->execute()) {
                return false;
            }
            $intervenant['diplomes'] = $statement->fetchAll(PDO::FETCH_ASSOC);

            // recup des créneaux de l'intervenant
            $c = new Creneau($this->pdo);
            $creneaux = $c->readAllIntervenant($id_intervenant);
            $intervenant['creneaux'] = is_array($creneaux) ? $creneaux : [];

            return $intervenant;
        } else {
            return false;
        }
    }

    /**
     * Return un array contenant tous les id_user des intervenants qui suivent un patient
     *
     * @param $id_patient
     * @return false|array Return an array of ids or false on failure
     */
    public function getIntervenantsSuivantsPatient($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT DISTINCT c.id_user
            FROM creneaux
                JOIN liste_participants_creneau lpc on creneaux.id_creneau = lpc.id_creneau
                JOIN creneaux_intervenant ON creneaux.id_creneau = creneaux_intervenant.id_creneau
                JOIN coordonnees c on creneaux_intervenant.id_intervenant = c.id_intervenant
                JOIN a_role ar on c.id_user = ar.id_user
            WHERE ar.id_role_user = 3
                AND lpc.abandon = 0
                AND lpc.reorientation = 0
                AND lpc.id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * @return string the error message of the last operation
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    private function requiredParametersPresent($parameters): bool
    {
        // l'id_territoire n'est pas nécessaire lors de l'import d'un intervenant par API,
        // mais n'est nécessaire si ce n'est pas un import
        $id_territoire_present_if_required = (!empty($parameters['id_territoire']) && empty($parameters['id_api'])) ||
            !empty($parameters['id_api']);

        // l'id_api_structure est nécessaire lors de l'import d'un intervenant par API,
        // mais n'est nécessaire si ce n'est pas un import
        $id_api_structure_present_if_required = (!empty($parameters['id_api_structure']) && !empty($parameters['id_api'])) ||
            empty($parameters['id_api']);

        return
            $id_api_structure_present_if_required &&
            $id_territoire_present_if_required &&
            !empty($parameters['nom_intervenant']) &&
            !empty($parameters['prenom_intervenant']) &&
            !empty($parameters['id_statut_intervenant']);
    }
}