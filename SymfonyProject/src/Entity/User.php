<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use Sportsante86\Sapa\Outils\Permissions;

class User
{
    private PDO $pdo;
    private string $errorMessage = '';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param $parameters
     * @return false|string
     */
    public function create($parameters)
    {
        if (!$this->checkParameters($parameters)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $role_user_ids = filter_var_array($parameters['role_user_ids'], FILTER_SANITIZE_NUMBER_INT);
            $nom_user = trim(mb_strtoupper($parameters['nom_user'], 'UTF-8'));
            $prenom_user = $parameters['prenom_user'];
            $email_user = $parameters['email_user'];
            // hashage du mdp
            $mdp = password_hash($parameters['mdp'], PASSWORD_DEFAULT);

            // paramètres optionnels
            $id_structure = isset($parameters['id_structure']) ?
                filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $tel_f_user = isset($parameters['tel_f_user']) ?
                filter_var(
                    $parameters['tel_f_user'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_p_user = isset($parameters['tel_p_user']) ?
                filter_var(
                    $parameters['tel_p_user'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            // attributs si l'utilisateur est intervenant
            $id_statut_intervenant = isset($parameters['id_statut_intervenant']) ?
                filter_var($parameters['id_statut_intervenant'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $numero_carte = $parameters['numero_carte'] ?? "";
            $diplomes = isset($parameters['diplomes']) ?
                filter_var_array($parameters['diplomes'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $id_intervenant = isset($parameters['id_intervenant']) ?
                filter_var($parameters['id_intervenant'], FILTER_SANITIZE_NUMBER_INT) :
                null;

            // attributs si coordinateur
            $est_coordinateur_peps = isset($parameters['est_coordinateur_peps']) ?
                ($parameters['est_coordinateur_peps'] ? "1" : "0") :
                "0";
            if (!in_array("2", $role_user_ids)) {
                $est_coordinateur_peps = "0"; // valeur par défaut si non coordinateur
            }

            // attributs si l'utilisateur est superviseur PEPS
            $nom_fonction = $parameters['nom_fonction'] ?? null;
            if (!in_array("7", $role_user_ids)) {
                $nom_fonction = null; // valeur par défaut si non superviseur PEPS
            }

            // vérification que l'email n'est pas déjà utilisé
            $query = '
                SELECT COUNT(*) as nb_email
                FROM users where identifiant = :identifiant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':identifiant', $email_user);
            if (!$statement->execute()) {
                throw new Exception('Error: SELECT COUNT(*) FROM users');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_email'] > 0)) {
                throw new Exception('Error: Email déjà utilisé');
            }

            // verification si l'utilisateur est un intervenant existant,
            // qu'il n'est pas déjà utilisateur et que l'intervenant existe
            $id_coordonnee = null;
            if ($id_intervenant != null) {
                $query = '
                    SELECT id_user, coordonnees.id_coordonnees
                    FROM coordonnees
                             JOIN intervenants i on coordonnees.id_coordonnees = i.id_coordonnees
                    WHERE i.id_intervenant = :id_intervenant';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $id_intervenant);
                if (!$statement->execute()) {
                    throw new Exception('Error: SELECT id_user, coordonnees.id_coordonnees FROM coordonnees');
                }
                if ($statement->rowCount() == 0) {
                    throw new Exception('Error: L\'intervenant n\'a pas été trouvé');
                }
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                if ($data['id_user'] != null) {
                    throw new Exception('Error: L\'intervenant est déjà utilisateur');
                }
                $id_coordonnee = $data['id_coordonnees'] ?? null;
            }

            // INSERT dans coordonnées
            if ($id_coordonnee == null) {
                $query = '
                    INSERT INTO coordonnees
                        (nom_coordonnees, prenom_coordonnees, tel_fixe_coordonnees, tel_portable_coordonnees, mail_coordonnees)
                    VALUES (:nom_user, :prenom_user, :tel_f_user, :tel_p_user, :email_user)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':nom_user', $nom_user);
                $statement->bindValue(':prenom_user', $prenom_user);
                $statement->bindValue(':tel_f_user', $tel_f_user);
                $statement->bindValue(':tel_p_user', $tel_p_user);
                $statement->bindValue(':email_user', $email_user);
                if (!$statement->execute()) {
                    throw new Exception('Error insert coordonnees');
                }
                $id_coordonnee = $this->pdo->lastInsertId();
            } else {
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
                $statement->bindValue(':nom_coordonnees', $nom_user);
                $statement->bindValue(':prenom_coordonnees', $prenom_user);
                $statement->bindValue(':mail_coordonnees', $email_user);
                $statement->bindValue(':tel_fixe_coordonnees', $tel_f_user);
                $statement->bindValue(':tel_portable_coordonnees', $tel_p_user);
                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE coordonnees');
                }
            }

            if (in_array("3", $role_user_ids)) {
                if ($id_intervenant == null) { // intervenant n'existe pas encore
                    // Ajout dans la table intervenant
                    $query = '
                        INSERT INTO intervenants (id_coordonnees, numero_carte, id_statut_intervenant, id_territoire)
                        VALUES (:id_coordonnees, :numero_carte, :id_statut_intervenant, :id_territoire)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_coordonnees', $id_coordonnee);
                    $statement->bindValue(':id_statut_intervenant', $id_statut_intervenant);
                    $statement->bindValue(':id_territoire', $id_territoire);
                    if (empty($numero_carte)) {
                        $statement->bindValue(':numero_carte', null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(':numero_carte', $numero_carte);
                    }
                    if (!$statement->execute()) {
                        throw new Exception('Error insert intervenants');
                    }
                    $id_intervenant = $this->pdo->lastInsertId();

                    // UPDATE de l'id du intervenant dans coordonnées
                    $query = '
                        UPDATE coordonnees
                        SET id_intervenant = :id_intervenant
                        WHERE id_coordonnees = :id_coordonnee';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_coordonnee', $id_coordonnee);
                    $statement->bindValue(':id_intervenant', $id_intervenant);
                    if (!$statement->execute()) {
                        throw new Exception('Error UPDATE coordonnees');
                    }

                    // Ajout dans la table a obtenu
                    if (is_array($diplomes)) {
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
                    }

                    // Ajout dans la table intervient_dans
                    $query = '
                            INSERT INTO intervient_dans (id_intervenant, id_structure)
                            VALUES (:id_intervenant, :id_structure)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_intervenant', $id_intervenant);
                    $statement->bindValue(':id_structure', $id_structure);
                    if (!$statement->execute()) {
                        throw new Exception('Error INTO intervient_dans');
                    }
                } else { // intervenant existant
                    $query = '
                        UPDATE intervenants
                        SET id_statut_intervenant = :id_statut_intervenant,
                            numero_carte          = :numero_carte,
                            id_territoire         = :id_territoire
                        WHERE id_intervenant = :id_intervenant';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_statut_intervenant', $id_statut_intervenant);
                    $statement->bindValue(':numero_carte', $numero_carte);
                    $statement->bindValue(':id_intervenant', $id_intervenant);
                    $statement->bindValue(':id_territoire', $id_territoire);
                    if (!$statement->execute()) {
                        throw new Exception('Error UPDATE intervenants');
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

                    // verification si la structure n'est pas déjà dans intervient_dans
                    $query = '
                        SELECT COUNT(*) as nb_intervention
                        FROM intervient_dans
                        WHERE id_structure = :id_structure
                          AND id_intervenant = :id_intervenant ';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_intervenant', $id_intervenant);
                    $statement->bindValue(':id_structure', $id_structure);
                    if (!$statement->execute()) {
                        throw new Exception('Error INTO intervient_dans');
                    }

                    $data = $statement->fetch(PDO::FETCH_ASSOC);
                    if (intval($data['nb_intervention'] == 0)) {
                        // Ajout dans la table intervient_dans
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
                }
            }

            // INSERT dans users
            $query = '
                INSERT INTO users
                    (identifiant, pswd, id_coordonnees, id_structure, id_territoire,
                     est_coordinateur_peps, fonction)
                VALUES (:identifiant, :pswd, :id_coordonnees, :id_structure, :id_territoire,
                        :est_coordinateur_peps, :fonction)';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':identifiant', $email_user);
            $statement->bindValue(':pswd', $mdp);
//            $statement->bindValue(':id_role_user', $id_role_user);
            $statement->bindValue(':id_coordonnees', $id_coordonnee);
            $statement->bindValue(':id_territoire', $id_territoire);
            $statement->bindValue(':est_coordinateur_peps', $est_coordinateur_peps);
            if (empty($nom_fonction)) {
                $statement->bindValue(':fonction', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':fonction', $nom_fonction);
            }
            if (empty($id_structure)) {
                $statement->bindValue(':id_structure', null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(':id_structure', $id_structure);
            }
            if (!$statement->execute()) {
                throw new Exception('Error insert users');
            }
            $id_user = $this->pdo->lastInsertId();

            // INSERT des roles
            foreach ($role_user_ids as $id_role_user) {
                $query = '
                    INSERT INTO a_role (id_user, id_role_user)
                    VALUES (:id_user, :id_role_user)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_role_user', $id_role_user);
                $statement->bindValue(':id_user', $id_user);
                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO a_role');
                }
            }

            // UPDATE de l'id du user dans coordonnées
            $query = '
                UPDATE coordonnees
                SET id_user = :id_user
                WHERE id_coordonnees = :id_coordonnee';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_coordonnee', $id_coordonnee);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            if ($id_structure != null) {
                // INSERT dans intervention
                $query = '
                INSERT INTO intervention (id_user, id_structure)
                VALUES (:id_user, :id_structure)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user', $id_user);
                $statement->bindValue(':id_structure', $id_structure);
                if (!$statement->execute()) {
                    throw new Exception('Error insert users');
                }
            }

            $this->pdo->commit();
            return $id_user;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function update($parameters)
    {
        if (empty($parameters['id_user']) ||
            !$this->checkParameters($parameters)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $role_user_ids = filter_var_array($parameters['role_user_ids'], FILTER_SANITIZE_NUMBER_INT);

            $id_user = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);
            $nom_user = trim(mb_strtoupper($parameters['nom_user'], 'UTF-8'));
            $prenom_user = $parameters['prenom_user'];
            $email_user = $parameters['email_user'];

            // paramètres optionnels
            // hashage du mdp
            $mdp = isset($parameters['mdp']) ?
                password_hash($parameters['mdp'], PASSWORD_DEFAULT) :
                null;
            $is_mdp_modified = $parameters['is_mdp_modified'] ?? false;
            $id_structure = isset($parameters['id_structure']) ?
                filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $tel_f_user = isset($parameters['tel_f_user']) ?
                filter_var(
                    $parameters['tel_f_user'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_p_user = isset($parameters['tel_p_user']) ?
                filter_var(
                    $parameters['tel_p_user'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $settings = $parameters['settings'] ?? [];

            // attributs si l'utilisateur est intervenant
            $id_statut_intervenant = isset($parameters['id_statut_intervenant']) ?
                filter_var($parameters['id_statut_intervenant'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $numero_carte = $parameters['numero_carte'] ?? "";
            $diplomes = isset($parameters['diplomes']) ?
                filter_var_array($parameters['diplomes'], FILTER_SANITIZE_NUMBER_INT) :
                [];

            // attributs si coordinateur
            $est_coordinateur_peps = isset($parameters['est_coordinateur_peps']) ?
                ($parameters['est_coordinateur_peps'] ? "1" : "0") :
                "0";
            if (!in_array("2", $role_user_ids)) {
                $est_coordinateur_peps = "0"; // valeur par défaut si non coordinateur
            }

            // attributs si l'utilisateur est superviseur PEPS
            $nom_fonction = $parameters['nom_fonction'] ?? null;
            if (!in_array("7", $role_user_ids)) {
                $nom_fonction = null; // valeur par défaut si non superviseur PEPS
            }

            // is_deactivated n'est update que s'il n'est pas null
            $is_deactivated = null;
            if (isset($parameters['is_deactivated'])) {
                $is_deactivated = $parameters['is_deactivated'] ? "1" : 0;
            }

            // vérification que l'email n'est pas déjà utilisé
            $query = '
                SELECT COUNT(*) as nb_email
                FROM users where identifiant = :identifiant AND id_user <> :id_user';
            $statement = $this->pdo->prepare($query);

            $statement->bindValue(':identifiant', $email_user);
            $statement->bindValue(':id_user', $id_user);

            if (!$statement->execute()) {
                throw new Exception('Error: SELECT COUNT(*) FROM users');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_email'] > 0)) {
                throw new Exception('Error: Email déjà utilisé');
            }

            ////////////////////////////////////////////////////
            // UPDATE Coordonnees
            ////////////////////////////////////////////////////
            $query = '
                UPDATE coordonnees
                SET nom_coordonnees          = :nom_coordonnees,
                    prenom_coordonnees       = :prenom_coordonnees,
                    tel_fixe_coordonnees     = :tel_fixe_coordonnees,
                    tel_portable_coordonnees = :tel_portable_coordonnees,
                    mail_coordonnees         = :email
                WHERE id_user = :id_user';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_user', $id_user);
            $stmt->bindValue(':nom_coordonnees', $nom_user);
            $stmt->bindValue(':prenom_coordonnees', $prenom_user);
            $stmt->bindValue(':tel_fixe_coordonnees', $tel_f_user);
            $stmt->bindValue(':tel_portable_coordonnees', $tel_p_user);
            $stmt->bindValue(':email', $email_user);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            ////////////////////////////////////////////////////
            // UPDATE user
            ////////////////////////////////////////////////////
            $query = '
                UPDATE users
                SET id_structure          = :id_structure,
                    identifiant           = :email,
                    id_territoire         = :id_territoire,
                    est_coordinateur_peps = :est_coordinateur_peps,
                    fonction              = :fonction
                WHERE id_user = :id_user';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_user', $id_user);
            $stmt->bindValue(':id_territoire', $id_territoire);
            $stmt->bindValue(':email', $email_user);
            $stmt->bindValue(':est_coordinateur_peps', $est_coordinateur_peps);
            if (empty($nom_fonction)) {
                $stmt->bindValue(':fonction', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':fonction', $nom_fonction);
            }
            if (empty($id_structure)) {
                $stmt->bindValue(':id_structure', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':id_structure', $id_structure);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE users');
            }

            ////////////////////////////////////////////////////
            // UPDATE des settings
            ////////////////////////////////////////////////////
            if (is_array($settings)) {
                foreach ($settings as $setting) {
                    // on verifie si l'user a déja defini le setting
                    $query = '
                        SELECT id_user_setting
                        FROM user_settings
                        WHERE id_user = :id_user
                          AND id_setting = :id_setting';
                    $stmt = $this->pdo->prepare($query);

                    $stmt->bindValue(':id_user', $id_user);
                    $stmt->bindValue(':id_setting', $setting['id_setting']);

                    if (!$stmt->execute()) {
                        throw new Exception('Error SELECT FROM user_settings');
                    }

                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $id_user_setting = $data['id_user_setting'] ?? null;

                    if (empty($id_user_setting)) {
                        // on insert un nouveau user_settings
                        $query_insert_settings = '
                            INSERT INTO user_settings (id_setting, id_user, valeur)
                            VALUES (:id_setting, :id_user, :valeur)';
                        $stmt_insert_settings = $this->pdo->prepare($query_insert_settings);

                        $stmt_insert_settings->bindValue(':id_setting', $setting['id_setting']);
                        $stmt_insert_settings->bindValue(':valeur', $setting['valeur']);
                        $stmt_insert_settings->bindValue(':id_user', $id_user);

                        if (!$stmt_insert_settings->execute()) {
                            throw new Exception('Error UPDATE user_settings');
                        }
                    } else {
                        // on update l'user_settings existant
                        $query_update_settings = '
                            UPDATE user_settings
                            SET valeur = :valeur
                            WHERE id_user_setting = :id_user_setting';
                        $stmt_update_settings = $this->pdo->prepare($query_update_settings);

                        $stmt_update_settings->bindValue(':valeur', $setting['valeur']);
                        $stmt_update_settings->bindValue(':id_user_setting', $id_user_setting);

                        if (!$stmt_update_settings->execute()) {
                            throw new Exception('Error UPDATE user_settings');
                        }
                    }
                }
            }

            ////////////////////////////////////////////////////
            // UPDATE du mot de passe
            ////////////////////////////////////////////////////
            if ($is_mdp_modified && $mdp != null && $mdp != "") {
                $query = "
                    UPDATE users
                    SET pswd = :pswd
                    WHERE id_user = :id_user";

                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_user', $id_user);
                $stmt->bindValue(':pswd', $mdp);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE users pwd');
                }
            }

            ////////////////////////////////////////////////////
            // Select de id_coordonnees de l'utilisateur
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_coordonnees
                FROM users
                WHERE id_user = :id_user';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":id_user", $id_user);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                return false;
            }
            $data_role = $stmt->fetch();
            $id_coordonnees = $data_role['id_coordonnees'];

            ////////////////////////////////////////////////////
            // Select du rôle actuel de l'utilisateur
            ////////////////////////////////////////////////////
            $query = "
                SELECT id_role_user
                FROM users
                JOIN a_role ar on users.id_user = ar.id_user
                WHERE users.id_user = :id_user";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_user", $id_user);
            $stmt->execute();
            $old_role_user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if (in_array("3", $old_role_user_ids) && !in_array("3", $role_user_ids)) {
                // l'user n'est plus intervenant

                // recup de l'id_intervenant
                $query = '
                        SELECT id_intervenant
                        FROM intervenants
                        WHERE id_coordonnees = :id_coordonnees';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(":id_coordonnees", $id_coordonnees);
                if ($stmt->execute()) {
                    $data = $stmt->fetch();
                    $id_intervenant = $data['id_intervenant'];
                    if (empty($id_intervenant)) {
                        throw new Exception('Error $id_intervenant empty');
                    }
                } else {
                    throw new Exception('Error SELECT id_intervenant');
                }

                ////////////////////////////////////////////////////
                // verification si l'intervenant à des créneaux
                ////////////////////////////////////////////////////
                $query = '
                        SELECT count(*) AS nb_creneaux
                        FROM creneaux_intervenant
                        WHERE id_intervenant = :id_intervenant';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_intervenant', $id_intervenant);

                if ($stmt->execute()) {
                    $data = $stmt->fetch();
                    if (intval($data['nb_creneaux']) > 0) {
                        throw new Exception('Error: Cet utilisateur intervient dans des créneaux');
                    }
                } else {
                    throw new Exception('Error SELECT count(*) as nb_creneaux');
                }

                // UPDATE de l'id du intervenant dans coordonnées
                $query = '
                        UPDATE coordonnees
                        SET id_intervenant = null
                        WHERE id_coordonnees = :id_coordonnees';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_coordonnees', $id_coordonnees);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE coordonnees');
                }

                // suppresion des diplomes
                $query_delete = 'DELETE FROM a_obtenu WHERE id_intervenant = :id_intervenant';
                $stmt_delete = $this->pdo->prepare($query_delete);
                $stmt_delete->bindValue(':id_intervenant', $id_intervenant);
                if (!$stmt_delete->execute()) {
                    throw new Exception('Error DELETE FROM a_obtenu');
                }

                // suppresion intervient_dans
                $query_delete = '
                        DELETE
                        FROM intervient_dans
                        WHERE id_intervenant = :id_intervenant';
                $stmt_delete = $this->pdo->prepare($query_delete);
                $stmt_delete->bindValue(':id_intervenant', $id_intervenant);

                if (!$stmt_delete->execute()) {
                    throw new Exception('Error DELETE FROM intervient_dans');
                }

                $query_delete = 'DELETE FROM intervenants WHERE id_intervenant = :id_intervenant';
                $stmt_delete = $this->pdo->prepare($query_delete);
                $stmt_delete->bindValue(':id_intervenant', $id_intervenant);

                if (!$stmt_delete->execute()) {
                    throw new Exception('Error DELETE FROM intervenants');
                }
            } elseif (!in_array("3", $old_role_user_ids) && in_array("3", $role_user_ids)) {
                // l'user n'était pas intervenant mais en est un maintenant

                // INSERT dans intervenants
                $query = '
                        INSERT INTO intervenants
                        (id_coordonnees, id_territoire, numero_carte, id_statut_intervenant)
                        VALUES (:id_coordonnee, :id_territoire, :numero_carte, :id_statut_intervenant)';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_coordonnee', $id_coordonnees);
                $stmt->bindValue(':id_territoire', $id_territoire);
                $stmt->bindValue(':numero_carte', $numero_carte);
                $stmt->bindValue(':id_statut_intervenant', $id_statut_intervenant);

                if ($stmt->execute()) {
                    $id_intervenant = $this->pdo->lastInsertId();
                } else {
                    throw new Exception('Error insert intervenants');
                }

                // UPDATE de l'id du intervenant dans coordonnées
                $query = '
                        UPDATE coordonnees
                        SET id_intervenant = :id_intervenant
                        WHERE id_coordonnees = :id_coordonnee';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_coordonnee', $id_coordonnees);
                $stmt->bindValue(':id_intervenant', $id_intervenant);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE coordonnees');
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
            } elseif (in_array("3", $old_role_user_ids) && in_array("3", $role_user_ids)) {
                // l'user reste intervenant

                $query = '
                        SELECT id_intervenant
                        FROM users
                        JOIN coordonnees c on c.id_coordonnees = users.id_coordonnees
                        WHERE c.id_user = :id_user';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_user', $id_user);
                $stmt->execute();
                $data = $stmt->fetch();
                $id_intervenant = $data['id_intervenant'];

                if (empty($id_intervenant)) {
                    throw new Exception('Error select id_intervenant');
                }

                // UPDATE l'intervenant
                $query = '
                        UPDATE intervenants
                        SET id_statut_intervenant = :id_statut_intervenant,
                            numero_carte          = :numero_carte,
                            id_territoire         = :id_territoire
                        WHERE id_intervenant = :id_intervenant';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_statut_intervenant', $id_statut_intervenant);
                $statement->bindValue(':numero_carte', $numero_carte);
                $statement->bindValue(':id_intervenant', $id_intervenant);
                $statement->bindValue(':id_territoire', $id_territoire);

                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE intervenants');
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
            }

            // update des rôles
            $query = '
                DELETE
                FROM a_role
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM a_role');
            }

