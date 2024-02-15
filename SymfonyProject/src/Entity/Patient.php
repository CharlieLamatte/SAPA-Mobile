<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;
use DateTime;
use Sportsante86\Sapa\Outils\ChaineCharactere;
use Sportsante86\Sapa\Outils\EncryptionManager;
use Sportsante86\Sapa\Outils\Permissions;

class Patient
{
    private PDO $pdo;
    private string $errorMessage;

    // source : Référentiel Identifiant national de santé Liste des OID des autorités d'affectation des INS V0.1
    private const OIDS_NIR = ["1.2.250.1.213.1.4.8", "1.2.250.1.213.1.4.10", "1.2.250.1.213.1.4.11"];
    private const OIDS_NIA = ["1.2.250.1.213.1.4.9"];

    public const NATURE_OID_NIA = 1;
    public const NATURE_OID_NIR = 2;
    public const NATURE_OID_INCONNU = 3;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->errorMessage = '';
    }

    /**
     * @param Permissions $permissions
     * @param             $parameters
     * @return false|string l'id du patient ou false en cas d'erreur
     */
    public function create(Permissions $permissions, $parameters)
    {
        if (!$this->checkParameters($parameters)) {
            $this->errorMessage = "Il manque des paramètres obligatoires";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_territoire = filter_var($parameters['id_territoire'], FILTER_SANITIZE_NUMBER_INT);
            $id_user = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);
            $id_structure = filter_var($parameters['id_structure'], FILTER_SANITIZE_NUMBER_INT);

            // ADMISSION
            $date_admission = $parameters['date_admission'];
            $nature_entretien = $parameters['nature_entretien'];

            // PATIENT
            $nom_naissance = EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins($parameters['nom_naissance'])
            );
            $premier_prenom_naissance = EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['premier_prenom_naissance']
                )
            );

            $sexe_patient = $parameters['sexe_patient'];
            $date_naissance = $parameters['date_naissance'];
            $adresse_patient = EncryptionManager::encrypt(
                trim(ChaineCharactere::mb_ucfirst($parameters['adresse_patient']))
            );
            $code_postal_patient = filter_var($parameters['code_postal_patient'], FILTER_SANITIZE_NUMBER_INT);
            $ville_patient = $parameters['ville_patient'];

            // Caisse d'assurance maladie
            $id_type_regime = filter_var($parameters['regime_assurance_maladie'], FILTER_SANITIZE_NUMBER_INT);
            $ville_regime = $parameters['ville_assurance_maladie'];
            $cp_regime = filter_var($parameters['code_postal_assurance_maladie'], FILTER_SANITIZE_NUMBER_INT);

            // paramètres optionnels
            // PATIENT
