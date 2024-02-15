<?php

namespace Sportsante86\Sapa\Model;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
 ;
use Sportsante86\Sapa\Outils\EncryptionManager;

class Seance
{
    private PDO $pdo;
    private string $errorMessage = '';

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
     * Creates a seance
     *
     * required parameters :
     * [
     *     'id_creneau' => string,
     *     'id_user' => string,
     *     'date' => string,
     * ]
     *
     * @param $parameters
     * @return false|string the id of the seance or false on failure
     */
    public function create($parameters)
    {
        if (empty($parameters['id_creneau']) ||
            empty($parameters['id_user']) ||
            empty($parameters['date'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $parameters['commentaire'] = $parameters['commentaire'] ?? "";

            // recuperation des heures du creneaux
            $query = '
                SELECT commence_a.id_heure as heure_debut,
                       se_termine_a.id_heure as heure_fin
                FROM creneaux
                         JOIN commence_a USING (id_creneau)
                         JOIN se_termine_a on creneaux.id_creneau = se_termine_a.id_creneau
                WHERE creneaux.id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $parameters['id_creneau']);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT creneau');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // insertion de la séance
            $id_seance = $this->insertSeance(array_merge($parameters, $data));

            $this->pdo->commit();
            return $id_seance;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e;
            return false;
        }
    }

    /**
     * Creates seances between two dates
     *
     * required parameters :
     * [
     *     'id_creneau' => string,
     *     'id_user' => string,
     *     'date_start' => string,
     *     'date_end' => string,
     * ]
     *
     * @param $parameters
     * @return false|array an array containing id of the created seances or false on failure
     */
    public function createBetweenTwoDates($parameters)
    {
        if (empty($parameters['id_creneau']) ||
            empty($parameters['id_user']) ||
            empty($parameters['date_start']) ||
            empty($parameters['date_end'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $parameters['commentaire'] = $parameters['commentaire'] ?? "";

            // recuperation des heures et id_jour du creneau
            $query = '
                SELECT commence_a.id_heure   as heure_debut,
                       se_termine_a.id_heure as heure_fin,
                       creneaux.id_jour
                FROM creneaux
                         JOIN commence_a USING (id_creneau)
                         JOIN se_termine_a on creneaux.id_creneau = se_termine_a.id_creneau
                WHERE creneaux.id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $parameters['id_creneau']);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT creneau');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return false;
            }

            $valid_begin_date = $this->getFirstValidDate($parameters['date_start'], $parameters['id_creneau']);
            $begin = new DateTime($valid_begin_date);
            $end = new DateTime($parameters['date_end']);
            $end = $end->modify('+1 day');

            $duration = $data['id_jour'] != "8" ? 'P1W' : 'P1D';
            $interval = new DateInterval($duration);
            $daterange = new DatePeriod($begin, $interval, $end);

            $ids = [];

            foreach ($daterange as $date) {
                $id_seance = $this->insertSeance(array_merge($parameters, $data, ['date' => $date->format('Y-m-d')]));
                $ids[] = $id_seance;
            }

            $this->pdo->commit();
            return $ids;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Creates seances between two dates
     * (from 1 week after the date of the seance to the end date, with 1 week interval)
     *
     * required parameters :
     * [
     *     'id_creneau' => string,
     *     'date_end' => string,
     * ]
     *
     * @param $parameters
     * @return false|array an array containing id of the created seances or false on failure
     */
    public function duplicateSeance($parameters)
    {
        if (empty($parameters['id_seance']) ||
            empty($parameters['date_end'])
        ) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // recuperation des données de la seance
            $query = '
                SELECT seance.id_creneau,
                       seance.id_user,
                       seance.heure_debut,
                       seance.heure_fin,
                       seance.commentaire_seance as commentaire,
                       seance.date_seance as date,
                       c.id_jour
                FROM seance
                JOIN creneaux c on seance.id_creneau = c.id_creneau
                WHERE seance.id_seance = :id_seance';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_seance', $parameters['id_seance']);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_seance');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            $begin = new DateTime($data['date']);
            $modifier = $data['id_jour'] != "8" ? '+7 day' : '+1 day';
            $begin = $begin->modify($modifier);

            $end = new DateTime($parameters['date_end']);
            $end = $end->modify('+1 day');

            $duration = $data['id_jour'] != "8" ? 'P1W' : 'P1D';
            $interval = new DateInterval($duration);
            $daterange = new DatePeriod($begin, $interval, $end);

            $ids = [];

            foreach ($daterange as $date) {
                $id_seance = $this->insertSeance(array_merge($parameters, $data, ['date' => $date->format('Y-m-d')]));
                $ids[] = $id_seance;
            }

            $this->pdo->commit();
            return $ids;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * required parameters :
     * [
     *     'id_seance' => string,
     *     'motif_annulation' => string,
     * ]
     *
     * @param $parameters
     * @return bool if the seance was successfully canceled
     */
    public function cancelSeance($parameters)
    {
        if (empty($parameters['motif_annulation']) ||
            empty($parameters['id_seance'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                UPDATE seance
                SET annulation_seance   = 1,
                    id_motif_annulation = :motif_annulation
                WHERE id_seance = :id_seance';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':motif_annulation', $parameters['motif_annulation']);
            $stmt->bindValue(':id_seance', $parameters['id_seance']);
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE seance');
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
     * @param $id_seance
     * @return bool if the seance was successfully validated
     */
    public function validateSeance($id_seance)
    {
        if (empty($id_seance)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                UPDATE seance
                SET validation_seance = 1
                WHERE id_seance = :id_seance';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_seance', $id_seance);
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE seance');
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
     * Update les participants (ajoute ou supprime patient)
     *
     *  required parameters :
     * [
     *      'id_seance' => string,
     *      'participants' => array, un array qui contient les ids des patients
     * ]
     *
     * @param $parameters array
     * @return bool if the participants are successfully updated
     */
    public function updateParticipantsSeance($parameters): bool
    {
        if (!is_array($parameters['participants']) ||
            empty($parameters['id_seance'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // la liste des participants actuels
            $query = '
                    SELECT id_patient 
                    FROM a_participe_a 
                    WHERE id_seance = :id_seance';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_seance', $parameters['id_seance']);
            $stmt->execute();
            $id_patient_start = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $ids_to_delete = array_diff($id_patient_start, $parameters['participants']);

            foreach ($parameters['participants'] as $id_patient) {
                /////////////////////////////////////////////
                /// verification si le patient est déjà présent
                /////////////////////////////////////////////
                $query = '
                    SELECT count(*) as patient_count
                    FROM a_participe_a 
                    WHERE id_patient = :id_patient AND id_seance = :id_seance';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_patient', $id_patient);
                $stmt->bindValue(':id_seance', $parameters['id_seance']);
                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT count(*) as patient_count');
                }
                $patient_count = $stmt->fetchColumn();

                if ($patient_count == 0) {
                    /////////////////////////////////////////////
                    /// Ajout des patients dans a_participe_a
                    /////////////////////////////////////////////
                    $query = '
                        INSERT INTO a_participe_a (id_patient, id_seance)
                        VALUES(:id_patient, :id_seance)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_seance', $parameters['id_seance']);
                    $stmt->bindValue(':id_patient', $id_patient);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO a_participe_a');
                    }
                }
            }

            foreach ($ids_to_delete as $id_patient) {
                /////////////////////////////////////////////
                /// Suppression des patients dans a_participe_a
                /////////////////////////////////////////////
                $query = '
                        DELETE FROM a_participe_a
                        WHERE id_patient = :id_patient AND id_seance = :id_seance';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_seance', $parameters['id_seance']);
                $stmt->bindValue(':id_patient', $id_patient);

                if (!$stmt->execute()) {
                    throw new Exception('Error DELETE FROM a_participe_a');
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
     *
     * required parameters :
     * [
     *     'id_seance' => string,
     *     'emargements' => array,
     * ]
     *
     * Exemple of the array 'emargements':
     * [
     *     [
     *         'id_patient' => string,
     *         'present' => string,
     *         'commentaire' => string,
     *     ]
     * ]
     *
     * @param $parameters array
     * @return bool if the seance was successfully "emarged"
     */
    public function emargeSeance($parameters)
    {
        if (!is_array($parameters['emargements']) ||
            empty($parameters['id_seance'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            foreach ($parameters['emargements'] as $emargement) {
                ////////////////////////////////////////////////////
                // Suppression
                ////////////////////////////////////////////////////
                $query = '
                    DELETE FROM a_participe_a
                    WHERE id_seance = :id_seance
                      AND id_patient = :id_patient ';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_seance', $parameters['id_seance']);
                $stmt->bindValue(':id_patient', $emargement['id_patient']);

                if (!$stmt->execute()) {
                    throw new Exception('Error DELETE FROM a_participe_a');
                }

                ////////////////////////////////////////////////////
                // Insertion dans a_participe_a
                ////////////////////////////////////////////////////
                $excuse = $emargement['present'] == "1" ? null : $emargement['excuse'];
                $query = '
                    INSERT INTO a_participe_a (id_patient, id_seance, presence, excuse, commentaire)
                    VALUES(:id_patient, :id_seance, :presence, :excuse, :commentaire)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_seance', $parameters['id_seance']);
                $stmt->bindValue(':id_patient', $emargement['id_patient']);
                $stmt->bindValue(':presence', $emargement['present']);
                if (is_null($excuse)) {
                    $stmt->bindValue(':excuse', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':excuse', $excuse);
                }

                $stmt->bindValue(':commentaire', $emargement['commentaire']);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO a_participe_a');
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
     *
     * required parameters :
     * [
     *     'id_seance' => string,
     *     'date' => string,
     *     'heure_debut' => string,
     *     'heure_fin' => string,
     * ]
     *
     * optional parameters :
     * [
     *     'commentaire' => string,
     *     'validation' => string,
     *     'id_user' => string,
     * ]
     *
     * @param $parameters array
     * @return bool if the seance was successfully updated
     */
    public function update($parameters): bool
    {
        if (empty($parameters['id_seance']) ||
            empty($parameters['date']) ||
            empty($parameters['heure_debut']) ||
            empty($parameters['heure_fin'])) {
            return false;
        }
        $extraHeureFin = $parameters['heure_fin'];
        $extraHeureDebut = $parameters['heure_debut'];
        $parameters['commentaire'] = $parameters['commentaire'] ?? null;

        try {
            $this->pdo->beginTransaction();

            ////////////////////////////////////
            /// On vérifie que le format de l'heure est le bon
            ////////////////////////////////////
            if (strlen($parameters['heure_debut']) > 2) {
                $query = '
                    SELECT id_heure
                    FROM heures 
                    WHERE heure = :extraHeureDebut';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':extraHeureDebut', $parameters['heure_debut']);
                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT id_heure');
                }
                $donnees = $stmt->fetch(PDO::FETCH_ASSOC);
                $extraHeureDebut = $donnees['id_heure'];
            }

            if (strlen($parameters['heure_fin']) > 2) {
                $query = '
                    SELECT id_heure
                    FROM heures 
                    WHERE heure = :extraHeureFin';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':extraHeureFin', $parameters['heure_fin']);
                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT id_heure');
                }
                $donnees = $stmt->fetch(PDO::FETCH_ASSOC);
                $extraHeureFin = $donnees['id_heure'];
            }
            ////////////////////////////////////
            /// Verification de changement de jour (si != 8 on ne change pas)
            ////////////////////////////////////

            $query = '
                select id_jour
                FROM  seance
                JOIN creneaux c on seance.id_creneau = c.id_creneau
                WHERE  id_seance = :id_seance';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_seance', $parameters['id_seance']);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT date_seance');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_jour = $data['id_jour'];
            if ($id_jour != 8) {
                $query = '
                    select date_seance
                    FROM  seance
                    WHERE  id_seance = :id_seance';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_seance', $parameters['id_seance']);
                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT date_seance');
                }
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $pre_date = new DateTime($data['date_seance']);
                $post_date = new DateTime($parameters['date']);
                //format de la différence en jours
                $diff_date = date_diff($pre_date, $post_date)->days;
                if (($diff_date % 7) != 0) {
                    throw new Exception('Error Creneaux non correspondant');
                }
            }

            $query = '
                UPDATE seance 
                SET date_seance = :date, heure_debut = :heure_debut, heure_fin = :heure_fin
                WHERE id_seance = :id_seance';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(':id_seance', $parameters['id_seance']);
            $stmt->bindValue(':date', $parameters['date']);
            $stmt->bindValue(':heure_debut', $extraHeureDebut);
            $stmt->bindValue(':heure_fin', $extraHeureFin);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE seance');
            }

            // on ne modifie pas le commentaire existant si le nouveau est null
            if (!is_null($parameters['commentaire'])) {
                $query = '
                    UPDATE seance 
                    SET commentaire_seance = :commentaire
                    WHERE id_seance = :id_seance';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_seance', $parameters['id_seance']);
                $stmt->bindValue(':commentaire', $parameters['commentaire']);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE seance (commmentaire)');
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

    public function delete($id_seance)
    {
        // TODO
    }

    public function readOne($id_seance)
    {
        if (empty($id_seance)) {
            return false;
        }

        $query = '
            SELECT creneaux.nom_creneau           as nom_creneau,
                   structure.nom_structure        as nom_structure,
                   creneaux.id_type_parcours      as id_type_parcours,
                   creneaux.nombre_participants   as nb_participant,
                   coordonnees.nom_coordonnees    as nom_intervenant,
                   coordonnees.prenom_coordonnees as prenom_intervenant,
                   structure.id_structure         as id_structure,
                   structure.nom_structure        as nom_structure,
                   adresse.nom_adresse            as adresse,
                   adresse.complement_adresse     as complement_adresse,
                   villes.code_postal             as code_postal,
                   villes.nom_ville               as nom_ville,
                   creneaux.type_seance           as type_seance,
                   type_parcours.type_parcours    as type_parcours,
                   creneaux.id_jour               as jour,
                   jours.nom_jour                 as nom_jour,
                   seance.heure_debut             as id_heure_debut,
                   seance.heure_fin               as id_heure_fin,
                   users.id_user                  as id_user,
                   seance.date_seance             as date_seance,
                   seance.id_seance               as id_seance,
                   seance.id_creneau              as id_creneau,
                   heuresDeb.heure                as heure_debut,
                   heuresFin.heure                as heure_fin,
                   seance.validation_seance       as valider,
                   seance.commentaire_seance      as commentaire_seance,
                   m.motif_annulation             as motif_annulation
            FROM seance
                     join creneaux on creneaux.id_creneau = seance.id_creneau
                     join structure on creneaux.id_structure = structure.id_structure
                     join users on seance.id_user = users.id_user
                     join coordonnees on users.id_coordonnees = coordonnees.id_coordonnees
                     join se_pratique_a on creneaux.id_creneau = se_pratique_a.id_creneau
                     join adresse on se_pratique_a.id_adresse = adresse.id_adresse
                     join se_localise_a on adresse.id_adresse = se_localise_a.id_adresse
                     join villes on se_localise_a.id_ville = villes.id_ville
                     join type_parcours on type_parcours.id_type_parcours = creneaux.id_type_parcours
                     join heures as heuresDeb on seance.heure_debut = heuresDeb.id_heure
                     join heures as heuresFin on seance.heure_fin = heuresFin.id_heure
                     join jours on creneaux.id_jour = jours.id_jour
                     left join motifannulation m on seance.id_motif_annulation = m.id_annulation
            WHERE id_seance = :id_seance';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_seance', $id_seance);
        if (!$stmt->execute()) {
            return false;
        }
        $seance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seance != false) {
            if ($seance['valider'] == 1) {
                $etat = 'Séance validée';
            } elseif (date("Y-m-d") < $seance['date_seance']) {
                $etat = 'Séance en attente de réalisation';
            } else {
                $query = '
                    SELECT COUNT(*) as patient_count
                    from a_participe_a
                    where a_participe_a.id_seance = :id_seance AND presence is not null';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(":id_seance", $seance['id_seance']);
                if (!$stmt->execute()) {
                    return false;
                }
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($data['patient_count'] > 0) {
                    $etat = 'Séance en attente de validation';
                } else {
                    $etat = 'Séance en attente d\'émargement';
                }
            }

            $seance['etat'] = $etat;
        }

        return $seance;
    }

    public function readMultiple($array_of_ids)
    {
        if (empty($array_of_ids) || !is_array($array_of_ids)) {
            return false;
        }

        $query = '
            SELECT creneaux.nom_creneau           as nom_creneau,
                   structure.nom_structure        as nom_structure,
                   creneaux.id_type_parcours      as id_type_parcours,
                   creneaux.nombre_participants   as nb_participant,
                   coordonnees.nom_coordonnees    as nom_intervenant,
                   coordonnees.prenom_coordonnees as prenom_intervenant,
                   structure.id_structure         as id_structure,
                   structure.nom_structure        as nom_structure,
                   adresse.nom_adresse            as adresse,
                   adresse.complement_adresse     as complement_adresse,
                   villes.code_postal             as code_postal,
                   villes.nom_ville               as nom_ville,
                   creneaux.type_seance           as type_seance,
                   type_parcours.type_parcours    as type_parcours,
                   creneaux.id_jour               as jour,
                   jours.nom_jour                 as nom_jour,
                   seance.heure_debut             as id_heure_debut,
                   seance.heure_fin               as id_heure_fin,
                   users.id_user                  as id_user,
                   seance.date_seance             as date_seance,
                   seance.id_seance               as id_seance,
                   seance.id_creneau              as id_creneau,
                   heuresDeb.heure                as heure_debut,
                   heuresFin.heure                as heure_fin,
                   seance.validation_seance       as valider,
                   seance.commentaire_seance      as commentaire_seance,
                   m.motif_annulation             as motif_annulation
            FROM seance
                     join creneaux on creneaux.id_creneau = seance.id_creneau
                     join structure on creneaux.id_structure = structure.id_structure
                     join users on seance.id_user = users.id_user
                     join coordonnees on users.id_coordonnees = coordonnees.id_coordonnees
                     join se_pratique_a on creneaux.id_creneau = se_pratique_a.id_creneau
                     join adresse on se_pratique_a.id_adresse = adresse.id_adresse
                     join se_localise_a on adresse.id_adresse = se_localise_a.id_adresse
                     join villes on se_localise_a.id_ville = villes.id_ville
                     join type_parcours on type_parcours.id_type_parcours = creneaux.id_type_parcours
                     join heures as heuresDeb on seance.heure_debut = heuresDeb.id_heure
                     join heures as heuresFin on seance.heure_fin = heuresFin.id_heure
                     join jours on creneaux.id_jour = jours.id_jour
                     left join motifannulation m on seance.id_motif_annulation = m.id_annulation
            WHERE id_seance IN (' . implode(',', array_map('intval', $array_of_ids)) . ')';

        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute()) {
            return false;
        }
        $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($seances != false) {
            foreach ($seances as &$seance) {
                if ($seance['valider'] == 1) {
                    $etat = 'Séance validée';
                } elseif (date("Y-m-d") < $seance['date_seance']) {
                    $etat = 'Séance en attente de réalisation';
                } else {
                    $query = '
                    SELECT COUNT(*) as patient_count
                    from a_participe_a
                    where a_participe_a.id_seance = :id_seance AND presence is not null';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_seance", $seance['id_seance']);
                    //$stmt->bindValue(":id_creneau", $seance['id_creneau']);
                    if (!$stmt->execute()) {
                        return false;
                    }
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($data['patient_count'] > 0) {
                        $etat = 'Séance en attente de validation';
                    } else {
                        $etat = 'Séance en attente d\'émargement';
                    }
                }

                $seance['etat'] = $etat;
            }
        }

        return $seances;
    }

    public function readAll($id_user)
    {
        if (empty($id_user)) {
            return false;
        }

        $query = '
            SELECT creneaux.nom_creneau           as nom_creneau,
                   structure.nom_structure        as nom_structure,
                   creneaux.id_type_parcours      as id_type_parcours,
                   creneaux.nombre_participants   as nb_participant,
                   coordonnees.nom_coordonnees    as nom_intervenant,
                   coordonnees.prenom_coordonnees as prenom_intervenant,
                   structure.id_structure         as id_structure,
                   structure.nom_structure        as nom_structure,
                   adresse.nom_adresse            as adresse,
                   adresse.complement_adresse     as complement_adresse,
                   villes.code_postal             as code_postal,
                   villes.nom_ville               as nom_ville,
                   creneaux.type_seance           as type_seance,
                   type_parcours.type_parcours    as type_parcours,
                   creneaux.id_jour               as jour,
                   jours.nom_jour                 as nom_jour,
                   seance.heure_debut             as id_heure_debut,
                   seance.heure_fin               as id_heure_fin,
                   users.id_user                  as id_user,
                   seance.date_seance             as date_seance,
                   seance.id_seance               as id_seance,
                   seance.id_creneau              as id_creneau,
                   heuresDeb.heure                as heure_debut,
                   heuresFin.heure                as heure_fin,
                   seance.validation_seance       as valider,
                   seance.commentaire_seance      as commentaire_seance
            FROM seance
                     join creneaux on creneaux.id_creneau = seance.id_creneau
                     join structure on creneaux.id_structure = structure.id_structure
                     join users on seance.id_user = users.id_user
                     join coordonnees on users.id_coordonnees = coordonnees.id_coordonnees
                     join se_pratique_a on creneaux.id_creneau = se_pratique_a.id_creneau
                     join adresse on se_pratique_a.id_adresse = adresse.id_adresse
                     join se_localise_a on adresse.id_adresse = se_localise_a.id_adresse
                     join villes on se_localise_a.id_ville = villes.id_ville
                     join type_parcours on type_parcours.id_type_parcours = creneaux.id_type_parcours
                     join heures as heuresDeb on seance.heure_debut = heuresDeb.id_heure
                     join heures as heuresFin on seance.heure_fin = heuresFin.id_heure
                     join jours on creneaux.id_jour = jours.id_jour
            WHERE seance.annulation_seance = 0
              AND seance.id_user = :id_user';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_user', $id_user);
        if (!$stmt->execute()) {
            return false;
        }
        $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($seances != false) {
            foreach ($seances as &$seance) {
                if ($seance['valider'] == 1) {
                    $etat = 'Séance validée';
                } elseif (date("Y-m-d") < $seance['date_seance']) {
                    $etat = 'Séance en attente de réalisation';
                } else {
                    $query = '
                    SELECT COUNT(*) as patient_count
                    from a_participe_a
                    where a_participe_a.id_seance = :id_seance AND presence is not null';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_seance", $seance['id_seance']);
                    if (!$stmt->execute()) {
                        return false;
                    }
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($data['patient_count'] > 0) {
                        $etat = 'Séance en attente de validation';
                    } else {
                        $etat = 'Séance en attente d\'émargement';
                    }
                }

                $seance['etat'] = $etat;
            }
        }

        return $seances;
    }

    /**
     * Récupétions des séances d'une structure qui ont lieu la semaine de $today,
     * ainsi la semaine suivante et la semaine précédente
     *
     * @param string $id_structure
     * @param string $today la date d'ajourd'hui au format 'AAAA-MM-JJ'
     * @return array|false Returns an array containing all the result set rows, false on failure
     */
    public function readAllStructure($id_structure, $today)
    {
        if (empty($id_structure) || empty($today)) {
            return false;
        }

        $query = '
            SELECT creneaux.nom_creneau                   as nom_creneau,
                   structure.nom_structure                as nom_structure,
                   creneaux.id_type_parcours              as id_type_parcours,
                   creneaux.nombre_participants           as nb_participant,
                   coordonnees.nom_coordonnees            as nom_intervenant,
                   coordonnees.prenom_coordonnees         as prenom_intervenant,
                   coordonnees.tel_fixe_coordonnees       as tel_fixe_intervenant,
                   coordonnees.tel_portable_coordonnees   as tel_portable_intervenant,
                   structure.id_structure                 as id_structure,
                   structure.nom_structure                as nom_structure,
                   ss.nom_statut_structure                as nom_statut_structure,
                   adresse.nom_adresse                    as adresse,
                   adresse.complement_adresse             as complement_adresse,
                   villes.code_postal                     as code_postal,
                   villes.nom_ville                       as nom_ville,
                   creneaux.type_seance                   as type_seance,
                   type_parcours.type_parcours            as type_parcours,
                   creneaux.id_jour                       as jour,
                   jours.nom_jour                         as nom_jour,
                   seance.heure_debut                     as id_heure_debut,
                   seance.heure_fin                       as id_heure_fin,
                   users.id_user                          as id_user,
                   seance.date_seance                     as date_seance,
                   seance.id_seance                       as id_seance,
                   seance.id_creneau                      as id_creneau,
                   heuresDeb.heure                        as heure_debut,
                   heuresFin.heure                        as heure_fin,
                   seance.validation_seance               as valider,
                   seance.commentaire_seance              as commentaire_seance,
                   COALESCE(antenne.nom_antenne, \'\')    as nom_antenne,
                   YEARWEEK(date_seance, 1)               as week_number
            FROM seance
                     join creneaux on creneaux.id_creneau = seance.id_creneau
                     join structure on creneaux.id_structure = structure.id_structure
                     join statuts_structure ss on structure.id_statut_structure = ss.id_statut_structure
                     join users on seance.id_user = users.id_user
                     join coordonnees on users.id_coordonnees = coordonnees.id_coordonnees
                     join se_pratique_a on creneaux.id_creneau = se_pratique_a.id_creneau
                     join adresse on se_pratique_a.id_adresse = adresse.id_adresse
                     join se_localise_a on adresse.id_adresse = se_localise_a.id_adresse
                     join villes on se_localise_a.id_ville = villes.id_ville
                     join type_parcours on type_parcours.id_type_parcours = creneaux.id_type_parcours
                     join heures as heuresDeb on seance.heure_debut = heuresDeb.id_heure
                     join heures as heuresFin on seance.heure_fin = heuresFin.id_heure
                     join jours on creneaux.id_jour = jours.id_jour
                     left join antenne on creneaux.id_antenne = antenne.id_antenne
            WHERE creneaux.id_structure = :id_structure 
            AND (YEARWEEK(date_seance, 1)= YEARWEEK(:today_1, 1)
              OR YEARWEEK(date_seance, 1)= YEARWEEK(:today_2 - interval 7 day, 1)
              OR YEARWEEK(date_seance, 1)= YEARWEEK(:today_3 + interval 7 day, 1))';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
        $stmt->bindValue(':today_1', $today);
        $stmt->bindValue(':today_2', $today);
        $stmt->bindValue(':today_3', $today);
        if (!$stmt->execute()) {
            return false;
        }
        $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($seances != false) {
            foreach ($seances as &$seance) {
                if ($seance['valider'] == 1) {
                    $etat = 'Séance validée';
                } elseif (date("Y-m-d") < $seance['date_seance']) {
                    $etat = 'Séance en attente de réalisation';
                } else {
                    $query = '
                    SELECT COUNT(*) as patient_count
                    from a_participe_a
                    where a_participe_a.id_seance = :id_seance AND presence is not null';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_seance", $seance['id_seance']);
                    if (!$stmt->execute()) {
                        return false;
                    }
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($data['patient_count'] > 0) {
                        $etat = 'Séance en attente de validation';
                    } else {
                        $etat = 'Séance en attente d\'émargement';
                    }
                }

                $seance['etat'] = $etat;
            }
        }

        return $seances;
    }

    /**
     * @param $id_seance L'id de la séance
     * @return array|false La liste des participants de la séance ou false en cas d'erreur
     */
    public function readParticipants($id_seance)
    {
        if (empty($id_seance)) {
            return false;
        }

        $query = "
            SELECT a_participe_a.id_patient,
                   a_participe_a.presence,
                   a_participe_a.excuse,
                   COALESCE(a_participe_a.commentaire, '')                                                     as commentaire,
                   coordonnees.id_coordonnees,
                   IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)               as nom_patient,
                   IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                      premier_prenom_naissance)                                                                as prenom_patient,
                   coordonnees.mail_coordonnees,
                   coordonnees.tel_portable_coordonnees                                                        as tel_fixe_patient,
                   coordonnees.tel_fixe_coordonnees                                                            as tel_portable_patient,
                   patients.date_admission,
                   s.validation_seance                                                                         as valider,
                   coordonnees.prenom_coordonnees                                                              as prenom_medecin,
                   coordonnees.nom_coordonnees                                                                 as nom_medecin,
                   a.nom_antenne
            FROM a_participe_a
                     JOIN patients using (id_patient)
                     JOIN antenne a on patients.id_antenne = a.id_antenne
                     JOIN seance s on a_participe_a.id_seance = s.id_seance
                     JOIN coordonnees ON patients.id_coordonnee = coordonnees.id_coordonnees
                     LEFT JOIN suit s2 on patients.id_patient = s2.id_patient
                     LEFT JOIN coordonnees c2 ON s2.id_medecin = c2.id_medecin
            WHERE a_participe_a.id_seance = :id_seance";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_seance', $id_seance);
        $stmt->execute();

        $patients = [];
        while ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patient['nom_patient'] = !empty($patient['nom_patient']) ? EncryptionManager::decrypt(
                $patient['nom_patient']
            ) : "";
            $patient['prenom_patient'] = !empty($patient['prenom_patient']) ? EncryptionManager::decrypt(
                $patient['prenom_patient']
            ) : "";
            $patient['mail_coordonnees'] = !empty($patient['mail_coordonnees']) ? EncryptionManager::decrypt(
                $patient['mail_coordonnees']
            ) : "";
            $patient['tel_fixe_patient'] = !empty($patient['tel_fixe_patient']) ? EncryptionManager::decrypt(
                $patient['tel_fixe_patient']
            ) : "";
            $patient['tel_portable_patient'] = !empty($patient['tel_portable_patient']) ? EncryptionManager::decrypt(
                $patient['tel_portable_patient']
            ) : "";
            $patient['telephone'] = !empty($patient['tel_fixe_patient']) ?
                $patient['tel_fixe_patient'] :
                (!empty($patient['tel_portable_patient']) ?
                    $patient['tel_portable_patient'] :
                    "Inconnu");

            $patients[] = $patient;
        }

        // tri ordre alphabétique nom, puis prénom en cas de nom égal
        usort($patients, function ($a, $b) {
            if ($a['nom_patient'] == $b['nom_patient']) {
                if ($a['prenom_patient'] == $b['prenom_patient']) {
                    return 0;
                }
                return ($a['prenom_patient'] < $b['prenom_patient']) ? -1 : 1;
            }

            return ($a['nom_patient'] < $b['nom_patient']) ? -1 : 1;
        });

        return $patients;
    }

    /**
     * Update les participants des séances qui se passe après $date
     *
     * @param string $date au format 'yyyy-mm-dd'
     * @param        $id_creneau
     * @return bool Si l'update a été réalisé avec succès
     */
    public function updateParticipantsSeancesAfter($date, $id_creneau): bool
    {
        if (empty($date) || empty($id_creneau)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // recuperation de la nouvelle liste des participants du créneau
            $query = '
                SELECT id_patient
                FROM liste_participants_creneau
                WHERE liste_participants_creneau.id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_patient');
            }
            $patient_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            // recuperation des séances qui se passent après $date
            $query = '
                SELECT id_seance
                FROM seance
                WHERE date_seance > :date_seance AND id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':date_seance', $date);
            $stmt->bindValue(':id_creneau', $id_creneau);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_patient');
            }
            $seance_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            ////////////////////////////////////////////////////
            // Suppression de a_participe_a des séances qui se passent après $date
            ////////////////////////////////////////////////////
            foreach ($seance_ids as $id_seance) {
                $query = '
                    DELETE FROM a_participe_a
                    WHERE id_seance = :id_seance';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_seance', $id_seance);

                if (!$stmt->execute()) {
                    throw new Exception('Error DELETE FROM a_participe_a');
                }
            }

            ////////////////////////////////////////////////////
            // Insertion dans a_participe_a
            ////////////////////////////////////////////////////
            foreach ($seance_ids as $id_seance) {
                foreach ($patient_ids as $id_patient) {
                    $query = '
                        INSERT INTO a_participe_a (id_patient, id_seance, presence)
                        VALUES(:id_patient, :id_seance, :presence)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_seance', $id_seance);
                    $stmt->bindValue(':id_patient', $id_patient);
                    $stmt->bindValue(':presence', null);

                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO a_participe_a');
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
     * Update les données basiques (heure_debut et heure_fin) des séances qui se passe après $date
     *
     * @param string $date au format 'yyyy-mm-dd'
     * @param        $id_creneau
     * @return bool Si l'update a été réalisé avec succès
     */
    public function updateSeancesAfter($date, $id_creneau): bool
    {
        if (empty($date) || empty($id_creneau)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // recuperation de la nouvelle liste des participants du créneau
            $query = '
                SELECT 
                    creneaux.id_creneau,
                    commence_a.id_heure as id_heure_commence,
                    se_termine_a.id_heure as id_heure_termine,
                    id_jour
                FROM creneaux
                JOIN commence_a USING (id_creneau)
                JOIN se_termine_a USING (id_creneau)
                WHERE creneaux.id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_creneau', $id_creneau);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT FROM creneaux');
            }
            $creneaux = $stmt->fetch(PDO::FETCH_ASSOC);

            // recuperation des séances qui se passent après $date
            $query = '
                SELECT id_seance
                FROM seance
                WHERE date_seance > :date_seance AND id_creneau = :id_creneau';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':date_seance', $date);
            $stmt->bindValue(':id_creneau', $id_creneau);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_patient');
            }
            $seance_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            ////////////////////////////////////////////////////
            // Suppression de a_participe_a des séances qui se passent après $date
            ////////////////////////////////////////////////////
            foreach ($seance_ids as $id_seance) {
                $query = '
                UPDATE seance 
                SET heure_debut = :heure_debut, heure_fin = :heure_fin
                WHERE id_seance = :id_seance';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(':id_seance', $id_seance);
                $stmt->bindValue(':heure_debut', $creneaux['id_heure_commence']);
                $stmt->bindValue(':heure_fin', $creneaux['id_heure_termine']);

                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE seance');
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
     * Insert une séance et ajoute les participants du créneau à la séance
     *
     * @param $parameters
     * @return false|string
     * @throws Exception
     */
    private function insertSeance($parameters)
    {
        $query = '
            INSERT INTO seance
            (id_creneau, id_user, date_seance, commentaire_seance, validation_seance, heure_debut, heure_fin)
            VALUES (:id_creneau, :id_user, :date_seance, :commentaire_seance, 0, :heure_debut, :heure_fin)';
        $stmt = $this->pdo->prepare($query);

        $stmt->bindValue(":id_creneau", $parameters['id_creneau']);
        $stmt->bindValue(":id_user", $parameters['id_user']);
        $stmt->bindValue(":date_seance", $parameters['date']);
        $stmt->bindValue(':commentaire_seance', $parameters['commentaire']);
        $stmt->bindValue(':heure_debut', $parameters['heure_debut']);
        $stmt->bindValue(':heure_fin', $parameters['heure_fin']);

        if (!$stmt->execute()) {
            throw new Exception('Error INSERT INTO seance');
        }

        $id_seance = $this->pdo->lastInsertId();

        // recuperation des participants du créneaux
        // recupération de la date de démarrage la plus récente en cas de doublon
        $query = '
            SELECT liste_participants_creneau.id_patient, MIN(activite_choisie.date_demarrage) as date_demarrage
            FROM liste_participants_creneau
                     LEFT JOIN orientation on orientation.id_patient = liste_participants_creneau.id_patient
                     LEFT JOIN activite_choisie on activite_choisie.id_orientation = orientation.id_orientation
            WHERE liste_participants_creneau.id_creneau = :id_creneau1
              AND activite_choisie.id_creneau = :id_creneau2
            GROUP BY liste_participants_creneau.id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_creneau1', $parameters['id_creneau']);
        $stmt->bindValue(':id_creneau2', $parameters['id_creneau']);
        if (!$stmt->execute()) {
            throw new Exception('Error SELECT id_patient');
        }
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ////////////////////////////////////////////////////
        // Insertion dans a_participe_a
        ////////////////////////////////////////////////////
        foreach ($patients as $patient) {
            // les patients ne sont ajoutés que si leur date_demarrage a commencé ou si la date_demarrage n'est pas renseigné
            if (empty($patient['date_demarrage']) || ($patient['date_demarrage'] <= $parameters['date'])) {
                $query = '
                    insert into a_participe_a (id_patient, id_seance)
                    values(:id_patient, :id_seance)';

                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_seance', $id_seance);
                $stmt->bindValue(':id_patient', $patient["id_patient"]);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO a_participe_a');
                }
            }
        }

        return $id_seance;
    }

    /**
     * @param int $min_days le nombre de jours (>0) à partir duquel une séance est considérée en retard
     * @param string $today date au format "AAAA-MM-JJ"
     * @return array un array qui contient les ids des seances qui sont en retard d'émargement d'exactement $min_days
     *     jours
     */
    public function getAllSeanceEmargementLate($min_days, $today)
    {
        if (empty($min_days) || empty($today)) {
            return [];
        }

        $query = '
            SELECT dsdl.id_seance
            FROM (SELECT id_seance,
                         date_seance,
                         DATEDIFF(:today, date_seance) AS days_late
                  FROM seance) dsdl
            WHERE dsdl.days_late = :min_days';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':min_days', $min_days);
        $stmt->bindValue(':today', $today);
        if (!$stmt->execute()) {
            return [];
        }

        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if ($ids) {
            return $ids;
        }

        return [];
    }

    /**
     * @param string $date_str date au format 'yyyy-mm-dd' à partir de laquelle on recherche la première date
     * @param string $id_creneau
     * @return string|null Return la première date valide pour le créneau donnée
     */
    public function getFirstValidDate($date_str, $id_creneau)
    {
        if (empty($date_str) || empty($id_creneau)) {
            return null;
        }

        $jours = [
            '1' => 'monday',
            '2' => 'tuesday',
            '3' => 'wednesday',
            '4' => 'thursday',
            '5' => 'friday',
            '6' => 'saturday',
            '7' => 'sunday',
        ];

        $query = '
            SELECT id_jour
            FROM creneaux
            WHERE id_creneau = :id_creneau';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_creneau', $id_creneau);
        if (!$stmt->execute()) {
            return null;
        }
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }

        $id_jour = $data['id_jour'];
        if ($id_jour == "8") {
            return $date_str;
        }

        $jour_number = date("N", strtotime($date_str));
        if ($id_jour == $jour_number) {
            return $date_str;
        }

        try {
            $date = new DateTime($date_str);
        } catch (Exception $e) {
            return null;
        }

        $date = $date->modify('next ' . $jours[$id_jour]);

        return $date->format('Y-m-d');
    }