            foreach ($role_user_ids as $id_role_user) {
                $query = '
                    INSERT INTO a_role (id_user, id_role_user)
                    VALUES (:id_user, :id_role_user)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user', $id_user);
                $statement->bindValue(':id_role_user', $id_role_user);

                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO a_role');
                }
            }

            // désactivation du compte
            if (isset($is_deactivated)) {
                $query = '
                    UPDATE users
                    SET is_deactivated            = :is_deactivated,
                        is_deactivated_updated_at = NOW()
                    WHERE id_user = :id_user';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_user', $id_user);
                $stmt->bindValue(':is_deactivated', $is_deactivated);
                if (!$stmt->execute()) {
                    throw new Exception('Error désactivation du compte');
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
     * Deletes a user
     *
     * @param $id_user string the id of the user to be deleted
     * @return bool if the deletion was successful
     */
    public function delete($id_user): bool
    {
        if (empty($id_user)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // verification si l'utilisateur existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) AS nb_user
                FROM users
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_user']) == 0) {
                    throw new Exception('Error: Cet utilisateur n\'existe pas');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_user');
            }

            ////////////////////////////////////////////////////
            // verification si l'utilisateur a mis une observation
            ////////////////////////////////////////////////////
            $query = '
                SELECT 
                    count(*) AS nb_observations
                FROM observation
                         JOIN users on observation.id_user = users.id_user
                WHERE observation.id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_observations']) > 0) {
                    throw new Exception('Error: Cet utilisateur possède des observations');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_observations');
            }

            ////////////////////////////////////////////////////
            // verification si l'utilisateur a ajoute un patient
            ////////////////////////////////////////////////////
            $query = '
                SELECT 
                    count(*) AS nb_patients
                FROM patients
                         JOIN users on patients.id_user = users.id_user
                WHERE patients.id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_patients']) > 0) {
                    throw new Exception('Error: Cet utilisateur a ajoute des patients');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_patients');
            }

            ////////////////////////////////////////////////////
            // verification si l'utilisateur a ajoute une evaluation
            ////////////////////////////////////////////////////
            $query = '
                SELECT 
                    count(*) AS nb_evaluations
                FROM evaluations
                         JOIN users on evaluations.id_user = users.id_user
                WHERE evaluations.id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_evaluations']) > 0) {
                    throw new Exception('Error: Cet utilisateur a ajoute des evaluations');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_evaluations');
            }

            ////////////////////////////////////////////////////
            // verification si l'utilisateur a ajoute un questionnaire
            ////////////////////////////////////////////////////
            $query = '
                SELECT 
                    count(*) AS nb_questionnaires
                FROM questionnaire_instance
                         JOIN users on questionnaire_instance.id_user = users.id_user
                WHERE questionnaire_instance.id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if ($statement->execute()) {
                $data = $statement->fetch();
                if (intval($data['nb_questionnaires']) > 0) {
                    throw new Exception('Error: Cet utilisateur a ajoute des questionnaires');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_questionnaires');
            }

            ////////////////////////////////////////////////////
            // SELECT id_coordonnees
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_coordonnees FROM coordonnees
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if ($statement->execute()) {
                $data = $statement->fetch();
                $id_coordonnees = $data['id_coordonnees'];
            } else {
                throw new Exception('Error SELECT id_coordonnees');
            }

            // verification si l'utilisateur est un intervenant existant
            ////////////////////////////////////////////////////
            // SELECT id_intervenant
            ////////////////////////////////////////////////////
            $query = '
                SELECT 
                    id_intervenant
                FROM coordonnees
                         JOIN users on coordonnees.id_coordonnees = users.id_coordonnees
                WHERE coordonnees.id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);

            if ($statement->execute()) {
                $data = $statement->fetch();
                $id_intervenant = $data['id_intervenant'];
            } else {
                throw new Exception('Error SELECT id_intervenant');
            }

            if (!is_null($id_intervenant)) {
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
                // verification si l'intervenant à des seances
                ////////////////////////////////////////////////////
                $query = '
                    SELECT count(*) AS nb_seances
                    FROM seance
                    WHERE id_user = :id_user';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_user', $id_user);
                if ($statement->execute()) {
                    $data = $statement->fetch();
                    if (intval($data['nb_seances']) > 0) {
                        throw new Exception('Error: Cet intervenant intervient dans des séances');
                    }
                } else {
                    throw new Exception('Error SELECT count(*) as nb_seances');
                }

                ////////////////////////////////////////////////////
                // DELETE FROM a_obtenu
                ////////////////////////////////////////////////////
                $query = "
                    DELETE FROM a_obtenu
                    WHERE id_intervenant = :id_intervenant";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $id_intervenant);
                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM a_obtenu');
                }

                ////////////////////////////////////////////////////
                // DELETE FROM intervient_dans
                ////////////////////////////////////////////////////
                $query = "
                    DELETE FROM intervient_dans
                    WHERE id_intervenant = :id_intervenant";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_intervenant', $id_intervenant);
                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM intervient_dans');
                }
            }

            ////////////////////////////////////////////////////
            // DELETE FROM intervenants
            ////////////////////////////////////////////////////
            $query = "
                DELETE FROM intervenants
                WHERE id_coordonnees = :id_coordonnees";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_coordonnees', $id_coordonnees);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervenants');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM intervention
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM intervention
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM intervention');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM coordonnees
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM coordonnees
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM users
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM users
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM users');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM user_settings
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM user_settings
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM user_settings');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM a_role
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM a_role
                WHERE id_user = :id_user';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_user', $id_user);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM a_role');
            }

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error activating foreign key checks');
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
     * session parameters:
     * [
     *     'id_role_user' => string,
     *     'est_coordinateur_peps' => bool,
     *     'id_statut_structure' => string, // nécessaire si utilisateur non-admin
     *     'id_structure' => string, // nécessaire si utilisateur non-admin
     *     'id_territoire' => string,
     * ]
     *
     * @param array $session
     * @return array|false Returns an array of associative arrays or false on failure
     */
    public function readAll(array $session)
    {
        if (empty($session['role_user_ids']) ||
            !isset($session['est_coordinateur_peps']) ||
            empty($session['id_territoire'])) {
            return false;
        }

        try {
            // roles qui ont accès à la liste des évaluateurs
            $authorized_roles = [
                Permissions::COORDONNATEUR_NON_MSS,
                Permissions::COORDONNATEUR_MSS,
                Permissions::COORDONNATEUR_PEPS,
                Permissions::SUPER_ADMIN,
                Permissions::RESPONSABLE_STRUCTURE,
                Permissions::EVALUATEUR,
            ];

            $permissions = new Permissions($session);
            $roles_user = $permissions->getRolesUser();

            if (array_intersect($authorized_roles, $roles_user) == []) {
                return [];
            }
        } catch (Exception $e) {
            return false;
        }

        // la structure est obligatoire pour les utilisateurs qui ne sont pas admin
        $id_structure_present_if_required =
            (!in_array("1", $session['role_user_ids']) && !empty($session['id_structure'])) ||
            in_array("1", $session['role_user_ids']);

        // la id_statut_structure est obligatoire pour les utilisateurs qui ne sont pas admin
        $id_statut_structure_present_if_required =
            (!in_array("1", $session['role_user_ids']) && !empty($session['id_statut_structure'])) ||
            in_array("1", $session['role_user_ids']);

        if (!$id_structure_present_if_required ||
            !$id_statut_structure_present_if_required) {
            return false;
        }

        $query = '
            SELECT DISTINCT users.id_territoire,
                            nom_coordonnees          as nom_user,
                            prenom_coordonnees       as prenom_user,
                            tel_fixe_coordonnees     as tel_f_user,
                            tel_portable_coordonnees as tel_p_user,
                            mail_coordonnees         as email_user,
                            users.id_user            as id_user,
                            nom_territoire,
                            coordonnees.id_intervenant
            FROM coordonnees
                     JOIN users USING (id_user)
                     JOIN territoire on users.id_territoire = territoire.id_territoire
                     JOIN a_role ar on users.id_user = ar.id_user ';

        if ($permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permissions->hasRole(Permissions::EVALUATEUR)) {
            $query .= " WHERE users.id_structure = " . $session['id_structure'] . " AND ar.id_role_user <> 1 ";
        } elseif ($permissions->hasRole(Permissions::RESPONSABLE_STRUCTURE)) {
            $query .= " WHERE users.id_structure = " . $session['id_structure']
                . " AND (ar.id_role_user = 3 OR ar.id_role_user = 6)";
        } elseif (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= " WHERE users.id_territoire = " . $session['id_territoire'] . " AND ar.id_role_user <> 1 ";
        }
        $query .= " ORDER BY id_user ";

        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute()) {
            return false;
        }

        $users = [];
        while ($users_item = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // recup info sur la structure de l'user
            $query_struc = '
                SELECT structure.id_structure,
                       nom_structure,
                       nom_statut_structure,
                       complement_adresse,
                       nom_adresse,
                       code_postal,
                       nom_ville
                FROM users
                         JOIN structure USING (id_structure)
                         JOIN statuts_structure USING (id_statut_structure)
                         JOIN se_situe_a on structure.id_structure = se_situe_a.id_structure
                         JOIN adresse USING (id_adresse)
                         JOIN se_localise_a USING (id_adresse)
                         JOIN villes USING (id_ville)
                WHERE id_user = :id_user';
            $stmt_struc = $this->pdo->prepare($query_struc);
            $stmt_struc->bindValue(':id_user', $users_item['id_user']);
            if (!$stmt_struc->execute()) {
                return false;
            }
            $data = $stmt_struc->fetch(PDO::FETCH_ASSOC);

            $users_item["structure"] = [
                "id_structure" => $data['id_structure'] ?? null,
                "nom_structure" => $data['nom_structure'] ?? null,
                "nom_statut_structure" => $data['nom_statut_structure'] ?? null,
                "complement_adresse" => $data['complement_adresse'] ?? null,
                "nom_adresse" => $data['nom_adresse'] ?? null,
                "code_postal" => $data['code_postal'] ?? null,
                "nom_ville" => $data['nom_ville'] ?? null
            ];

            // recup roles
            $roles_str = $this->getRoles($users_item['id_user']);
            if (!$roles_user) {
                return false;
            }
            $users_item["role_user"] = $roles_str;

            // recup d'autres infos si l'utilisateur est un intervenant
            if (!is_null($users_item['id_intervenant'])) {
                // infos intervenant
                $query_int = '
                    SELECT i.id_intervenant,
                           numero_carte,
                           id_statut_intervenant
                    FROM users
                             JOIN coordonnees c on users.id_coordonnees = c.id_coordonnees
                             JOIN intervenants i on c.id_intervenant = i.id_intervenant
                    WHERE users.id_user = :id_user';
                $stmt_int = $this->pdo->prepare($query_int);
                $stmt_int->bindValue(':id_user', $users_item['id_user']);
                if (!$stmt_int->execute()) {
                    return false;
                }
                $data = $stmt_int->fetch(PDO::FETCH_ASSOC);

                $users_item["id_intervenant"] = $data["id_intervenant"] ?? '';
                $users_item["numero_carte"] = $data['numero_carte'] ?? '';
                $users_item["id_statut_intervenant"] = $data['id_statut_intervenant'] ?? '';

                // diplomes d'un intervenant
                $query_int = '
                    SELECT id_diplome                           
                    FROM users
                             JOIN coordonnees c on users.id_coordonnees = c.id_coordonnees
                             JOIN intervenants i on c.id_intervenant = i.id_intervenant
                             LEFT JOIN a_obtenu ao on c.id_intervenant = ao.id_intervenant
                    WHERE users.id_user = :id_user';
                $stmt_int = $this->pdo->prepare($query_int);
                $stmt_int->bindValue(':id_user', $users_item['id_user']);
                if (!$stmt_int->execute()) {
                    return false;
                }
                while ($data_diplome = $stmt_int->fetch(PDO::FETCH_ASSOC)) {
                    $users_item["diplomes"][] = $data_diplome['id_diplome'];
                }
            } else {
                $users_item['id_intervenant'] = "";
                $users_item['numero_carte'] = "";
                $users_item['id_statut_intervenant'] = "";
                $users_item['diplomes'] = [];
            }

            $users[] = $users_item;
        }

        return $users;
    }

    /**
     * Récupération des évaluateurs (évaluateurs + coordos)
     *
     * session parameters:
     * [
     *     'id_role_user' => string,
     *     'est_coordinateur_peps' => bool,
     *     'id_statut_structure' => string, // nécessaire si utilisateur non-admin
     *     'id_structure' => string, // nécessaire si utilisateur non-admin
     *     'id_territoire' => string,
     * ]
     *
     * @param array $session
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAllEvaluateur(array $session)
    {
        if (empty($session['id_territoire'])) {
            return false;
        }

        try {
            // roles qui ont accès à la liste des évaluateurs
            $authorized_roles = [
                Permissions::COORDONNATEUR_NON_MSS,
                Permissions::COORDONNATEUR_MSS,
                Permissions::COORDONNATEUR_PEPS,
                Permissions::SUPER_ADMIN,
                Permissions::SECRETAIRE,
            ];

            $permissions = new Permissions($session);
            $roles_user = $permissions->getRolesUser();

            if (array_intersect($authorized_roles, $roles_user) == []) {
                return [];
            }
        } catch (Exception $e) {
            return false;
        }

        $query = "
            SELECT DISTINCT *
            FROM ((SELECT c.id_user,
                          nom_coordonnees    as nom_evaluateur,
                          prenom_coordonnees as prenom_evaluateur,
                          nom_structure as structure_evaluateur
                   FROM users u
                            JOIN structure s on u.id_structure = s.id_structure
                            JOIN coordonnees c on u.id_coordonnees = c.id_coordonnees
                            JOIN a_role ar on u.id_user = ar.id_user
                   WHERE ar.id_role_user = 5";
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= " AND u.id_territoire = :id_territoire_1";
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS) ||
            $permissions->hasRole(Permissions::SECRETAIRE)) {
            $query .= " AND u.id_structure = :id_structure";
        }
        $query .= "
                  )
                  UNION
                  (SELECT c.id_user,
                          nom_coordonnees    as nom_evaluateur,
                          prenom_coordonnees as prenom_evaluateur,
                          nom_structure as structure_evaluateur
                  FROM users u
                            JOIN structure s on u.id_structure = s.id_structure
                            JOIN coordonnees c on u.id_coordonnees = c.id_coordonnees
                            JOIN a_role ar on u.id_user = ar.id_user
                  WHERE ar.id_role_user = 2 ";
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= " AND u.id_territoire = :id_territoire_2";
        }
        $query .= " 
            )) as user_eval_and_coordo
            ORDER BY nom_evaluateur";

        $stmt = $this->pdo->prepare($query);
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt->bindValue(':id_territoire_1', $session['id_territoire']);
            $stmt->bindValue(':id_territoire_2', $session['id_territoire']);
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS) ||
            $permissions->hasRole(Permissions::SECRETAIRE)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
        }

        $stmt->execute();
        $evaluateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($evaluateurs as $key => $evaluateur) {
            $roles_str = $this->getRoles($evaluateur['id_user']);
            if (!$roles_str) {
                return false;
            }
            $evaluateurs[$key]["role_user"] = $roles_str;
        }

        return $evaluateurs;
    }

    /**
     * Récupération des coordonnateurs PEPS dans la même région que l'utilisateur
     *
     * session parameters:
     * [
     *     'id_role_user' => string,
     *     'est_coordinateur_peps' => bool,
     *     'id_territoire' => string,
     * ]
     *
     * @param array $session
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAllCoordosPEPS($session)
    {
        if (empty($session['id_territoire'])) {
            return false;
        }

        $permission = new Permissions($session);
        if (!$permission->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            return false;
        }

        $query = "
            SELECT DISTINCT c.id_user,
                            nom_coordonnees    as nom_coordo_peps,
                            prenom_coordonnees as prenom_coordo_peps,
                            nom_territoire as territoire_coordo_peps
            FROM users u
                 JOIN coordonnees c on u.id_coordonnees = c.id_coordonnees
                 JOIN a_role ar on u.id_user = ar.id_user
                 JOIN territoire t on u.id_territoire = t.id_territoire
            WHERE ar.id_role_user = 2 
            AND u.est_coordinateur_peps = 1 
            AND id_region = (SELECT id_region 
                                FROM territoire
                                WHERE id_territoire = :id_territoire)
            ORDER BY nom_coordo_peps";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue("id_territoire", $session['id_territoire']);
        if (!$stmt->execute()) {
            return false;
        }
        $coordos_peps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $coordos_peps;
    }

    /**
     * @param $id_user
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_user)
    {
        if (empty($id_user)) {
            return false;
        }

        $query = '
            SELECT users.id_territoire      as id_territoire,
                   nom_coordonnees          as nom_user,
                   prenom_coordonnees       as prenom_user,
                   tel_fixe_coordonnees     as tel_f_user,
                   tel_portable_coordonnees as tel_p_user,
                   mail_coordonnees         as email_user,
                   users.id_user            as id_user,
                   nom_territoire,
                   est_coordinateur_peps,
                   fonction                 as nom_fonction,
                   is_deactivated
            FROM coordonnees
                     JOIN users USING (id_user)
                     JOIN territoire t on users.id_territoire = t.id_territoire
            WHERE users.id_user = :id_user';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);
        if (!$statement->execute()) {
            return false;
        }
        if ($statement->rowCount() == 0) {
            return false;
        }
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        // récupération des rôles de l'user
        $query = "
            SELECT ar.id_role_user, role_user
            FROM users
            JOIN a_role ar on users.id_user = ar.id_user
            JOIN role_user ru on ar.id_role_user = ru.id_role_user
            WHERE users.id_user = :id_user";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(":id_user", $id_user);
        if (!$statement->execute()) {
            return false;
        }
        if ($statement->rowCount() == 0) {
            return false;
        }

        $user['role_user_ids'] = [];
        $roles = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $user['role_user_ids'][] = $row['id_role_user'];
            $roles[] = $row['role_user'];
        }
        $user["role_user"] = implode('/', $roles);

        // recup info sur la structure de l'user
        $query = '
            SELECT id_structure, nom_structure, nom_statut_structure, complement_adresse, nom_adresse, code_postal, nom_ville
            FROM users
                     JOIN structure USING (id_structure)
                     JOIN statuts_structure USING (id_statut_structure)
                     JOIN se_situe_a USING (id_structure)
                     JOIN adresse USING (id_adresse)
                     JOIN se_localise_a USING (id_adresse)
                     JOIN villes USING (id_ville)
            WHERE id_user = :id_user';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);
        if (!$statement->execute()) {
            return false;
        }
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        $user["structure"] = [
            "id_structure" => $data['id_structure'] ?? null,
            "nom_structure" => $data['nom_structure'] ?? null,
            "nom_statut_structure" => $data['nom_statut_structure'] ?? null,
            "complement_adresse" => $data['complement_adresse'] ?? null,
            "nom_adresse" => $data['nom_adresse'] ?? null,
            "code_postal" => $data['code_postal'] ?? null,
            "nom_ville" => $data['nom_ville'] ?? null
        ];

        // recup des settings de l'user
        $query = '
            SELECT id_user_setting, nom, valeur, s.id_setting
            FROM users
                     JOIN user_settings us on users.id_user = us.id_user
                     JOIN settings s on us.id_setting = s.id_setting
            WHERE users.id_user = :id_user';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);
        if (!$statement->execute()) {
            return false;
        }

        $user["settings"] = [];
        while ($row_settings = $statement->fetch(PDO::FETCH_ASSOC)) {
            $settings_item = [
                "id_user_setting" => $row_settings['id_user_setting'],
                "id_setting" => $row_settings['id_setting'],
                "nom" => $row_settings['nom'],
                "valeur" => $row_settings['valeur']
            ];
            $user["settings"][] = $settings_item;
        }

        // recup d'autres infos si l'utilisateur est un intervenant
        $user['id_intervenant'] = "";
        $user['numero_carte'] = "";
        $user['id_statut_intervenant'] = "";
        $user['diplomes'] = [];
        if (in_array("3", $user['role_user_ids'])) {
            $query = '
                SELECT i.id_intervenant,
                       numero_carte,
                       id_statut_intervenant
                FROM users
                         JOIN coordonnees c on users.id_coordonnees = c.id_coordonnees
                         JOIN intervenants i on c.id_intervenant = i.id_intervenant
                WHERE users.id_user = :id_user';
            $statement_int = $this->pdo->prepare($query);
            $statement_int->bindValue(':id_user', $id_user);
            if (!$statement_int->execute()) {
                return false;
            }
            $data = $statement_int->fetch(PDO::FETCH_ASSOC);

            // infos intervenant
            $user["id_intervenant"] = $data["id_intervenant"];
            $user["numero_carte"] = $data['numero_carte'];
            $user["id_statut_intervenant"] = $data['id_statut_intervenant'];

            // recup des diplomes
            $query = '
                SELECT d.id_diplome, d.nom_diplome
                FROM a_obtenu 
                     JOIN diplome d on a_obtenu.id_diplome = d.id_diplome
                WHERE a_obtenu.id_intervenant = :id_intervenant';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_intervenant', $user["id_intervenant"]);
            if (!$statement->execute()) {
                return false;
            }
            $user['diplomes'] = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $user;
    }

    /**
     * @param $id_user
     * @return false|string the roles of a user separated by "/"
     */
    public function getRoles($id_user)
    {
        if (empty($id_user)) {
            return false;
        }

        $query_roles = "
            SELECT role_user
            FROM users
            JOIN a_role ar on users.id_user = ar.id_user
            JOIN role_user ru on ar.id_role_user = ru.id_role_user
            WHERE users.id_user = :id_user";
        $stmt_roles = $this->pdo->prepare($query_roles);
        $stmt_roles->bindValue(":id_user", $id_user);
        if (!$stmt_roles->execute()) {
            return false;
        }
        if ($stmt_roles->rowCount() == 0) {
            return false;
        }
        $roles = $stmt_roles->fetchAll(PDO::FETCH_COLUMN, 0);

        return implode('/', $roles);
    }

    /**
     * @return string the error message of the last operation
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    private function checkParameters($parameters)
    {
        // verif de longueur du numéro carte si présente
        $is_numero_carte_valid = empty($parameters['numero_carte']) ||
            (is_string($parameters['numero_carte']) && strlen($parameters['numero_carte']) <= 11);

        // l'id_statut_intervenant est obligatoire pour les intervenants
        $intervenant_parameters_present_if_required =
            (in_array("3", $parameters['role_user_ids']) &&
                !empty($parameters['id_statut_intervenant'])) ||
            !in_array("3", $parameters['role_user_ids']);

        // est_coordinnateur_peps est obligatoire pour les coordonnateurs
        $coordonnateur_parameters_present_if_required =
            (in_array("2", $parameters['role_user_ids']) &&
                isset($parameters['est_coordinateur_peps'])) ||
            !in_array("2", $parameters['role_user_ids']);

        // la structure est obligatoire pour les utilisateurs qui ne sont pas admin
        $id_structure_present_if_required =
            (!in_array("1", $parameters['role_user_ids']) && !empty($parameters['id_structure'])) ||
            in_array("1", $parameters['role_user_ids']);

        // le mdp est obligatoire pour un utilisateur qui n'existe pas encore existant
        $mdp_present_if_required =
            (empty($parameters['id_user']) && !empty($parameters['mdp'])) ||
            !empty($parameters['id_user']);

        return
            $is_numero_carte_valid &&
            $intervenant_parameters_present_if_required &&
            $coordonnateur_parameters_present_if_required &&
            $id_structure_present_if_required &&
            $mdp_present_if_required &&
            !empty($parameters['nom_user']) &&
            !empty($parameters['prenom_user']) &&
            !empty($parameters['role_user_ids']) &&
            !empty($parameters['email_user']) &&
            !empty($parameters['id_territoire']);
    }
}