<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use Sportsante86\Sapa\Outils\ChaineCharactere;
use Sportsante86\Sapa\Outils\FilesManager;
use Sportsante86\Sapa\Outils\Permissions;

use function Sportsante86\Sapa\Outils\traitement_nom_ville;

class Structure
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

    public function create($parameters)
    {
        if (empty($parameters['nom_structure']) ||
            empty($parameters['id_statut_structure']) ||
            empty($parameters['nom_adresse']) ||
            empty($parameters['code_postal']) ||
            empty($parameters['nom_ville']) ||
            empty($parameters['id_statut_juridique'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $nom_structure = trim(mb_strtoupper(addslashes($parameters['nom_structure']), 'UTF-8'));
            $id_statut_structure = filter_var($parameters['id_statut_structure'], FILTER_SANITIZE_NUMBER_INT);
            $nom_adresse = ChaineCharactere::mb_ucfirst(addslashes($parameters['nom_adresse']));
            $code_postal = $parameters['code_postal'];
            $ville = traitement_nom_ville($parameters['nom_ville']);
            $id_statut_juridique = filter_var($parameters['id_statut_juridique'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $complement_adresse = $parameters['complement_adresse'] ?? "";
            $id_territoire = isset($parameters['id_territoire']) ?
                filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT) :
                null;
            $intervenants = $parameters['intervenants'] ?? [];
            $antennes = $parameters['antennes'] ?? [];
            $lien_ref_structure = $parameters['lien_ref_structure'] ?? "";
            $id_api = $parameters['id_api'] ?? null;
            $code_onaps = $parameters['code_onaps'] ?? "";

            // autres infos

            // representant de la structure
            $nom_representant = $parameters['nom_representant'] ?? "";
            $prenom_representant = $parameters['prenom_representant'] ?? "";
            $email = $parameters['email'] ?? "";
            $tel_fixe = isset($parameters['tel_fixe']) ?
                filter_var(
                    $parameters['tel_fixe'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_portable = isset($parameters['tel_portable']) ?
                filter_var(
                    $parameters['tel_portable'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            if ($id_territoire == null || $id_territoire == '') {
                // Récupération de l'id du territoire s'il n'a pas été fourni à la création
                $query = '
                    SELECT t.id_territoire
                    FROM villes
                             JOIN territoire t on villes.id_departement = t.id_departement
                    WHERE code_postal = :code_postal
                    LIMIT 1';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':code_postal', $code_postal);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT id_territoire');
                }
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_territoire = $data['id_territoire'];

                if (empty($id_territoire)) {
                    throw new Exception('Error: Il n\'y a pas de territoire pour le code_postal=' . $code_postal);
                }
            }

            // verification si la structure à déja été importé
            if (!empty($id_api)) {
                $query = '
                    SELECT id_structure
                    FROM structure
                    WHERE id_api = :id_api';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_api', $id_api);

                if (!$stmt->execute()) {
                    throw new Exception('Error select id_structure');
                }
                if ($stmt->rowCount() > 0) {
                    throw new Exception('Error: element déja importé');
                }
            }

            // Insertion dans structure
            $query = '
                INSERT INTO structure (id_api, nom_structure, code_onaps, id_statut_structure, id_territoire,
                                       id_statut_juridique, lien_ref_structure)
                VALUES (:id_api, :nom_structure, :code_onaps, :id_statut_structure, :id_territoire,
                        :id_statut_juridique, :lien_ref_structure)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_structure', $nom_structure);
            $stmt->bindValue(':id_statut_structure', $id_statut_structure);
            $stmt->bindValue(':id_territoire', $id_territoire);
            $stmt->bindValue(':id_statut_juridique', $id_statut_juridique);
            $stmt->bindValue(':lien_ref_structure', $lien_ref_structure);
            if (empty($id_api)) {
                $stmt->bindValue(':id_api', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':id_api', $id_api);
            }
            if (empty($code_onaps)) {
                $stmt->bindValue(':code_onaps', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':code_onaps', $code_onaps);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO structure');
            }
            $id_structure = $this->pdo->lastInsertId();

            ////////////////////////////////////////////////////
            // Insertion dans adresse
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO adresse(nom_adresse, complement_adresse)
                VALUES (:nom_adresse, :complement_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_adresse', $nom_adresse);
            $stmt->bindValue(':complement_adresse', $complement_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error insert adresse');
            }
            $id_adresse = $this->pdo->lastInsertId();

            ////////////////////////////////////////////////////
            // Insertion dans se_situe_a
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO se_situe_a(id_structure, id_adresse)
                VALUES (:id_structure, :id_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error insert se_situe_a');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id de la ville -> insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_ville
                from villes
                WHERE nom_ville LIKE :nom_ville
                  AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if (!$stmt->execute()) {
                throw new Exception('Error select villes');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_ville = $data['id_ville'] ?? null;
            if (empty($id_ville)) {
                throw new Exception(
                    'Error: La ville \'' . $ville . '\' qui a en code_postal \'' . $code_postal . '\' n\'a pas été trouvé dans la BDD'
                );
            }

            ////////////////////////////////////////////////////
            // Insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO se_localise_a(id_adresse, id_ville)
                VALUES (:id_adresse, :id_ville)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);
            $stmt->bindValue(':id_ville', $id_ville);

            if (!$stmt->execute()) {
                throw new Exception('Error insert se_localise_a');
            }

            ////////////////////////////////////////////////////
            // Ajout dans la table intervient_dans
            ////////////////////////////////////////////////////
            if (is_array($intervenants)) {
                foreach ($intervenants as $id_intervenant) {
                    $query = '
                        INSERT INTO intervient_dans (id_intervenant, id_structure)
                        VALUES (:id_intervenant, :id_structure)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_intervenant', $id_intervenant);
                    $stmt->bindValue(':id_structure', $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error insert intervient_dans');
                    }
                }
            }

            ////////////////////////////////////////////////////
            // Insertion dans coordonnees si le nom et prenom sont entrés
            ////////////////////////////////////////////////////
            if (!empty($nom_representant) && !empty($prenom_representant)) {
                $query = '
                    INSERT INTO coordonnees (nom_coordonnees, prenom_coordonnees, tel_fixe_coordonnees,
                                             tel_portable_coordonnees, mail_coordonnees)
                    VALUES (:nom_coordonnees, :prenom_coordonnees, :tel_fixe_coordonnees, :tel_portable_coordonnees,
                            :mail_coordonnees)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':nom_coordonnees', $nom_representant);
                $stmt->bindValue(':prenom_coordonnees', $prenom_representant);
                $stmt->bindValue(':tel_fixe_coordonnees', $tel_fixe);
                $stmt->bindValue(':tel_portable_coordonnees', $tel_portable);
                $stmt->bindValue(':mail_coordonnees', $email);

                if (!$stmt->execute()) {
                    throw new Exception('Error insert coordonnees tel=' . $tel_fixe . ' @id=' . $id_api);
                }
                $id_coordonnees = $this->pdo->lastInsertId();

                // Update dans des coordonnées du responsable de la structure
                $query = '
                    UPDATE structure
                    SET id_coordonnees = :id_coordonnees
                    WHERE id_structure = :id_structure';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_coordonnees', $id_coordonnees);
                $stmt->bindValue(':id_structure', $id_structure);
                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE structure');
                }
            }

            ////////////////////////////////////////////////////
            // insertion des antennes
            ////////////////////////////////////////////////////
            if (is_array($antennes)) {
                if (count($antennes) == 0) {
                    // s'il n'y a pas d'antennes entrées, on ajoute une antenne avec le même nom que la structure
                    $query = '
                        INSERT INTO antenne (id_structure, nom_antenne)
                        VALUES (:id_structure, :nom_antenne)';
                    $stmt = $this->pdo->prepare($query);

                    $stmt->bindValue(':nom_antenne', $nom_structure);
                    $stmt->bindValue(':id_structure', $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO antenne');
                    }
                } else {
                    foreach ($antennes as $value) {
                        $query = '
                            INSERT INTO antenne (id_structure, nom_antenne)
                            VALUES (:id_structure, :nom_antenne)';
                        $stmt = $this->pdo->prepare($query);

                        $stmt->bindValue(':nom_antenne', $value['nom_antenne']);
                        $stmt->bindValue(':id_structure', $id_structure);

                        if (!$stmt->execute()) {
                            throw new Exception('Error INSERT INTO antenne');
                        }
                    }
                }
            }

            $this->pdo->commit();
            return $id_structure;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }

    public function update($parameters)
    {
        if (empty($parameters['nom_structure']) ||
            empty($parameters['id_structure']) ||
            empty($parameters['id_territoire']) ||
            empty($parameters['id_statut_structure']) ||
            empty($parameters['nom_adresse']) ||
            empty($parameters['code_postal']) ||
            empty($parameters['nom_ville']) ||
            empty($parameters['id_statut_juridique'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $nom_structure = trim(mb_strtoupper(addslashes($parameters['nom_structure']), 'UTF-8'));
            $id_statut_structure = filter_var($parameters['id_statut_structure'], FILTER_SANITIZE_NUMBER_INT);
            $id_structure = filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT);
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $nom_adresse = ChaineCharactere::mb_ucfirst(addslashes($parameters['nom_adresse']));
            $code_postal = $parameters['code_postal'];
            $ville = traitement_nom_ville($parameters['nom_ville']);
            $id_statut_juridique = filter_var($parameters['id_statut_juridique'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            $complement_adresse = $parameters['complement_adresse'] ?? "";
            $intervenants = $parameters['intervenants'] ?? [];
            $antennes = $parameters['antennes'] ?? [];
            $lien_ref_structure = $parameters['lien_ref_structure'] ?? "";
            $code_onaps = $parameters['code_onaps'] ?? "";

            // representant de la structure
            $nom_representant = $parameters['nom_representant'] ?? "";
            $prenom_representant = $parameters['prenom_representant'] ?? "";
            $email = $parameters['email'] ?? "";
            $tel_fixe = isset($parameters['tel_fixe']) ?
                filter_var(
                    $parameters['tel_fixe'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_portable = isset($parameters['tel_portable']) ?
                filter_var(
                    $parameters['tel_portable'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";

            ////////////////////////////////////////////////////
            // Update nom nom_structure et statut_structure
            ////////////////////////////////////////////////////
            $query = '
                UPDATE structure
                SET nom_structure       = :nom_structure,
                    code_onaps          = :code_onaps,
                    id_statut_structure = :id_statut_structure,
                    id_territoire       = :id_territoire,
                    id_statut_juridique = :id_statut_juridique,
                    lien_ref_structure  = :lien_ref_structure
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':nom_structure', $nom_structure);
            $stmt->bindValue(':id_statut_structure', $id_statut_structure);
            $stmt->bindValue(':id_structure', $id_structure);
            $stmt->bindValue(':id_territoire', $id_territoire);
            $stmt->bindValue(':id_statut_juridique', $id_statut_juridique);
            if (!empty($code_onaps)) {
                $stmt->bindValue(':code_onaps', $code_onaps);
            } else {
                $stmt->bindValue(':code_onaps', null, PDO::PARAM_NULL);
            }
            if (!empty($lien_ref_structure)) {
                $stmt->bindValue(':lien_ref_structure', $lien_ref_structure);
            } else {
                $stmt->bindValue(':lien_ref_structure', null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE structure');
            }

            ////////////////////////////////////////////////////
            // Récup id_adresse
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_adresse
                FROM structure
                         JOIN se_situe_a USING (id_structure)
                         JOIN adresse USING (id_adresse)
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_adresse FROM structure');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_adresse = $data['id_adresse'];

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
            // Récupération de l'id de la ville -> insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_ville
                from villes
                WHERE nom_ville LIKE :nom_ville
                  AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if (!$stmt->execute()) {
                throw new Exception('Error select villes');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_ville = $data['id_ville'];
            if (empty($id_ville)) {
                throw new Exception(
                    'Error: La ville \'' . $ville . '\' qui a en code_postal \'' . $code_postal . '\' n\'a pas été trouvé dans la BDD'
                );
            }

            ////////////////////////////////////////////////////
            // Update id_ville
            ////////////////////////////////////////////////////
            $query = '
                UPDATE se_localise_a
                SET id_ville = :id_ville
                WHERE id_adresse = :id_adresse';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_ville', $id_ville);
            $stmt->bindValue(':id_adresse', $id_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE se_localise_a');
            }

            ////////////////////////////////////////////////////
            // Insertion des intervenants dans intervient_dans
            ////////////////////////////////////////////////////
            $intervenant_not_deleted = [];
            // recup de tous les intervenant qui interviennent dans la structure
            $query = '
                SELECT intervient_dans.id_intervenant
                FROM intervient_dans
                WHERE intervient_dans.id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_structure", $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT intervient_dans.id_intervenant');
            }

            // on supprime si l'intervenant n'intervient dans aucune structure
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Nombre de créneaux dont est chargé l'intervenant dans la structure actuelle
                $query = '
                    SELECT COUNT(creneaux_intervenant.id_intervenant) AS nb_intervenant
                    FROM intervenants
                             JOIN creneaux_intervenant on intervenants.id_intervenant = creneaux_intervenant.id_intervenant
                             JOIN creneaux ON creneaux_intervenant.id_creneau = creneaux.id_creneau
                    WHERE creneaux_intervenant.id_intervenant = :id_intervenant
                      AND creneaux.id_structure = :id_structure';
                $stmt_num = $this->pdo->prepare($query);
                $stmt_num->bindValue(":id_intervenant", $row['id_intervenant']);
                $stmt_num->bindValue(":id_structure", $id_structure);

                if (!$stmt_num->execute()) {
                    throw new Exception('Error SELECT COUNT(creneaux.id_intervenant)');
                }
                $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
                $nb_intervenant_suivi = $data['nb_intervenant'];

                // si l'intervenant n'a pas de créneaux en charge
                if ($nb_intervenant_suivi == 0) {
                    $query = '
                        DELETE FROM intervient_dans
                        WHERE id_structure = :id_structure
                          AND id_intervenant = :id_intervenant';
                    $stmt_del = $this->pdo->prepare($query);

                    $stmt_del->bindValue(':id_intervenant', $row['id_intervenant']);
                    $stmt_del->bindValue(':id_structure', $id_structure);

                    if (!$stmt_del->execute()) {
                        throw new Exception('Error DELETE intervient_dans');
                    }
                } else {
                    $intervenant_not_deleted[] = $row['id_intervenant'];
                }
            }

            $array_without_intervenant_not_deleted = array_diff($intervenants, $intervenant_not_deleted);
            foreach ($array_without_intervenant_not_deleted as $id_intervenant) {
                $query = '
                    INSERT INTO intervient_dans (id_intervenant,id_structure) 
                    VALUES (:id_intervenant,:id_structure)';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_intervenant', $id_intervenant);
                $stmt->bindValue(':id_structure', $id_structure);

                if (!$stmt->execute()) {
                    throw new Exception('Error insert intervient_dans');
                }
            }

            ////////////////////////////////////////////////////
            // Insertion des antennes
            ////////////////////////////////////////////////////
            $antennes_id = []; // la nouvelle liste des id_antenne de la structure
            foreach ($antennes as $value) {
                if (!empty($value['id_antenne'])) {
                    $antennes_id[] = $value['id_antenne'];
                }
            }

            $antennes_not_deleted = []; // les id des antennes qui n'ont pas été supprimé
            // recup de toutes les antennes de la structure
            $query = '
                SELECT id_antenne
                FROM antenne
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_structure", $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_antenne FROM antenne');
            }

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // recup nombre de patients qui sont affectés a cette antenne
                $query = '
                    SELECT COUNT(id_patient) AS nb_patient
                    FROM patients
                    WHERE patients.id_antenne = :id_antenne';
                $stmt_num = $this->pdo->prepare($query);
                $stmt_num->bindValue(':id_antenne', $row['id_antenne']);
                $stmt_num->execute();

                if (!$stmt_num->execute()) {
                    throw new Exception('SELECT COUNT(id_patient)');
                }
                $data = $stmt_num->fetch(PDO::FETCH_ASSOC);
                $nb_patient = $data['nb_patient'];

                // on supprime l'antenne si aucun patient n'y est affecté
                // et si elle n'est plus dans la liste des id d'antennes
                // et s'il restera au moins une antenne dans la structure
                if ($nb_patient == 0 &&
                    !in_array($row['id_antenne'], $antennes_id) &&
                    (count($antennes)) > 0) {
                    $query = '
                        DELETE FROM antenne
                        WHERE id_antenne = :id_antenne';
                    $stmt_del = $this->pdo->prepare($query);

                    $stmt_del->bindValue(':id_antenne', $row['id_antenne']);

                    if (!$stmt_del->execute()) {
                        throw new Exception('Error DELETE intervient_dans');
                    }
                } else {
                    $antennes_not_deleted[] = $row['id_antenne'];
                }
            }

            foreach ($antennes as $value) {
                if (empty($value['id_antenne'])) {
                    $query = '
                        INSERT INTO antenne (id_structure, nom_antenne) 
                        VALUES (:id_structure, :nom_antenne)';
                    $stmt = $this->pdo->prepare($query);

                    $stmt->bindValue(':nom_antenne', $value['nom_antenne']);
                    $stmt->bindValue(':id_structure', $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO antenne');
                    }
                } else {
                    // on UPDATE le nom de l'antenne si elle n'a pas été supprimé
                    if (in_array($value['id_antenne'], $antennes_not_deleted)) {
                        $query = '
                            UPDATE antenne
                            SET nom_antenne = :nom_antenne
                            WHERE id_antenne = :id_antenne';
                        $stmt = $this->pdo->prepare($query);

                        $stmt->bindValue(':nom_antenne', $value['nom_antenne']);
                        $stmt->bindValue(':id_antenne', $value['id_antenne']);

                        if (!$stmt->execute()) {
                            throw new Exception('Error UPDATE antenne');
                        }
                    }
                }
            }

            ////////////////////////////////////////////////////
            // modif des coordonnées du responsable
            ////////////////////////////////////////////////////
            $query = '
                SELECT coordonnees.id_coordonnees
                FROM coordonnees
                         JOIN structure on coordonnees.id_coordonnees = structure.id_coordonnees
                WHERE structure.id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT coordonnees.id_coordonnees');
            }
            if ($stmt->rowCount() == 0) {
                // on insert un nouveau responsable
                $query = '
                    INSERT INTO coordonnees (nom_coordonnees, prenom_coordonnees, tel_fixe_coordonnees,
                                             tel_portable_coordonnees, mail_coordonnees)
                    VALUES (:nom_coordonnees, :prenom_coordonnees, :tel_fixe_coordonnees, :tel_portable_coordonnees,
                            :mail_coordonnees)';
                $stmt_coord = $this->pdo->prepare($query);

                $stmt_coord->bindValue(':nom_coordonnees', $nom_representant);
                $stmt_coord->bindValue(':prenom_coordonnees', $prenom_representant);
                $stmt_coord->bindValue(':tel_fixe_coordonnees', $tel_fixe);
                $stmt_coord->bindValue(':tel_portable_coordonnees', $tel_portable);
                $stmt_coord->bindValue(':mail_coordonnees', $email);

                if (!$stmt_coord->execute()) {
                    throw new Exception('Error insert coordonnees');
                }
                $id_coordonnees = $this->pdo->lastInsertId();

                // Update dans structure des coordonnées du responsable de la structure
                $query = '
                    UPDATE structure 
                    SET id_coordonnees = :id_coordonnees
                    WHERE id_structure = :id_structure ';
                $stmt_coord = $this->pdo->prepare($query);

                $stmt_coord->bindValue(':id_coordonnees', $id_coordonnees);
                $stmt_coord->bindValue(':id_structure', $id_structure);

                if (!$stmt_coord->execute()) {
                    throw new Exception('Error UPDATE structure');
                }
            } elseif ($stmt->rowCount() == 1) {
                // on update le responsable existant
                $data = $stmt->fetch();
                $id_coordonnees = $data['id_coordonnees'];

                $query = '
                    UPDATE coordonnees
                    SET nom_coordonnees          = :nom_coordonnees,
                        prenom_coordonnees       = :prenom_coordonnees,
                        tel_fixe_coordonnees     = :tel_fixe_coordonnees,
                        tel_portable_coordonnees = :tel_portable_coordonnees,
                        mail_coordonnees         = :mail_coordonnees
                    WHERE id_coordonnees = :id_coordonnees';
                $stmt_coord = $this->pdo->prepare($query);

                $stmt_coord->bindValue(':id_coordonnees', $id_coordonnees);
                $stmt_coord->bindValue(':nom_coordonnees', $nom_representant);
                $stmt_coord->bindValue(':prenom_coordonnees', $prenom_representant);
                $stmt_coord->bindValue(':tel_fixe_coordonnees', $tel_fixe);
                $stmt_coord->bindValue(':tel_portable_coordonnees', $tel_portable);
                $stmt_coord->bindValue(':mail_coordonnees', $email);

                if (!$stmt_coord->execute()) {
                    throw new Exception('Error UPDATE coordonnees');
                }
            } else {
                throw new Exception('Error Plus d\'une coordonnée trouvé');
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
     * @param $parameters
     * @return bool
     */
    public function saveLogo($parameters): bool
    {
        $is_logo_est_present_boolean = gettype($parameters['logo_est_present']) == "boolean";
        $is_logo_data_present_if_required =
            ($is_logo_est_present_boolean && $parameters['logo_est_present'] && !empty($parameters['logo_data'])) ||
            ($is_logo_est_present_boolean && !$parameters['logo_est_present']);

        if (empty($parameters['id_statut_structure']) ||
            empty($parameters['id_structure']) ||
            !$is_logo_est_present_boolean ||
            !$is_logo_data_present_if_required) {
            return false;
        }

        $logo_path = FilesManager::rootDirectory() . '/uploads/logo_mss/';

        try {
            $this->pdo->beginTransaction();

            $est_mss = $parameters['id_statut_structure'] == "1";
            if ($est_mss && $parameters['logo_est_present']) {
                // enregistrement de l'image
                $filename = "mss_" . $parameters['id_structure'] . '_' . uniqid();
                $saved_filename = FilesManager::save_image_from_base64($parameters['logo_data'], $logo_path, $filename);
                if (empty($saved_filename)) {
                    throw new Exception('Error saving logo file');
                }

                // vérifier s'il y a un logo précédent
                $query = '
                    SELECT logo_fichier
                    FROM structure
                    WHERE id_structure = :id_structure
                    LIMIT 1';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_structure', $parameters['id_structure']);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT logo_fichier');
                }

                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($data) {
                    $previous_logo_filename = $data['logo_fichier'] ?? null;
                }

                // Suppression du logo precedent si nécessaire
                if (!empty($previous_logo_filename)) {
                    FilesManager::delete_file($logo_path . $previous_logo_filename);
                }

                // update du nom du fichier
                $query = '
                    UPDATE structure
                    SET logo_fichier = :logo_fichier
                    WHERE id_structure = :id_structure';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':logo_fichier', $saved_filename);
                $stmt->bindValue(':id_structure', $parameters['id_structure']);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE structure SET logo_fichier');
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
     * @param $id_structure
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        $query = '
            SELECT id_structure,
                   nom_structure,
                   code_onaps,
                   id_statut_structure,
                   nom_statut_structure,
                   nom_adresse,
                   complement_adresse,
                   code_postal,
                   nom_ville,
                   id_territoire,
                   nom_territoire,
                   id_statut_juridique,
                   nom_coordonnees          as nom_representant,
                   prenom_coordonnees       as prenom_representant,
                   tel_fixe_coordonnees     as tel_fixe,
                   tel_portable_coordonnees as tel_portable,
                   mail_coordonnees         as email,
                   lien_ref_structure,
                   logo_fichier
            FROM structure
                     JOIN statuts_structure USING (id_statut_structure)
                     JOIN se_situe_a USING (id_structure)
                     JOIN adresse USING (id_adresse)
                     JOIN se_localise_a USING (id_adresse)
                     JOIN villes USING (id_ville)
                     JOIN territoire USING (id_territoire)
                     LEFT JOIN coordonnees USING (id_coordonnees)
            WHERE structure.id_structure = :id_structure';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->execute();

        $structure = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($structure)) {
            // recup des intervenants de la structure
            $i = new Intervenant($this->pdo);
            $intervenants = $i->readAllStructure($id_structure);
            $structure['intervenants'] = is_array($intervenants) ? $intervenants : [];

            // recup des créneaux de la structure
            $c = new Creneau($this->pdo);
            $creneaux = $c->readAllStructure($id_structure);
            $structure['creneaux'] = is_array($creneaux) ? $creneaux : [];

            // récup des antennes de la structure
            $a = new Antenne($this->pdo);
            $antennes = $a->readAllStructure($id_structure);
            $structure['antennes'] = is_array($antennes) ? $antennes : [];
        }
        return $structure;
    }

    /**
     * Récupère les structures pour un utilisateur donné, le territoire peut être spécifié s'il est différent de
     * $session['id_territoire']
     *
     * session parameters:
     * [
     *     'id_role_user' => string,
     *     'est_coordinateur_peps' => bool,
     *     'id_statut_structure' => string|null,
     *     'id_territoire' => string,
     * ]
     *
     * @param             $session
     * @param string|null $id_territoire paramètre optionnel
     * @return array|false Return an associative array or false on failure
     */
    public function readAll($session, $id_territoire_filter = null)
    {
        if (empty($session['role_user_ids']) ||
            !isset($session['est_coordinateur_peps']) ||
            empty($session['id_territoire'])) {
            return false;
        }

        try {
            $permission = new Permissions($session);
        } catch (Exception $e) {
            return false;
        }

        $id_territoire = $session['id_territoire'];

        $query = '
            SELECT structure.id_structure,
                   structure.nom_structure,
                   structure.id_statut_structure,
                   structure.id_territoire,
                   structure.lien_ref_structure,
                   statuts_structure.nom_statut_structure,
                   adresse.nom_adresse,
                   adresse.complement_adresse,
                   villes.code_postal,
                   villes.nom_ville,
                   territoire.nom_territoire
            FROM structure
                     JOIN statuts_structure USING (id_statut_structure)
                     JOIN se_situe_a USING (id_structure)
                     JOIN adresse USING (id_adresse)
                     JOIN se_localise_a USING (id_adresse)
                     JOIN villes USING (id_ville)
                     JOIN territoire USING (id_territoire)
            WHERE 1=1 ';

        if (!empty($id_territoire_filter) || !$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND id_territoire = :id_territoire ';
        }
        // seul le SUPER_ADMIN peut voir les structures "Partenaires" (id_statut_structure=5)
        if (!$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $query .= ' AND id_statut_structure <> 5 ';
        }
        $query .= ' ORDER BY nom_structure ';

        $stmt = $this->pdo->prepare($query);
        if (!empty($id_territoire_filter) || !$permission->hasRole(Permissions::SUPER_ADMIN)) {
            $stmt->bindValue(':id_territoire', $id_territoire_filter ?? $id_territoire);
        }
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Return un array contenant tous les id_user des coordinateurs MSS ou de structure sportive
     * de la structure donnée
     *
     * @param $id_structure
     * @return false|array Return an array of ids or false on failure
     */
    public function getCoordinateurMssOuStructureSportive($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        $query = '
            SELECT DISTINCT c.id_user
            FROM structure
                     JOIN users u on structure.id_structure = u.id_structure
                     JOIN a_role ar on u.id_user = ar.id_user
                     JOIN coordonnees c on u.id_user = c.id_user
            WHERE structure.id_structure = :id_structure
              AND ar.id_role_user = 2
              AND u.est_coordinateur_peps != 1';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fuses two structures
     *
     * @param $id_structure_from
     * @param $id_structure_target
     * @return bool if the fusion was successful
     */
    public function fuse($id_structure_from, $id_structure_target)
    {
        if (empty($id_structure_from) || empty($id_structure_target)) {
            return false;
        }

        // On vérifie que $id_structure_from existe
        $query = '
            SELECT id_structure
            FROM structure
            WHERE id_structure = :id_structure';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_structure', $id_structure_from);
        $statement->execute();
        if ($statement->rowCount() === 0) {
            return false;
        }

        // On vérifie que $id_structure_target existe
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_structure', $id_structure_target);
        $statement->execute();
        if ($statement->rowCount() === 0) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            ///////////////////////////////////////
            /// Changement de l'antenne
            ///////////////////////////////////////
            $query = '
                UPDATE antenne
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE antenne');
            }

            ///////////////////////////////////////
            /// Changement des creneaux
            ///////////////////////////////////////
            $query = '
                UPDATE creneaux
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE creneaux');
            }

            ///////////////////////////////////////
            /// Changement des intervenants
            ///////////////////////////////////////
            $query = '
                UPDATE intervenants
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE intervenants');
            }

            ///////////////////////////////////////
            /// Changement des oriente_vers
            ///////////////////////////////////////
            $query = '
                UPDATE oriente_vers
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE oriente_vers');
            }

            ///////////////////////////////////////
            /// Changement des users
            ///////////////////////////////////////
            $query = '
                UPDATE users
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE users');
            }

            ///////////////////////////////////////
            /// Vérification si on ne perd pas de données sensible pendant la fusion
            ///////////////////////////////////////
            $query = '
                SELECT id_api, code_onaps
                FROM structure
                WHERE id_structure = :id_structure';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure', $id_structure_target);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT structure (target)');
            }
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $id_api_target = $row['id_api'];
            $onaps_target = $row['code_onaps'];

            $statement2 = $this->pdo->prepare($query);
            $statement2->bindValue(':id_structure', $id_structure_from);
            if (!$statement2->execute()) {
                throw new Exception('Error SELECT structure (from)');
            }
            $res = $statement2->fetch(PDO::FETCH_ASSOC);
            $id_api_from = $res['id_api'];
            $onaps_from = $res['code_onaps'];

            if ($id_api_from != null && $id_api_target === null) {
                ///////////////////////////////////////
                /// On garde l'id_api non NULL
                ///////////////////////////////////////
                $query = '
                    UPDATE structure
                    SET structure.id_api = :id_api
                    WHERE id_structure = :id_structure';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_structure', $id_structure_target);
                $statement->bindValue(':id_api', $res['id_api']);
                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE structure');
                }
            }

            if ($onaps_from != null && $onaps_target === null) {
                ///////////////////////////////////////
                /// On garde le code_onaps non NULL
                ///////////////////////////////////////
                $query = '
                    UPDATE structure
                    SET structure.code_onaps = :code_onaps
                    WHERE id_structure = :id_structure';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_structure', $id_structure_target);
                $statement->bindValue(':code_onaps', $res['code_onaps']);
                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE structure');
                }
            }

            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            /////////////////////////////
            // intervient_dans (pour cette table la primary key est (id_intervenant, id_structure)
            //////////////////////////////
            // intervient_dans de id_structure_target
            $query = '
                SELECT id_intervenant
                FROM intervient_dans
                WHERE id_structure = :id_structure_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM intervient_dans (target)');
            }
            $intervenant_ids_target = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // intervient_dans de id_structure_from
            $query = '
                SELECT id_intervenant
                FROM intervient_dans
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM intervient_dans (from)');
            }
            $intervenant_ids_from = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($intervenant_ids_from as $id_intervenant) {
                if (in_array($id_intervenant, $intervenant_ids_target)) {
                    // suppression si déja présent dans target
                    $query = '
                        DELETE FROM intervient_dans
                        WHERE id_intervenant = :id_intervenant
                          AND id_structure = :id_structure_from';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_structure_from', $id_structure_from);
                    $statement->bindValue(':id_intervenant', $id_intervenant);
                    if (!$statement->execute()) {
                        throw new Exception('Error DELETE FROM intervient_dans');
                    }
                }
            }

            $query = '
                UPDATE intervient_dans
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE intervient_dans');
            }

            /////////////////////////////
            // intervention
            //////////////////////////////
            // intervention de id_structure_target
            $query = '
                SELECT id_user
                FROM intervention
                WHERE id_structure = :id_structure_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM intervention (target)');
            }
            $user_ids_target = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // intervention de id_structure_from
            $query = '
                SELECT id_user
                FROM intervention
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM intervention (from)');
            }
            $user_ids_from = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($user_ids_from as $id_user) {
                if (in_array($id_user, $user_ids_target)) {
                    // suppression si déja présent dans target
                    $query = '
                        DELETE FROM intervention
                        WHERE id_user = :id_user
                          AND id_structure = :id_structure_from';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_structure_from', $id_structure_from);
                    $statement->bindValue(':id_user', $id_user);

                    if (!$statement->execute()) {
                        throw new Exception('Error DELETE FROM intervention');
                    }
                }
            }

            $query = '
                UPDATE intervention
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE intervention');
            }

            ///////////////////////////////////////
            /// Changement des settings_synthese
            ///////////////////////////////////////
            // verification si la structure_from a des synthèses
            $query = '
                SELECT COUNT(*) as synthese_count
                FROM settings_synthese
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT count FROM settings_synthese (from)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $synthese_count_from = intval($data['synthese_count']);

            // verification si la structure_target a des synthèses
            $query = '
                SELECT COUNT(*) as synthese_count
                FROM settings_synthese
                WHERE id_structure = :id_structure_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT count FROM settings_synthese (target)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $synthese_count_target = intval($data['synthese_count']);

            if ($synthese_count_from > 0 && $synthese_count_target > 0) {
                // suppression si déja présent dans target
                $query = '
                    DELETE FROM settings_synthese
                    WHERE id_structure = :id_structure_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_structure_from', $id_structure_from);
                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM settings_synthese');
                }
            }

            $query = '
                UPDATE settings_synthese
                SET id_structure = :id_structure_target
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_target', $id_structure_target);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE settings_synthese');
            }

            ///////////////////////////////////////
            /// Delete de se_situe_a
            ///////////////////////////////////////
            $query = '
                DELETE 
                FROM se_situe_a
                WHERE id_structure = :id_structure_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure_from', $id_structure_from);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE se_situe_a');
            }

            ////////////////////////////////////
            /// DELETE de la structure
            ////////////////////////////////////
            $query = '
                DELETE 
                FROM structure
                WHERE id_structure = :id_structure';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_structure', $id_structure_from);

            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }

            if (!$statement->execute()) {
                throw new Exception('Error DELETE structure');
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
     * @param $id_structure
     * @return bool if the deletion was successful
     */
    public function delete($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            ////////////////////////////////////////////////////
            // verification si la structure à des intervenants
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_intervenants
                FROM intervient_dans
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_intervenants');
            }

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_intervenants']) > 0) {
                throw new Exception('Il y a des intervenants qui font partie de cette structure');
            }

            ////////////////////////////////////////////////////
            // verification si la structure à des créneaux
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_creneaux
                FROM creneaux
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_creneaux');
            }

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_creneaux']) > 0) {
                throw new Exception('Il y a des créneaux dans cette structure');
            }

            ////////////////////////////////////////////////////
            // verification si des patients qui ont été orientés vers cette structure
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_oriente_vers
                FROM oriente_vers
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_oriente_vers');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_oriente_vers']) > 0) {
                throw new Exception('Il y a des patients qui ont été orientés vers cette structure');
            }

            ////////////////////////////////////////////////////
            // verification s'il y a des patient qui sont lié a une antenne de la structure
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_patients
                FROM patients
                JOIN antenne a on patients.id_antenne = a.id_antenne
                WHERE a.id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_patients');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_patients']) > 0) {
                throw new Exception('Il y a des patients qui sont rattaché à une des antennes de cette structure');
            }

            ////////////////////////////////////////////////////
            // verification s'il y a des utilisateurs qui font parti de la structure
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_users
                FROM users
                WHERE users.id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT count(*) as nb_users');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (intval($data['nb_users']) > 0) {
                throw new Exception('Il y a des utilisateurs dans cette structure');
            }

            ////////////////////////////////////////////////////
            // Récupération id_coordonnees du représentant si existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_coordonnees
                FROM structure
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_coordonnees');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_coordonnees = $data['id_coordonnees'] ?? null;

            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // DELETE FROM coordonnees si existe
            ////////////////////////////////////////////////////
            if (!empty($id_coordonnees)) {
                $query = '
                    DELETE
                    FROM coordonnees
                    WHERE id_coordonnees = :id_coordonnees';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_coordonnees', $id_coordonnees);

                if (!$stmt->execute()) {
                    throw new Exception('Error DELETE FROM coordonnees');
                }
            }
            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id_adresse
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_adresse 
                FROM se_situe_a
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error select id_adresse');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_adresse = $data['id_adresse'] ?? null;
            if (empty($id_adresse)) {
                throw new Exception(
                    'Error: L\'id_adresse de la structure \'' . $id_structure . '\'  n\'a pas été trouvé dans la BDD'
                );
            }

            ////////////////////////////////////////////////////
            // DELETE intervention
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM intervention
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM intervention');
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
            // DELETE se_situe_a
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM se_situe_a
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM se_situe_a');
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
            // DELETE antennes
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM antenne
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM antenne');
            }

            ////////////////////////////////////////////////////
            // DELETE intervient_dans
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM intervient_dans
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM intervient_dans');
            }

            ////////////////////////////////////////////////////
            // DELETE settings_synthese
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM settings_synthese
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM settings_synthese');
            }

            ////////////////////////////////////////////////////
            // DELETE structure
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM structure
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_structure', $id_structure);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM structure');
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
     * Return un array contenant tous les id_user des responsables structures
     * de la structure donnée
     *
     * @param $id_structure
     * @return false|array Return an array of ids or false on failure
     */
    public function getResponsableStructure($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        $query = '
            SELECT DISTINCT c.id_user
            FROM structure
                     JOIN users u on structure.id_structure = u.id_structure
                     JOIN a_role ar on u.id_user = ar.id_user
                     JOIN coordonnees c on u.id_user = c.id_user
            WHERE ar.id_role_user = 6
                AND structure.id_structure = :id_structure';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}