    /**
     * @param        $id_creneau
     * @param string $date date au format 'yyyy-mm-dd'
     * @return bool Si la date est valide pour le créneau donné (même jour ou créneau dispo tous les jours)
     */
    public function isDateValid($id_creneau, $date)
    {
        if (empty($id_creneau) || empty($date)) {
            return false;
        }

        $query = '
            SELECT id_jour
            FROM creneaux
            WHERE id_creneau = :id_creneau';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":id_creneau", $id_creneau);
        if (!$stmt->execute()) {
            $this->errorMessage = "Error executing query";
            return false;
        }
        if ($stmt->rowCount() == 0) {
            return false;
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_jour = $data['id_jour'];

        $jour_str = date("l", strtotime($date));

        return
            ($id_jour == 8) ||
            ($id_jour == 1 && $jour_str == "Monday") ||
            ($id_jour == 2 && $jour_str == "Tuesday") ||
            ($id_jour == 3 && $jour_str == "Wednesday") ||
            ($id_jour == 4 && $jour_str == "Thursday") ||
            ($id_jour == 5 && $jour_str == "Friday") ||
            ($id_jour == 6 && $jour_str == "Saturday") ||
            ($id_jour == 7 && $jour_str == "Sunday");
    }

    /** Supprime toutes les séances d'un utilisateur
     *
     * @param $id_user int L'identifiant de l'utilisateur
     * @return void
     * @throws Exception
     */
    public function deleteAllSeancesUser($id_user)
    {
        if (empty($id_user)) {
            throw new Exception('Error missing id_user');
        }

        $query = '
            SELECT id_seance
            FROM seance
            WHERE id_user = :id_user';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_user', $id_user);

        if (!$statement->execute()) {
            throw new Exception('Error SELECT id_seance');
        }
        $seance_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

        // disable foreign_key_checks if not already disabled
        $query = 'SELECT @@foreign_key_checks';
        $statement = $this->pdo->prepare($query);
        if (!$statement->execute()) {
            throw new Exception('Error SELECT @@foreign_key_checks');
        }
        $foreign_key_checks = $statement->fetch(PDO::FETCH_COLUMN, 0);

        if ($foreign_key_checks == '1') {
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }
        }

        if ($seance_ids) {
            foreach ($seance_ids as $id_seance) {
                $query = '
                    DELETE FROM seance
                    WHERE id_seance = :id_seance';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_seance', $id_seance);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM seance');
                }

                $query = '
                    DELETE FROM a_participe_a
                    WHERE id_seance = :id_seance';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_seance', $id_seance);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM a_participe_a');
                }
            }
        }

        // re-enable foreign_key_checks if it was enabled at the start of the function
        if ($foreign_key_checks == '1') {
            if (!$this->pdo->query("SET foreign_key_checks=1")) {
                throw new Exception('Error re-enabling foreign key checks');
            }
        }
    }
}