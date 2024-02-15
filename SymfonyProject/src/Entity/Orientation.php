<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class Orientation
{
    private PDO $pdo;
    private string $errorMessage = '';

    const DEFAULT_MAP_URL = 'https://referencement.peps-na.fr/';

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

    public function readAllActivitesChoisies($id_patient)
    {
        if (empty($id_patient)) {
            $this->errorMessage = "Missing required parameters";
            return false;
        }

        // recupération de l'orientation du patient
        $query = '
            SELECT id_orientation
            FROM orientation
            WHERE id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);

        if (!$stmt->execute()) {
            $this->errorMessage = "Error SELECT FROM orientation";
            return false;
        }
        if ($stmt->rowCount() == 0) {
            return [];
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_orientation = $data['id_orientation'];

        // recuperation de toutes les activites du patient
        $query = '
            SELECT id_activite_choisie,
                   statut,
                   commentaire,
                   date_demarrage,
                   id_orientation,
                   activite_choisie.id_creneau,
                   s.id_structure,
                   s.id_territoire
            FROM activite_choisie
            JOIN creneaux c on activite_choisie.id_creneau = c.id_creneau
            JOIN structure s on c.id_structure = s.id_structure
            WHERE id_orientation = :id_orientation';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_orientation', $id_orientation);

        if (!$stmt->execute()) {
            $this->errorMessage = "Error SELECT FROM activite_choisie";
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateActivitesChoisies($parameters)
    {
        if (empty($parameters['id_patient'])) {
            $this->errorMessage = "Missing required parameters";
            return false;
        }

        if (!is_array($parameters['activites'])) {
            $this->errorMessage = "Invalid parameters";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $date = date("Y-m-d");

            ////////////////////////////////////////////////////
            // Récup id_orientation
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_orientation
                FROM orientation
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $parameters['id_patient']);

            if (!$stmt->execute()) {
                throw new Exception('Error FROM orientation');
            }
            $row_count = $stmt->rowCount();

            if ($row_count == 0) {
                // on insert l'orientation du patient
                $query = '
                    INSERT INTO orientation (id_patient, date_orientation)
                    VALUES (:id_patient, :date_orientation)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_patient', $parameters['id_patient']);
                $stmt->bindValue(':date_orientation', $date);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO orientation');
                }
                $id_orientation = $this->pdo->lastInsertId();
            } else {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_orientation = $data['id_orientation'];

                // on update la date d'orientation du patient
                $query = '
                    UPDATE orientation
                    SET date_orientation = :date_orientation
                    WHERE id_orientation = :id_orientation';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':date_orientation', $date);
                $stmt->bindValue(':id_orientation', $id_orientation);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE orientation');
                }
            }

            //on delete toutes anciennes activités du patient
            $query = '
                DELETE
                FROM activite_choisie
                WHERE id_orientation = :id_orientation';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_orientation', $id_orientation);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM activite_choisie');
            }

            //on supprime le bénéficiaire des listes de participants sur lequel il ne doit plus être
            $query = '
                SELECT id_creneau
                FROM liste_participants_creneau
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $parameters['id_patient']);

            if (!$stmt->execute()) {
                return false;
            }

            $acts = [];
            foreach ($parameters['activites'] as $act) {
                $acts[] = $act['id_creneau'];
            }

            $liste_supprimer = [];
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
                if (!in_array(strval($id), $acts)) {
                    $liste_supprimer[] = $id;
                }
            }

            foreach ($liste_supprimer as $ls) {
                $query =
                    'DELETE
                    FROM liste_participants_creneau
                    WHERE id_patient = :id_patient
                    AND id_creneau = :id_creneau';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_patient', $parameters['id_patient']);
                $stmt->bindValue('id_creneau', $ls);

                if (!$stmt->execute()) {
                    throw new Exception('Error DELETE FROM liste_participants_creneau');
                }
            }

            foreach ($parameters['activites'] as $value) {
                // insertion dans activite_choisie
                $query = '
                    INSERT INTO activite_choisie (statut, commentaire, date_demarrage, id_orientation, id_creneau)
                    VALUES (:statut, :commentaire, :date_demarrage, :id_orientation, :id_creneau)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':statut', $value['statut']);
                $stmt->bindValue(':commentaire', $value['commentaire']);
                $stmt->bindValue(':id_orientation', $id_orientation);
                $stmt->bindValue(':id_creneau', $value['id_creneau']);
                if (empty($value['date_demarrage'])) {
                    $stmt->bindValue(':date_demarrage', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':date_demarrage', $value['date_demarrage']);
                }

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO activite_choisie' . json_encode($value));
                }

                ////////////////////////////////////////////////////
                // Verification que la liste n'a pas déja été ajouté
                ////////////////////////////////////////////////////
                $query = '
                    SELECT id_liste_participants_creneau
                    FROM liste_participants_creneau
                    WHERE id_creneau = :id_creneau
                      AND id_patient = :id_patient';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_creneau', $value['id_creneau']);
                $stmt->bindValue(':id_patient', $parameters['id_patient']);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT COUNT(id_liste_participants_creneau)');
                }

                $is_already_added = $stmt->rowCount() > 0;

                if (!$is_already_added) {
                    ////////////////////////////////////////////////////
                    // Insertion dans liste_participants_creneau
                    ////////////////////////////////////////////////////
                    $query = '
                        INSERT INTO liste_participants_creneau (id_patient, id_creneau, status_participant, propose_inscrit,
                                                                abandon, reorientation)
                        VALUES (:id_patient, :id_creneau, :status_participant, :propose_inscrit, :abandon,
                                :reorientation)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_patient", $parameters['id_patient']);
                    $stmt->bindValue(":id_creneau", $value['id_creneau']);
                    $stmt->bindValue(":status_participant", 'PEPS');
                    $stmt->bindValue(':propose_inscrit', '0');
                    $stmt->bindValue(':abandon', '0');
                    $stmt->bindValue(':reorientation', '0');

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO liste_participants_creneau');
                    }
                }

                ////////////////////////////////////////////////////
                // MAJ du type de parcours du patient
                ////////////////////////////////////////////////////
                $query = '
                    SELECT id_type_parcours
                    FROM creneaux
                    WHERE id_creneau = :id_creneau';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_creneau', $value['id_creneau']);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT FROM creneaux');
                }

                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_type_parcours = $data['id_type_parcours'];

                if (!empty($id_type_parcours)) {
                    // update du parcours (Elan, passerelle, ...) du patient
                    $query = '
                        UPDATE orientation
                        SET id_type_parcours = :id_type_parcours
                        WHERE id_patient = :id_patient';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_patient', $parameters['id_patient']);
                    $stmt->bindValue(':id_type_parcours', $id_type_parcours);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO orientation');
                    }
                }

                ////////////////////////////////////////////////////
                // Récupération de la structure où a lieu l'activité
                ////////////////////////////////////////////////////
                $query = '
                    SELECT id_structure
                    FROM creneaux
                    WHERE id_creneau = :id_creneau';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_creneau', $value['id_creneau']);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT COUNT(id_liste_participants_creneau)');
                }

                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_structure = $data['id_structure'];

                ////////////////////////////////////////////////////
                // Verification si le patient est déjà orienté vers la structure de l'activité
                ////////////////////////////////////////////////////
                $query = '
                    SELECT id_patient, id_structure
                    FROM oriente_vers
                    WHERE id_structure = :id_structure
                      AND id_patient = :id_patient';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_structure', $id_structure);
                $stmt->bindValue(':id_patient', $parameters['id_patient']);

                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT FROM oriente_vers');
                }

                $is_already_added = $stmt->rowCount() > 0;

                if (!$is_already_added) {
                    ////////////////////////////////////////////////////
                    // Insertion dans oriente_vers
                    ////////////////////////////////////////////////////
                    $query = '
                        INSERT INTO oriente_vers
                            (id_patient, id_structure)
                        VALUES (:id_patient, :id_structure)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_patient", $parameters['id_patient']);
                    $stmt->bindValue(":id_structure", $id_structure);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO oriente_vers');
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

    public function getMapUrl($id_patient)
    {
        if (empty($id_patient)) {
            return self::DEFAULT_MAP_URL;
        }

        $query = '
            SELECT lien_ref_structure
            FROM patients
            JOIN antenne a on patients.id_antenne = a.id_antenne  
            JOIN structure s on a.id_structure = s.id_structure
            WHERE id_patient = :id_patient';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id_patient", $id_patient);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $map_url = $data['lien_ref_structure'];

        if (empty($map_url)) {
            $query = '
                SELECT lien_ref_territoire
                FROM patients
                JOIN territoire t on patients.id_territoire = t.id_territoire
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_patient", $id_patient);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $map_url = $data['lien_ref_territoire'];
        }

        return !empty($map_url) ? $map_url : self::DEFAULT_MAP_URL;
    }
}