//            $matricule_ins = !empty($parameters['matricule_ins']) ? EncryptionManager::encrypt(
//                trim($parameters['matricule_ins'])
//            ) : "";
//            $oid = !empty($parameters['oid']) ? EncryptionManager::encrypt(trim($parameters['oid'])) : "";
//            $code_insee_naissance = !empty($parameters['code_insee_naissance']) ? EncryptionManager::encrypt(
//                trim(
//                    $parameters['code_insee_naissance']
//                )
//            ) : "";
            $nom_utilise = !empty($parameters['nom_utilise']) ? EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['nom_utilise']
                )
            ) : "";
            $prenom_utilise = !empty($parameters['prenom_utilise']) ? EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['prenom_utilise']
                )
            ) : "";
            $liste_prenom_naissance = !empty($parameters['liste_prenom_naissance']) ? EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['liste_prenom_naissance']
                )
            ) : "";

            $tel_fixe_patient = !empty($parameters['tel_fixe_patient']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_fixe_patient'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";
            $tel_portable_patient = !empty($parameters['tel_portable_patient']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_portable_patient'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";
            $email_patient = !empty($parameters['email_patient']) ?
                EncryptionManager::encrypt(
                    filter_var($parameters['email_patient'], FILTER_SANITIZE_EMAIL, ['options' => ['default' => ""]])
                ) :
                "";

            $complement_adresse_patient = !empty($parameters['complement_adresse_patient']) ?
                EncryptionManager::encrypt(
                    trim(ChaineCharactere::mb_ucfirst($parameters['complement_adresse_patient']))
                ) :
                "";

            //date de la prochaine évaluation
            if (empty($parameters['date_eval_suiv']) || $parameters['date_eval_suiv'] == "") {
                $date_eval_suiv = null;
            } else {
                $date_eval_suiv = $parameters['date_eval_suiv'];
            }


            // Contact d'urgence
            $nom_urgence = !empty($parameters['nom_urgence']) ?
                EncryptionManager::encrypt(trim(mb_strtoupper($parameters['nom_urgence'], 'UTF-8'))) :
                "";
            $prenom_urgence = !empty($parameters['prenom_urgence']) ?
                EncryptionManager::encrypt(trim(mb_strtoupper($parameters['prenom_urgence'], 'UTF-8'))) :
                "";
            $id_lien = !empty($parameters['id_lien']) ? $parameters['id_lien'] : "17"; // lien par défaut est "Autre"
            $tel_portable_urgence = !empty($parameters['tel_portable_urgence']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_portable_urgence'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";
            $tel_fixe_urgence = !empty($parameters['tel_fixe_urgence']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_fixe_urgence'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";

            $intervalle = $parameters['intervalle'] ?? '3'; // l'intervalle entre évaluation par défaut est de 3 mois

            // medecin prescripteur
            $id_medecin = $parameters['id_medecin'] ?? "";

            // medecin traitant
            $memeMed = $parameters['meme_med'] ?? "";
            $id_med_traitant = $parameters['id_med_traitant'] ?? "";

            // autre professionnel de santé
            $autre_prof_sante_ids = isset($parameters['id_autre']) ?
                filter_var_array($parameters['id_autre'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $autre_prof_sante_ids = array_filter($autre_prof_sante_ids);

            // mutuelle
            $id_mutuelle = $parameters['id_mutuelle'] ?? "";

            // Autres infos
            $est_non_peps = isset($parameters["est_non_peps"]) ?
                ($parameters["est_non_peps"] == 'checked' ? 1 : 0) :
                0;
            $est_pris_en_charge = $parameters['est_pris_en_charge'] ?? null;
            $hauteur_prise_en_charge = $parameters['hauteur_prise_en_charge'] ?? "";
            $sit_part_prevention_chute = $parameters['sit_part_prevention_chute'] ?? "NON";
            $sit_part_prevention_chute = $sit_part_prevention_chute == 'YES' ? 1 : 0;
            $sit_part_education_therapeutique = $parameters['sit_part_education_therapeutique'] ?? "NON";
            $sit_part_education_therapeutique = $sit_part_education_therapeutique == 'YES' ? 1 : 0;
            $sit_part_grossesse = $parameters['sit_part_grossesse'] ?? "NON";
            $sit_part_grossesse = $sit_part_grossesse == 'YES' ? 1 : 0;
            $sit_part_sedentarite = $parameters['sit_part_sedentarite'] ?? "NON";
            $sit_part_sedentarite = $sit_part_sedentarite == 'YES' ? 1 : 0;
            $sit_part_autre = $parameters['sit_part_autre'] ?? "";
            $qpv = $parameters['qpv'] ?? "NON";
            $qpv = $qpv == 'YES' ? 1 : 0;
            $zrr = $parameters['zrr'] ?? "NON";
            $zrr = $zrr == 'YES' ? 1 : 0;

            // insert dans table adresse
            $query = '
                INSERT INTO adresse (nom_adresse, complement_adresse)
                VALUES (:nom_adresse, :complement_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":nom_adresse", $adresse_patient);
            $stmt->bindValue(":complement_adresse", $complement_adresse_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO adresse');
            }
            $id_adresse = $this->pdo->lastInsertId();

            // TODO permettre a l'utilisateur de choisir l'antenne
            $query = '
                SELECT id_antenne
                FROM antenne
                WHERE id_structure = :id_structure';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_structure", $id_structure);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_antenne');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_antenne = $data['id_antenne'];

            //Récupère id ville en comparaison avec le cp et ville
            $query = '
                SELECT id_ville
                FROM villes
                WHERE nom_ville = :nom_ville AND code_postal= :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":nom_ville", $ville_patient);
            $stmt->bindValue(":code_postal", $code_postal_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_ville');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_ville = $data['id_ville'];

            //Insere dans table selocalisea id_adresse et id_villle qu'on a recup
            $query = '
                INSERT INTO se_localise_a (id_ville, id_adresse)
                VALUES (:id_ville, :id_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_ville", $id_ville);
            $stmt->bindValue(":id_adresse", $id_adresse);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT se_localise_a');
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //CPAM//
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            // SELECT pour récupérer l'id_ville de la ville_cpam
            $query = '
                SELECT id_ville
                from villes
                where nom_ville = :nom_ville AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":nom_ville", $ville_regime);
            $stmt->bindValue(":code_postal", $cp_regime);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_ville');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $villeRegime = $data['id_ville'];

            $query = '
                SELECT id_reside
                FROM reside
                WHERE id_caisse_assurance_maladie = :id_caisse_assurance_maladie AND id_ville = :id_ville';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_caisse_assurance_maladie", $id_type_regime);
            $stmt->bindValue(":id_ville", $villeRegime);
            if (!$stmt->execute()) {
                throw new Exception('Error SELECT id_reside');
            }
            $lignes = $stmt->rowCount();
            if ($lignes == 0) {
                $query = '
                    INSERT INTO reside (id_caisse_assurance_maladie, id_ville)
                    VALUES(:id_caisse_assurance_maladie, :id_ville);';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(":id_caisse_assurance_maladie", $id_type_regime);
                $stmt->bindValue(":id_ville", $villeRegime);
                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO reside');
                }
                $idReside = $this->pdo->lastInsertId();
            } else {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $idReside = $data['id_reside'];
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //BENEFICIARE//
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $query = '
                INSERT INTO patients (nom_naissance, premier_prenom_naissance, liste_prenom_naissance,
                                      nom_utilise, prenom_utilise, sexe, date_naissance,
                                      date_admission, intervalle, est_non_peps, est_pris_en_charge_financierement,
                                      hauteur_prise_en_charge_financierement, sit_part_prevention_chute,
                                      sit_part_education_therapeutique, sit_part_grossesse, sit_part_sedentarite, est_dans_qpv,
                                      est_dans_zrr, sit_part_autre, id_mutuelle, id_reside, id_user, id_adresse, id_territoire,
                                      id_antenne, date_eval_suiv)
                VALUES (:nom_naissance, :premier_prenom_naissance, :liste_prenom_naissance,
                        :nom_utilise, :prenom_utilise, :sexe, :date_naissance,
                        :date_admission, :intervalle, :est_non_peps, :est_pris_en_charge_financierement,
                        :hauteur_prise_en_charge_financierement, :sit_part_prevention_chute,
                        :sit_part_education_therapeutique, :sit_part_grossesse, :sit_part_sedentarite, :est_dans_qpv,
                        :est_dans_zrr, :sit_part_autre, :id_mutuelle, :id_reside, :id_user, :id_adresse, :id_territoire,
                        :id_antenne, :date_eval_suiv)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":premier_prenom_naissance", $premier_prenom_naissance);
            $stmt->bindValue(":nom_naissance", $nom_naissance);
            $stmt->bindValue(":sexe", $sexe_patient);
            $stmt->bindValue(":date_naissance", $date_naissance);
            $stmt->bindValue(":id_adresse", $id_adresse);
            $stmt->bindValue(":id_user", $id_user);
            $stmt->bindValue(":id_territoire", $id_territoire);
            $stmt->bindValue(":id_antenne", $id_antenne);
            $stmt->bindValue(":date_eval_suiv", $date_eval_suiv);
            $stmt->bindValue(":date_admission", $date_admission);
            //$stmt->bindValue(":code_insee_naissance", $code_insee_naissance);
            $stmt->bindValue(":nom_utilise", $nom_utilise);
            $stmt->bindValue(":prenom_utilise", $prenom_utilise);
            $stmt->bindValue(":liste_prenom_naissance", $liste_prenom_naissance);
            if (!empty($est_pris_en_charge) && $est_pris_en_charge == 'checked') {
                $stmt->bindValue(':est_pris_en_charge_financierement', 1);
                $stmt->bindValue(':hauteur_prise_en_charge_financierement', $hauteur_prise_en_charge);
            } else {
                $stmt->bindValue(':est_pris_en_charge_financierement', 0);
                $stmt->bindValue(':hauteur_prise_en_charge_financierement', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(":est_non_peps", $est_non_peps);
            $stmt->bindValue(":sit_part_autre", $sit_part_autre);
            $stmt->bindValue(":sit_part_education_therapeutique", $sit_part_education_therapeutique);
            $stmt->bindValue(":sit_part_grossesse", $sit_part_grossesse);
            $stmt->bindValue(":sit_part_prevention_chute", $sit_part_prevention_chute);
            $stmt->bindValue(":sit_part_sedentarite", $sit_part_sedentarite);
            $stmt->bindValue(":est_dans_qpv", $qpv);
            $stmt->bindValue(":est_dans_zrr", $zrr);
            $stmt->bindValue(":intervalle", $intervalle);
            $stmt->bindValue(":id_reside", $idReside);
            if (!empty($id_mutuelle)) {
                $stmt->bindValue(":id_mutuelle", $id_mutuelle);
            } else {
                $stmt->bindValue(':id_mutuelle', null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO patients');
            }
            $id_patient = $this->pdo->lastInsertId();

            //INSERT dans table parcours pour nature entretien
            $query = '
                INSERT INTO parcours (id_patient, nature_entretien_initial)
                VALUES (:id_patient, :nature_entretien_initial)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_patient", $id_patient);
            $stmt->bindValue(":nature_entretien_initial", $nature_entretien);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO parcours');
            }

            // insert dans coordonnees
            $query = '
                INSERT INTO coordonnees (tel_fixe_coordonnees, tel_portable_coordonnees, mail_coordonnees, id_patient)
                VALUES (:tel_fixe_coordonnees, :tel_portable_coordonnees, :mail_coordonnees, :id_patient)';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":tel_fixe_coordonnees", $tel_fixe_patient);
            $stmt->bindValue(":tel_portable_coordonnees", $tel_portable_patient);
            $stmt->bindValue(":mail_coordonnees", $email_patient);
            $stmt->bindValue(":id_patient", $id_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO coordonnees');
            }
            $id_coordo_patient = $this->pdo->lastInsertId();

            //UPDATE TABLE PATIENT AVEC coordonnees
            $query = '
                UPDATE patients
                SET id_coordonnee = :id_coordonnee
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_coordonnee", $id_coordo_patient);
            $stmt->bindValue(":id_patient", $id_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE patients');
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //CONTACT URGENCE//
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            // ajout des coordonnes du contact
            $query = '
                INSERT INTO coordonnees (nom_coordonnees,prenom_coordonnees,tel_fixe_coordonnees,tel_portable_coordonnees)
                VALUES (:nom_coordonnees, :prenom_coordonnees, :tel_fixe_coordonnees, :tel_portable_coordonnees)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":nom_coordonnees", $nom_urgence);
            $stmt->bindValue(":prenom_coordonnees", $prenom_urgence);
            $stmt->bindValue(":tel_fixe_coordonnees", $tel_fixe_urgence);
            $stmt->bindValue(":tel_portable_coordonnees", $tel_portable_urgence);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO coordonnees CONTACT URGENCE');
            }
            $id_coordonnees = $this->pdo->lastInsertId();

            // ajout dans la table a contacter en cas urgence
            $query = '
                INSERT INTO a_contacter_en_cas_urgence (id_patient, id_lien, id_coordonnee)
                VALUES (:id_patient, :id_lien, :id_coordonnee)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":id_patient", $id_patient);
            $stmt->bindValue(":id_lien", $id_lien);
            $stmt->bindValue(":id_coordonnee", $id_coordonnees);
            if (!$stmt->execute()) {
                throw new Exception('Error INSERT INTO a_contacter_en_cas_urgence');
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //MEDECIN PRESCRIPTEUR//
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if (!empty($id_medecin)) {
                $query = '
                    INSERT INTO prescrit (id_patient, id_medecin)
                    VALUES (:id_patient, :id_medecin)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(":id_patient", $id_patient);
                $stmt->bindValue(":id_medecin", $id_medecin);
                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO prescrit');
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //MEDECIN TRAITANT//
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //Recupere valeur du bouton radio, si oui on insère le même id que med prescripteur
            if ($memeMed == "oui") {
                //même que prescripteur
                if (!empty($id_medecin)) {
                    $query = '
                        INSERT INTO traite (id_patient, id_medecin)
                        VALUES (:id_patient, :id_medecin)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_patient", $id_patient);
                    $stmt->bindValue(":id_medecin", $id_medecin);
                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO traite');
                    }
                }
            } else {
                if (!empty($id_med_traitant)) {
                    $query = '
                        INSERT INTO traite (id_patient, id_medecin)
                        VALUES (:id_patient, :id_medecin)';
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(":id_patient", $id_patient);
                    $stmt->bindValue(":id_medecin", $id_med_traitant);
                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO traite');
                    }
                }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //AUTRE PRO SANTE//
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            foreach ($autre_prof_sante_ids as $id_autre) {
                $query = '
                    INSERT INTO suit (id_patient, id_medecin)
                    VALUES (:id_patient, :id_medecin)';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(":id_patient", $id_patient);
                $stmt->bindValue(":id_medecin", $id_autre);
                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO suit');
                }
            }

            if ($permissions->hasRole(Permissions::INTERVENANT)) {
                ////////////////////////////////////////////////////
                // Insertion dans oriente_vers
                ////////////////////////////////////////////////////
                $query = '
                    INSERT INTO oriente_vers
                    (id_patient, id_structure)
                    VALUES(:id_patient, :id_structure)';
                $stmt = $this->pdo->prepare($query);

                $stmt->bindValue(":id_patient", $id_patient);
                $stmt->bindValue(":id_structure", $id_structure);

                if (!$stmt->execute()) {
                    throw new Exception('Error INSERT INTO oriente_vers');
                }
            }

            $this->pdo->commit();
            return $id_patient;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function updateSuiviMedical($parameters)
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['id_caisse_assurance_maladie']) ||
            empty($parameters['code_postal_assurance_maladie']) ||
            empty($parameters['ville_cpam'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            //CAM
            $id_CAM = $parameters['id_caisse_assurance_maladie'];
            $cp_CAM = $parameters['code_postal_assurance_maladie'];
            $ville_CAM = $parameters['ville_cpam'];

            // optionnnel
            $id_med_prescripteur = $parameters['id_med'] ?? null;
            $id_med_traitant = $parameters['id_med_traitant'] ?? null;
            $autre_prof_sante_ids = isset($parameters['id_autre']) ?
                filter_var_array($parameters['id_autre'], FILTER_SANITIZE_NUMBER_INT) :
                [];
            $autre_prof_sante_ids = array_filter($autre_prof_sante_ids);

            //MUTUELLE
            $id_mutuelle = $parameters['id_mutuelle'] ?? null;

            //MEDECIN PRESCRIPTEUR
            $statement = $this->pdo->prepare("DELETE FROM prescrit WHERE id_patient = :id_patient");
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM prescrit');
            }

            if (!empty($id_med_prescripteur)) {
                $query = "
                    INSERT INTO prescrit (id_patient, id_medecin)
                    VALUES (:id_patient, :id_medecin)";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_patient", $id_patient);
                $statement->bindValue(":id_medecin", $id_med_prescripteur);
                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO prescrit');
                }
            }

            //MEDECIN TRAITANT
            $statement = $this->pdo->prepare("DELETE FROM traite WHERE id_patient = :id_patient");
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error FROM traite');
            }

            if (!empty($id_med_traitant)) {
                $query = "
                    INSERT INTO traite (id_patient, id_medecin)
                    VALUES (:id_patient, :id_medecin)";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_patient", $id_patient);
                $statement->bindValue(":id_medecin", $id_med_traitant);
                if (!$statement->execute()) {
                    throw new Exception('Error insert traite');
                }
            }

            //AUTRE PRO SANTE
            $statement = $this->pdo->prepare("DELETE FROM suit WHERE id_patient = :id_patient");
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM suit');
            }

            foreach ($autre_prof_sante_ids as $id_autre) {
                $query = "
                    INSERT INTO suit (id_patient, id_medecin)
                    VALUES (:id_patient, :id_medecin)";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_patient", $id_patient);
                $statement->bindValue(":id_medecin", $id_autre);
                if (!$statement->execute()) {
                    throw new Exception('Error insert suit');
                }
            }

            // CAM
            $query = "SELECT id_ville
                from villes
                where nom_ville = :nom_ville
                  AND code_postal = :code_postal";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":nom_ville", $ville_CAM);
            $statement->bindValue(":code_postal", $cp_CAM);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT id_ville');
            }
            $data = $statement->fetch();
            $id_ville_cpam = $data['id_ville'] ?? null;

            if (empty($id_ville_cpam)) {
                throw new Exception("La ville de la CAM n'a pas été trouvée");
            }

            $query = "
                SELECT id_reside
                FROM reside
                where id_caisse_assurance_maladie = :id_caisse_assurance_maladie AND id_ville = :id_ville";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_caisse_assurance_maladie", $id_CAM);
            $statement->bindValue(":id_ville", $id_ville_cpam);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT id_reside');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            $id_reside = $data['id_reside'] ?? null;

            if (empty($id_reside)) {
                $query = "
                    INSERT INTO reside (id_caisse_assurance_maladie, id_ville)
                    values (:id_caisse_assurance_maladie, :id_ville)";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_caisse_assurance_maladie", $id_CAM);
                $statement->bindValue(":id_ville", $id_ville_cpam);
                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO reside');
                }
                $id_reside = $this->pdo->lastInsertId();
            }

            $query = "
                UPDATE patients
                SET id_reside = :id_reside,
                    id_mutuelle = :id_mutuelle
                WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_reside", $id_reside);
            $statement->bindValue(":id_patient", $id_patient);
            if (empty($id_mutuelle)) {
                $statement->bindValue(":id_mutuelle", null, PDO::PARAM_NULL);
            } else {
                $statement->bindValue(":id_mutuelle", $id_mutuelle);
            }
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE patients');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function updateAutresInformations($parameters)
    {
        if (empty($parameters['id_patient'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_patient = $parameters['id_patient'];

            // optionnel
            $est_non_peps = isset($parameters["est_non_peps"]) ?
                ($parameters["est_non_peps"] == 'checked' ? 1 : 0) :
                0;
            $est_pris_en_charge = isset($parameters["est_pris_en_charge"]) ?
                ($parameters["est_pris_en_charge"] == 'checked' ? 1 : 0) :
                0;
            $hauteur_prise_en_charge = $parameters["hauteur_prise_en_charge"] ?? "";

            $sit_part_prevention_chute = isset($parameters["sit_part_prevention_chute"]) ?
                ($parameters['sit_part_prevention_chute'] == 'checked' ? 1 : 0) :
                0;
            $sit_part_education_therapeutique = isset($parameters["sit_part_education_therapeutique"]) ?
                ($parameters['sit_part_education_therapeutique'] == 'checked' ? 1 : 0) :
                0;
            $sit_part_grossesse = isset($parameters["sit_part_grossesse"]) ?
                ($parameters['sit_part_grossesse'] == 'checked' ? 1 : 0) :
                0;
            $sit_part_sedentarite = isset($parameters["sit_part_sedentarite"]) ?
                ($parameters['sit_part_sedentarite'] == 'checked' ? 1 : 0) :
                0;
            $sit_part_autre = $parameters['sit_part_autre'] ?? "";

            $qpv = isset($parameters["qpv"]) ?
                ($parameters['qpv'] == 'checked' ? 1 : 0) :
                0;
            $zrr = isset($parameters["zrr"]) ?
                ($parameters['zrr'] == 'checked' ? 1 : 0) :
                0;

            $query = '
                UPDATE patients
                SET
                est_non_peps = :est_non_peps,
                est_pris_en_charge_financierement = :est_pris_en_charge_financierement,
                hauteur_prise_en_charge_financierement = :hauteur_prise_en_charge_financierement,
                sit_part_autre = :sit_part_autre,
                sit_part_education_therapeutique = :sit_part_education_therapeutique,
                sit_part_grossesse = :sit_part_grossesse,
                sit_part_prevention_chute = :sit_part_prevention_chute,
                sit_part_sedentarite = :sit_part_sedentarite,
                est_dans_qpv = :est_dans_qpv,
                est_dans_zrr = :est_dans_zrr
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);
            $stmt->bindValue(':est_non_peps', $est_non_peps);
            $stmt->bindValue(':est_pris_en_charge_financierement', $est_pris_en_charge);
            if ($est_pris_en_charge == 1) {
                $stmt->bindValue(':hauteur_prise_en_charge_financierement', $hauteur_prise_en_charge);
            } else {
                $stmt->bindValue(':hauteur_prise_en_charge_financierement', null, PDO::PARAM_NULL);
            }
            $stmt->bindValue(":sit_part_autre", $sit_part_autre);
            $stmt->bindValue(":sit_part_education_therapeutique", $sit_part_education_therapeutique);
            $stmt->bindValue(":sit_part_grossesse", $sit_part_grossesse);
            $stmt->bindValue(":sit_part_prevention_chute", $sit_part_prevention_chute);
            $stmt->bindValue(":sit_part_sedentarite", $sit_part_sedentarite);
            $stmt->bindValue(":est_dans_qpv", $qpv);
            $stmt->bindValue(":est_dans_zrr", $zrr);

            if (!$stmt->execute()) {
                throw new Exception('Error update patients');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function updateCoordonnees($parameters)
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['nom_naissance']) ||
            empty($parameters['premier_prenom_naissance']) ||
            empty($parameters['sexe_patient']) ||
            empty($parameters['date_naissance']) ||
            empty($parameters['adresse_patient']) ||
            empty($parameters['code_postal_patient']) ||
            empty($parameters['ville_patient'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            //Récupération de l'ensemble des données saisies de la page précédente
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);

            // PATIENT
            $nom_naissance = EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins($parameters['nom_naissance'])
            );
            $premier_prenom_naissance = EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['premier_prenom_naissance']
                )
            );
            $sexe_patient = $parameters['sexe_patient'];
            $date_naissance = $parameters['date_naissance'];
            $adresse_patient = EncryptionManager::encrypt(
                trim(ChaineCharactere::mb_ucfirst($parameters['adresse_patient']))
            );
            $code_postal_patient = filter_var($parameters['code_postal_patient'], FILTER_SANITIZE_NUMBER_INT);
            $ville_patient = $parameters['ville_patient'];

            // paramètres optionnels
            // PATIENT
            $code_insee_naissance = !empty($parameters['code_insee_naissance']) ? EncryptionManager::encrypt(
                trim(
                    $parameters['code_insee_naissance']
                )
            ) : "";
            $nom_utilise = !empty($parameters['nom_utilise']) ? EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['nom_utilise']
                )
            ) : "";
            $prenom_utilise = !empty($parameters['prenom_utilise']) ? EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['prenom_utilise']
                )
            ) : "";
            $liste_prenom_naissance = !empty($parameters['liste_prenom_naissance']) ? EncryptionManager::encrypt(
                ChaineCharactere::format_compatible_ins(
                    $parameters['liste_prenom_naissance']
                )
            ) : "";

            $tel_fixe_patient = !empty($parameters['tel_fixe_patient']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_fixe_patient'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";
            $tel_portable_patient = !empty($parameters['tel_portable_patient']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_portable_patient'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";
            $email_patient = !empty($parameters['email_patient']) ?
                EncryptionManager::encrypt(
                    filter_var($parameters['email_patient'], FILTER_SANITIZE_EMAIL, ['options' => ['default' => ""]])
                ) :
                "";

            $complement_adresse_patient = !empty($parameters['complement_adresse_patient']) ?
                EncryptionManager::encrypt(
                    trim(ChaineCharactere::mb_ucfirst($parameters['complement_adresse_patient']))
                ) :
                "";

            // Contact d'urgence
            $nom_urgence = !empty($parameters['nom_urgence']) ?
                EncryptionManager::encrypt(trim(mb_strtoupper($parameters['nom_urgence'], 'UTF-8'))) :
                "";
            $prenom_urgence = !empty($parameters['prenom_urgence']) ?
                EncryptionManager::encrypt(trim(mb_strtoupper($parameters['prenom_urgence'], 'UTF-8'))) :
                "";
            $id_lien = !empty($parameters['id_lien']) ? $parameters['id_lien'] : "17"; // lien par défaut est "Autre"
            $tel_portable_urgence = !empty($parameters['tel_portable_urgence']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_portable_urgence'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";
            $tel_fixe_urgence = !empty($parameters['tel_fixe_urgence']) ?
                EncryptionManager::encrypt(
                    filter_var(
                        $parameters['tel_fixe_urgence'],
                        FILTER_SANITIZE_NUMBER_INT,
                        ['options' => ['default' => ""]]
                    )
                ) :
                "";

            $query = "
                UPDATE coordonnees
                SET 
                    tel_portable_coordonnees = :tel_portable_coordonnees,
                    tel_fixe_coordonnees = :tel_fixe_coordonnees,
                    mail_coordonnees = :mail_coordonnees
                WHERE coordonnees.id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":tel_portable_coordonnees", $tel_portable_patient);
            $statement->bindValue(":tel_fixe_coordonnees", $tel_fixe_patient);
            $statement->bindValue(":mail_coordonnees", $email_patient);
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            $query = "
                UPDATE patients 
                SET premier_prenom_naissance = :premier_prenom_naissance,
                    nom_naissance = :nom_naissance,
                    sexe = :sexe,
                    date_naissance = :date_naissance,
                    nom_utilise = :nom_utilise,
                    prenom_utilise = :prenom_utilise,
                    liste_prenom_naissance = :liste_prenom_naissance,
                    code_insee_naissance = :code_insee_naissance,
                    id_type_statut_identite = :id_type_statut_identite,
                    id_type_piece_identite = :id_type_piece_identite
                WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":premier_prenom_naissance", $premier_prenom_naissance);
            $statement->bindValue(":nom_naissance", $nom_naissance);
            $statement->bindValue(":sexe", $sexe_patient);
            $statement->bindValue(":date_naissance", $date_naissance);
            $statement->bindValue(":id_patient", $id_patient);
            $statement->bindValue(":code_insee_naissance", $code_insee_naissance);
            $statement->bindValue(":nom_utilise", $nom_utilise);
            $statement->bindValue(":prenom_utilise", $prenom_utilise);
            $statement->bindValue(":liste_prenom_naissance", $liste_prenom_naissance);
            $statement->bindValue(":id_type_statut_identite", "1"); // 'Provisoire'
            $statement->bindValue(":id_type_piece_identite", "1"); // 'Aucun'

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE patients');
            }

            //Adresse
            //recup id adresse
            $query = "
                SELECT id_adresse
                from patients
                where id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT id_adresse');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $id_adresse_patient = $data['id_adresse'] ?? null;

            if (empty($id_adresse_patient)) {
                throw new Exception("L'adresse du patient n'a pas été trouvée");
            }

            $query = "
                UPDATE adresse
                SET 
                    nom_adresse = :nom_adresse,
                    complement_adresse = :complement_adresse 
                WHERE id_adresse = :id_adresse";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":nom_adresse", $adresse_patient);
            $statement->bindValue(":complement_adresse", $complement_adresse_patient);
            $statement->bindValue(":id_adresse", $id_adresse_patient);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE adresse');
            }

            //Pour ville -> récupère id_ville associé puis dans localise à avec id_adresse
            $query = "
                SELECT id_ville
                FROM villes
                WHERE nom_ville = :nom_ville AND code_postal = :code_postal";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":nom_ville", $ville_patient);
            $statement->bindValue(":code_postal", $code_postal_patient);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT id_ville');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $id_ville = $data['id_ville'] ?? null;

            if (empty($id_ville)) {
                throw new Exception("La ville du patient n'a pas été trouvée");
            }

            // vérification si se_localise_a existe déjà
            $query = "
                SELECT COUNT(*) as se_localise_a_count
                FROM se_localise_a
                WHERE id_adresse = :id_adresse";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_adresse", $id_adresse_patient);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT COUNT(*) FROM se_localise_a');
            }
            $se_localise_a_count = intval($statement->fetch(PDO::FETCH_COLUMN, 0));

            if ($se_localise_a_count > 0) {
                //Modifie donc table se localise
                $query = "
                    UPDATE se_localise_a
                    SET id_ville = :id_ville
                    WHERE id_adresse = :id_adresse";
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_ville", $id_ville);
                $statement->bindValue(":id_adresse", $id_adresse_patient);
                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE se_localise_a');
                }
            } else {
                $query = '
                    INSERT INTO se_localise_a (id_ville, id_adresse)
                    VALUES (:id_ville, :id_adresse)';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(":id_ville", $id_ville);
                $statement->bindValue(":id_adresse", $id_adresse_patient);
                if (!$statement->execute()) {
                    throw new Exception('Error INSERT INTO se_localise_a');
                }
            }

            //CONTACT URGENCE
            //On récupère l'id coordonnées correspondant
            $query = "
                SELECT id_coordonnee
                from a_contacter_en_cas_urgence
                where id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error SELECT id_coordonnee');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $id_coordonnee_urgence = $data['id_coordonnee'] ?? null;

            if (empty($id_coordonnee_urgence)) {
                throw new Exception("id_coordonnee du patient n'a pas été trouvée");
            }

            $query = "
                UPDATE coordonnees
                SET 
                    nom_coordonnees = :nom_coordonnees,
                    prenom_coordonnees = :prenom_coordonnees,
                    tel_fixe_coordonnees = :tel_fixe_coordonnees,
                    tel_portable_coordonnees = :tel_portable_coordonnees
                WHERE id_coordonnees = :id_coordonnees";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":nom_coordonnees", $nom_urgence);
            $statement->bindValue(":prenom_coordonnees", $prenom_urgence);
            $statement->bindValue(":tel_fixe_coordonnees", $tel_fixe_urgence);
            $statement->bindValue(":tel_portable_coordonnees", $tel_portable_urgence);
            $statement->bindValue(":id_coordonnees", $id_coordonnee_urgence);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE coordonnees urgence');
            }

            $query = "
                UPDATE a_contacter_en_cas_urgence
                SET id_lien = :id_lien
                WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_lien", $id_lien);
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE a_contacter_en_cas_urgence');
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
     * required parameters:
     * [
     *     'id_patient' => string,
     *     'nom_naissance' => string,
     *     'premier_prenom_naissance' => string,
     *     'sexe_patient' => string,
     *     'date_naissance' => string,
     *     'matricule_ins' => string,
     *     'oid' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'code_insee_naissance' => string,
     *     'cle' => string,
     *     'liste_prenom_naissance' => string,
     *     'historique" => array
     * ]
     *
     * exemple d'un élémennt de l'array historique:
     * [
     *     'matricule' => [
     *         'numIdentifiant' => string,
     *         'cle' => string,
     *         'typeMatricule' =>string,
     *     ],
     *     'oid' => string,
     *     'dateDeb' => string,
     *     'dateFin' => string,
     * ]
     *
     * @param array $parameters
     * @return bool if the update was successful
     */
    public function updateInsData(array $parameters): bool
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['nom_naissance']) ||
            empty($parameters['premier_prenom_naissance']) ||
            empty($parameters['sexe_patient']) ||
            empty($parameters['date_naissance']) ||
            empty($parameters['matricule_ins']) ||
            empty($parameters['oid'])

        ) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $nom_naissance = EncryptionManager::encrypt($parameters['nom_naissance']);
            $premier_prenom_naissance = EncryptionManager::encrypt($parameters['premier_prenom_naissance']);
            $sexe_patient = $parameters['sexe_patient'];
            $date_naissance = $parameters['date_naissance'];

            $matricule_ins = EncryptionManager::encrypt($parameters['matricule_ins']);
            $oid = EncryptionManager::encrypt($parameters['oid']);

            // paramètres optionnels
            $code_insee_naissance = !empty($parameters['code_insee_naissance']) ? EncryptionManager::encrypt(
                $parameters['code_insee_naissance']
            ) : "";
            $liste_prenom_naissance = !empty($parameters['liste_prenom_naissance']) ? EncryptionManager::encrypt(
                $parameters['liste_prenom_naissance']
            ) : "";
            $cle = $parameters['cle'];

            $query = "
                UPDATE patients 
                SET premier_prenom_naissance = :premier_prenom_naissance,
                    nom_naissance = :nom_naissance,
                    sexe = :sexe,
                    date_naissance = :date_naissance,
                    matricule_ins = :matricule_ins,
                    cle = :cle,
                    oid = :oid,
                    code_insee_naissance = :code_insee_naissance,
                    liste_prenom_naissance = :liste_prenom_naissance,
                    id_type_statut_identite = :id_type_statut_identite
                WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":premier_prenom_naissance", $premier_prenom_naissance);
            $statement->bindValue(":nom_naissance", $nom_naissance);
            $statement->bindValue(":sexe", $sexe_patient);
            $statement->bindValue(":date_naissance", $date_naissance);
            $statement->bindValue(":id_patient", $id_patient);
            $statement->bindValue(":matricule_ins", $matricule_ins);
            $statement->bindValue(":oid", $oid);
            $statement->bindValue(":code_insee_naissance", $code_insee_naissance);
            $statement->bindValue(":liste_prenom_naissance", $liste_prenom_naissance);
            $statement->bindValue(":id_type_statut_identite", "2"); // 'Récupérée'
            if (!empty($cle)) {
                $statement->bindValue(":cle", $cle);
            } else {
                $statement->bindValue(":cle", null, PDO::PARAM_NULL);
            }

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE patients');
            }

            // suppression de l'historique des INS précédent
            $query = "
                DELETE FROM historique_ins_patient
                WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_patient", $id_patient);
            $statement->execute();

            // ajout de l'historique des INS
            if (is_array($parameters['historique'])) {
                foreach ($parameters['historique'] as $elem) {
                    $query = "
                        INSERT INTO historique_ins_patient (id_patient, num_identifiant, cle, type_matricule, oid, date_deb, date_fin) 
                        VALUES (:id_patient, :num_identifiant, :cle, :type_matricule, :oid, :date_deb, :date_fin)";
                    $statement = $this->pdo->prepare($query);
                    // obligatoire
                    $statement->bindValue(":id_patient", $id_patient);
                    // optionnel
                    if (is_null($elem['matricule']['numIdentifiant'])) {
                        $statement->bindValue(":num_identifiant", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":num_identifiant", $elem['matricule']['numIdentifiant']);
                    }
                    if (is_null($elem['matricule']['cle'])) {
                        $statement->bindValue(":cle", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":cle", $elem['matricule']['cle']);
                    }
                    if (is_null($elem['oid'])) {
                        $statement->bindValue(":oid", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":oid", $elem['oid']);
                    }
                    if (is_null($elem['matricule']['typeMatricule'])) {
                        $statement->bindValue(":type_matricule", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":type_matricule", $elem['matricule']['typeMatricule']);
                    }
                    if (is_null($elem['oid'])) {
                        $statement->bindValue(":oid", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":oid", $elem['oid']);
                    }
                    if (is_null($elem['dateDeb'])) {
                        $statement->bindValue(":date_deb", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":date_deb", $elem['dateDeb']);
                    }
                    if (is_null($elem['dateFin'])) {
                        $statement->bindValue(":date_fin", null, PDO::PARAM_NULL);
                    } else {
                        $statement->bindValue(":date_fin", $elem['dateFin']);
                    }

                    $statement->execute();
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

    public function updateEvaluateur($parameters)
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['id_user'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $id_user = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);

            $query = "
                UPDATE patients 
				SET id_user = :id_user
				WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_user", $id_user);
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE evaluateur du patient');
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    public function updateAntenne($parameters)
    {
        if (empty($parameters['id_patient']) ||
            empty($parameters['id_antenne'])) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $id_antenne = filter_var($parameters['id_antenne'], FILTER_SANITIZE_NUMBER_INT);

            $query = "
                UPDATE patients 
				SET id_antenne = :id_antenne
				WHERE id_patient = :id_patient";
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(":id_antenne", $id_antenne);
            $statement->bindValue(":id_patient", $id_patient);
            if (!$statement->execute()) {
                throw new Exception('Error UPDATE antenne du patient');
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
     * required parameters:
     * [
     *     'id_patient' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'date_debut_programme' => string,
     *     'intervalle' => string,
     * ]
     *
     * @param $parameters
     * @return bool if the update was successful
     */
    public function updateParcours($parameters)
    {
        if (empty($parameters['id_patient'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // obligatoire
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);

            // optionnel
            $date_debut_programme = isset($parameters['date_debut_programme']) ? trim(
                $parameters['date_debut_programme']
            ) : null;
            $date_admission = isset($parameters['date_admission']) ? trim(
                $parameters['date_admission']
            ) : null;
            $intervalle = isset($parameters['intervalle']) ? filter_var(
                $parameters['intervalle'],
                FILTER_SANITIZE_NUMBER_INT
            ) : null;
            $date_eval_suiv = isset($parameters['date_eval_suiv']) ? trim(
                $parameters['date_eval_suiv']
            ) : null;

            if (!empty($date_debut_programme)) {
                // verification si parcours existe
                $query = "
                    SELECT count(*) as parcours_count
                    FROM parcours
                    WHERE id_patient= :id_patient";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_patient', $id_patient);
                if (!$stmt->execute()) {
                    throw new Exception('Error SELECT count(*) as parcours_count');
                }
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                $parcours_count = $data['parcours_count'];

                if ($parcours_count == 0) {
                    $query = "
                        INSERT INTO parcours (id_patient, date_debut_programme)
                        VALUES (:id_patient, :date_debut_programme)";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_patient', $id_patient);
                    $stmt->bindValue(':date_debut_programme', $date_debut_programme);
                    if (!$stmt->execute()) {
                        throw new Exception('Error INSERT INTO parcours');
                    }
                } else {
                    $query = "
                        UPDATE parcours
                        SET date_debut_programme = :date_debut_programme
                        WHERE id_patient = :id_patient";
                    $stmt = $this->pdo->prepare($query);
                    $stmt->bindValue(':id_patient', $id_patient);
                    $stmt->bindValue(':date_debut_programme', $date_debut_programme);
                    if (!$stmt->execute()) {
                        throw new Exception('Error UPDATE parcours');
                    }
                }
            }

            if (!empty($intervalle)) {
                $query = '
                    UPDATE patients
                    SET intervalle = :intervalle
                    WHERE id_patient = :id_patient';

                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':intervalle', $intervalle, PDO::PARAM_INT);
                $stmt->bindValue(':id_patient', $id_patient);
                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE parcours SET intervalle');
                }
            }

            if (!empty($date_admission)) {
                $query = '
                    UPDATE patients
                    SET date_admission = :date_admission
                    WHERE id_patient = :id_patient';

                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':date_admission', $date_admission);
                $stmt->bindValue(':id_patient', $id_patient);
                if (!$stmt->execute()) {
                    throw new Exception('Error UPDATE parcours SET date_admission');
                }
            }

            $query = '
                UPDATE patients
                SET date_eval_suiv = :date_eval_suiv
                WHERE id_patient = :id_patient';

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':date_eval_suiv', $date_eval_suiv);
            $stmt->bindValue(':id_patient', $id_patient);
            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE parcours SET date_eval_suiv');
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
     * required parameters:
     * [
     *     'id_patient' => string,
     *     'consentement' => string, ("0", "1" ou "attente")
     * ]
     *
     * @param array $parameters
     * @return bool if the update was successful
     */
    public function updateConsentement(array $parameters): bool
    {
        if (!isset($parameters['id_patient']) ||
            !isset($parameters['consentement'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!($parameters['consentement'] == "0" ||
            $parameters['consentement'] == "1" ||
            $parameters['consentement'] == "attente")) {
            $this->errorMessage = "Le paramètre consentement est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $parameters['id_patient'] = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);

            $query = "
                UPDATE patients
                SET consentement = :consentement
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $parameters['id_patient']);

            if ($parameters['consentement'] == "attente") {
                $stmt->bindValue(':consentement', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':consentement', $parameters['consentement']);
            }
            $stmt->execute();

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * Fusionne 2 patients
     *
     * @param $id_patient_from
     * @param $id_patient_target
     * @return bool if the fusion was successful
     */
    public function fuse($id_patient_from, $id_patient_target)
    {
        if (empty($id_patient_from) || empty($id_patient_target)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = '
                SELECT patients.id_patient, c.id_coordonnees, patients.id_adresse
                FROM patients
                         JOIN coordonnees c on patients.id_coordonnee = c.id_coordonnees
                WHERE patients.id_patient = :id_patient';

            // check that $id_patient_from exists
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT patients.id_patient');
            }
            if ($statement->rowCount() == 0) {
                throw new Exception('Error id_patient_from n\'existe pas');
            }
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $id_coordonnees_from = $row['id_coordonnees'];
            $id_adresse_from = $row['id_adresse'];

            // check that $id_patient_target exists
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT patients.id_patient');
            }
            if ($statement->rowCount() == 0) {
                throw new Exception('Error id_patient_target n\'existe pas');
            }
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $id_coordonnees_target = $row['id_coordonnees'];
            $id_adresse_target = $row['id_adresse'];

            /////////////////////////////
            // prescription
            //////////////////////////////
            $query = '
                UPDATE prescription
                SET id_patient = :id_patient_target
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE prescription');
            }

            /////////////////////////////
            // objectif_patient
            //////////////////////////////
            $query = '
                UPDATE objectif_patient
                SET id_patient = :id_patient_target
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE objectif_patient');
            }

            /////////////////////////////
            // observation
            //////////////////////////////
            $query = '
                UPDATE observation
                SET id_patient = :id_patient_target
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE observation');
            }

            /////////////////////////////
            // questionnaire_instance
            //////////////////////////////
            $query = '
                UPDATE questionnaire_instance
                SET id_patient = :id_patient_target
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE questionnaire_instance');
            }

            /////////////////////////////
            // suppressions
            //////////////////////////////
            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            /////////////////////////////
            // a_participe_a (pour cette table la primary key est (id_patient, id_seance)
            //////////////////////////////
            // a_participe_a de id_patient_target
            $query = '
                SELECT id_seance
                FROM a_participe_a
                WHERE id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM a_participe_a (target)');
            }
            $seance_ids_target = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // a_participe_a de id_patient_from
            $query = '
                SELECT id_seance
                FROM a_participe_a
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM a_participe_a (from)');
            }
            $seance_ids_from = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($seance_ids_from as $id_seance) {
                if (in_array($id_seance, $seance_ids_target)) {
                    // suppression si déja présent dans target
                    $query = '
                        DELETE FROM a_participe_a
                        WHERE id_patient = :id_patient_from AND id_seance = :id_seance';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_patient_from', $id_patient_from);
                    $statement->bindValue(':id_seance', $id_seance);

                    if (!$statement->execute()) {
                        throw new Exception('Error DELETE FROM a_participe_a');
                    }
                }
            }

            $query = '
                UPDATE a_participe_a
                SET id_patient = :id_patient_target
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE a_participe_a');
            }

            /////////////////////////////
            // evaluations
            //////////////////////////////
            // nombre d'évaluation pour id_patient_from
            $query = '
                SELECT count(*) as evaluation_count
                FROM evaluations
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT count(*) as evaluation_count (from)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $evaluation_count_from = $data['evaluation_count'];

            // nombre d'évaluation pour id_patient_target
            $query = '
                SELECT count(*) as evaluation_count
                FROM evaluations
                WHERE id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT count(*) as evaluation_count (target)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $evaluation_count_target = $data['evaluation_count'];

            // on garde les evaluations de id_patient_target s'il a fait au moins autant
            // d'évaluations que id_patient_from, sinon on garde les evaluations de id_patient_from
            if (intval($evaluation_count_target) >= intval($evaluation_count_from)) {
                $evaluation = new Evaluation($this->pdo);
                $evaluation->deleteAllEvaluationPatient($id_patient_from);
            } else {
                $evaluation = new Evaluation($this->pdo);
                $evaluation->deleteAllEvaluationPatient($id_patient_target);

                $query = '
                    UPDATE evaluations
                    SET id_patient = :id_patient_target
                    WHERE id_patient = :id_patient_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_target', $id_patient_target);
                $statement->bindValue(':id_patient_from', $id_patient_from);

                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE evaluations');
                }
            }

            /////////////////////////////
            // activites_physiques
            //////////////////////////////
            // nombre d'activites_physiques pour id_patient_from
            $query = '
                SELECT count(*) as activites_physiques_count
                FROM activites_physiques
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT count(*) as activites_physiques_count (from)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $activites_physiques_count_from = $data['activites_physiques_count'];

            $query = '
                SELECT count(*) as activites_physiques_count
                FROM activites_physiques
                WHERE id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT count(*) as activites_physiques_count (target)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $activites_physiques_count_target = $data['activites_physiques_count'];

            // on garde les activites_physiques de id_patient_target s'il a fait au moins autant
            // d'activites_physiques que id_patient_from, sinon on garde les activites_physiques de id_patient_from
            if (intval($activites_physiques_count_target) >= intval($activites_physiques_count_from)) {
                $query = '
                    DELETE FROM activites_physiques
                    WHERE id_patient = :id_patient_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_from', $id_patient_from);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM activites_physiques (target>=from)');
                }
            } else {
                $query = '
                    DELETE FROM activites_physiques
                    WHERE id_patient = :id_patient_target';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_target', $id_patient_target);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM activites_physiques (target<from)');
                }

                $query = '
                    UPDATE activites_physiques
                    SET id_patient = :id_patient_target
                    WHERE id_patient = :id_patient_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_target', $id_patient_target);
                $statement->bindValue(':id_patient_from', $id_patient_from);

                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE activites_physiques');
                }
            }

            /////////////////////////////
            // orientation (il doit y avoir au max 1 par patient)
            //////////////////////////////
            // nombre d'orientation pour id_patient_from
            $query = '
                SELECT count(*) as orientation_count
                FROM orientation
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT count(*) as activites_physiques_count (from)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $orientation_count_from = $data['orientation_count'];

            $query = '
                SELECT count(*) as orientation_count
                FROM orientation
                WHERE id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT count(*) as activites_physiques_count (target)');
            }
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $orientation_count_target = $data['orientation_count'];

            // on garde les activites_physiques de id_patient_target s'il a fait au moins autant
            // d'activites_physiques que id_patient_from, sinon on garde les activites_physiques de id_patient_from
            if (intval($orientation_count_target) >= intval($orientation_count_from)) {
                $query = '
                    DELETE FROM orientation
                    WHERE id_patient = :id_patient_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_from', $id_patient_from);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM orientation (target>=from)');
                }
            } else {
                $query = '
                    DELETE FROM orientation
                    WHERE id_patient = :id_patient_target';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_target', $id_patient_target);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM orientation (target<from)');
                }

                $query = '
                    UPDATE orientation
                    SET id_patient = :id_patient_target
                    WHERE id_patient = :id_patient_from';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_patient_target', $id_patient_target);
                $statement->bindValue(':id_patient_from', $id_patient_from);

                if (!$statement->execute()) {
                    throw new Exception('Error UPDATE orientation');
                }
            }

            /////////////////////////////
            // liste_participants_creneau
            //////////////////////////////
            $query = '
                UPDATE liste_participants_creneau
                SET id_patient = :id_patient_target
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error UPDATE liste_participants_creneau');
            }

            // liste_participants_creneau de id_patient_target
            $query = '
                SELECT id_liste_participants_creneau, id_creneau
                FROM liste_participants_creneau
                WHERE id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM liste_participants_creneau (from)');
            }
            $liste_participants_creneaux = $statement->fetchAll(PDO::FETCH_ASSOC);

            $creneaux_ids = [];
            if ($liste_participants_creneaux) {
                foreach ($liste_participants_creneaux as $liste_participants_creneau) {
                    if (in_array($liste_participants_creneau['id_creneau'], $creneaux_ids)) {
                        // suppression des doublons (même id_creneau)
                        $query = '
                            DELETE FROM liste_participants_creneau
                            WHERE id_liste_participants_creneau = :id_liste_participants_creneau';
                        $statement = $this->pdo->prepare($query);
                        $statement->bindValue(
                            ':id_liste_participants_creneau',
                            $liste_participants_creneau['id_liste_participants_creneau']
                        );

                        if (!$statement->execute()) {
                            throw new Exception('Error DELETE FROM liste_participants_creneau');
                        }
                    }

                    $creneaux_ids[] = $liste_participants_creneau['id_creneau'];
                }
            }

            /////////////////////////////
            // suit
            //////////////////////////////
            // id_medecin en commun
            $query = '
                SELECT DISTINCT id_medecin
                FROM suit
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_medecin (suit)');
            }
            $id_medecins = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // suppression suit
            $query = '
                DELETE
                FROM suit
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM suit');
            }

            // insertion suit des id_medecin commun
            if ($id_medecins) {
                foreach ($id_medecins as $id_medecin) {
                    $query = '
                        INSERT INTO suit (id_patient, id_medecin)
                        VALUES (:id_patient_target, :id_medecin)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_patient_target', $id_patient_target);
                    $statement->bindValue(':id_medecin', $id_medecin);

                    if (!$statement->execute()) {
                        throw new Exception('Error INSERT INTO suit');
                    }
                }
            }

            /////////////////////////////
            // traite
            //////////////////////////////
            // id_medecin en commun
            $query = '
                SELECT DISTINCT id_medecin
                FROM traite
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_medecin (traite)');
            }
            $id_medecins = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // suppression traite
            $query = '
                DELETE
                FROM traite
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM traite');
            }

            // insertion traite des id_medecin commun
            if ($id_medecins) {
                foreach ($id_medecins as $id_medecin) {
                    $query = '
                        INSERT INTO traite (id_patient, id_medecin)
                        VALUES (:id_patient_target, :id_medecin)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_patient_target', $id_patient_target);
                    $statement->bindValue(':id_medecin', $id_medecin);

                    if (!$statement->execute()) {
                        throw new Exception('Error INSERT INTO traite');
                    }
                }
            }

            /////////////////////////////
            // prescrit
            //////////////////////////////
            // id_medecin en commun
            $query = '
                SELECT DISTINCT id_medecin
                FROM prescrit
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_medecin (prescrit)');
            }
            $id_medecins = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // suppression traite
            $query = '
                DELETE
                FROM prescrit
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM prescrit');
            }

            // insertion prescrit des id_medecin commun
            if ($id_medecins) {
                foreach ($id_medecins as $id_medecin) {
                    $query = '
                        INSERT INTO prescrit (id_patient, id_medecin)
                        VALUES (:id_patient_target, :id_medecin)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_patient_target', $id_patient_target);
                    $statement->bindValue(':id_medecin', $id_medecin);

                    if (!$statement->execute()) {
                        throw new Exception('Error INSERT INTO prescrit');
                    }
                }
            }

            /////////////////////////////
            // adresse
            //////////////////////////////
            $query = '
                DELETE
                FROM adresse
                WHERE id_adresse = :id_adresse_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_adresse_from', $id_adresse_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM adresse');
            }

            /////////////////////////////
            // parcours
            //////////////////////////////
            $query = '
                DELETE
                FROM parcours
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM parcours');
            }

            /////////////////////////////
            // coordonnees du patient
            //////////////////////////////
            $query = '
                DELETE
                FROM coordonnees
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            /////////////////////////////
            // coordonnees du contact d'urgence
            //////////////////////////////
            $query = '
                SELECT id_coordonnee
                FROM a_contacter_en_cas_urgence
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT FROM a_contacter_en_cas_urgence');
            }
            $data_urgence = $statement->fetch(PDO::FETCH_ASSOC);
            $id_coordonnees_urgence = $data_urgence['id_coordonnee'] ?? null;

            if ($id_coordonnees_urgence) {
                // suppression coordonnees contact urgence
                $query = '
                    DELETE
                    FROM coordonnees
                    WHERE id_coordonnees = :id_coordonnees';
                $statement = $this->pdo->prepare($query);
                $statement->bindValue(':id_coordonnees', $id_coordonnees_urgence);

                if (!$statement->execute()) {
                    throw new Exception('Error DELETE FROM coordonnees (contact urgence)');
                }
            }

            /////////////////////////////
            // a_contacter_en_cas_urgence
            //////////////////////////////
            $query = '
                DELETE
                FROM a_contacter_en_cas_urgence
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM a_contacter_en_cas_urgence');
            }

            /////////////////////////////
            // oriente_vers
            //////////////////////////////
            // id_structure en commun
            $query = '
                SELECT DISTINCT id_structure
                FROM oriente_vers
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_structure');
            }
            $id_structures = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // suppression oriente_vers
            $query = '
                DELETE
                FROM oriente_vers
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM oriente_vers');
            }

            // insertion oriente_vers des id_structure communes
            if ($id_structures) {
                foreach ($id_structures as $id_structure) {
                    $query = '
                        INSERT INTO oriente_vers (id_patient, id_structure)
                        VALUES (:id_patient_target, :id_structure)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_patient_target', $id_patient_target);
                    $statement->bindValue(':id_structure', $id_structure);

                    if (!$statement->execute()) {
                        throw new Exception('Error INSERT INTO oriente_vers');
                    }
                }
            }

            /////////////////////////////
            // souffre_de
            //////////////////////////////
            // id_pathologie_ou_etat en commun
            $query = '
                SELECT DISTINCT id_pathologie_ou_etat
                FROM souffre_de
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error SELECT DISTINCT id_pathologie_ou_etat');
            }
            $id_pathologie_ou_etats = $statement->fetchAll(PDO::FETCH_COLUMN, 0);

            // suppression de l'id -1 si nécessaire
            if ($id_pathologie_ou_etats && count($id_pathologie_ou_etats) > 1) {
                $id_pathologie_ou_etats = array_filter($id_pathologie_ou_etats, function ($val) {
                    return $val != "-1";
                });
            }

            // suppression souffre_de
            $query = '
                DELETE
                FROM souffre_de
                WHERE id_patient = :id_patient_from OR id_patient = :id_patient_target';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);
            $statement->bindValue(':id_patient_target', $id_patient_target);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM souffre_de');
            }

            // insertion souffre_de des id_pathologie_ou_etat communes
            if ($id_pathologie_ou_etats) {
                foreach ($id_pathologie_ou_etats as $id_pathologie_ou_etat) {
                    $query = '
                        INSERT INTO souffre_de (id_patient, id_pathologie_ou_etat)
                        VALUES (:id_patient_target, :id_pathologie_ou_etat)';
                    $statement = $this->pdo->prepare($query);
                    $statement->bindValue(':id_patient_target', $id_patient_target);
                    $statement->bindValue(':id_pathologie_ou_etat', $id_pathologie_ou_etat);

                    if (!$statement->execute()) {
                        throw new Exception('Error INSERT INTO souffre_de');
                    }
                }
            }

            /////////////////////////////
            // patients
            //////////////////////////////
            $query = '
                DELETE
                FROM patients
                WHERE id_patient = :id_patient_from';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient_from', $id_patient_from);

            if (!$statement->execute()) {
                throw new Exception('Error DELETE FROM patients');
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
     * @param $id_patient l'id du patient à supprimer
     * @return bool si la suppression a réussi
     */
    public function delete($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();
            ////////////////////////////////////////////////////
            // Verification si le patient à des prescriptions
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) as nb_prescrition
                from prescription
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_prescrition']) > 0) {
                    throw new Exception('Le bénéficiaire a au moins une prescription');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_prescrition');
            }

            ////////////////////////////////////////////////////
            // Verification si le patient à des évaluations
            ////////////////////////////////////////////////////
            $query = '
                    SELECT count(*) as nb_eval
                    from evaluations
                    WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_eval']) > 0) {
                    throw new Exception('Le bénéficiaire a au moins une évaluation');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_eval');
            }

            ////////////////////////////////////////////////////
            // Verification si le patient à des ALD
            ////////////////////////////////////////////////////
            $query = "
                    SELECT count(*) as nb_ald
                    FROM souffre_de
                    WHERE id_patient = :id_patient
                      AND souffre_de.id_pathologie_ou_etat <> -1";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_ald']) > 0) {
                    throw new Exception('Les ALDs du bénéficiaire sont renseignées');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_ald');
            }

            ////////////////////////////////////////////////////
            // Verification si le patient à des activités physique
            ////////////////////////////////////////////////////
            $query = "
                    SELECT count(*) as nb_activite_physique
                    FROM activites_physiques
                    WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_activite_physique']) > 0) {
                    throw new Exception('Les activités physiques du bénéficiaire sont renseignées');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_activite_physique');
            }

            ////////////////////////////////////////////////////
            // Verification si le patient à des questionnaires
            ////////////////////////////////////////////////////
            $query = '
                    SELECT count(*) as nb_questionnaire
                    FROM questionnaire_instance
                    WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_questionnaire']) > 0) {
                    throw new Exception('Le bénéficiaire a au moins un questionnaire');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_questionnaire');
            }

            ////////////////////////////////////////////////////
            // Verification si le patient à des objectifs
            ////////////////////////////////////////////////////
            $query = "
                    SELECT count(*) as nb_objectif
                    FROM objectif_patient
                    WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_objectif']) > 0) {
                    throw new Exception('Le bénéficiaire a au moins un objectif');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_traite');
            }

            ////////////////////////////////////////////////////
            // Verification si le patient est orienté
            ////////////////////////////////////////////////////
            $query = '
                    SELECT count(*) as nb_orientation
                    FROM orientation
                    WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (intval($data['nb_orientation']) > 0) {
                    throw new Exception('Le bénéficiaire est orienté');
                }
            } else {
                throw new Exception('Error SELECT count(*) as nb_orientation');
            }

            ////////////////////////////////////////////////////
            // Suppression du patient
            ////////////////////////////////////////////////////

            if (!$this->pdo->query("SET foreign_key_checks=0")) {
                throw new Exception('Error disabling foreign key checks');
            }

            ////////////////////////////////////////////////////
            // DELETE coordonnees du patient
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM coordonnees
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM coordonnees');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id_adresse
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_adresse
                FROM patients
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $id_adresse = $data['id_adresse'];
                if (empty($id_adresse)) {
                    throw new Exception(
                        'Error: L\'id_adresse du patient \'' . $id_patient . '\'  n\'a pas été trouvé dans la BDD'
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
            // DELETE parcours
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM parcours
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM parcours');
            }

            ////////////////////////////////////////////////////
            // Récupération de l'id_coordonnee du contact d'urgence
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_coordonnee as id_coordonnee_contact
                FROM a_contacter_en_cas_urgence
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if ($stmt->execute()) {
                $data = $stmt->fetch();
                $id_coordonnee_contact = $data['id_coordonnee_contact'];
            } else {
                throw new Exception('Error select id_coordonnee as id_coordonnee_contact');
            }

            ////////////////////////////////////////////////////
            // DELETE a_contacter_en_cas_urgence
            ////////////////////////////////////////////////////
            $query = '
                DELETE
                FROM a_contacter_en_cas_urgence
                WHERE id_patient = :id_patient';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM a_contacter_en_cas_urgence');
            }

            ////////////////////////////////////////////////////
            // DELETE coordonnees du contact d'urgence patient
            ////////////////////////////////////////////////////
            if ($id_coordonnee_contact != null) {
                $query = '
                    DELETE
                    FROM coordonnees
                    WHERE id_coordonnees = :id_coordonnee_contact';
                $stmt = $this->pdo->prepare($query);
                $stmt->bindValue(':id_coordonnee_contact', $id_coordonnee_contact);

                if (!$stmt->execute()) {
                    throw new Exception('Error DELETE FROM coordonnees');
                }
            }

            ////////////////////////////////////////////////////
            // DELETE prescrit
            ////////////////////////////////////////////////////
            $query = "
                DELETE
                FROM prescrit
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM prescrit');
            }

            ////////////////////////////////////////////////////
            // DELETE traite
            ////////////////////////////////////////////////////
            $query = "
                DELETE
                FROM traite
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM traite');
            }

            ////////////////////////////////////////////////////
            // DELETE suit
            ////////////////////////////////////////////////////
            $query = "
                DELETE
                FROM suit
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM suit');
            }

            ////////////////////////////////////////////////////
            // DELETE oriente_vers
            ////////////////////////////////////////////////////
            $query = "
                DELETE
                FROM oriente_vers
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM oriente_vers');
            }

            ////////////////////////////////////////////////////
            // DELETE souffre_de
            ////////////////////////////////////////////////////
            $query = "
                DELETE
                FROM souffre_de
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM souffre_de');
            }

            ////////////////////////////////////////////////////
            // DELETE patients
            ////////////////////////////////////////////////////
            $query = "
                DELETE
                FROM patients
                WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);

            if (!$stmt->execute()) {
                throw new Exception('Error DELETE FROM patients');
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
     * Vérification de l'identité d'un' patient qui a le statut "récupéré" avec une pièce d’identité à haut niveau de
     * confiance Update du statut de confiance d'un patient vers "Qualifiée"
     *
     * @param $id_patient
     * @param $id_type_statut_identite
     * @return bool si la vérification a été effectué avec succès
     */
    public function verifyIdentity($id_patient, $id_type_piece_identite): bool
    {
        if (empty($id_patient) || empty($id_type_piece_identite)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if ($id_type_piece_identite == "1") {
            $this->errorMessage = "Le type de pièce d'identité est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            ////////////////////////////////////////////////////
            // verification si le patient existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_type_statut_identite
                FROM patients
                WHERE id_patient = :id_patient';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient', $id_patient);

            if ($statement->execute()) {
                if ($statement->rowCount() == 0) {
                    throw new Exception('Ce patient n\'existe pas');
                }
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                $id_type_statut_identite = $data['id_type_statut_identite'];
            } else {
                throw new Exception('Error SELECT id_type_statut_identite');
            }

            if ($id_type_statut_identite != 2) {
                throw new Exception('Ce patient n\'a pas le statut d\'identité "Récupérée"');
            }

            // update
            $query = '
                UPDATE patients
                SET id_type_piece_identite = :id_type_piece_identite,
                    id_type_statut_identite = :id_type_statut_identite
                WHERE id_patient = :id_patient';

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_type_piece_identite', $id_type_piece_identite);
            $statement->bindValue(':id_type_statut_identite', "4"); // "Qualifiée"
            $statement->bindValue(':id_patient', $id_patient);

            if (!$statement->execute()) {
                throw new Exception('Error update patient');
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
     * Sets the est_archive field of a patient
     *
     * @param $id_patient
     * @param $is_archived
     * @return bool
     */
    public function setArchiveStatus($id_patient, $is_archived): bool
    {
        if (empty($id_patient)) {
            return false;
        }

        if (gettype($is_archived) != "boolean") {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $est_archive = $is_archived ? "1" : "0";

            ////////////////////////////////////////////////////
            // verification si le patient existe
            ////////////////////////////////////////////////////
            $query = '
                SELECT count(*) AS patient_count
                FROM patients
                WHERE id_patient = :id_patient';
            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient', $id_patient);

            if ($statement->execute()) {
                $data = $statement->fetch(PDO::FETCH_ASSOC);
                if (intval($data['patient_count']) == 0) {
                    throw new Exception('Error: Cet patient n\'existe pas');
                }
            } else {
                throw new Exception('Error SELECT count(*) AS patient_count');
            }

            // update
            $query = '
                UPDATE patients
                SET est_archive = :est_archive,
                    date_archivage = CURRENT_DATE
                WHERE id_patient = :id_patient';

            $statement = $this->pdo->prepare($query);
            $statement->bindValue(':id_patient', $id_patient);
            $statement->bindValue(':est_archive', $est_archive);

            if (!$statement->execute()) {
                throw new Exception('Error update patient');
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
     * @param $id_patient
     * @return array|false Return an associative array or false on failure
     */
    public function readOne($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = "
            SELECT p.id_patient,
                   premier_prenom_naissance,
                   nom_naissance,
                   matricule_ins,
                   cle,
                   oid,
                   code_insee_naissance,
                   nom_utilise,
                   prenom_utilise,
                   liste_prenom_naissance,
                   IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance) as nom_patient,
                   IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                      premier_prenom_naissance)                                                  as prenom_patient,
                   sexe                                                                          as sexe_patient,
                   date_naissance,
                   nom_adresse,
                   complement_adresse,
                   code_postal,
                   nom_ville,
                   mail_coordonnees                                                              as email_patient,
                   tel_fixe_coordonnees                                                          as tel_fixe_patient,
                   tel_portable_coordonnees                                                      as tel_portable_patient,
                   est_pris_en_charge_financierement,
                   hauteur_prise_en_charge_financierement,
                   sit_part_autre,
                   sit_part_education_therapeutique,
                   sit_part_grossesse,
                   sit_part_prevention_chute,
                   sit_part_sedentarite,
                   est_dans_zrr,
                   est_dans_qpv,
                   est_archive,
                   est_non_peps,
                   p.id_user,
                   p.id_mutuelle,
                   p.id_antenne,
                   antenne.id_structure,
                   consentement,
                   id_type_statut_identite,
                   id_type_piece_identite,
                   p.id_territoire
            FROM patients p
                     JOIN coordonnees c ON p.id_patient = c.id_patient
                     JOIN adresse a ON a.id_adresse = p.id_adresse
                     JOIN antenne on p.id_antenne = antenne.id_antenne
                     LEFT JOIN se_localise_a loc ON loc.id_adresse = a.id_adresse
                     LEFT JOIN villes v ON v.id_ville = loc.id_ville
            WHERE p.id_patient = :id_patient";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$patient) {
            return false;
        }

        $patient['nom_patient'] = !empty($patient['nom_patient']) ? EncryptionManager::decrypt(
            $patient['nom_patient']
        ) : "";
        $patient['prenom_patient'] = !empty($patient['prenom_patient']) ? ChaineCharactere::mb_ucfirst(
            mb_strtolower(
                EncryptionManager::decrypt(
                    $patient['prenom_patient']
                )
            )
        ) : "";
        $patient['premier_prenom_naissance'] = !empty($patient['premier_prenom_naissance']) ? EncryptionManager::decrypt(
            $patient['premier_prenom_naissance']
        ) : "";
        $patient['nom_naissance'] = !empty($patient['nom_naissance']) ? EncryptionManager::decrypt(
            $patient['nom_naissance']
        ) : "";
        $patient['matricule_ins'] = !empty($patient['matricule_ins']) ? EncryptionManager::decrypt(
            $patient['matricule_ins']
        ) : "";
        $patient['oid'] = !empty($patient['oid']) ? EncryptionManager::decrypt($patient['oid']) : "";

        $nature_oid = self::NATURE_OID_INCONNU;
        if (in_array($patient['oid'], self::OIDS_NIA)) {
            $nature_oid = self::NATURE_OID_NIA;
        } elseif (in_array($patient['oid'], self::OIDS_NIR)) {
            $nature_oid = self::NATURE_OID_NIR;
        }
        $patient['nature_oid'] = $nature_oid;

        $patient['code_insee_naissance'] = !empty($patient['code_insee_naissance']) ? EncryptionManager::decrypt(
            $patient['code_insee_naissance']
        ) : "";
        $patient['nom_utilise'] = !empty($patient['nom_utilise']) ? EncryptionManager::decrypt(
            $patient['nom_utilise']
        ) : "";
        $patient['prenom_utilise'] = !empty($patient['prenom_utilise']) ? EncryptionManager::decrypt(
            $patient['prenom_utilise']
        ) : "";
        $patient['liste_prenom_naissance'] = !empty($patient['liste_prenom_naissance']) ? EncryptionManager::decrypt(
            $patient['liste_prenom_naissance']
        ) : "";
        $patient['email_patient'] = !empty($patient['email_patient']) ? EncryptionManager::decrypt(
            $patient['email_patient']
        ) : "";
        $patient['tel_fixe_patient'] = !empty($patient['tel_fixe_patient']) ? EncryptionManager::decrypt(
            $patient['tel_fixe_patient']
        ) : "";
        $patient['tel_portable_patient'] = !empty($patient['tel_portable_patient']) ? EncryptionManager::decrypt(
            $patient['tel_portable_patient']
        ) : "";
        $patient['nom_adresse'] = !empty($patient['nom_adresse']) ? EncryptionManager::decrypt(
            $patient['nom_adresse']
        ) : "";
        $patient['complement_adresse'] = !empty($patient['complement_adresse']) ? EncryptionManager::decrypt(
            $patient['complement_adresse']
        ) : "";

        //REQUETE CONTACT URGENCE pour récupérer les informations relatives au contact d'urgence
        $query = '
            SELECT nom_coordonnees as nom_contact_urgence,
                   prenom_coordonnees as prenom_contact_urgence,
                   tel_fixe_coordonnees as tel_fixe_contact_urgence,
                   tel_portable_coordonnees as tel_portable_contact_urgence,
                   l.id_lien,
                   l.type_lien
            FROM coordonnees
                     JOIN a_contacter_en_cas_urgence ON coordonnees.id_coordonnees = a_contacter_en_cas_urgence.id_coordonnee
                     JOIN liens l on a_contacter_en_cas_urgence.id_lien = l.id_lien
            WHERE a_contacter_en_cas_urgence.id_patient = :id_patient';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();
        $contact_urgence = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$contact_urgence) {
            $contact_urgence = [
                'nom_contact_urgence' => null,
                'prenom_contact_urgence' => null,
                'tel_fixe_contact_urgence' => null,
                'tel_portable_contact_urgence' => null,
                'id_lien' => null,
                'type_lien' => null,
            ];
        }

        $contact_urgence['nom_contact_urgence'] = !empty($contact_urgence['nom_contact_urgence']) ? EncryptionManager::decrypt(
            $contact_urgence['nom_contact_urgence']
        ) : "";
        $contact_urgence['prenom_contact_urgence'] = !empty($contact_urgence['prenom_contact_urgence']) ? EncryptionManager::decrypt(
            $contact_urgence['prenom_contact_urgence']
        ) : "";
        $contact_urgence['tel_fixe_contact_urgence'] = !empty($contact_urgence['tel_fixe_contact_urgence']) ? EncryptionManager::decrypt(
            $contact_urgence['tel_fixe_contact_urgence']
        ) : "";
        $contact_urgence['tel_portable_contact_urgence'] = !empty($contact_urgence['tel_portable_contact_urgence']) ? EncryptionManager::decrypt(
            $contact_urgence['tel_portable_contact_urgence']
        ) : "";

        $patient = array_merge($patient, $contact_urgence);

        // caisse assurance maladie
        $query = "
            SELECT nom_regime,
                   nom_ville as nom_ville_cam,
                   code_postal as code_postal_cam,
                   cam.id_caisse_assurance_maladie
            FROM caisse_assurance_maladie cam
                     JOIN reside r ON r.id_caisse_assurance_maladie = cam.id_caisse_assurance_maladie
                     JOIN patients p ON p.id_reside = r.id_reside
                     JOIN villes v ON v.id_ville = r.id_ville
            WHERE p.id_patient = :id_patient";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        $stmt->execute();
        $cam = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cam) {
            $cam = [
                'nom_regime' => null,
                'nom_ville_cam' => null,
                'code_postal_cam' => null,
                'id_caisse_assurance_maladie' => null,
            ];
        }

        return array_merge($patient, $cam);
    }

    /**
     * session parameters:
     * [
     *     'id_role_user' => string,
     *     'est_coordinateur_peps' => bool,
     *     'id_statut_structure' => string|null, // null si id_role_user=1
     *     'id_territoire' => string,
     *     'id_structure' => string|null, // null si id_role_user=1
     *     'id_user' => string,
     * ]
     *
     * @param $session
     * @param $est_archive
     * @return array|false
     * @throws Exception
     */
    public function readAll($session, $est_archive)
    {
        if (empty($session['role_user_ids']) ||
            !isset($session['est_coordinateur_peps']) ||
            empty($session['id_territoire']) ||
            empty($session['id_user']) ||
            (empty($session['id_structure']) && !in_array("1", $session['role_user_ids'])) ||
            (empty($session['id_statut_structure']) && !in_array("1", $session['role_user_ids']))) {
            return false;
        }

        $permission = new Permissions($session);

        // requête particulière pour l'intervenant
        if ($permission->hasRole(Permissions::INTERVENANT) &&
            !$permission->isIntervenantAndOtherRole()) {
            // TODO filtre selon archive
            return $this->readAllOriente($session['id_structure']);
        }
        // pas d'accès pour les superviseurs
        if ($permission->hasRole(Permissions::SUPERVISEUR_PEPS)) {
            return false;
        }

        $patients = [];

        $main_query = "
            SELECT DISTINCT premier_prenom_naissance,
                            nom_naissance,
                            matricule_ins,
                            oid,
                            cle,
                            code_insee_naissance,
                            nom_utilise,
                            prenom_utilise,
                            liste_prenom_naissance,
                            IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)                     as nom_patient,
                            IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                               premier_prenom_naissance)                                                                      as prenom_patient,
                            c.tel_fixe_coordonnees                                                                            as tel_fixe_patient,
                            c.tel_portable_coordonnees                                                                        as tel_portable_patient,
                            c.mail_coordonnees                                                                                as mail_patient,
                            cmed.nom_coordonnees                                                                              as nom_medecin,
                            cmed.prenom_coordonnees                                                                           as prenom_medecin,
                            cuser.nom_coordonnees                                                                             as nom_suivi,
                            cuser.prenom_coordonnees                                                                          as prenom_suivi,
                            p.id_user,
                            p.id_patient,
                            DATE_FORMAT(p.date_admission, '%d/%m/%Y')                                                         as date_admission,
                            nom_antenne,
                            s.nom_structure,
                            intervalle,
                            DATE_FORMAT(p.date_archivage, '%d/%m/%Y')                                                         as date_archivage,
                            est_non_peps,
                            p.est_archive,
                            p.date_eval_suiv
            FROM patients p
                     JOIN coordonnees c on p.id_coordonnee = c.id_coordonnees
                     JOIN antenne a on a.id_antenne = p.id_antenne
                     JOIN structure s on a.id_structure = s.id_structure
                     LEFT JOIN prescrit ON p.id_patient = prescrit.id_patient
                     LEFT JOIN medecins m on prescrit.id_medecin = m.id_medecin
                     LEFT JOIN coordonnees cmed on m.id_medecin = cmed.id_medecin
                     LEFT JOIN coordonnees cuser on cuser.id_user = p.id_user
                     LEFT JOIN users u on cuser.id_user = u.id_user
            WHERE est_archive = :est_archive ";

        // filtres selon les rôles
        if ($permission->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            $main_query .= ' AND p.id_territoire = :id_territoire 
                             AND p.est_non_peps = 0 '; // les coordo PEPS ne doivent pas voir les patients non-PEPS
        } elseif (
            $permission->hasRole(Permissions::RESPONSABLE_STRUCTURE) ||
            $permission->hasRole(Permissions::REFERENT) ||
            $permission->hasRole(Permissions::SECRETAIRE)) {
            $main_query .= ' AND s.id_structure = :id_structure ';
        } elseif ($permission->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permission->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $main_query .= ' AND (s.id_structure = :id_structure
                             OR u.id_user = :id_user) ';
        } elseif ($permission->hasRole(Permissions::EVALUATEUR)) {
            $main_query .= ' AND u.id_user = :id_user ';
        }

        $stmt = $this->pdo->prepare($main_query);

        $stmt->bindValue(':est_archive', $est_archive);
        if ($permission->hasRole(Permissions::COORDONNATEUR_PEPS)) {
            $stmt->bindValue(':id_territoire', $session['id_territoire']);
        } elseif (
            $permission->hasRole(Permissions::RESPONSABLE_STRUCTURE) ||
            $permission->hasRole(Permissions::REFERENT) ||
            $permission->hasRole(Permissions::SECRETAIRE)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
        } elseif ($permission->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permission->hasRole(Permissions::COORDONNATEUR_NON_MSS)) {
            $stmt->bindValue(':id_structure', $session['id_structure']);
            $stmt->bindValue(':id_user', $session['id_user']);
        } elseif ($permission->hasRole(Permissions::EVALUATEUR)) {
            $stmt->bindValue(':id_user', $session['id_user']);
        }

        $stmt->execute();
        $u = new User($this->pdo);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['nom_patient'] = !empty($row['nom_patient']) ? EncryptionManager::decrypt(
                $row['nom_patient']
            ) : "";
            $row['prenom_patient'] = !empty($row['prenom_patient']) ? EncryptionManager::decrypt(
                $row['prenom_patient']
            ) : "";
            $row['premier_prenom_naissance'] = !empty($row['premier_prenom_naissance']) ? EncryptionManager::decrypt(
                $row['premier_prenom_naissance']
            ) : "";
            $row['nom_naissance'] = !empty($row['nom_naissance']) ? EncryptionManager::decrypt(
                $row['nom_naissance']
            ) : "";
            $row['matricule_ins'] = !empty($row['matricule_ins']) ? EncryptionManager::decrypt(
                $row['matricule_ins']
            ) : "";
            $row['oid'] = !empty($row['oid']) ? EncryptionManager::decrypt($row['oid']) : "";
            $row['code_insee_naissance'] = !empty($row['code_insee_naissance']) ? EncryptionManager::decrypt(
                $row['code_insee_naissance']
            ) : "";
            $row['nom_utilise'] = !empty($row['nom_utilise']) ? EncryptionManager::decrypt(
                $row['nom_utilise']
            ) : "";
            $row['prenom_utilise'] = !empty($row['prenom_utilise']) ? EncryptionManager::decrypt(
                $row['prenom_utilise']
            ) : "";
            $row['liste_prenom_naissance'] = !empty($row['liste_prenom_naissance']) ? EncryptionManager::decrypt(
                $row['liste_prenom_naissance']
            ) : "";
            $row['mail_patient'] = !empty($row['mail_patient']) ? EncryptionManager::decrypt(
                $row['mail_patient']
            ) : "";
            $row['tel_fixe_patient'] = !empty($row['tel_fixe_patient']) ? EncryptionManager::decrypt(
                $row['tel_fixe_patient']
            ) : "";
            $row['tel_portable_patient'] = !empty($row['tel_portable_patient']) ? EncryptionManager::decrypt(
                $row['tel_portable_patient']
            ) : "";
            $row['nom_adresse'] = !empty($row['nom_adresse']) ? EncryptionManager::decrypt(
                $row['nom_adresse']
            ) : "";
            $row['complement_adresse'] = !empty($row['complement_adresse']) ? EncryptionManager::decrypt(
                $row['complement_adresse']
            ) : "";

            // mettre en bool si le patient est archivé
            $row['est_archive'] = $row['est_archive'] == 1;

            // récupération de la dernière évaluation
            $query_item = '
                SELECT id_type_eval, date_eval
                FROM evaluations
                WHERE id_patient = :id_patient
                ORDER BY id_type_eval DESC
                LIMIT 1';
            $stmt_item = $this->pdo->prepare($query_item);
            $stmt_item->bindValue(':id_patient', $row['id_patient']);
            $stmt_item->execute();
            $data = $stmt_item->fetch(PDO::FETCH_ASSOC);
            $row['id_type_eval'] = $data['id_type_eval'] ?? null;
            $row['date_eval'] = $data['date_eval'] ?? null;
            $row['role_user_suivi'] = $u->getRoles($row['id_user']) ?? "";

            // récupération si le patient a une prescription
            $query_item = '
                SELECT COUNT(*) as count_prescription
                FROM prescription
                WHERE id_patient = :id_patient';
            $stmt_item = $this->pdo->prepare($query_item);
            $stmt_item->bindValue(':id_patient', $row['id_patient']);
            $stmt_item->execute();
            $data = $stmt_item->fetch(PDO::FETCH_ASSOC);
            $row['a_prescription'] = intval($data['count_prescription']) > 0;

            // récupération si le patient a terminé le programme
            $query_item = '
                SELECT COUNT(*) as count_questionnaire_instance
                FROM questionnaire_instance
                WHERE id_questionnaire = 5
                  AND id_patient = :id_patient';
            $stmt_item = $this->pdo->prepare($query_item);
            $stmt_item->bindValue(':id_patient', $row['id_patient']);
            $stmt_item->execute();
            $data = $stmt_item->fetch(PDO::FETCH_ASSOC);
            $row['a_termine_programme'] = intval($data['count_questionnaire_instance']) > 0;

            $patients[] = $row;
        }

        return $patients;
    }

    /**
     * Récupération des bénéficiaires suivis par l'utilisateur connecté
     *
     * @param session La session de l'utilisateur
     *
     * @return array|false @return array|false return an array of associative arrays or false on failure
     */
    public function readAllSuivi($session)
    {
        if (empty($session['id_user']) ||
            !isset($session['est_coordinateur_peps']) ||
            empty($session['role_user_ids']) ||
            empty($session['id_statut_structure'])) {
            return false;
        }

        $permission = new Permissions($session);

        // pas d'accès pour tous les rôles sauf coordonnateurs PEPS et MSS
        if (!$permission->hasRole(Permissions::COORDONNATEUR_PEPS)
            && !$permission->hasRole(Permissions::COORDONNATEUR_MSS)) {
            return false;
        }

        $patients = [];

        $main_query = "
            SELECT DISTINCT premier_prenom_naissance,
                            nom_naissance,
                            matricule_ins,
                            oid,
                            code_insee_naissance,
                            nom_utilise,
                            prenom_utilise,
                            liste_prenom_naissance,
                            IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)                     as nom_patient,
                            IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                               premier_prenom_naissance)                                                                      as prenom_patient,
                            c.tel_fixe_coordonnees                                                                            as tel_fixe_patient,
                            c.tel_portable_coordonnees                                                                        as tel_portable_patient,
                            c.mail_coordonnees                                                                                as mail_patient,
                            cmed.nom_coordonnees                                                                              as nom_medecin,
                            cmed.prenom_coordonnees                                                                           as prenom_medecin,
                            cuser.nom_coordonnees                                                                             as nom_suivi,
                            cuser.prenom_coordonnees                                                                          as prenom_suivi,
                            p.id_user,
                            p.id_patient,
                            DATE_FORMAT(p.date_admission, '%d/%m/%Y')                                                         as date_admission,
                            nom_antenne,
                            s.nom_structure,
                            intervalle,
                            DATE_FORMAT(p.date_archivage, '%d/%m/%Y')                                                         as date_archivage,
                            est_non_peps,
                            p.est_archive,
                            p.date_eval_suiv
            FROM patients p
                     JOIN coordonnees c on p.id_coordonnee = c.id_coordonnees
                     JOIN antenne a on a.id_antenne = p.id_antenne
                     JOIN structure s on a.id_structure = s.id_structure
                     LEFT JOIN prescrit ON p.id_patient = prescrit.id_patient
                     LEFT JOIN medecins m on prescrit.id_medecin = m.id_medecin
                     LEFT JOIN coordonnees cmed on m.id_medecin = cmed.id_medecin
                     LEFT JOIN coordonnees cuser on cuser.id_user = p.id_user
                     LEFT JOIN users u on cuser.id_user = u.id_user
            WHERE est_archive = 0";

        $stmt = $this->pdo->prepare($main_query);

        $stmt->execute();
        $u = new User($this->pdo);

        $query_check = "SELECT id_patient FROM dossiers_suivi WHERE id_user = :id_user";
        $stmt_check = $this->pdo->prepare($query_check);
        $stmt_check->bindValue(':id_user', $session['id_user']);
        $stmt_check->execute();
        $check = $stmt_check->fetchAll(PDO::FETCH_ASSOC);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (in_array($row['id_patient'], array_column($check, 'id_patient'))) {
                $row['nom_patient'] = !empty($row['nom_patient']) ? EncryptionManager::decrypt(
                    $row['nom_patient']
                ) : "";
                $row['prenom_patient'] = !empty($row['prenom_patient']) ? EncryptionManager::decrypt(
                    $row['prenom_patient']
                ) : "";
                $row['premier_prenom_naissance'] = !empty($row['premier_prenom_naissance']) ? EncryptionManager::decrypt(
                    $row['premier_prenom_naissance']
                ) : "";
                $row['nom_naissance'] = !empty($row['nom_naissance']) ? EncryptionManager::decrypt(
                    $row['nom_naissance']
                ) : "";
                $row['matricule_ins'] = !empty($row['matricule_ins']) ? EncryptionManager::decrypt(
                    $row['matricule_ins']
                ) : "";
                $row['oid'] = !empty($row['oid']) ? EncryptionManager::decrypt($row['oid']) : "";
                $row['code_insee_naissance'] = !empty($row['code_insee_naissance']) ? EncryptionManager::decrypt(
                    $row['code_insee_naissance']
                ) : "";
                $row['nom_utilise'] = !empty($row['nom_utilise']) ? EncryptionManager::decrypt(
                    $row['nom_utilise']
                ) : "";
                $row['prenom_utilise'] = !empty($row['prenom_utilise']) ? EncryptionManager::decrypt(
                    $row['prenom_utilise']
                ) : "";
                $row['liste_prenom_naissance'] = !empty($row['liste_prenom_naissance']) ? EncryptionManager::decrypt(
                    $row['liste_prenom_naissance']
                ) : "";
                $row['mail_patient'] = !empty($row['mail_patient']) ? EncryptionManager::decrypt(
                    $row['mail_patient']
                ) : "";
                $row['tel_fixe_patient'] = !empty($row['tel_fixe_patient']) ? EncryptionManager::decrypt(
                    $row['tel_fixe_patient']
                ) : "";
                $row['tel_portable_patient'] = !empty($row['tel_portable_patient']) ? EncryptionManager::decrypt(
                    $row['tel_portable_patient']
                ) : "";
                $row['nom_adresse'] = !empty($row['nom_adresse']) ? EncryptionManager::decrypt(
                    $row['nom_adresse']
                ) : "";
                $row['complement_adresse'] = !empty($row['complement_adresse']) ? EncryptionManager::decrypt(
                    $row['complement_adresse']
                ) : "";

                // mettre en bool si le patient est archivé
                $row['est_archive'] = $row['est_archive'] == 1;

                // récupération de la dernière évaluation
                $query_item = '
                    SELECT id_type_eval, date_eval
                    FROM evaluations
                    WHERE id_patient = :id_patient
                    ORDER BY id_type_eval DESC
                    LIMIT 1';
                $stmt_item = $this->pdo->prepare($query_item);
                $stmt_item->bindValue(':id_patient', $row['id_patient']);
                $stmt_item->execute();
                $data = $stmt_item->fetch(PDO::FETCH_ASSOC);
                $row['id_type_eval'] = $data['id_type_eval'] ?? null;
                $row['date_eval'] = $data['date_eval'] ?? null;
                $row['role_user_suivi'] = $u->getRoles($row['id_user']) ?? "";

                // récupération si le patient a une prescription
                $query_item = '
                    SELECT COUNT(*) as count_prescription
                    FROM prescription
                    WHERE id_patient = :id_patient';
                $stmt_item = $this->pdo->prepare($query_item);
                $stmt_item->bindValue(':id_patient', $row['id_patient']);
                $stmt_item->execute();
                $data = $stmt_item->fetch(PDO::FETCH_ASSOC);
                $row['a_prescription'] = intval($data['count_prescription']) > 0;

                // récupération si le patient a terminé le programme
                $query_item = '
                    SELECT COUNT(*) as count_questionnaire_instance
                    FROM questionnaire_instance
                    WHERE id_questionnaire = 5
                      AND id_patient = :id_patient';
                $stmt_item = $this->pdo->prepare($query_item);
                $stmt_item->bindValue(':id_patient', $row['id_patient']);
                $stmt_item->execute();
                $data = $stmt_item->fetch(PDO::FETCH_ASSOC);
                $row['a_termine_programme'] = intval($data['count_questionnaire_instance']) > 0;

                $patients[] = $row;
            }
        }

        return $patients;
    }

    /**
     * Récupération des patients qui ont été orentés vers une structure donnée
     *
     * @param string $id_structure l'id de la structure vers laquelles sont orientés les patients
     *
     * @return array|false @return array|false Return an array of associative arrays or false on failure
     */
    public function readAllOriente($id_structure)
    {
        if (empty($id_structure)) {
            return false;
        }

        $query = "
            SELECT DISTINCT patients.id_patient,
                            IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)                     as nom_patient,
                            IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise, premier_prenom_naissance) as prenom_patient,
                            DATE_FORMAT(date_admission, '%d/%m/%Y') as date_admission,
                            tel_fixe_coordonnees                    as tel_fixe_patient,
                            tel_portable_coordonnees                as tel_portable_patient,
                            mail_coordonnees                        as mail_patient
            FROM patients
                     JOIN coordonnees ON coordonnees.id_coordonnees = patients.id_coordonnee
                     JOIN oriente_vers ON patients.id_patient = oriente_vers.id_patient
            WHERE oriente_vers.id_structure = :id_structure";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_structure', $id_structure);
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
            $patient['tel_fixe_patient'] = !empty($patient['tel_fixe_patient']) ? EncryptionManager::decrypt(
                $patient['tel_fixe_patient']
            ) : "";
            $patient['tel_portable_patient'] = !empty($patient['tel_portable_patient']) ? EncryptionManager::decrypt(
                $patient['tel_portable_patient']
            ) : "";
            $patient['mail_patient'] = !empty($patient['mail_patient']) ? EncryptionManager::decrypt(
                $patient['mail_patient']
            ) : "";

            $patients[] = $patient;
        }

        return $patients;
    }

    /**
     * Récupération de toutes les données basiques patients de tous les patients
     *
     * @param string $filter_territoire filtre selon l'id_territoire des patients
     * @return array|false Return an array of associative arrays or false on failure
     */
    public function readAllBasic($filter_territoire = null)
    {
        if (!empty($filter_territoire)) {
            $filter_territoire = filter_var($filter_territoire, FILTER_SANITIZE_NUMBER_INT);
        }

        $query = "
            SELECT p.id_patient,
                   nom_naissance,
                   premier_prenom_naissance,
                   nom_utilise,
                   prenom_utilise,
                   IF(nom_utilise IS NOT NULL AND nom_utilise != '', nom_utilise, nom_naissance)                     as nom_patient,
                   IF(prenom_utilise IS NOT NULL AND prenom_utilise != '', prenom_utilise,
                      premier_prenom_naissance)                                                                      as prenom_patient,
                   sexe                                                                                              as sexe_patient,
                   date_naissance,
                   nom_adresse,
                   complement_adresse,
                   code_postal,
                   nom_ville,
                   mail_coordonnees                                                                                  as email_patient,
                   tel_fixe_coordonnees                                                                              as tel_fixe_patient,
                   tel_portable_coordonnees                                                                          as tel_portable_patient,
                   est_pris_en_charge_financierement,
                   hauteur_prise_en_charge_financierement,
                   sit_part_autre,
                   sit_part_education_therapeutique,
                   sit_part_grossesse,
                   sit_part_prevention_chute,
                   sit_part_sedentarite,
                   est_dans_zrr,
                   est_dans_qpv,
                   est_archive
            FROM patients p
                     JOIN coordonnees c ON p.id_patient = c.id_patient
                     JOIN adresse a ON a.id_adresse = p.id_adresse
                     LEFT JOIN se_localise_a loc ON loc.id_adresse = a.id_adresse
                     LEFT JOIN villes v ON v.id_ville = loc.id_ville ";

        if (!empty($filter_territoire)) {
            $query .= " WHERE p.id_territoire = :id_territoire ";
        }

        $statement = $this->pdo->prepare($query);
        if (!empty($filter_territoire)) {
            $statement->bindValue(":id_territoire", $filter_territoire);
        }
        if (!$statement->execute()) {
            return false;
        }

        $patients = [];
        while ($patient = $statement->fetch(PDO::FETCH_ASSOC)) {
            $patient['nom_patient'] = !empty($patient['nom_patient']) ? EncryptionManager::decrypt(
                $patient['nom_patient']
            ) : "";
            $patient['prenom_patient'] = !empty($patient['prenom_patient']) ? EncryptionManager::decrypt(
                $patient['prenom_patient']
            ) : "";
            $patient['premier_prenom_naissance'] = !empty($patient['premier_prenom_naissance']) ? EncryptionManager::decrypt(
                $patient['premier_prenom_naissance']
            ) : "";
            $patient['nom_naissance'] = !empty($patient['nom_naissance']) ? EncryptionManager::decrypt(
                $patient['nom_naissance']
            ) : "";
            $patient['matricule_ins'] = !empty($patient['matricule_ins']) ? EncryptionManager::decrypt(
                $patient['matricule_ins']
            ) : "";
            $patient['oid'] = !empty($patient['oid']) ? EncryptionManager::decrypt($patient['oid']) : "";
            $patient['code_insee_naissance'] = !empty($patient['code_insee_naissance']) ? EncryptionManager::decrypt(
                $patient['code_insee_naissance']
            ) : "";
            $patient['nom_utilise'] = !empty($patient['nom_utilise']) ? EncryptionManager::decrypt(
                $patient['nom_utilise']
            ) : "";
            $patient['prenom_utilise'] = !empty($patient['prenom_utilise']) ? EncryptionManager::decrypt(
                $patient['prenom_utilise']
            ) : "";
            $patient['liste_prenom_naissance'] = !empty($patient['liste_prenom_naissance']) ? EncryptionManager::decrypt(
                $patient['liste_prenom_naissance']
            ) : "";
            $patient['email_patient'] = !empty($patient['email_patient']) ? EncryptionManager::decrypt(
                $patient['email_patient']
            ) : "";
            $patient['tel_fixe_patient'] = !empty($patient['tel_fixe_patient']) ? EncryptionManager::decrypt(
                $patient['tel_fixe_patient']
            ) : "";
            $patient['tel_portable_patient'] = !empty($patient['tel_portable_patient']) ? EncryptionManager::decrypt(
                $patient['tel_portable_patient']
            ) : "";
            $patient['nom_adresse'] = !empty($patient['nom_adresse']) ? EncryptionManager::decrypt(
                $patient['nom_adresse']
            ) : "";
            $patient['complement_adresse'] = !empty($patient['complement_adresse']) ? EncryptionManager::decrypt(
                $patient['complement_adresse']
            ) : "";

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
     * @param string $today date au format "AAAA-MM-JJ"
     * @return array Un array qui contient les ids des patient qui ont en retard d'émargement d'exactement (intervalle
     *     * 30) jours ou dont la date d'évaluation suivante est (date du jour + 1)
     */
    public function getAllPatientEvaluationLate($today)
    {
        if (empty($today)) {
            return [];
        }

        $date = new DateTime($today);
        $date->modify('+1 day');
        $todayPlusOneday = $date->format('Y-m-d');

        // on considère qu'un mois dure 30 jours
        $query = '
            SELECT subquery2.id_patient
            FROM (SELECT p.id_patient,
                         p.intervalle,
                         p.date_eval_suiv,
                         evaluations.id_type_eval,
                         DATEDIFF(:today, evaluations.date_eval) AS days_late
                  FROM (SELECT e.id_patient, MAX(e.id_type_eval) as id_type_eval_max
                        FROM evaluations e
                        GROUP BY e.id_patient) subquery1
                           JOIN evaluations on subquery1.id_patient = evaluations.id_patient AND
                                               subquery1.id_type_eval_max = evaluations.id_type_eval
                           JOIN patients p on evaluations.id_patient = p.id_patient) subquery2
            WHERE ((subquery2.date_eval_suiv IS NULL AND subquery2.days_late = subquery2.intervalle * 30)
                    OR subquery2.date_eval_suiv = :today_plus_one)
              AND subquery2.id_type_eval <> 14'; // id_type_eval de l'évaluation finale
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':today', $today);
        $stmt->bindValue(':today_plus_one', $todayPlusOneday);
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
     * @param $id_patient
     * @return int|null Return the age of a patient or null on failure
     */
    public function getAge($id_patient)
    {
        $query = '
            SELECT (DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(date_naissance)), \'%Y\') + 0) as age
            from patients
            WHERE patients.id_patient = :id_patient';

        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':id_patient', $id_patient);
        if (!$statement->execute()) {
            return null;
        }
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }

        return $data['age'] ? intval($data['age']) : null;
    }

    /**
     * @return array les types de statuts d'identité
     */
    public function getTypeStatutIdentite(): array
    {
        $query = '
            SELECT id_type_statut_identite, nom
            from type_statut_identite';

        $statement = $this->pdo->prepare($query);
        if (!$statement->execute()) {
            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    /** Met à jour la date de la prochaine évaluation (peut être null).
     * @param $id_patient int Identifiant du patient
     * @param $date_eval_suiv string Date de la prochaine évaluation (peut être null)
     * @return bool true si réussi, false sinon
     */
    public function updateDateEvalSuiv($id_patient, $date_eval_suiv)
    {
        if ($id_patient == null) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $query = "UPDATE patients
                    SET date_eval_suiv = :date_eval_suiv
                    WHERE id_patient = :id_patient";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_patient', $id_patient);
            $stmt->bindValue(':date_eval_suiv', $date_eval_suiv);

            if (!$stmt->execute()) {
                throw new Exception('Erreur UPDATE patients SET date_eval_suiv');
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
     * @return array les types de pièces d'identité
     */
    public function getTypePieceIdentite(): array
    {
        $query = '
            SELECT id_type_piece_identite, nom
            from type_piece_identite';

        $statement = $this->pdo->prepare($query);
        if (!$statement->execute()) {
            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?? [];
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
        return
            !empty($parameters['date_admission']) &&
            !empty($parameters['nature_entretien']) &&
            !empty($parameters['nom_naissance']) &&
            !empty($parameters['premier_prenom_naissance']) &&
            !empty($parameters['sexe_patient']) &&
            !empty($parameters['date_naissance']) &&
            !empty($parameters['adresse_patient']) &&
            !empty($parameters['code_postal_patient']) &&
            !empty($parameters['ville_patient']) &&
            !empty($parameters['regime_assurance_maladie']) &&
            !empty($parameters['ville_assurance_maladie']) &&
            !empty($parameters['code_postal_assurance_maladie']) &&
            !empty($parameters['id_structure']) &&
            !empty($parameters['id_user']) &&
            !empty($parameters['id_territoire']);
    }
}