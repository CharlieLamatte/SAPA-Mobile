<?php

namespace Sportsante86\Sapa\Outils;

use Exception;
use PDO;

class Authentification
{
    private PDO $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    /**
     * Return si l'utilisateur existe dans la BDD
     *
     * @param string $email L'email de l'utilisateur
     * @return bool Si l'utilisateur existe dans la BDD
     */
    public function user_exists($email)
    {
        $query = '
            SELECT id_user
            FROM users
            WHERE identifiant = :identifiant';
        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":identifiant", $email);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    /**
     * Return si le mot de passe de l'utilisateur donné est valide
     *
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe en clair
     * @return bool Si le mot de passe est valide
     */
    public function is_password_valid($email, $password)
    {
        $query = '
            SELECT id_user,
                   pswd as pswd_hash
            FROM users
            WHERE identifiant = :identifiant';
        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":identifiant", $email);
        if (!$stmt->execute()) {
            return false;
        }

        if ($stmt->rowCount() == 0) {
            return false;
        }
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return password_verify($password, $data["pswd_hash"]);
    }

    /**
     * Return si l'utilisateur est désactivé
     *
     * @param string $email L'email de l'utilisateur
     * @return bool Si l'utilisateur est désactivé (par default true, par exemple si l'utilisateur n'existe pas)
     */
    public function is_user_deactivated($email): bool
    {
        $query = '
            SELECT is_deactivated
            FROM users
            WHERE identifiant = :identifiant';
        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":identifiant", $email);
        if (!$stmt->execute()) {
            return true;
        }

        if ($stmt->rowCount() == 0) {
            return true;
        }
        $is_deactivated = $stmt->fetchColumn();

        return $is_deactivated == "1";
    }

    /**
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe en clair
     * @return bool si l'utilisateur a été login correctement
     * @throws Exception
     */
    public function login($email, $password)
    {
        if ($this->user_exists($email)) {
            if (!$this->is_password_valid($email, $password)) {
                throw new Exception("Mot de passe ou email invalide.");
            }

            if ($this->is_user_deactivated($email)) {
                throw new Exception("Le compte a été désactivé");
            }

            if (!$this->init_session($email)) {
                throw new Exception("Error init_session");
            }

            $this->update_compteur($_SESSION['id_user']);
        } else {
            throw new Exception("Mot de passe ou email invalide.");
        }

        return true;
    }

    /**
     * Initialise la session de l'utilisateur donné
     *
     * @param string $email L'email de l'utilisateur
     * @return bool Si la session a été créée correctement
     */
    public function init_session($email)
    {
        $_SESSION['is_connected'] = false;

        $roles_user = $this->get_roles_user($email);

        if (empty($roles_user)) {
            return false;
        }

        $query = "
            SELECT nom_coordonnees    as nom,
                   prenom_coordonnees as prenom,
                   mail_coordonnees   as email,
                   users.est_coordinateur_peps,
                   users.id_user,
                   users.id_territoire,
                   users.id_structure,
                   s.id_statut_structure,
                   s.logo_fichier,
                   t.nom_territoire,
                   t.id_region,
                   tt.id_type_territoire
            FROM coordonnees
                     JOIN users ON users.id_coordonnees = coordonnees.id_coordonnees
                     JOIN territoire t ON users.id_territoire = t.id_territoire
                     JOIN type_territoire tt on t.id_type_territoire = tt.id_type_territoire
                     LEFT JOIN structure s ON s.id_structure = users.id_structure
            WHERE users.identifiant = :identifiant";

        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":identifiant", $email);
        if (!$stmt->execute()) {
            return false;
        }
        if ($stmt->rowCount() == 0) {
            return false;
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['id_territoire'] = $data['id_territoire'];
        $_SESSION['id_type_territoire'] = $data['id_type_territoire'];
        $_SESSION['id_region'] = $data['id_region'];
        $_SESSION['nom_territoire'] = $data['nom_territoire'];
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['id_structure'] = $data['id_structure'];
        $_SESSION['id_statut_structure'] = $data['id_statut_structure'];
        $_SESSION['email_connecte'] = $email;
        $_SESSION['nom_connecte'] = $data['nom'];
        $_SESSION['prenom_connecte'] = $data['prenom'];
        $_SESSION['role_user_ids'] = $roles_user;
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['est_coordinateur_peps'] = isset($data['est_coordinateur_peps']) && $data['est_coordinateur_peps'] == '1';
        $_SESSION['logo_fichier'] = $data['logo_fichier'];
        $_SESSION['est_en_nouvelle_aquitaine'] = $data['id_region'] == 13;

        $query = "
            SELECT role_user
            FROM users
            JOIN a_role ar on users.id_user = ar.id_user
            JOIN role_user ru on ar.id_role_user = ru.id_role_user
            WHERE users.identifiant = :identifiant";
        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":identifiant", $email);
        if (!$stmt->execute()) {
            return false;
        }
        if ($stmt->rowCount() == 0) {
            return false;
        }
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $roles_str = implode('/', $roles);
        $_SESSION['role_user'] = $roles_str;

        if (!in_array("1", $roles_user)) {
            // recherche du numéro de departement
            $query = '
                SELECT id_departement
                FROM departement
                JOIN villes USING(id_departement)
                JOIN se_localise_a USING(id_ville)
                JOIN se_situe_a USING(id_adresse)
                JOIN users USING (id_structure)
                WHERE id_user = :id_user';
            $stmt = $this->bdd->prepare($query);
            $stmt->bindValue(":id_user", $_SESSION['id_user']);
            if (!$stmt->execute()) {
                return false;
            }
            if ($stmt->rowCount() == 0) {
                return false;
            }
            $data = $stmt->fetch();
            $_SESSION['id_dep'] = $data['id_departement'];
        }

        $_SESSION['is_connected'] = true;

        return true;
    }

    /**
     * Return les id_role_user de l'utilisateur
     *
     * @param string $email L'email de l'utilisateur
     * @return array Les id_role_user de l'utilisateur ou un empty array si non trouvé
     */
    public function get_roles_user($email)
    {
        $query = "
            SELECT id_role_user
            FROM users
            JOIN a_role ar on users.id_user = ar.id_user
            WHERE identifiant = :identifiant";
        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":identifiant", $email);
        if (!$stmt->execute()) {
            return [];
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Incrémente le compteur du nombre de connexions pour l'utilisateur donné
     *
     * @param $id_user L'id de l'utilisateur
     * @return bool Si la mise à jour été réalisée avec succès
     */
    public function update_compteur($id_user): bool
    {
        $query = '
            UPDATE users
            SET compteur = compteur + 1
            WHERE id_user = :id_user';
        $stmt = $this->bdd->prepare($query);
        $stmt->bindValue(":id_user", $id_user);

        return $stmt->execute();
    }
}