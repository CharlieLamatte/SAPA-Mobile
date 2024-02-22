<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

use Sportsante86\Sapa\Outils\ChaineCharactere;

use Sportsante86\Sapa\Outils\EncryptionManager;
use Sportsante86\Sapa\Outils\Permissions;

use function Sportsante86\Sapa\Outils\traitement_nom_ville;

class Creneau
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
     * Creates a creneau
     *
     * required parameters (not import from API):
     * [
     *     'nom_creneau' => string,
     *     'code_postal' => string,
     *     'nom_ville' => string,
     *     'jour' => string,
     *     'heure_debut' => string,
     *     'heure_fin' => string,
     *     'type_creneau' => string,
     *     'id_structure' => string,
     *     'intervenant_ids' => array,
     *     'pathologie' => string,
     *     'type_seance' => string,
     *     'id_role_user' => string,
     * ]
     *
     * required parameters (import from API):
     * [
     *     'id_api' => string,
     *     'id_api_structure' => string,
     *     'id_api_intervenant' => string,
     *     'nom_creneau' => string,
     *     'code_postal' => string,
     *     'nom_ville' => string,
     *     'jour' => string,
     *     'heure_debut' => string,
     *     'heure_fin' => string,
     *     'type_creneau' => string,
     *     'pathologie' => string,
     *     'type_seance' => string,
     *     'id_role_user' => string,
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

            // paramètres obligatoires
            $nom_creneau = trim(ChaineCharactere::mb_ucfirst($parameters['nom_creneau']));
            $jour = filter_var($parameters['jour'], FILTER_SANITIZE_NUMBER_INT);
            $heureDeb = filter_var($parameters['heure_debut'], FILTER_SANITIZE_NUMBER_INT);
            $heureFin = filter_var($parameters['heure_fin'], FILTER_SANITIZE_NUMBER_INT);
            $id_type_parcours = filter_var($parameters['type_creneau'], FILTER_SANITIZE_NUMBER_INT);
            $pathologie_creneau = trim($parameters['pathologie']);
            $type_seance = trim($parameters['type_seance']);
            $code_postal = filter_var($parameters['code_postal'], FILTER_SANITIZE_NUMBER_INT);
            $ville = traitement_nom_ville($parameters['nom_ville']);
            $nom_adresse = isset($parameters['nom_adresse']) ?
                trim(ChaineCharactere::mb_ucfirst($parameters['nom_adresse'])) :
                "Non renseigné";

            // paramètres optionnels
            $id_structure = isset($parameters['id_structure']) ?
                filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $intervenant_ids = isset($parameters['intervenant_ids']) ?
                filter_var_array($parameters['intervenant_ids'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $nombre_participants = $parameters['nb_participant'] ?? "";
            $facilite_paiement = $parameters['paiement'] ?? "";
            $public_vise = $parameters['public_vise'] ?? "";
            $prix_creneau = $parameters['tarif'] ?? "";
            $id_api_structure = $parameters['id_api_structure'] ?? null;
            $id_api_intervenant = $parameters['id_api_intervenant'] ?? null;
            $id_api = $parameters['id_api'] ?? null;
            $complement_adresse = isset($parameters['complement_adresse']) ?
                trim(ChaineCharactere::mb_ucfirst($parameters['complement_adresse'])) :
                "";
            $description_creneau = $parameters['description'] ?? "";

            ////////////////////////////////////////////////////
            // si on insére des données issues de l'API
            ////////////////////////////////////////////////////
            if (!empty($id_api_structure)) {
                // recup de l'id_structure
                $query = 'SELECT id_structure FROM structure WHERE id_api = :id_api';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_api', $id_api_structure);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 0) {
                        throw new Exception('Error: La structure de ce créneau n\'a pas été importée');
                    }
                    $data = $stmt->fetch();
                    $id_structure = $data['id_structure'];
                } else {
                    throw new Exception('Error: L\'id_structure n\'a pas été trouvé pour structure');
                }

                // recup de l'id_intervenant
                $query = 'SELECT id_intervenant FROM intervenants WHERE id_api = :id_api';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_api', $id_api_intervenant);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 0) {
                        throw new Exception('Error: L\'id_intervenant de ce créneau n\'a pas été importé');
                    }
                    $data = $stmt->fetch();
                    $intervenant_ids = [$data['id_intervenant']];
                } else {
                    throw new Exception('Error: L\'id_intervenant n\'a pas été trouvé pour intervenant');
                }
            }

            if (!empty($id_api)) {
                // verification si le créneau n'à pas déja été importé
                $query = '
                    SELECT id_api, id_jour, type_seance
                    FROM creneaux 
                    WHERE id_api = :id_api AND id_jour = :id_jour AND type_seance = :type_seance';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_api', $id_api);
                $stmt->bindValue(':id_jour', $jour);
                $stmt->bindValue(':type_seance', $type_seance);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        throw new Exception('Error: element déja importé');
                    }
                    $stmt->closeCursor();
                } else {
                    throw new Exception('Error select FROM creneaux');
                }

                // verif si le jour est valide
                if ($jour == '-1') {
                    throw new Exception('Error: Le jour est invalide');
                }
            }

            ////////////////////////////////////////////////////
            // insert dans la table creneau
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO creneaux
                (id_api, nom_creneau, id_jour, prix_creneau, nombre_participants, public_vise, facilite_paiement,
                 description_creneau, id_type_parcours, pathologie_creneau, 
                 type_seance, id_structure)
                VALUES (:id_api, :nom_creneau, :id_jour, :prix_creneau, :nombre_participants, :public_vise,
                        :facilite_paiement, :description_creneau, :id_type_parcours, :pathologie_creneau,
                        :type_seance, :id_structure)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_creneau', $nom_creneau);
            $stmt->bindValue(':id_jour', $jour);
            $stmt->bindValue(':prix_creneau', $prix_creneau);
            $stmt->bindValue(':nombre_participants', $nombre_participants);
            $stmt->bindValue(':public_vise', $public_vise);
            $stmt->bindValue(':facilite_paiement', $facilite_paiement);
            $stmt->bindValue(':description_creneau', $description_creneau);
            $stmt->bindValue(':id_type_parcours', $id_type_parcours);
            $stmt->bindValue(':pathologie_creneau', $pathologie_creneau);
            //$stmt->bindValue(':id_intervenant', $id_intervenant);
            $stmt->bindValue(':type_seance', $type_seance);
            $stmt->bindValue(':id_structure', $id_structure);
            if (empty($id_api)) {
                $stmt->bindValue(':id_api', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':id_api', $id_api);
            }

            if ($stmt->execute()) {
                $id_creneau = $this->pdo->lastInsertId();
            } else {
                throw new Exception('Error: INSERT INTO creneaux');
            }

            ////////////////////////////////////////////////////
            // INSERT INTO commence_a
            ////////////////////////////////////////////////////
            $query = 'INSERT INTO commence_a (id_creneau, id_heure) VALUES (:id_creneau, :id_heure)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);
            $stmt->bindValue(':id_heure', $heureDeb);

            if (!$stmt->execute()) {
                throw new Exception('Error: INSERT INTO commence_a');
            }

            ////////////////////////////////////////////////////
            // INSERT INTO se_termine_a
            ////////////////////////////////////////////////////
            $query = 'INSERT INTO se_termine_a (id_creneau, id_heure) VALUES (:id_creneau, :id_heure)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);
            $stmt->bindValue(':id_heure', $heureFin);

            if (!$stmt->execute()) {
                throw new Exception('Error: INSERT INTO se_termine_a');
            }

            ////////////////////////////////////////////////////
            // Insertion dans adresse
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO adresse(nom_adresse, complement_adresse)
                VALUES (:nom_adresse, :complement_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_adresse', $nom_adresse);
            $stmt->bindValue(':complement_adresse', $complement_adresse);

            if ($stmt->execute()) {
                $id_adresse = $this->pdo->lastInsertId();
            } else {
                throw new Exception('Error: INSERT INTO adresse');
            }

            ////////////////////////////////////////////////////
            // Insertion dans se_pratique_a
            ////////////////////////////////////////////////////
            $query = 'INSERT INTO se_pratique_a(id_creneau, id_adresse) VALUES (:id_creneau, :id_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error: INSERT INTO se_pratique_a');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id de la ville -> insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = 'SELECT id_ville from villes WHERE nom_ville = :nom_ville AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $id_ville = $data['id_ville'];
                if (empty($id_ville)) {
                    throw new Exception(
                        'Error: La ville \'' . $ville . '\' qui a en code_postal \'' . $code_postal . '\' n\'a pas été trouvé dans la BDD'
                    );
                }
            } else {
                throw new Exception('Error: SELECT id_ville');
            }

            ////////////////////////////////////////////////////
            // Insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = 'INSERT INTO se_localise_a (id_adresse, id_ville) VALUES (:id_adresse, :id_ville)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);
            $stmt->bindValue(':id_ville', $id_ville);

            if (!$stmt->execute()) {
                throw new Exception('Error: INSERT INTO se_localise_a');
            }

            foreach ($intervenant_ids as $id_intervenant) {
                ////////////////////////////////////////////////////
                // INSERT INTO creneaux_intervenant
                ////////////////////////////////////////////////////
                $query = '
                    INSERT INTO creneaux_intervenant (id_creneau, id_intervenant)
                    VALUES (:id_creneau, :id_intervenant)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_creneau', $id_creneau);
                $statement->bindValue(':id_intervenant', $id_intervenant);

                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO creneaux_intervenant');
                }

                ////////////////////////////////////////////////////
                // Insertion dans intervient_dans
                ////////////////////////////////////////////////////
                // Vérif si l'intervenant et la structure sont sont déja dans intervient_dans
                $query = '
                SELECT COUNT(id_intervenant) AS nb_intervenant
                FROM intervient_dans
                WHERE id_intervenant = :id_intervenant AND id_structure = :id_structure';
                $stmt_num = $this->pdo->prepare($query);
                $stmt_num->bindValue(":id_intervenant", $id_intervenant);
                $stmt_num->bindValue(":id_structure", $id_structure);
                $stmt_num->execute();
                $data = $stmt_num->fetch();
                $nb_intervenant_suivi = $data['nb_intervenant'];
                $stmt_num->CloseCursor();

                // si l'intervenant est pas déja ajouté dans intervient_dans
                if ($nb_intervenant_suivi == 0) {
                    $query = '
                    INSERT INTO intervient_dans (id_intervenant, id_structure) 
                    VALUES (:id_intervenant, :id_structure)';
                    $stmt = $this->pdo->prepare($query);

                    $stmt->bindValue(':id_intervenant', $id_intervenant);
                    $stmt->bindValue(':id_structure', $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error: INSERT INTO intervient_dans');
                    }
                }

                ////////////////////////////////////////////////////
                // Insertion dans intervention
                ////////////////////////////////////////////////////
                // Vérif si l'intervenant est un utilisateur
                $query = '
                SELECT c.id_user
                FROM users
                JOIN coordonnees c on users.id_user = c.id_user
                WHERE id_intervenant = :id_intervenant';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_intervenant', $id_intervenant);

                if (!$stmt->execute()) {
                    throw new Exception('Error: SELECT c.id_user');
                }
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_user = $data['id_user'] ?? null;

                if (!empty($id_user)) {
                    // Vérif si l'intervenant et la structure sont sont déja dans intervention
                    $query = '
                    SELECT COUNT(id_user) AS nb_user
                    FROM intervention
                    WHERE id_user = :id_user AND id_structure = :id_structure';
                    $stmt_num = $this->pdo->prepare($query);
                    $stmt_num->bindValue(":id_user", $id_user);
                    $stmt_num->bindValue(":id_structure", $id_structure);
                    $stmt_num->execute();
                    $data = $stmt_num->fetch();
                    $nb_user = $data['nb_user'];

                    // si l'intervenant est pas déja ajouté dans intervention
                    if ($nb_user == 0) {
                        $query = '
                        INSERT INTO intervention (id_user, id_structure) 
                        VALUES (:id_user, :id_structure)';
                        $stmt = $this->pdo->prepare($query);

                        $stmt->bindValue(':id_user', $id_user);
                        $stmt->bindValue(':id_structure', $id_structure);

                        if (!$stmt->execute()) {
                            throw new Exception('Error: INSERT INTO intervention');
                        }
                    }
                }
            }

            $this->pdo->commit();
            return $id_creneau;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Updates a creneau
     *
     * required parameters:
     * [
     *     'nom_creneau' => string,
     *     'code_postal' => string,
     *     'nom_ville' => string,
     *     'jour' => string,
     *     'heure_debut' => string,
     *     'heure_fin' => string,
     *     'type_creneau' => string,
     *     'id_structure' => string,
     *     'id_intervenant' => string,
     *     'pathologie' => string,
     *     'type_seance' => string,
     *     'id_creneau' => string,
     *     'activation' => string, ("0" or "1")
     * ]
     *
     * @param $parameters
     * @return false|string the id of the intervenant or false on failure
     */
    public function update($parameters): bool
    {
        if (!$this->requiredParametersPresent($parameters) ||
            empty($parameters['id_creneau']) ||
            !($parameters['activation'] == "0" || $parameters['activation'] == "1")) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_creneau = $parameters['id_creneau'];
            $nom_creneau = trim(ChaineCharactere::mb_ucfirst($parameters['nom_creneau']));
            $jour = filter_var($parameters['jour'], FILTER_SANITIZE_NUMBER_INT);
            $heureDeb = filter_var($parameters['heure_debut'], FILTER_SANITIZE_NUMBER_INT);
            $heureFin = filter_var($parameters['heure_fin'], FILTER_SANITIZE_NUMBER_INT);
            $id_type_parcours = filter_var($parameters['type_creneau'], FILTER_SANITIZE_NUMBER_INT);
            $id_structure = filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT);
            $intervenant_ids = isset($parameters['intervenant_ids']) ?
                filter_var_array($parameters['intervenant_ids'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $pathologie_creneau = trim($parameters['pathologie']);
            $type_seance = trim($parameters['type_seance']);
            $code_postal = filter_var($parameters['code_postal'], FILTER_SANITIZE_NUMBER_INT);
            $ville = traitement_nom_ville($parameters['nom_ville']);
            $nom_adresse = isset($parameters['nom_adresse']) ?
                trim(ChaineCharactere::mb_ucfirst($parameters['nom_adresse'])) :
                "Non renseigné";
            $activation = filter_var($parameters['activation'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $nombre_participants = $parameters['nb_participant'] ?? "";
            $facilite_paiement = $parameters['paiement'] ?? "";
            $public_vise = $parameters['public_vise'] ?? "";
            $prix_creneau = $parameters['tarif'] ?? "";
            $complement_adresse = isset($parameters['complement_adresse']) ?
                trim(ChaineCharactere::mb_ucfirst($parameters['complement_adresse'])) :
                "";
            $description_creneau = $parameters['description'] ?? "";

            //UPDATE creneaux
            $query = '
                UPDATE
                    creneaux
                SET nom_creneau         = :nom_creneau,
                    id_type_parcours    = :id_type_parcours,
                    id_jour             = :id_jour,
                    id_structure        = :id_structure,
                    prix_creneau        = :prix_creneau,
                    public_vise         = :public_vise,
                    facilite_paiement   = :facilite_paiement,
                    description_creneau = :description_creneau,
                    type_seance         = :type_seance,
                    pathologie_creneau  = :pathologie_creneau,
                    nombre_participants = :nombre_participants,
                    activation          = :activation
                WHERE id_creneau = :id_creneau';

            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_creneau', $id_creneau);
            $stmt->bindValue(':nom_creneau', $nom_creneau);
            $stmt->bindValue(':id_type_parcours', $id_type_parcours);
            $stmt->bindValue(':id_jour', $jour);
            $stmt->bindValue(':id_structure', $id_structure);
            $stmt->bindValue(':prix_creneau', $prix_creneau);
            $stmt->bindValue(':public_vise', $public_vise);
            $stmt->bindValue(':facilite_paiement', $facilite_paiement);
            $stmt->bindValue(':description_creneau', $description_creneau);
            $stmt->bindValue(':type_seance', $type_seance);
            $stmt->bindValue(':pathologie_creneau', $pathologie_creneau);
            $stmt->bindValue(':nombre_participants', $nombre_participants);
            $stmt->bindValue(':activation', $activation);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE creneaux');
            }

            //UPDATE commence_a
            $query = '
                UPDATE commence_a
                SET id_heure = :id_heure
                WHERE id_creneau = :id_creneau';

            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_creneau', $id_creneau);
            $stmt->bindValue(':id_heure', $heureDeb);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE commence_a');
            }

            //UPDATE se_termine_a
            $query = '
                UPDATE se_termine_a
                SET id_heure = :id_heure
                WHERE id_creneau = :id_creneau';

            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_creneau', $id_creneau);
            $stmt->bindValue(':id_heure', $heureFin);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE se_termine_a');
            }

            ////////////////////////////////////////////////////
            // Récup id_adresse
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_adresse
                FROM se_pratique_a
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_creneau', $id_creneau);

            $stmt->execute();
            $data = $stmt->fetch();
            $id_adresse = $data['id_adresse'] ?? null;

            ////////////////////////////////////////////////////
            // Update nom_adresse et complement_adresse
            ////////////////////////////////////////////////////
            $query = '
                UPDATE adresse
                SET complement_adresse = :complement_adresse,
                    nom_adresse        = :nom_adresse
                WHERE id_adresse = :id_adresse';

            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':nom_adresse', $nom_adresse);
            $stmt->bindValue(':complement_adresse', $complement_adresse);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE adresse');
            }

            ////////////////////////////////////////////////////
            // Récup id_ville
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_ville
                from villes
                WHERE nom_ville = :nom_ville AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $newIdVille = $data['id_ville'];
                if (empty($newIdVille)) {
                    throw new Exception(
                        'Error: La ville \'' . $ville . '\' (' . $code_postal . ') n\'a pas été trouvé dans la BDD'
                    );
                }
            } else {
                throw new Exception('Error: SELECT id_ville');
            }

            ////////////////////////////////////////////////////
            // Update se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                UPDATE se_localise_a
                SET id_ville = :id_ville
                WHERE id_adresse = :id_adresse';

            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_ville', $newIdVille);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE se_localise_a');
            }

            ////////////////////////////////////////////////////
            // INSERT INTO creneaux_intervenant
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM creneaux_intervenant
                WHERE id_creneau = :id_creneau';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_creneau', $id_creneau);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM creneaux_intervenant');
            }

            foreach ($intervenant_ids as $id_intervenant) {
                ////////////////////////////////////////////////////
                // INSERT INTO creneaux_intervenant
                ////////////////////////////////////////////////////
                $query = '
                    INSERT INTO creneaux_intervenant (id_creneau, id_intervenant)
                    VALUES (:id_creneau, :id_intervenant)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_creneau', $id_creneau);
                $statement->bindValue(':id_intervenant', $id_intervenant);

                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO creneaux_intervenant');
                }

                ////////////////////////////////////////////////////
                // Insertion dans intervient_dans
                ////////////////////////////////////////////////////
                // Vérif si l'intervenant et la structure sont déja dans intervient_dans
                $query = '
                    SELECT COUNT(id_intervenant) AS nb_intervenant
                    FROM intervient_dans
                    WHERE id_intervenant = :id_intervenant AND id_structure = :id_structure';
                $stmt_num = $this->pdo->prepare($query);
                $stmt_num->bindValue(":id_intervenant", $id_intervenant);
                $stmt_num->bindValue(":id_structure", $id_structure);
                $stmt_num->execute();
                $data = $stmt_num->fetch();
                $nb_intervenant_suivi = $data['nb_intervenant'];
                $stmt_num->CloseCursor();

                // si l'intervenant est pas déja ajouté dans intervient_dans
                if ($nb_intervenant_suivi == 0) {
                    $query = '
                        INSERT INTO intervient_dans (id_intervenant, id_structure) 
                        VALUES (:id_intervenant, :id_structure)';
                    $stmt = $this->pdo->prepare($query);

                    $stmt->bindValue(':id_intervenant', $id_intervenant);
                    $stmt->bindValue(':id_structure', $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error: INSERT INTO intervient_dans');
                    }
                }

                ////////////////////////////////////////////////////
                // Insertion dans intervention
                ////////////////////////////////////////////////////
                // Vérif si l'intervenant est un utilisateur
                $query = '
                    SELECT c.id_user
                    FROM users
                    JOIN coordonnees c on users.id_user = c.id_user
                    WHERE id_intervenant = :id_intervenant';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_intervenant', $id_intervenant);

                if (!$stmt->execute()) {
                    throw new Exception('Error: SELECT c.id_user');
                }
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_user = $data['id_user'] ?? null;

                if (!empty($id_user)) {
                    // Vérif si l'intervenant et la structure sont déjà dans intervention
                    $query = '
                        SELECT COUNT(id_user) AS nb_user
                        FROM intervention
                        WHERE id_user = :id_user AND id_structure = :id_structure';
                    $stmt_num = $this->pdo->prepare($query);
                    $stmt_num->bindValue(":id_user", $id_user);
                    $stmt_num->bindValue(":id_structure", $id_structure);
                    $stmt_num->execute();
                    $data = $stmt_num->fetch();
                    $nb_user = $data['nb_user'];

                    // si l'intervenant est pas déja ajouté dans intervention
                    if ($nb_user == 0) {
                        $query = '
                            INSERT INTO intervention (id_user, id_structure) 
                            VALUES (:id_user, :id_structure)';
                        $stmt = $this->pdo->prepare($query);

                        $stmt->bindValue(':id_user', $id_user);
                        $stmt->bindValue(':id_structure', $id_structure);

                        if (!$stmt->execute()) {
                            throw new Exception('Error: INSERT INTO intervention');
                        }
                    }
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
     * @param       $id_creneau
     * @param array $patients Les patients qui participent au créneaux au format:
     * [
     *     'id_patient' => string,
     *     'status_participant' => string,
     *     'propose_inscrit' => string, "0" ou "1"
     *     'abandon' => string, "0" ou "1"
     *     'reorientation' => string, "0" ou "1"
     * ]
     * @return bool Si l'update a été réalisé avec succès
     */
    public function updateParticipants($id_creneau, $patients): bool
    {
        if (empty($id_creneau) || !is_array($patients)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            ////////////////////////////////////////////////////
            // Suppression des valeurs précédentes
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM liste_participants_creneau
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);
            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM liste_participants_creneau)');
            }

            foreach ($patients as $value) {
                ////////////////////////////////////////////////////
                // Insertion dans liste_participants_creneau
                ////////////////////////////////////////////////////
                $query = '
                    INSERT INTO liste_participants_creneau (id_patient, id_creneau, status_participant,
                                                            propose_inscrit, abandon, reorientation)
                    VALUES (:id_patient, :id_creneau, :status_participant, :propose_inscrit, :abandon,
                            :reorientation)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_creneau', $id_creneau);
                $stmt->bindValue(':id_patient', $value['id_patient']);
                $stmt->bindValue(":status_participant", $value['status_participant']);
                $stmt->bindValue(':propose_inscrit', $value['propose_inscrit']);
                $stmt->bindValue(':abandon', $value['abandon']);
                $stmt->bindValue(':reorientation', $value['reorientation']);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO liste_participants_creneau');
                }
            }

            ////////////////////////////////////////////////////////////////////////
            // Mise à jour des activités pour les bénéficiaires supprimés du créneau
            ////////////////////////////////////////////////////////////////////////
            $query =
                'SELECT id_patient
                FROM orientation
                JOIN activite_choisie ON orientation.id_orientation = activite_choisie.id_orientation
                WHERE activite_choisie.id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_patient FROM orientation');
            }

            $ids_patients = [];
            foreach ($patients as $p) {
                $ids_patients[] = $p['id_patient'];
            }

            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $ipa) {
                if (!in_array(strval($ipa), $ids_patients)) {
                    $query =
                        "UPDATE activite_choisie
                         SET statut = 'Terminée'
                         WHERE id_creneau = :id_creneau
                         AND id_orientation = (SELECT id_orientation 
                                                FROM orientation
                                                WHERE id_patient = :id_patient)";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_patient', $ipa);
                    $stmt->bindValue(':id_creneau', $id_creneau);

                    if (!$stmt->execute()) {
                        throw new Exception('Error UPDATE activite_choisie');
                    }
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
     * Return les particiaoants d'un créneaux
     *
     * @param $id_creneau
     * @return array|false Return an associative array or false on failure
     */
    public function readAllParticipantsCreneau($id_creneau)
    {
        if (empty($id_creneau)) {
            return false;
        }

        $query = "
            SELECT patients.id_patient,
                   IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance) as nom_patient,
                   IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                      premier_prenom_naissance)                                                  as prenom_patient,
                   id_liste_participants_creneau,
                   propose_inscrit,
                   abandon,
                   reorientation,
                   status_participant
            FROM liste_participants_creneau
                     JOIN patients ON liste_participants_creneau.id_patient = patients.id_patient
                     JOIN coordonnees ON patients.id_coordonnee = coordonnees.id_coordonnees
            WHERE liste_participants_creneau.id_creneau = :id_creneau";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_creneau', $id_creneau);
        if (!$stmt->execute()) {
            return false;
        }

        $patients = [];
        while ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patient['nom_patient'] = !empty($patient['nom_patient']) ? EncryptionManager::decrypt(
                $patient['nom_patient']
            ) : "";
            $patient['prenom_patient'] = !empty($patient['prenom_patient']) ? EncryptionManager::decrypt(
                $patient['prenom_patient']
            ) : "";

            $patients[] = $patient;
        }

        return $patients;
    }

    /**
     * Deletes a creneau
     *
     * @param $id_creneau string the id of the creneau to be deleted
     * @return bool if the deletion is successful
     */
    public function delete($id_creneau): bool
    {
        if (empty($id_creneau)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            ////////////////////////////////////////////////////
            // Verification que le creneau existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_creneau
                FROM creneaux
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_creneau FROM creneaux');
            }
            if ($stmt->rowCount() == 0) {
                throw new Exception('Error: le creneau n\'existe pas');
            }

            ////////////////////////////////////////////////////
            // Verification si des patients n'ont pas choisi cette activité
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_patient_choix
                from activite_choisie
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_patient_choix');
            }
            $data = $stmt->fetch();
            if (intval($data['nb_patient_choix']) > 0) {
                throw new Exception('Error: Des patients ont choisi cette activité');
            }

            ////////////////////////////////////////////////////
            // Verification si des patients ne sont pas dans la liste des participants pour ce créneau
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_patient_participants
                from liste_participants_creneau
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_patient_participants');
            }
            $data = $stmt->fetch();
            if (intval($data['nb_patient_participants']) > 0) {
                throw new Exception('Error: Des patients sont dans la liste des participants');
            }

            ////////////////////////////////////////////////////
            // Verification si le creneau est utilisé par les seances
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_seance
                from seance
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_seance');
            }
            $data = $stmt->fetch();
            if (intval($data['nb_seance']) > 0) {
                throw new Exception('Error: Il y a des séances pour ce créneau');
            }

            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // DELETE commence_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM commence_a
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM commence_a');
            }

            ////////////////////////////////////////////////////
            // DELETE se_termine_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM se_termine_a
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM se_termine_a');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id_adresse
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_adresse 
                FROM se_pratique_a
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error select id_adresse');
            }
            $data = $stmt->fetch();
            $id_adresse = $data['id_adresse'];
            if (empty($id_adresse)) {
                throw new Exception(
                    'Error: L\'id_adresse du creneau \'' . $id_creneau . '\'  n\'a pas été trouvé dans la BDD'
                );
            }

            ////////////////////////////////////////////////////
            // DELETE se_situe_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM se_pratique_a
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM se_pratique_a');
            }

            ////////////////////////////////////////////////////
            // DELETE se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM se_localise_a
                WHERE id_adresse = :id_adresse';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM se_localise_a');
            }

            ////////////////////////////////////////////////////
            // DELETE adresse
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM adresse
                WHERE id_adresse = :id_adresse';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM adresse');
            }

            ////////////////////////////////////////////////////
            // Récupération du nombre de crenaux(autre que celui supprimé) lequel l'intervenant intervient(dans la même structure)
            ////////////////////////////////////////////////////
            $query = '
                SELECT creneaux.id_structure, creneaux_intervenant.id_intervenant
                FROM creneaux
                JOIN creneaux_intervenant
                WHERE creneaux.id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error select id_structure, id_intervenant');
            }
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $creneau) {
                $id_structure = $creneau['id_structure'];
                $id_intervenant = $creneau['id_intervenant'];

                $query = '
                    SELECT count(*) as nb_creneaux
                    from creneaux
                    JOIN creneaux_intervenant
                    WHERE id_intervenant = :id_intervenant
                      AND id_structure = :id_structure
                      AND creneaux.id_creneau <> :id_creneau';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_intervenant', $id_intervenant);
                $stmt->bindValue(':id_structure', $id_structure);
                $stmt->bindValue(':id_creneau', $id_creneau);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT count(*) as nb_creneaux');
                }
                $data = $stmt->fetch();
                $nb_creneaux = intval($data['nb_creneaux']);

                ////////////////////////////////////////////////////
                // DELETE intervient_dans (si intervenant n'intervient pas dans d'autres créneaux de la structure)
                ////////////////////////////////////////////////////
                if ($nb_creneaux == 0) {
                    $query = '
                        DELETE FROM intervient_dans
                        WHERE id_intervenant = :id_intervenant
                          AND id_structure = :id_structure';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_intervenant', $id_intervenant);
                    $stmt->bindValue(':id_structure', $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error DELETE FROM intervient_dans');
                    }
                }
            }

            ////////////////////////////////////////////////////
            // DELETE creneaux_intervenant
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM creneaux_intervenant
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM creneaux_intervenant');
            }

            ////////////////////////////////////////////////////
            // DELETE creneaux
            ////////////////////////////////////////////////////
            $query = '
                DELETE FROM creneaux
                WHERE id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM creneaux');
            }

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->query("SET foreign_key_checks=1"); // TODO is this necessary?
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id_creneau
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_creneau)
    {
        $query = '
            SELECT creneaux.id_creneau          as id_creneau,
                   creneaux.nom_creneau         as nom_creneau,
                   creneaux.prix_creneau        as tarif,
                   creneaux.public_vise         as public_vise,
                   creneaux.description_creneau as description,
                   creneaux.type_seance         as type_seance,
                   creneaux.pathologie_creneau  as pathologie,
                   creneaux.id_type_parcours    as id_type_parcours,
                   creneaux.id_jour             as id_jour,
                   creneaux.nombre_participants as nombre_participants,
                   creneaux.id_structure        as id_structure,
                   creneaux.facilite_paiement   as facilite_paiement,
                   creneaux.activation          as activation,
                   jours.nom_jour               as jour,
                   heureCommence.heure          as nom_heure_debut,
                   heureCommence.id_heure       as heure_debut,
                   heureTermine.heure           as nom_heure_fin,
                   heureTermine.id_heure        as heure_fin,
                   type_parcours.type_parcours  as type_parcours,
                   structure.nom_structure      as nom_structure,
                   coordonnees.nom_coordonnees,
                   coordonnees.prenom_coordonnees,
                   apratique.nom_adresse        as nom_adresse,
                   apratique.complement_adresse as complement_adresse,
                   villepratique.nom_ville      as nom_ville,
                   villepratique.code_postal    as code_postal
            FROM creneaux
                     LEFT JOIN structure USING (id_structure)
                     LEFT JOIN jours USING (id_jour)
                     LEFT JOIN commence_a USING (id_creneau)
                     LEFT JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     LEFT JOIN se_termine_a USING (id_creneau)
                     LEFT JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                     LEFT JOIN type_parcours USING (id_type_parcours)
                     LEFT JOIN se_pratique_a USING (id_creneau)
                     LEFT JOIN adresse apratique ON se_pratique_a.id_adresse = apratique.id_adresse
                     LEFT JOIN se_localise_a localpratique ON apratique.id_adresse = localpratique.id_adresse
                     LEFT JOIN villes villepratique ON localpratique.id_ville = villepratique.id_ville
                     LEFT JOIN intervenants ON intervenants.id_intervenant = (SELECT id_intervenant
                                                                              FROM creneaux_intervenant
                                                                              WHERE creneaux_intervenant.id_creneau = creneaux.id_creneau
                                                                              LIMIT 1)
                     LEFT JOIN coordonnees ON coordonnees.id_coordonnees = intervenants.id_coordonnees
            WHERE creneaux.id_creneau = :id_creneau';

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_creneau', $id_creneau);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result != false) {
            // récupération du nombre de participants du créneau
            $query = 'SELECT COUNT(*) as nb_participants_creneau
                FROM liste_participants_creneau
                WHERE id_creneau = :id_creneau';

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_creneau', $id_creneau);
            $statement->execute();

            $result[] = $statement->fetch(PDO::FETCH_ASSOC);

            // récupération des intervenants du créneau
            $query = '
                SELECT coordonnees.nom_coordonnees as nom_intervenant,
                       coordonnees.prenom_coordonnees as prenom_intervenant,
                       creneaux_intervenant.id_intervenant
                FROM creneaux_intervenant
                JOIN coordonnees ON creneaux_intervenant.id_intervenant = coordonnees.id_intervenant
                WHERE creneaux_intervenant.id_creneau = :id_creneau';

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_creneau', $id_creneau);
            $statement->execute();

            $intervenants = $statement->fetchAll(PDO::FETCH_ASSOC);
            $result["intervenants"] = $intervenants ?: [];
        }

        return $result;
    }

    /**
     * @param array $session required parameters:
     * [
     *     'id_role_user' => string,
     *     'id_territoire' => string,
     *     'id_user' => string,
     *     'id_structure' => string|null, can be null if id_role_user=1 (super admin)
     * ]
     *
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAll(array $session)
    {
        try {
            $permissions = new Permissions($session);
        } catch (Exception $e) {
            return false;
        }

        // requête pour un utilisateur autre qu'intervenant (ou s'il est intervenant a aussi un autre rôle)
        $query = '
            SELECT creneaux.id_creneau          as id_creneau,
                   creneaux.nom_creneau         as nom_creneau,
                   creneaux.nombre_participants as nb_participant,
                   creneaux.prix_creneau        as tarif,
                   creneaux.public_vise         as public_vise,
                   creneaux.description_creneau as description,
                   creneaux.type_seance         as type_seance,
                   creneaux.pathologie_creneau  as pathologie,
                   creneaux.id_type_parcours    as id_type_parcours,
                   creneaux.id_jour             as id_jour,
                   creneaux.nombre_participants as nombre_participants,
                   creneaux.id_structure        as id_structure,
                   creneaux.facilite_paiement   as facilite_paiement,
                   creneaux.activation,
                   jours.nom_jour               as jour,
                   heureCommence.heure          as nom_heure_debut,
                   heureCommence.id_heure       as heure_debut,
                   heureTermine.heure           as nom_heure_fin,
                   heureTermine.id_heure        as heure_fin,
                   type_parcours.type_parcours  as type_parcours,
                   structure.nom_structure      as nom_structure,
                   coordonnees.nom_coordonnees,
                   coordonnees.prenom_coordonnees,
                   apratique.nom_adresse        as nom_adresse,
                   apratique.complement_adresse as complement_adresse,
                   villepratique.nom_ville      as nom_ville,
                   villepratique.code_postal    as code_postal
            FROM creneaux
                     LEFT JOIN structure ON creneaux.id_structure = structure.id_structure
                     LEFT JOIN jours ON creneaux.id_jour = jours.id_jour
                     LEFT JOIN commence_a ON creneaux.id_creneau = commence_a.id_creneau
                     LEFT JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     LEFT JOIN se_termine_a ON creneaux.id_creneau = se_termine_a.id_creneau
                     LEFT JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                     LEFT JOIN type_parcours ON creneaux.id_type_parcours = type_parcours.id_type_parcours
                     LEFT JOIN se_pratique_a ON creneaux.id_creneau = se_pratique_a.id_creneau
                     LEFT JOIN adresse apratique ON se_pratique_a.id_adresse = apratique.id_adresse
                     LEFT JOIN se_localise_a localpratique ON apratique.id_adresse = localpratique.id_adresse
                     LEFT JOIN villes villepratique ON localpratique.id_ville = villepratique.id_ville
                     LEFT JOIN intervenants ON intervenants.id_intervenant = (SELECT id_intervenant
                                                                              FROM creneaux_intervenant
                                                                              WHERE creneaux_intervenant.id_creneau = creneaux.id_creneau
                                                                              LIMIT 1)
                     LEFT JOIN coordonnees ON coordonnees.id_coordonnees = intervenants.id_coordonnees
            WHERE 1 = 1 ';
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND structure.id_territoire = :id_territoire ';
        }
        if ($permissions->hasRole(Permissions::RESPONSABLE_STRUCTURE)) {
            $query .= ' AND creneaux.id_structure = :id_structure ';
        }

        $statement = $this->pdo->prepare($query);
        if (!$permissions->hasRole(Permissions::SUPER_ADMIN)) {
            $statement->bindValue(':id_territoire', $session['id_territoire']);
        }
        if ($permissions->hasRole(Permissions::RESPONSABLE_STRUCTURE)) {
            $statement->bindValue(':id_structure', $session['id_structure']);
        }

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $id_user
     * @param boolean|null $filtre_labelise si true return les créneaux labellisés, si false return les créneaux non
     *     labellisés, sinon return les créneaux labellisés et non labellisés
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAllUser($id_user, bool $filtre_labelise = null)
    {
        $is_filtre_labelise_set = gettype($filtre_labelise) == "boolean";

        $query = '
            SELECT creneaux.id_creneau          as id_creneau,
                   creneaux.nom_creneau         as nom_creneau,
                   creneaux.nombre_participants as nb_participant,
                   creneaux.prix_creneau        as tarif,
                   creneaux.public_vise         as public_vise,
                   creneaux.description_creneau as description,
                   creneaux.type_seance         as type_seance,
                   creneaux.pathologie_creneau  as pathologie,
                   creneaux.id_type_parcours    as id_type_parcours,
                   creneaux.id_jour             as id_jour,
                   creneaux.nombre_participants as nombre_participants,
                   creneaux.id_structure        as id_structure,
                   creneaux.facilite_paiement   as facilite_paiement,
                   creneaux.activation,
                   jours.nom_jour               as jour,
                   heureCommence.heure          as nom_heure_debut,
                   heureCommence.id_heure       as heure_debut,
                   heureTermine.heure           as nom_heure_fin,
                   heureTermine.id_heure        as heure_fin,
                   type_parcours.type_parcours  as type_parcours,
                   structure.nom_structure      as nom_structure,
                   coordonnees.nom_coordonnees,
                   coordonnees.prenom_coordonnees,
                   apratique.nom_adresse        as nom_adresse,
                   apratique.complement_adresse as complement_adresse,
                   villepratique.nom_ville      as nom_ville,
                   villepratique.code_postal    as code_postal
            FROM creneaux
                     LEFT JOIN structure ON creneaux.id_structure = structure.id_structure
                     LEFT JOIN jours ON creneaux.id_jour = jours.id_jour
                     LEFT JOIN commence_a ON creneaux.id_creneau = commence_a.id_creneau
                     LEFT JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     LEFT JOIN se_termine_a ON creneaux.id_creneau = se_termine_a.id_creneau
                     LEFT JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                     LEFT JOIN type_parcours ON creneaux.id_type_parcours = type_parcours.id_type_parcours
                     LEFT JOIN se_pratique_a ON creneaux.id_creneau = se_pratique_a.id_creneau
                     LEFT JOIN adresse apratique ON se_pratique_a.id_adresse = apratique.id_adresse
                     LEFT JOIN se_localise_a localpratique ON apratique.id_adresse = localpratique.id_adresse
                     LEFT JOIN villes villepratique ON localpratique.id_ville = villepratique.id_ville
                     LEFT JOIN creneaux_intervenant ON creneaux.id_creneau = creneaux_intervenant.id_creneau
                     LEFT JOIN coordonnees ON coordonnees.id_intervenant = creneaux_intervenant.id_intervenant
            WHERE coordonnees.id_user = :id_user ';
        if ($is_filtre_labelise_set && $filtre_labelise) {
            $query .= ' AND creneaux.id_type_parcours != 4 '; // on garde les créneaux labellisés
        } else {
            if ($is_filtre_labelise_set) {
                $query .= ' AND creneaux.id_type_parcours = 4 '; // on garde les créneaux non labellisés
            }
        }

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return un array contenant tous les créneaux de l'intervenant donné
     *
     * @param $id_intervenant string id_intervenant
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAllIntervenant($id_intervenant)
    {
        if (empty($id_intervenant)) {
            return false;
        }

        $query = '
            SELECT creneaux.id_creneau          as id_creneau,
                   creneaux.nom_creneau         as nom_creneau,
                   creneaux.prix_creneau        as tarif,
                   creneaux.public_vise         as public_vise,
                   creneaux.description_creneau as description,
                   creneaux.type_seance         as type_seance,
                   creneaux.pathologie_creneau  as pathologie,
                   creneaux.id_type_parcours    as id_type_parcours,
                   creneaux.id_jour             as id_jour,
                   creneaux.nombre_participants as nombre_participants,
                   creneaux.id_structure        as id_structure,
                   creneaux.facilite_paiement   as facilite_paiement,
                   creneaux.activation,
                   jours.nom_jour               as jour,
                   heureCommence.heure          as nom_heure_debut,
                   heureCommence.id_heure       as heure_debut,
                   heureTermine.heure           as nom_heure_fin,
                   heureTermine.id_heure        as heure_fin,
                   type_parcours.type_parcours  as type_parcours,
                   structure.nom_structure      as nom_structure,
                   coordonnees.nom_coordonnees,
                   coordonnees.prenom_coordonnees,
                   apratique.nom_adresse        as nom_adresse,
                   apratique.complement_adresse as complement_adresse,
                   villepratique.nom_ville      as nom_ville,
                   villepratique.code_postal    as code_postal
            FROM creneaux
                     JOIN creneaux_intervenant ON creneaux.id_creneau = creneaux_intervenant.id_creneau
                     LEFT JOIN structure USING (id_structure)
                     LEFT JOIN jours USING (id_jour)
                     LEFT JOIN commence_a ON creneaux.id_creneau = commence_a.id_creneau
                     LEFT JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     LEFT JOIN se_termine_a ON creneaux.id_creneau = se_termine_a.id_creneau
                     LEFT JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                     LEFT JOIN intervenants ON creneaux_intervenant.id_intervenant = intervenants.id_intervenant
                     LEFT JOIN coordonnees ON coordonnees.id_coordonnees = intervenants.id_coordonnees
                     LEFT JOIN type_parcours USING (id_type_parcours)
                     LEFT JOIN se_pratique_a ON creneaux.id_creneau = se_pratique_a.id_creneau
                     LEFT JOIN adresse apratique ON se_pratique_a.id_adresse = apratique.id_adresse
                     LEFT JOIN se_localise_a localpratique ON apratique.id_adresse = localpratique.id_adresse
                     LEFT JOIN villes villepratique ON localpratique.id_ville = villepratique.id_ville
            WHERE creneaux_intervenant.id_intervenant = :id_intervenant';

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_intervenant', $id_intervenant);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return un array contenant tous les créneaux de la structure donnée
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
            SELECT creneaux.id_creneau          as id_creneau,
                   creneaux.nom_creneau         as nom_creneau,
                   creneaux.prix_creneau        as tarif,
                   creneaux.public_vise         as public_vise,
                   creneaux.description_creneau as description,
                   creneaux.type_seance         as type_seance,
                   creneaux.pathologie_creneau  as pathologie,
                   creneaux.id_type_parcours    as id_type_parcours,
                   creneaux.id_jour             as id_jour,
                   creneaux.nombre_participants as nombre_participants,
                   creneaux.id_structure        as id_structure,
                   creneaux.facilite_paiement   as facilite_paiement,
                   creneaux.activation,
                   jours.nom_jour               as jour,
                   heureCommence.heure          as nom_heure_debut,
                   heureCommence.id_heure       as heure_debut,
                   heureTermine.heure           as nom_heure_fin,
                   heureTermine.id_heure        as heure_fin,
                   type_parcours.type_parcours  as type_parcours,
                   structure.nom_structure      as nom_structure,
                   coordonnees.nom_coordonnees,
                   coordonnees.prenom_coordonnees,
                   apratique.nom_adresse        as nom_adresse,
                   apratique.complement_adresse as complement_adresse,
                   villepratique.nom_ville      as nom_ville,
                   villepratique.code_postal    as code_postal
            FROM creneaux
                     LEFT JOIN structure USING (id_structure)
                     LEFT JOIN jours USING (id_jour)
                     LEFT JOIN commence_a USING (id_creneau)
                     LEFT JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     LEFT JOIN se_termine_a USING (id_creneau)
                     LEFT JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                     LEFT JOIN type_parcours USING (id_type_parcours)
                     LEFT JOIN se_pratique_a USING (id_creneau)
                     LEFT JOIN adresse apratique ON se_pratique_a.id_adresse = apratique.id_adresse
                     LEFT JOIN se_localise_a localpratique ON apratique.id_adresse = localpratique.id_adresse
                     LEFT JOIN villes villepratique ON localpratique.id_ville = villepratique.id_ville
                     LEFT JOIN intervenants ON intervenants.id_intervenant = (SELECT id_intervenant
                                                                              FROM creneaux_intervenant
                                                                              WHERE creneaux_intervenant.id_creneau = creneaux.id_creneau
                                                                              LIMIT 1)
                     LEFT JOIN coordonnees ON coordonnees.id_coordonnees = intervenants.id_coordonnees
            WHERE creneaux.id_structure = :id_structure
            ORDER BY nom_creneau';

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_structure', $id_structure);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return un array contenant tous les créneaux vers lesquels un patient est orienté
     *
     * @param $id_patient string id_patient
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAllPatient($id_patient)
    {
        if (empty($id_patient)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = '
            SELECT creneaux.id_creneau          as id_creneau,
                   creneaux.nom_creneau         as nom_creneau,
                   creneaux.prix_creneau        as tarif,
                   creneaux.public_vise         as public_vise,
                   creneaux.description_creneau as description,
                   creneaux.type_seance         as type_seance,
                   creneaux.pathologie_creneau  as pathologie,
                   creneaux.id_type_parcours    as id_type_parcours,
                   creneaux.id_jour             as id_jour,
                   creneaux.nombre_participants as nombre_participants,
                   creneaux.id_structure        as id_structure,
                   creneaux.facilite_paiement   as facilite_paiement,
                   creneaux.activation,
                   jours.nom_jour               as jour,
                   heureCommence.heure          as nom_heure_debut,
                   heureCommence.id_heure       as heure_debut,
                   heureTermine.heure           as nom_heure_fin,
                   heureTermine.id_heure        as heure_fin,
                   type_parcours.type_parcours  as type_parcours,
                   structure.nom_structure      as nom_structure,
                   coordonnees.nom_coordonnees,
                   coordonnees.prenom_coordonnees,
                   apratique.nom_adresse        as nom_adresse,
                   apratique.complement_adresse as complement_adresse,
                   villepratique.nom_ville      as nom_ville,
                   villepratique.code_postal    as code_postal,
                   t.nom_territoire
            FROM creneaux
                     LEFT JOIN structure USING (id_structure)
                     LEFT JOIN jours USING (id_jour)
                     LEFT JOIN commence_a USING (id_creneau)
                     LEFT JOIN heures heureCommence ON commence_a.id_heure = heureCommence.id_heure
                     LEFT JOIN se_termine_a USING (id_creneau)
                     LEFT JOIN heures heureTermine ON se_termine_a.id_heure = heureTermine.id_heure
                     LEFT JOIN type_parcours USING (id_type_parcours)
                     LEFT JOIN se_pratique_a USING (id_creneau)
                     LEFT JOIN adresse apratique ON se_pratique_a.id_adresse = apratique.id_adresse
                     LEFT JOIN se_localise_a localpratique ON apratique.id_adresse = localpratique.id_adresse
                     LEFT JOIN villes villepratique ON localpratique.id_ville = villepratique.id_ville
                     LEFT JOIN territoire t on structure.id_territoire = t.id_territoire
                     LEFT JOIN activite_choisie ac on creneaux.id_creneau = ac.id_creneau
                     LEFT JOIN orientation o on ac.id_orientation = o.id_orientation
                     LEFT JOIN intervenants ON intervenants.id_intervenant = (SELECT id_intervenant
                                                                              FROM creneaux_intervenant
                                                                              WHERE creneaux_intervenant.id_creneau = creneaux.id_creneau
                                                                              LIMIT 1)
                     LEFT JOIN coordonnees ON coordonnees.id_coordonnees = intervenants.id_coordonnees
            WHERE o.id_patient = :id_patient
            ORDER BY nom_creneau';

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_patient', $id_patient);
        if (!$statement->execute()) {
            $this->errorMessage = "Erreur lors de l'exécution de la requête";
            return false;
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_creneau
     * @return array|false Return the id_user of the intervenants
     */
    public function getUserIds($id_creneau)
    {
        $query = '
            SELECT id_user
            FROM creneaux
                JOIN creneaux_intervenant ON creneaux.id_creneau = creneaux_intervenant.id_creneau
                JOIN intervenants ON creneaux_intervenant.id_intervenant = intervenants.id_intervenant
                JOIN coordonnees ON intervenants.id_coordonnees = coordonnees.id_coordonnees
            WHERE creneaux.id_creneau = :id_creneau AND id_user IS NOT NULL';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id_creneau", $id_creneau);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function requiredParametersPresent($parameters): bool
    {
        // l'id_intervenant n'est pas nécessaire lors de l'import d'un créneau par API,
        // mais n'est nécessaire si ce n'est pas un import
        $intervenant_ids_present_if_required = (empty($parameters['id_api']) && is_array(
                    $parameters['intervenant_ids']
                ) && !empty($parameters['intervenant_ids'])) ||
            !empty($parameters['id_api']);

        // l'id_api_structure est nécessaire lors de l'import d'un créneau par API,
        // mais n'est nécessaire si ce n'est pas un import
        $id_structure_present_if_required = (!empty($parameters['id_structure']) && empty($parameters['id_api'])) ||
            !empty($parameters['id_api']);

        // si on importe des données de l'API, l'id_api, l'id_api_structure et l'id_api_intervenant sont nécessaires
        $all_api_ids_present_if_required =
            (!empty($parameters['id_api']) &&
                !empty($parameters['id_api_structure']) &&
                !empty($parameters['id_api_intervenant'])) ||
            empty($parameters['id_api']);

        return
            $intervenant_ids_present_if_required &&
            $id_structure_present_if_required &&
            $all_api_ids_present_if_required &&
            !empty($parameters['nom_creneau']) &&
            !empty($parameters['code_postal']) &&
            !empty($parameters['nom_ville']) &&
            !empty($parameters['jour']) &&
            !empty($parameters['heure_debut']) &&
            !empty($parameters['heure_fin']) &&
            !empty($parameters['type_creneau']) &&
            !empty($parameters['pathologie']) &&
            !empty($parameters['type_seance']);
    }
}