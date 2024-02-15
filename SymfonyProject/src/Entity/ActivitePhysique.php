<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

class ActivitePhysique
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
     * required parameters:
     * [
     *     'id_user' => string,
     *     'id_patient' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'activite_physique_autonome' => string,
     *     'activite_physique_encadree' => string,
     *     'activite_anterieure' => string,
     *     'disponibilite' => string,
     *     'frein_activite' => string,
     *     'activite_envisagee' => string,
     *     'point_fort_levier' => string,
     * ]
     *
     * @param $parameters
     * @return false|string l'id ou false en cas d'échec
     */
    public function create($parameters)
    {
        if (empty($parameters['id_user']) ||
            empty($parameters['id_patient'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!($parameters['a_activite_anterieure'] == "0" || $parameters['a_activite_anterieure'] == "1") ||
            !($parameters['a_activite_autonome'] == "0" || $parameters['a_activite_autonome'] == "1") ||
            !($parameters['a_activite_encadree'] == "0" || $parameters['a_activite_encadree'] == "1") ||
            !($parameters['a_activite_envisagee'] == "0" || $parameters['a_activite_envisagee'] == "1") ||
            !($parameters['a_activite_frein'] == "0" || $parameters['a_activite_frein'] == "1") ||
            !($parameters['a_activite_point_fort_levier'] == "0" || $parameters['a_activite_point_fort_levier'] == "1") ||

            !($parameters['est_dispo_lundi'] == "0" || $parameters['est_dispo_lundi'] == "1") ||
            !($parameters['est_dispo_mardi'] == "0" || $parameters['est_dispo_mardi'] == "1") ||
            !($parameters['est_dispo_mercredi'] == "0" || $parameters['est_dispo_mercredi'] == "1") ||
            !($parameters['est_dispo_jeudi'] == "0" || $parameters['est_dispo_jeudi'] == "1") ||
            !($parameters['est_dispo_vendredi'] == "0" || $parameters['est_dispo_vendredi'] == "1") ||
            !($parameters['est_dispo_samedi'] == "0" || $parameters['est_dispo_samedi'] == "1") ||
            !($parameters['est_dispo_dimanche'] == "0" || $parameters['est_dispo_dimanche'] == "1")) {
            $this->errorMessage = "Il y a au moins un des paramètres qui est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // si le patient n'a pas l'activité le texte est par défaut un string vide
            if ($parameters['a_activite_anterieure'] == "0") {
                $parameters['activite_anterieure'] = "";
            }
            if ($parameters['a_activite_autonome'] == "0") {
                $parameters['activite_physique_autonome'] = "";
            }
            if ($parameters['a_activite_encadree'] == "0") {
                $parameters['activite_physique_encadree'] = "";
            }
            if ($parameters['a_activite_envisagee'] == "0") {
                $parameters['activite_envisagee'] = "";
            }
            if ($parameters['a_activite_frein'] == "0") {
                $parameters['frein_activite'] = "";
            }
            if ($parameters['a_activite_point_fort_levier'] == "0") {
                $parameters['point_fort_levier'] = "";
            }

            if ($parameters['est_dispo_lundi'] == "0") {
                $parameters['heure_debut_lundi'] = null;
                $parameters['heure_fin_lundi'] = null;
            }
            if ($parameters['est_dispo_mardi'] == "0") {
                $parameters['heure_debut_mardi'] = null;
                $parameters['heure_fin_mardi'] = null;
            }
            if ($parameters['est_dispo_mercredi'] == "0") {
                $parameters['heure_debut_mercredi'] = null;
                $parameters['heure_fin_mercredi'] = null;
            }
            if ($parameters['est_dispo_jeudi'] == "0") {
                $parameters['heure_debut_jeudi'] = null;
                $parameters['heure_fin_jeudi'] = null;
            }
            if ($parameters['est_dispo_vendredi'] == "0") {
                $parameters['heure_debut_vendredi'] = null;
                $parameters['heure_fin_vendredi'] = null;
            }
            if ($parameters['est_dispo_samedi'] == "0") {
                $parameters['heure_debut_samedi'] = null;
                $parameters['heure_fin_samedi'] = null;
            }
            if ($parameters['est_dispo_dimanche'] == "0") {
                $parameters['heure_debut_dimanche'] = null;
                $parameters['heure_fin_dimanche'] = null;
            }

            // paramètres obligatoires
            $id_user = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $date_observation = date('y-m-d H:i:s');

            // paramètres optionnels
            $parameters['activite_physique_autonome'] = isset($parameters['activite_physique_autonome']) ?
                trim($parameters['activite_physique_autonome']) :
                "";
            $parameters['activite_physique_encadree'] = isset($parameters['activite_physique_encadree']) ?
                trim($parameters['activite_physique_encadree']) :
                "";
            $parameters['activite_anterieure'] = isset($parameters['activite_anterieure']) ?
                trim($parameters['activite_anterieure']) :
                "";
            $parameters['disponibilite'] = isset($parameters['disponibilite']) ?
                trim($parameters['disponibilite']) :
                "";
            $parameters['frein_activite'] = isset($parameters['frein_activite']) ?
                trim($parameters['frein_activite']) :
                "";
            $parameters['activite_envisagee'] = isset($parameters['activite_envisagee']) ?
                trim($parameters['activite_envisagee']) :
                "";
            $parameters['point_fort_levier'] = isset($parameters['point_fort_levier']) ?
                trim($parameters['point_fort_levier']) :
                "";

            $query = '
                INSERT INTO activites_physiques
                (activite_physique_autonome, activite_physique_encadree, activite_anterieure,
                 disponibilite, frein_activite, activite_envisagee, point_fort_levier,
                 a_activite_anterieure, a_activite_autonome, a_activite_encadree, a_activite_envisagee,
                 a_activite_frein, a_activite_point_fort_levier, est_dispo_lundi, est_dispo_mardi,
                 est_dispo_mercredi, est_dispo_jeudi, est_dispo_vendredi, est_dispo_samedi,
                 est_dispo_dimanche, heure_debut_lundi, heure_debut_mardi, heure_debut_mercredi,
                 heure_debut_jeudi, heure_debut_vendredi, heure_debut_samedi, heure_debut_dimanche,
                 heure_fin_lundi, heure_fin_mardi, heure_fin_mercredi, heure_fin_jeudi,
                 heure_fin_vendredi, heure_fin_samedi, heure_fin_dimanche, id_patient, id_user,
                 date_observation)
                VALUES (:activite_physique_autonome, :activite_physique_encadree, :activite_anterieure,
                         :disponibilite, :frein_activite, :activite_envisagee, :point_fort_levier,
                         :a_activite_anterieure, :a_activite_autonome, :a_activite_encadree, :a_activite_envisagee,
                         :a_activite_frein, :a_activite_point_fort_levier, :est_dispo_lundi, :est_dispo_mardi,
                         :est_dispo_mercredi, :est_dispo_jeudi, :est_dispo_vendredi, :est_dispo_samedi,
                         :est_dispo_dimanche, :heure_debut_lundi, :heure_debut_mardi, :heure_debut_mercredi,
                         :heure_debut_jeudi, :heure_debut_vendredi, :heure_debut_samedi, :heure_debut_dimanche,
                         :heure_fin_lundi, :heure_fin_mardi, :heure_fin_mercredi, :heure_fin_jeudi,
                         :heure_fin_vendredi, :heure_fin_samedi, :heure_fin_dimanche, :id_patient, :id_user,
                         :date_observation)';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":activite_physique_autonome", $parameters['activite_physique_autonome']);
            $stmt->bindValue(":activite_physique_encadree", $parameters['activite_physique_encadree']);
            $stmt->bindValue(":activite_anterieure", $parameters['activite_anterieure']);
            $stmt->bindValue(":disponibilite", $parameters['disponibilite']);
            $stmt->bindValue(":frein_activite", $parameters['frein_activite']);
            $stmt->bindValue(":activite_envisagee", $parameters['activite_envisagee']);
            $stmt->bindValue(":point_fort_levier", $parameters['point_fort_levier']);
            $stmt->bindValue(":id_patient", $id_patient);
            $stmt->bindValue(":id_user", $id_user);
            $stmt->bindValue(":date_observation", $date_observation);

            $stmt->bindValue(":a_activite_anterieure", $parameters['a_activite_anterieure']);
            $stmt->bindValue(":a_activite_autonome", $parameters['a_activite_autonome']);
            $stmt->bindValue(":a_activite_encadree", $parameters['a_activite_encadree']);
            $stmt->bindValue(":a_activite_envisagee", $parameters['a_activite_envisagee']);
            $stmt->bindValue(":a_activite_frein", $parameters['a_activite_frein']);
            $stmt->bindValue(":a_activite_point_fort_levier", $parameters['a_activite_point_fort_levier']);

            $stmt->bindValue(":est_dispo_lundi", $parameters['est_dispo_lundi']);
            $stmt->bindValue(":est_dispo_mardi", $parameters['est_dispo_mardi']);
            $stmt->bindValue(":est_dispo_mercredi", $parameters['est_dispo_mercredi']);
            $stmt->bindValue(":est_dispo_jeudi", $parameters['est_dispo_jeudi']);
            $stmt->bindValue(":est_dispo_vendredi", $parameters['est_dispo_vendredi']);
            $stmt->bindValue(":est_dispo_samedi", $parameters['est_dispo_samedi']);
            $stmt->bindValue(":est_dispo_dimanche", $parameters['est_dispo_dimanche']);

            if (isset($parameters['heure_debut_lundi'])) {
                $stmt->bindValue(":heure_debut_lundi", $parameters['heure_debut_lundi']);
            } else {
                $stmt->bindValue(":heure_debut_lundi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_mardi'])) {
                $stmt->bindValue(":heure_debut_mardi", $parameters['heure_debut_mardi']);
            } else {
                $stmt->bindValue(":heure_debut_mardi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_mercredi'])) {
                $stmt->bindValue(":heure_debut_mercredi", $parameters['heure_debut_mercredi']);
            } else {
                $stmt->bindValue(":heure_debut_mercredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_jeudi'])) {
                $stmt->bindValue(":heure_debut_jeudi", $parameters['heure_debut_jeudi']);
            } else {
                $stmt->bindValue(":heure_debut_jeudi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_vendredi'])) {
                $stmt->bindValue(":heure_debut_vendredi", $parameters['heure_debut_vendredi']);
            } else {
                $stmt->bindValue(":heure_debut_vendredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_samedi'])) {
                $stmt->bindValue(":heure_debut_samedi", $parameters['heure_debut_samedi']);
            } else {
                $stmt->bindValue(":heure_debut_samedi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_dimanche'])) {
                $stmt->bindValue(":heure_debut_dimanche", $parameters['heure_debut_dimanche']);
            } else {
                $stmt->bindValue(":heure_debut_dimanche", null, PDO::PARAM_NULL);
            }

            if (isset($parameters['heure_fin_lundi'])) {
                $stmt->bindValue(":heure_fin_lundi", $parameters['heure_fin_lundi']);
            } else {
                $stmt->bindValue(":heure_fin_lundi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_mardi'])) {
                $stmt->bindValue(":heure_fin_mardi", $parameters['heure_fin_mardi']);
            } else {
                $stmt->bindValue(":heure_fin_mardi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_mercredi'])) {
                $stmt->bindValue(":heure_fin_mercredi", $parameters['heure_fin_mercredi']);
            } else {
                $stmt->bindValue(":heure_fin_mercredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_jeudi'])) {
                $stmt->bindValue(":heure_fin_jeudi", $parameters['heure_fin_jeudi']);
            } else {
                $stmt->bindValue(":heure_fin_jeudi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_vendredi'])) {
                $stmt->bindValue(":heure_fin_vendredi", $parameters['heure_fin_vendredi']);
            } else {
                $stmt->bindValue(":heure_fin_vendredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_samedi'])) {
                $stmt->bindValue(":heure_fin_samedi", $parameters['heure_fin_samedi']);
            } else {
                $stmt->bindValue(":heure_fin_samedi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_dimanche'])) {
                $stmt->bindValue(":heure_fin_dimanche", $parameters['heure_fin_dimanche']);
            } else {
                $stmt->bindValue(":heure_fin_dimanche", null, PDO::PARAM_NULL);
            }

            $stmt->execute();
            $id_activite_physique = $this->pdo->lastInsertId();
            $this->pdo->commit();

            return $id_activite_physique;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * required parameters:
     * [
     *     'id_activite_physique' => string,
     *     'id_user' => string,
     *     'id_patient' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'activite_physique_autonome' => string,
     *     'activite_physique_encadree' => string,
     *     'activite_anterieure' => string,
     *     'disponibilite' => string,
     *     'frein_activite' => string,
     *     'activite_envisagee' => string,
     *     'point_fort_levier' => string,
     * ]
     *
     * @param $parameters
     * @return bool si l'update a été réalisé avec succès
     */
    public function update($parameters)
    {
        if (empty($parameters['id_activite_physique']) ||
            empty($parameters['id_user']) ||
            empty($parameters['id_patient'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        if (!($parameters['a_activite_anterieure'] == "0" || $parameters['a_activite_anterieure'] == "1") ||
            !($parameters['a_activite_autonome'] == "0" || $parameters['a_activite_autonome'] == "1") ||
            !($parameters['a_activite_encadree'] == "0" || $parameters['a_activite_encadree'] == "1") ||
            !($parameters['a_activite_envisagee'] == "0" || $parameters['a_activite_envisagee'] == "1") ||
            !($parameters['a_activite_frein'] == "0" || $parameters['a_activite_frein'] == "1") ||
            !($parameters['a_activite_point_fort_levier'] == "0" || $parameters['a_activite_point_fort_levier'] == "1")) {
            $this->errorMessage = "Il y a au moins un des paramètres qui est invalide";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // si le patient n'a pas l'activité le texte est par défaut un string vide
            if ($parameters['a_activite_anterieure'] == "0") {
                $parameters['activite_anterieure'] = "";
            }
            if ($parameters['a_activite_autonome'] == "0") {
                $parameters['activite_physique_autonome'] = "";
            }
            if ($parameters['a_activite_encadree'] == "0") {
                $parameters['activite_physique_encadree'] = "";
            }
            if ($parameters['a_activite_envisagee'] == "0") {
                $parameters['activite_envisagee'] = "";
            }
            if ($parameters['a_activite_frein'] == "0") {
                $parameters['frein_activite'] = "";
            }
            if ($parameters['a_activite_point_fort_levier'] == "0") {
                $parameters['point_fort_levier'] = "";
            }

            if ($parameters['est_dispo_lundi'] == "0") {
                $parameters['heure_debut_lundi'] = null;
                $parameters['heure_fin_lundi'] = null;
            }
            if ($parameters['est_dispo_mardi'] == "0") {
                $parameters['heure_debut_mardi'] = null;
                $parameters['heure_fin_mardi'] = null;
            }
            if ($parameters['est_dispo_mercredi'] == "0") {
                $parameters['heure_debut_mercredi'] = null;
                $parameters['heure_fin_mercredi'] = null;
            }
            if ($parameters['est_dispo_jeudi'] == "0") {
                $parameters['heure_debut_jeudi'] = null;
                $parameters['heure_fin_jeudi'] = null;
            }
            if ($parameters['est_dispo_vendredi'] == "0") {
                $parameters['heure_debut_vendredi'] = null;
                $parameters['heure_fin_vendredi'] = null;
            }
            if ($parameters['est_dispo_samedi'] == "0") {
                $parameters['heure_debut_samedi'] = null;
                $parameters['heure_fin_samedi'] = null;
            }
            if ($parameters['est_dispo_dimanche'] == "0") {
                $parameters['heure_debut_dimanche'] = null;
                $parameters['heure_fin_dimanche'] = null;
            }

            // si les disponibilités sont renseignées, le champ texte "disponibilite" n'est plus nécessaire
            if ($parameters['est_dispo_lundi'] == "1" ||
                $parameters['est_dispo_mardi'] == "1" ||
                $parameters['est_dispo_mercredi'] == "1" ||
                $parameters['est_dispo_jeudi'] == "1" ||
                $parameters['est_dispo_vendredi'] == "1" ||
                $parameters['est_dispo_samedi'] == "1" ||
                $parameters['est_dispo_dimanche'] == "1") {
                $parameters['disponibilite'] = "";
            }

            // paramètres obligatoires
            $id_activite_physique = filter_var($parameters['id_activite_physique'], FILTER_SANITIZE_NUMBER_INT);
            $id_user = filter_var($parameters['id_user'], FILTER_SANITIZE_NUMBER_INT);
            $id_patient = filter_var($parameters['id_patient'], FILTER_SANITIZE_NUMBER_INT);
            $date_observation = date('y-m-d H:i:s');

            // paramètres optionnels
            $parameters['activite_physique_autonome'] = isset($parameters['activite_physique_autonome']) ?
                trim($parameters['activite_physique_autonome']) :
                "";
            $parameters['activite_physique_encadree'] = isset($parameters['activite_physique_encadree']) ?
                trim($parameters['activite_physique_encadree']) :
                "";
            $parameters['activite_anterieure'] = isset($parameters['activite_anterieure']) ?
                trim($parameters['activite_anterieure']) :
                "";
            $parameters['disponibilite'] = isset($parameters['disponibilite']) ?
                trim($parameters['disponibilite']) :
                "";
            $parameters['frein_activite'] = isset($parameters['frein_activite']) ?
                trim($parameters['frein_activite']) :
                "";
            $parameters['activite_envisagee'] = isset($parameters['activite_envisagee']) ?
                trim($parameters['activite_envisagee']) :
                "";
            $parameters['point_fort_levier'] = isset($parameters['point_fort_levier']) ?
                trim($parameters['point_fort_levier']) :
                "";

            $query = '
                UPDATE activites_physiques
                SET activite_physique_autonome   = :activite_physique_autonome,
                    activite_physique_encadree   = :activite_physique_encadree,
                    activite_anterieure          = :activite_anterieure,
                    disponibilite                = :disponibilite,
                    frein_activite               = :frein_activite,
                    activite_envisagee           = :activite_envisagee,
                    point_fort_levier            = :point_fort_levier,
                    id_patient                   = :id_patient,
                    id_user                      = :id_user,
                    date_observation             = :date_observation,
                    a_activite_anterieure        = :a_activite_anterieure,
                    a_activite_autonome          = :a_activite_autonome,
                    a_activite_encadree          = :a_activite_encadree,
                    a_activite_envisagee         = :a_activite_envisagee,
                    a_activite_frein             = :a_activite_frein,
                    a_activite_point_fort_levier = :a_activite_point_fort_levier,
                    est_dispo_lundi              = :est_dispo_lundi,
                    est_dispo_mardi              = :est_dispo_mardi,
                    est_dispo_mercredi           = :est_dispo_mercredi,
                    est_dispo_jeudi              = :est_dispo_jeudi,
                    est_dispo_vendredi           = :est_dispo_vendredi,
                    est_dispo_samedi             = :est_dispo_samedi,
                    est_dispo_dimanche           = :est_dispo_dimanche,
                    heure_debut_lundi            = :heure_debut_lundi,
                    heure_debut_mardi            = :heure_debut_mardi,
                    heure_debut_mercredi         = :heure_debut_mercredi,
                    heure_debut_jeudi            = :heure_debut_jeudi,
                    heure_debut_vendredi         = :heure_debut_vendredi,
                    heure_debut_samedi           = :heure_debut_samedi,
                    heure_debut_dimanche         = :heure_debut_dimanche,
                    heure_fin_lundi              = :heure_fin_lundi,
                    heure_fin_mardi              = :heure_fin_mardi,
                    heure_fin_mercredi           = :heure_fin_mercredi,
                    heure_fin_jeudi              = :heure_fin_jeudi,
                    heure_fin_vendredi           = :heure_fin_vendredi,
                    heure_fin_samedi             = :heure_fin_samedi,
                    heure_fin_dimanche           = :heure_fin_dimanche
                WHERE id_activite_physique = :id_activite_physique';
            $stmt = $this->pdo->prepare($query);

            $stmt->bindValue(":id_activite_physique", $id_activite_physique);
            $stmt->bindValue(":activite_physique_autonome", $parameters['activite_physique_autonome']);
            $stmt->bindValue(":activite_physique_encadree", $parameters['activite_physique_encadree']);
            $stmt->bindValue(":activite_anterieure", $parameters['activite_anterieure']);
            $stmt->bindValue(":disponibilite", $parameters['disponibilite']);
            $stmt->bindValue(":frein_activite", $parameters['frein_activite']);
            $stmt->bindValue(":activite_envisagee", $parameters['activite_envisagee']);
            $stmt->bindValue(":point_fort_levier", $parameters['point_fort_levier']);
            $stmt->bindValue(":id_patient", $id_patient);
            $stmt->bindValue(":id_user", $id_user);
            $stmt->bindValue(":date_observation", $date_observation);

            $stmt->bindValue(":a_activite_anterieure", $parameters['a_activite_anterieure']);
            $stmt->bindValue(":a_activite_autonome", $parameters['a_activite_autonome']);
            $stmt->bindValue(":a_activite_encadree", $parameters['a_activite_encadree']);
            $stmt->bindValue(":a_activite_envisagee", $parameters['a_activite_envisagee']);
            $stmt->bindValue(":a_activite_frein", $parameters['a_activite_frein']);
            $stmt->bindValue(":a_activite_point_fort_levier", $parameters['a_activite_point_fort_levier']);

            $stmt->bindValue(":est_dispo_lundi", $parameters['est_dispo_lundi']);
            $stmt->bindValue(":est_dispo_mardi", $parameters['est_dispo_mardi']);
            $stmt->bindValue(":est_dispo_mercredi", $parameters['est_dispo_mercredi']);
            $stmt->bindValue(":est_dispo_jeudi", $parameters['est_dispo_jeudi']);
            $stmt->bindValue(":est_dispo_vendredi", $parameters['est_dispo_vendredi']);
            $stmt->bindValue(":est_dispo_samedi", $parameters['est_dispo_samedi']);
            $stmt->bindValue(":est_dispo_dimanche", $parameters['est_dispo_dimanche']);

            if (isset($parameters['heure_debut_lundi'])) {
                $stmt->bindValue(":heure_debut_lundi", $parameters['heure_debut_lundi']);
            } else {
                $stmt->bindValue(":heure_debut_lundi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_mardi'])) {
                $stmt->bindValue(":heure_debut_mardi", $parameters['heure_debut_mardi']);
            } else {
                $stmt->bindValue(":heure_debut_mardi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_mercredi'])) {
                $stmt->bindValue(":heure_debut_mercredi", $parameters['heure_debut_mercredi']);
            } else {
                $stmt->bindValue(":heure_debut_mercredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_jeudi'])) {
                $stmt->bindValue(":heure_debut_jeudi", $parameters['heure_debut_jeudi']);
            } else {
                $stmt->bindValue(":heure_debut_jeudi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_vendredi'])) {
                $stmt->bindValue(":heure_debut_vendredi", $parameters['heure_debut_vendredi']);
            } else {
                $stmt->bindValue(":heure_debut_vendredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_samedi'])) {
                $stmt->bindValue(":heure_debut_samedi", $parameters['heure_debut_samedi']);
            } else {
                $stmt->bindValue(":heure_debut_samedi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_debut_dimanche'])) {
                $stmt->bindValue(":heure_debut_dimanche", $parameters['heure_debut_dimanche']);
            } else {
                $stmt->bindValue(":heure_debut_dimanche", null, PDO::PARAM_NULL);
            }

            if (isset($parameters['heure_fin_lundi'])) {
                $stmt->bindValue(":heure_fin_lundi", $parameters['heure_fin_lundi']);
            } else {
                $stmt->bindValue(":heure_fin_lundi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_mardi'])) {
                $stmt->bindValue(":heure_fin_mardi", $parameters['heure_fin_mardi']);
            } else {
                $stmt->bindValue(":heure_fin_mardi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_mercredi'])) {
                $stmt->bindValue(":heure_fin_mercredi", $parameters['heure_fin_mercredi']);
            } else {
                $stmt->bindValue(":heure_fin_mercredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_jeudi'])) {
                $stmt->bindValue(":heure_fin_jeudi", $parameters['heure_fin_jeudi']);
            } else {
                $stmt->bindValue(":heure_fin_jeudi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_vendredi'])) {
                $stmt->bindValue(":heure_fin_vendredi", $parameters['heure_fin_vendredi']);
            } else {
                $stmt->bindValue(":heure_fin_vendredi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_samedi'])) {
                $stmt->bindValue(":heure_fin_samedi", $parameters['heure_fin_samedi']);
            } else {
                $stmt->bindValue(":heure_fin_samedi", null, PDO::PARAM_NULL);
            }
            if (isset($parameters['heure_fin_dimanche'])) {
                $stmt->bindValue(":heure_fin_dimanche", $parameters['heure_fin_dimanche']);
            } else {
                $stmt->bindValue(":heure_fin_dimanche", null, PDO::PARAM_NULL);
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
     * @param $id_activite_physique
     * @return false|array Return an associative array or false on failure
     */
    public function readOne($id_activite_physique)
    {
        if (empty($id_activite_physique)) {
            return false;
        }

        $query = '
            SELECT id_activite_physique,
                   activite_physique_autonome,
                   activite_physique_encadree,
                   activite_anterieure,
                   disponibilite,
                   frein_activite,
                   activite_envisagee,
                   point_fort_levier,
                   a_activite_anterieure,
                   a_activite_autonome,
                   a_activite_encadree,
                   a_activite_envisagee,
                   a_activite_frein,
                   a_activite_point_fort_levier,
                   est_dispo_lundi,
                   est_dispo_mardi,
                   est_dispo_mercredi,
                   est_dispo_jeudi,
                   est_dispo_vendredi,
                   est_dispo_samedi,
                   est_dispo_dimanche,
                   heure_debut_lundi,
                   heure_debut_mardi,
                   heure_debut_mercredi,
                   heure_debut_jeudi,
                   heure_debut_vendredi,
                   heure_debut_samedi,
                   heure_debut_dimanche,
                   heure_fin_lundi,
                   heure_fin_mardi,
                   heure_fin_mercredi,
                   heure_fin_jeudi,
                   heure_fin_vendredi,
                   heure_fin_samedi,
                   heure_fin_dimanche,
                   id_patient,
                   id_user,
                   date_observation
            FROM activites_physiques
            WHERE id_activite_physique = :id_activite_physique';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_activite_physique', $id_activite_physique);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id_patient
     * @return false|array Return an associative array or false on failure
     */
    public function readOnePatient($id_patient)
    {
        if (empty($id_patient)) {
            return false;
        }

        $query = '
            SELECT id_activite_physique,
                   activite_physique_autonome,
                   activite_physique_encadree,
                   activite_anterieure,
                   disponibilite,
                   frein_activite,
                   activite_envisagee,
                   point_fort_levier,
                   a_activite_anterieure,
                   a_activite_autonome,
                   a_activite_encadree,
                   a_activite_envisagee,
                   a_activite_frein,
                   a_activite_point_fort_levier,
                   est_dispo_lundi,
                   est_dispo_mardi,
                   est_dispo_mercredi,
                   est_dispo_jeudi,
                   est_dispo_vendredi,
                   est_dispo_samedi,
                   est_dispo_dimanche,
                   heure_debut_lundi,
                   heure_debut_mardi,
                   heure_debut_mercredi,
                   heure_debut_jeudi,
                   heure_debut_vendredi,
                   heure_debut_samedi,
                   heure_debut_dimanche,
                   heure_fin_lundi,
                   heure_fin_mardi,
                   heure_fin_mercredi,
                   heure_fin_jeudi,
                   heure_fin_vendredi,
                   heure_fin_samedi,
                   heure_fin_dimanche,
                   id_patient,
                   id_user,
                   date_observation
            FROM activites_physiques
            WHERE id_patient = :id_patient
            ORDER BY id_activite_physique
            LIMIT 1';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_patient', $id_patient);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}