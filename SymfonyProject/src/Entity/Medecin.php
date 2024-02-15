<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

use Sportsante86\Sapa\Outils\ChaineCharactere;
use Sportsante86\Sapa\Outils\Permissions;

class Medecin
{
    private PDO $pdo;
    private string $errorMessage;

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
     * Creates a medecin
     *
     * required parameters:
     * [
     *     'nom_coordonnees' => string,
     *     'prenom_coordonnees' => string,
     *     'poste_medecin' => string,
     *     'id_specialite_medecin' => string,
     *     'id_lieu_pratique' => string,
     *     'nom_adresse' => string,
     *     'code_postal' => string,
     *     'nom_ville' => string,
     *     'id_territoire' => string,
     *     'tel_fixe_coordonnees' => string,
     * ]
     *
     * optionnal parameters:
     * [
     *     'complement_adresse' => string,
     *     'mail_coordonnees' => string,
     *     'tel_portable_coordonnees' => string,
     * ]
     *
     * @param array $parameters
     * @return false|string the id of the medecin or false on failure
     */
    public function create(array $parameters)
    {
        if (empty($parameters['nom_coordonnees']) ||
            empty($parameters['prenom_coordonnees']) ||
            empty($parameters['poste_medecin']) ||
            empty($parameters['id_specialite_medecin']) ||
            empty($parameters['id_lieu_pratique']) ||
            empty($parameters['nom_adresse']) ||
            empty($parameters['code_postal']) ||
            empty($parameters['nom_ville']) ||
            empty($parameters['id_territoire']) ||
            empty($parameters['tel_fixe_coordonnees'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $nom_coordonnees = trim(mb_strtoupper($parameters['nom_coordonnees'], 'UTF-8'));
            $prenom_coordonnees = trim(ChaineCharactere::mb_ucfirst($parameters['prenom_coordonnees']));
            $poste_medecin = trim(ChaineCharactere::mb_ucfirst($parameters['poste_medecin']));
            $id_specialite_medecin = filter_var($parameters['id_specialite_medecin'], FILTER_SANITIZE_NUMBER_INT);
            $id_lieu_pratique = filter_var($parameters['id_lieu_pratique'], FILTER_SANITIZE_NUMBER_INT);
            $nom_adresse = trim(ChaineCharactere::mb_ucfirst($parameters['nom_adresse']));
            $code_postal = filter_var($parameters['code_postal'], FILTER_SANITIZE_NUMBER_INT);
            $nom_ville = trim($parameters['nom_ville']);
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $tel_fixe_coordonnees = isset($parameters['tel_fixe_coordonnees']) ?
                filter_var(
                    $parameters['tel_fixe_coordonnees'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            // optionnel
            $complement_adresse = isset($parameters['complement_adresse']) ?
                trim(ChaineCharactere::mb_ucfirst($parameters['complement_adresse'])) :
                "";
            $mail_coordonnees = isset($parameters['mail_coordonnees']) ?
                trim($parameters['mail_coordonnees']) :
                "";
            $tel_portable_coordonnees = isset($parameters['tel_portable_coordonnees']) ?
                filter_var(
                    $parameters['tel_portable_coordonnees'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            // Insertion dans coordonnees
            $query = '
                INSERT INTO coordonnees (nom_coordonnees, prenom_coordonnees, tel_fixe_coordonnees,
                                         tel_portable_coordonnees, mail_coordonnees)
                VALUES (:nom_coordonnees, :prenom_coordonnees, :tel_fixe_coordonnees, :tel_portable_coordonnees,
                        :mail_coordonnees)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_coordonnees', $nom_coordonnees);
            $stmt->bindValue(':prenom_coordonnees', $prenom_coordonnees);
            $stmt->bindValue(':tel_fixe_coordonnees', $tel_fixe_coordonnees);
            $stmt->bindValue(':tel_portable_coordonnees', $tel_portable_coordonnees);
            $stmt->bindValue(':mail_coordonnees', $mail_coordonnees);

            if (!$stmt->execute()) {
                throw new Exception('Error insert INTO coordonnees');
            }
            $id_coordonnees = $this->pdo->lastInsertId();

            // Insertion dans medecins
            $query = '
                INSERT INTO medecins (poste_medecin, id_coordonnees, id_territoire, id_specialite_medecin)
                VALUES (:poste_medecin, :id_coordonnees, :id_territoire, :id_specialite_medecin)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':poste_medecin', $poste_medecin);
            $stmt->bindValue(':id_coordonnees', $id_coordonnees);
            $stmt->bindValue(':id_territoire', $id_territoire);
            $stmt->bindValue(':id_specialite_medecin', $id_specialite_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO medecins');
            }
            $id_medecin = $this->pdo->lastInsertId();

            // Update dans coordonnees pour ajouter l'idMedecin
            $query = '
                UPDATE coordonnees
                SET id_medecin = :id_medecin
                WHERE id_coordonnees = :id_coordonnees';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_coordonnees', $id_coordonnees);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            // Récupération de l'id de la structure -> insertion dans pratique_a
            $query = '
                INSERT INTO pratique_a (id_medecin, id_lieu_pratique)
                VALUES (:id_medecin, :id_lieu_pratique)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);
            $stmt->bindValue(':id_lieu_pratique', $id_lieu_pratique);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO pratique_a');
            }

            // insertion de l'adresse
            $query = '
                INSERT INTO adresse (nom_adresse, complement_adresse)
                VALUES (:nom_adresse, :complement_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_adresse', $nom_adresse);
            $stmt->bindValue(':complement_adresse', $complement_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO adresse');
            }
            $id_adresse = $this->pdo->lastInsertId();

            // Récupération de l'id de la ville -> insertion dans se_localise_a
            $query = '
                SELECT id_ville
                from villes
                WHERE nom_ville LIKE :nom_ville
                  AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $nom_ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if (!$stmt->execute()) {
                throw new Exception('Error select villes');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_ville = $data['id_ville'];
            if (empty($id_ville)) {
                throw new Exception(
                    'Error: La ville \'' . $nom_ville . '\' qui a en code_postal \'' . $code_postal . '\' n\'a pas été trouvé dans la BDD'
                );
            }

            // Insertion dans se_localise_a
            $query = '
                INSERT INTO se_localise_a (id_adresse, id_ville)
                VALUES (:id_adresse, :id_ville)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);
            $stmt->bindValue(':id_ville', $id_ville);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO se_localise_a');
            }

            // Insertion dans siege
            $query = '
                INSERT INTO siege (id_adresse, id_medecin)
                VALUES (:id_adresse, :id_medecin)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO siege');
            }

            $this->pdo->commit();
            return $id_medecin;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a medecin
     *
     * required parameters:
     * [
     *     'id_medecin' => string,
     *     'nom_coordonnees' => string,
     *     'prenom_coordonnees' => string,
     *     'poste_medecin' => string,
     *     'id_specialite_medecin' => string,
     *     'id_lieu_pratique' => string,
     *     'nom_adresse' => string,
     *     'code_postal' => string,
     *     'nom_ville' => string,
     *     'id_territoire' => string,
     *     'tel_fixe_coordonnees' => string,
     * ]
     *
     * optionnal parameters:
     * [
     *     'complement_adresse' => string,
     *     'mail_coordonnees' => string,
     *     'tel_portable_coordonnees' => string,
     * ]
     *
     * @param array $parameters
     * @return bool if the update was successful
     */
    public function update(array $parameters): bool
    {
        if (empty($parameters['id_medecin']) ||
            empty($parameters['nom_coordonnees']) ||
            empty($parameters['prenom_coordonnees']) ||
            empty($parameters['poste_medecin']) ||
            empty($parameters['id_specialite_medecin']) ||
            empty($parameters['id_lieu_pratique']) ||
            empty($parameters['nom_adresse']) ||
            empty($parameters['code_postal']) ||
            empty($parameters['nom_ville']) ||
            empty($parameters['id_territoire']) ||
            empty($parameters['tel_fixe_coordonnees'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_medecin = filter_var($parameters['id_medecin'], FILTER_SANITIZE_NUMBER_INT);
            $nom_coordonnees = trim(mb_strtoupper($parameters['nom_coordonnees'], 'UTF-8'));
            $prenom_coordonnees = trim(ChaineCharactere::mb_ucfirst($parameters['prenom_coordonnees']));
            $poste_medecin = trim(ChaineCharactere::mb_ucfirst($parameters['poste_medecin']));
            $id_specialite_medecin = filter_var($parameters['id_specialite_medecin'], FILTER_SANITIZE_NUMBER_INT);
            $id_lieu_pratique = filter_var($parameters['id_lieu_pratique'], FILTER_SANITIZE_NUMBER_INT);
            $nom_adresse = trim(ChaineCharactere::mb_ucfirst($parameters['nom_adresse']));
            $code_postal = filter_var($parameters['code_postal'], FILTER_SANITIZE_NUMBER_INT);
            $nom_ville = trim($parameters['nom_ville']);
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $tel_fixe_coordonnees = isset($parameters['tel_fixe_coordonnees']) ?
                filter_var(
                    $parameters['tel_fixe_coordonnees'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            // optionnel
            $complement_adresse = isset($parameters['complement_adresse']) ?
                trim(ChaineCharactere::mb_ucfirst($parameters['complement_adresse'])) :
                "";
            $mail_coordonnees = isset($parameters['mail_coordonnees']) ?
                trim($parameters['mail_coordonnees']) :
                "";
            $tel_portable_coordonnees = isset($parameters['tel_portable_coordonnees']) ?
                filter_var(
                    $parameters['tel_portable_coordonnees'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            //UPDATE Coordonnées
            $query = '
                UPDATE coordonnees
                SET nom_coordonnees          = :nom_coordonnees,
                    prenom_coordonnees       = :prenom_coordonnees,
                    tel_fixe_coordonnees     = :tel_fixe_coordonnees,
                    tel_portable_coordonnees = :tel_portable_coordonnees,
                    mail_coordonnees         = :mail_coordonnees
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_coordonnees', $nom_coordonnees);
            $stmt->bindValue(':prenom_coordonnees', $prenom_coordonnees);
            $stmt->bindValue(':tel_fixe_coordonnees', $tel_fixe_coordonnees);
            $stmt->bindValue(':tel_portable_coordonnees', $tel_portable_coordonnees);
            $stmt->bindValue(':mail_coordonnees', $mail_coordonnees);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            //UPDATE Medecins
            $query = '
                UPDATE medecins
                SET poste_medecin         = :poste_medecin,
                    id_specialite_medecin = :id_specialite_medecin,
                    id_territoire         = :id_territoire
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':poste_medecin', $poste_medecin);
            $stmt->bindValue(':id_specialite_medecin', $id_specialite_medecin);
            $stmt->bindValue(':id_medecin', $id_medecin);
            $stmt->bindValue(':id_territoire', $id_territoire);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE medecins');
            }

            //UPDATE pratique_a
            $query = '
                UPDATE pratique_a
                SET id_lieu_pratique = :id_lieu_pratique
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);
            $stmt->bindValue(':id_lieu_pratique', $id_lieu_pratique);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE pratique_a');
            }

            //recup id adresse
            $query = '
                SELECT id_adresse
                from siege
                where id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $id_adresse = $data['id_adresse'];
                if (empty($id_adresse)) {
                    throw new Exception('Error: L\'id_adresse n\'a pas été trouvé dans la BDD');
                }
            } else {
                throw new Exception('Error select id_adresse');
            }

            // update de l'adresse
            $query = '
                UPDATE adresse
                SET nom_adresse        = :nom_adresse,
                    complement_adresse = :complement_adresse
                WHERE id_adresse = :id_adresse';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_adresse', $nom_adresse);
            $stmt->bindValue(':complement_adresse', $complement_adresse);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE adresse');
            }

            //UPDATE code postal et ville
            // Récupération de l'id de la ville -> insertion dans se_localise_a
            $query = '
                SELECT id_ville
                from villes
                WHERE nom_ville = :nom_ville
                  AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $nom_ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $id_ville = $data['id_ville'];
                if (empty($id_ville)) {
                    throw new Exception(
                        'Error: La ville \'' . $nom_ville . '\' (' . $code_postal . ') n\'a pas été trouvé dans la BDD'
                    );
                }
            } else {
                throw new Exception('Error select villes');
            }

            // Récupération de l'id_lieu_pratique
            $query = '
                SELECT id_lieu_pratique
                FROM pratique_a
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_lieu_pratique');
            }

            if ($stmt->rowCount() == 0) {
                // insertion dans pratique_a
                $query = '
                    INSERT INTO pratique_a (id_medecin, id_lieu_pratique)
                    VALUES (:id_medecin, :id_lieu_pratique)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_medecin', $id_medecin);
                $stmt->bindValue(':id_lieu_pratique', $id_lieu_pratique);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO pratique_a');
                }
            } else {
                // update pratique_a
                $query = '
                    UPDATE se_localise_a
                    SET id_ville = :id_ville
                    WHERE id_adresse = :id_adresse';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_adresse', $id_adresse);
                $stmt->bindValue(':id_ville', $id_ville);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE se_localise_a');
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
     * @param $id_medecin
     * @return bool if the deletion was successful
     */
    public function delete($id_medecin): bool
    {
        if (empty($id_medecin)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // Verification si prescrit à des patients
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_prescrit
                from prescrit
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                if (intval($data['nb_prescrit']) > 0) {
                    throw new Exception("Erreur: Le médecin est le médecin prescripteur d'au moins un patient");
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_prescrit');
            }

            ////////////////////////////////////////////////////
            // Verification si traite des patients
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_traite
                from traite
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                if (intval($data['nb_traite']) > 0) {
                    throw new Exception("Erreur: Le médecin est le médecin traitant d'au moins un patient");
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_traite');
            }

            ////////////////////////////////////////////////////
            // Verification si suit des patients
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_suit
                from suit
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                if (intval($data['nb_suit']) > 0) {
                    throw new Exception(
                        "Erreur: Le médecin est enregistré comme 'Autres professionnels de santé' pour au moins un patient"
                    );
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_traite');
            }

            ////////////////////////////////////////////////////
            // DELETE coordonnees
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM coordonnees
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            ////////////////////////////////////////////////////
            // DELETE pratique_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM pratique_a
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM pratique_a');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id_adresse
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_adresse
                from siege
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $id_adresse = $data['id_adresse'];
                if (empty($id_adresse)) {
                    throw new Exception(
                        'Error: L\'id_adresse du medecin \'' . $id_medecin . '\'  n\'a pas été trouvé dans la BDD'
                    );
                }
            } else {
                throw new Exception('Error select id_adresse');
            }

            ////////////////////////////////////////////////////
            // DELETE se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM se_localise_a
                WHERE id_adresse = :id_adresse';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM se_localise_a');
            }

            ////////////////////////////////////////////////////
            // DELETE siege
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM siege
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM siege');
            }

            ////////////////////////////////////////////////////
            // DELETE adresse
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM adresse
                WHERE id_adresse = :id_adresse';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM adresse');
            }

            ////////////////////////////////////////////////////
            // DELETE medecins
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM medecins
                WHERE id_medecin = :id_medecin';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_medecin', $id_medecin);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM medecins');
            }

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
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
     * Fusionne 2 médecins
     *
     * @param $id_medecin_from
     * @param $id_medecin_target
     * @return bool if the fusion was successful
     */
    public function fuse($id_medecin_from, $id_medecin_target): bool
    {
        if (empty($id_medecin_from) || empty($id_medecin_target)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                SELECT medecins.id_medecin, c.id_coordonnees
                FROM medecins
                         JOIN coordonnees c on medecins.id_coordonnees = c.id_coordonnees
                WHERE medecins.id_medecin = :id_medecin';

            // check that $id_medecin_from exists
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT medecins.id_medecin');
            }
            if ($statement->rowCount() == 0) {
                throw new Exception('Error id_medecin_form n\'existe pas');
            }
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $id_coordonnees_from = $row['id_coordonnees'];

            // check that $id_medecin_target exists
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin', $id_medecin_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT medecins.id_medecin');
            }
            if ($statement->rowCount() == 0) {
                throw new Exception('Error id_medecin_target n\'existe pas');
            }
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $id_coordonnees_target = $row['id_coordonnees'];

            // update suit
            $query = '
                UPDATE suit
                SET id_medecin = :id_medecin_target
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_target', $id_medecin_target);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE suit');
            }

            // update prescrit
            $query = '
                UPDATE prescrit
                SET id_medecin = :id_medecin_target
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_target', $id_medecin_target);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE prescrit');
            }

            // update traite
            $query = '
                UPDATE traite
                SET id_medecin = :id_medecin_target
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_target', $id_medecin_target);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE traite');
            }

            // suppressions
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            // suppression pratique_a
            $query = '
                DELETE
                FROM pratique_a
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM pratique_a');
            }

            // suppression siege
            $query = '
                DELETE
                FROM siege
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM siege');
            }

            // suppression coordonnees
            $query = '
                DELETE
                FROM coordonnees
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            // suppression medecins
            $query = '
                DELETE
                FROM medecins
                WHERE id_medecin = :id_medecin_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_medecin_from', $id_medecin_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM medecins');
            }

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
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
     * @param $id_medecin
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_medecin)
    {
        if (empty($id_medecin)) {
            return false;
        }

        $query = '
            SELECT nom_coordonnees,
                   prenom_coordonnees,
                   medecins.id_territoire,
                   tel_fixe_coordonnees,
                   tel_portable_coordonnees,
                   mail_coordonnees,
                   id_medecin,
                   poste_medecin,
                   id_lieu_pratique,
                   id_specialite_medecin,
                   nom_specialite_medecin,
                   nom_lieu_pratique,
                   complement_adresse,
                   nom_adresse,
                   code_postal,
                   nom_ville
            FROM coordonnees
                     JOIN medecins USING (id_medecin)
                     JOIN siege USING (id_medecin)
                     LEFT JOIN pratique_a USING (id_medecin)
                     LEFT JOIN lieu_de_pratique USING (id_lieu_pratique)
                     LEFT JOIN adresse USING (id_adresse)
                     LEFT JOIN se_localise_a USING (id_adresse)
                     LEFT JOIN villes USING (id_ville)
                     LEFT JOIN specialite_medecin USING (id_specialite_medecin)
            WHERE medecins.id_medecin = :id_medecin';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_medecin', $id_medecin);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupération du médecin prescripteur d'un patient s'il en a un
     *
     * @param $id_patient
     * @return false|array Return an associative array or false on failure
     */
    public function readMedecinPrescripteurPatient($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT id_medecin
            FROM prescrit
            WHERE prescrit.id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return false;
        }

        return $this->readOne($data['id_medecin']);
    }

    /**
     * Récupération du médecin traitant d'un patient s'il en a un
     *
     * @param $id_patient
     * @return false|array Return an associative array or false on failure
     */
    public function readMedecinTraitantPatient($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT id_medecin
            FROM traite
            WHERE traite.id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return false;
        }

        return $this->readOne($data['id_medecin']);
    }

    /**
     * Récupération des autres professionnels de santé d'un patient
     *
     * @param $id_patient
     * @return false|array Return an array of associative arrays or false on failure
     */
    public function readAutresProfessionnelsSantePatient($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT distinct id_medecin
            FROM suit
            WHERE suit.id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $medecins = [];
        foreach ($ids as $id_medecin) {
            $medecin = $this->readOne($id_medecin);
            if ($medecin) {
                $medecins[] = $medecin;
            }
        }

        return $medecins;
    }

    /**
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAll()
    {
        $query = "
            SELECT DISTINCT nom_coordonnees,
                            prenom_coordonnees,
                            medecins.id_territoire as id_terr,
                            tel_fixe_coordonnees,
                            tel_portable_coordonnees,
                            mail_coordonnees,
                            id_medecin,
                            poste_medecin,
                            id_lieu_pratique,
                            id_specialite_medecin,
                            nom_specialite_medecin,
                            nom_lieu_pratique,
                            complement_adresse,
                            nom_adresse,
                            code_postal,
                            nom_ville
            FROM coordonnees
                     JOIN medecins USING (id_medecin)
                     JOIN siege USING (id_medecin)
                     LEFT JOIN pratique_a USING (id_medecin)
                     LEFT JOIN lieu_de_pratique USING (id_lieu_pratique)
                     LEFT JOIN adresse USING (id_adresse)
                     LEFT JOIN se_localise_a USING (id_adresse)
                     LEFT JOIN specialite_medecin USING (id_specialite_medecin)
                     LEFT JOIN villes USING (id_ville)";
        $statement = $this->pdo->prepare($query);

        if (!$statement->execute()) {
            return false;
        }

        return $statement->fetchAll();
    }

    /**
     * @param array $session la session de l'utilisateur
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAllMedecinsPrescripteurForExport($session)
    {
        if (empty($session['id_territoire'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            // roles qui ont accès à l'export des médecins
            $authorized_roles = [
                Permissions::COORDONNATEUR_MSS,
                Permissions::COORDONNATEUR_PEPS,
                Permissions::COORDONNATEUR_NON_MSS,
                Permissions::SUPER_ADMIN,
                Permissions::EVALUATEUR,
            ];

            $permissions = new Permissions($session);
            $roles_user = $permissions->getRolesUser();

            if (array_intersect($authorized_roles, $roles_user) == []) {
                $this->errorMessage = "L'utilisateur n'a pas la permission d'accès";
                return false;
            }
        } catch (Exception $e) {
            $this->errorMessage = "Erreur lors de la vérification des permissions";
            return false;
        }

        $query = "
            SELECT DISTINCT count(*)                 as nb_prescription,
                            nom_coordonnees          as nom,
                            prenom_coordonnees       as prenom,
                            tel_fixe_coordonnees     as tel_fixe,
                            tel_portable_coordonnees as tel_portable,
                            mail_coordonnees         as email,
                            poste_medecin,
                            nom_specialite_medecin,
                            nom_adresse,
                            complement_adresse,
                            code_postal,
                            nom_ville
            FROM patients
                     JOIN prescrit ON patients.id_patient = prescrit.id_patient
                     JOIN medecins m ON prescrit.id_medecin = m.id_medecin
                     JOIN coordonnees c on m.id_coordonnees = c.id_coordonnees
                     JOIN specialite_medecin ON m.id_specialite_medecin = specialite_medecin.id_specialite_medecin
                     JOIN antenne on patients.id_antenne = antenne.id_antenne
                     JOIN structure on antenne.id_structure = structure.id_structure
                     JOIN users u on patients.id_user = u.id_user
                     LEFT JOIN pratique_a ON m.id_medecin = pratique_a.id_medecin
                     LEFT JOIN lieu_de_pratique ON pratique_a.id_lieu_pratique = lieu_de_pratique.id_lieu_pratique
                     LEFT JOIN siege s ON s.id_medecin = m.id_medecin
                     LEFT JOIN adresse ON s.id_adresse = adresse.id_adresse
                     LEFT JOIN se_localise_a ON adresse.id_adresse = se_localise_a.id_adresse
                     LEFT JOIN villes ON se_localise_a.id_ville = villes.id_ville
            WHERE 1=1 ";

        // filtres selon les rôles
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND patients.id_territoire = :id_territoire ';
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS)) {
            $query .= ' AND (structure.id_structure = :id_structure
                             OR u.id_user = :id_user) ';
        } elseif ($permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $query .= ' AND (structure.id_structure = :id_structure
                             OR u.id_user = :id_user) ';
        } elseif ($permissions->hasRole(Permissions::EVALUATEUR)) {
            $query .= ' AND patients.id_user = :id_user ';
        }

        $query .= "
            GROUP BY nom_coordonnees, prenom_coordonnees, tel_fixe_coordonnees, tel_portable_coordonnees, mail_coordonnees,
                     poste_medecin, nom_specialite_medecin, nom_lieu_pratique, complement_adresse, nom_adresse, code_postal,
                     nom_ville
            ORDER BY nb_prescription DESC";

        $statement = $this->pdo->prepare($query);

        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $statement->bindValue(':id_territoire', $session['id_territoire']);
        }
        if ($permissions->hasRole(Permissions::COORDONNATEUR_MSS)) {
            $statement->bindValue(':id_structure', $session['id_structure']);
            $statement->bindValue(':id_user', $session['id_user']);
        } elseif ($permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $statement->bindValue(':id_structure', $session['id_structure']);
            $statement->bindValue(':id_user', $session['id_user']);
        } elseif ($permissions->hasRole(Permissions::EVALUATEUR)) {
            $statement->bindValue(':id_user', $session['id_user']);
        }

        if (!$statement->execute()) {
            return false;
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}