<?php

namespace Sportsante86\Sapa\Outils;

class Permissions
{
    public const SUPER_ADMIN = 'super_admin';
    public const COORDONNATEUR_PEPS = 'coordonnateur_peps';
    public const COORDONNATEUR_MSS = 'coordonnateur_mss';
    public const COORDONNATEUR_NON_MSS = 'coordonnateur_non_mss';
    public const INTERVENANT = 'intervenant';
    public const EVALUATEUR = 'evaluateur';
    public const REFERENT = 'referent';
    public const RESPONSABLE_STRUCTURE = 'responsable_structure';
    public const SUPERVISEUR_PEPS = 'superviseur';
    public const SECRETAIRE = 'secretaire';

    /**
     * @var array La liste des permissions de l'application
     */
    private array $permissions = [
        // Super admin
        self::SUPER_ADMIN => [
            // affichage des pages
            'can_view_page_utilisateur',
            'can_view_page_export',
            'can_view_page_tableau_de_bord',
            'can_view_page_import',
            'can_view_page_medecins',
            'can_view_page_strutures',
            'can_view_page_intervenants',
            'can_view_page_creneaux',
            'can_view_page_gestion_notifications_maj',
            'can_view_page_logs',
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_synthese',
            'can_view_page_patient_evaluation',
            'can_view_page_patient_prescription',
            'can_view_page_patient_orientation',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page bénéficiaire
            'can_modify_patient',
            // page sante
            'can_view_page_patient_donnees_sante',
            // page structure
            'can_edit_territoire_structure',
            'can_fuse_structure',
            // creneau
            'can_add_creneau',
            //professionnels de santé
            'can_fuse_professionnel_sante',
            // intervenant
            'can_fuse_intervenant',
            'can_edit_territoire_intervenant',
            //page export
            'can_export_patient_data',
            // suppression element page administration
            'can_delete_medecins',
            'can_delete_strutures',
            'can_delete_intervenants',
            'can_delete_creneaux',
            'can_delete_utilisateur',
            'can_delete_superviseur',
            'can_delete_super_admin',
            // tableau de bord
            'can_select_territoire',
            'can_select_structure',
            // page utilisateurs
            'can_add_utilisateur',
            'can_edit_territoire_utilisateur',
            // page journal d'activité
            'can_view_page_journal_activite',
            'can_export_journal_activite',
            // faire appel au téléservice INS
            'can_call_tls_ins'
        ],
        // Coordonnateur PEPS
        self::COORDONNATEUR_PEPS => [
            // affichage des pages
            'can_view_page_utilisateur',
            'can_view_page_export',
            'can_view_page_statistiques',
            'can_view_page_tableau_de_bord',
            'can_view_page_medecins',
            'can_view_page_strutures',
            'can_view_page_intervenants',
            'can_view_page_creneaux',
            'can_view_page_patient',
            'can_view_page_settings_patient', //
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_synthese',
            'can_view_page_patient_evaluation',
            'can_view_page_patient_prescription',
            'can_view_page_patient_orientation',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page bénéficiaire
            'can_modify_patient',
            'can_modify_parcours',
            'can_fuse_patient',
            // page sante
            'can_view_page_patient_donnees_sante',
            // page patient
            'can_add_patient',
            'can_archive_patient',
            // page d'accueil
            'can_view_colonne_evaluateur',
            // page creneau
            'can_add_creneau',
            //page export
            'can_export_patient_data',
            'can_export_medecins_prescripteur_data',
            // page utilisateurs
            'can_add_utilisateur',
            // tableau de bord
            'can_select_structure',
            // page settings
            'can_view_button_ma_structure',
            // faire appel au téléservice INS
            'can_call_tls_ins'
        ],
        // Intervenant
        self::INTERVENANT => [
            // affichage des pages
            'can_view_page_patient',
            'can_view_calendar',
            'can_view_page_patient_evaluation',
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page patient
            'can_add_patient',
            'can_modify_parcours',
            // page creneaux
            'can_view_page_mes_creneaux'
        ],
        // Référent
        self::REFERENT => [
        ],
        // Evaluateur
        self::EVALUATEUR => [
            // affichage des pages
            'can_view_page_utilisateur',
            'can_view_page_export',
            'can_view_page_medecins',
            'can_view_page_strutures',
            'can_view_page_intervenants',
            'can_view_page_creneaux',
            'can_view_page_patient',
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_synthese',
            'can_view_page_patient_evaluation',
            'can_view_page_patient_prescription',
            'can_view_page_patient_orientation',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page bénéficiaire
            'can_modify_patient',
            'can_modify_parcours',
            //page export
            'can_export_patient_data',
            'can_export_medecins_prescripteur_data',
            // page sante
            'can_view_page_patient_donnees_sante',
            // page patient
            'can_add_patient',
            // page d'accueil
            'can_view_colonne_evaluateur',
            // faire appel au téléservice INS
            'can_call_tls_ins'
        ],
        // Coordonnateur MSS
        self::COORDONNATEUR_MSS => [
            // affichage des pages
            'can_view_page_utilisateur',
            'can_view_page_export',
            'can_view_page_statistiques',
            'can_view_page_tableau_de_bord',
            'can_view_page_medecins',
            'can_view_page_strutures',
            'can_view_page_intervenants',
            'can_view_page_creneaux',
            'can_view_page_patient',
            'can_view_page_settings_patient', //
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_synthese',
            'can_view_page_patient_evaluation',
            'can_view_page_patient_prescription',
            'can_view_page_patient_orientation',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page bénéficiaire
            'can_modify_patient',
            'can_modify_parcours',
            'can_fuse_patient',
            // page sante
            'can_view_page_patient_donnees_sante',
            // page patient
            'can_add_patient',
            'can_archive_patient',
            // page creneau
            'can_add_creneau',
            //page export
            'can_export_patient_data',
            'can_export_medecins_prescripteur_data',
            // page d'accueil
            'can_view_colonne_evaluateur',
            // page settings
            'can_view_button_ma_structure',
            // page utilisateurs
            'can_add_utilisateur',
            // faire appel au téléservice INS
            'can_call_tls_ins'
        ],
        // Coordonnateur Non-MSS
        self::COORDONNATEUR_NON_MSS => [
            // affichage des pages
            'can_view_page_utilisateur',
            'can_view_page_statistiques',
            'can_view_page_export',
            'can_view_page_tableau_de_bord',
            'can_view_page_medecins',
            'can_view_page_strutures',
            'can_view_page_intervenants',
            'can_view_page_creneaux',
            'can_view_page_patient',
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_synthese',
            'can_view_page_patient_evaluation',
            'can_view_page_patient_prescription',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page bénéficiaire
            'can_modify_patient',
            'can_modify_parcours',
            // page patient
            'can_add_patient',
            'can_archive_patient',
            // page d'accueil
            'can_view_colonne_evaluateur',
            // page sante
            'can_view_page_patient_donnees_sante',
            // page creneau
            'can_add_creneau',
            // page settings
            'can_view_button_ma_structure',
            'can_view_button_mes_utilisateurs',
            //page export
            'can_export_patient_data',
            'can_export_medecins_prescripteur_data',
            // page utilisateurs
            'can_add_utilisateur',
            // faire appel au téléservice INS
            'can_call_tls_ins'
        ],
        // responsable structure
        self::RESPONSABLE_STRUCTURE => [
            // affichage des pages
            'can_view_page_utilisateur',
            'can_view_page_statistiques',
            'can_view_page_tableau_de_bord',
            'can_view_tableau_seance',
            'can_view_page_intervenants',
            'can_view_page_creneaux',
            'can_view_page_patient',
            // page settings
            'can_view_button_ma_structure',
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            'can_view_page_patient_sante',
            'can_view_page_patient_activite_physique',
            'can_view_page_patient_questionnaire',
            'can_view_page_patient_objectifs',
            'can_view_page_patient_suivi',
            'can_view_page_patient_progression',
            // page utilisateurs
            'can_add_utilisateur',
            //calendrier respo structure
            'can_view_page_calendrier_creneaux_types',
            // creneau
            'can_add_creneau',
        ],
        // Superviseur PEPS
        self::SUPERVISEUR_PEPS => [
            'can_view_page_tableau_de_bord',
            // tableau de bord
            'can_select_territoire',
            'can_select_structure',
        ],
        self::SECRETAIRE => [
            // affichage des pages
            'can_view_page_patient',
            // page patient
            'can_add_patient',
            'can_archive_patient',
            // page bénéficiaire
            'can_modify_patient',
            'can_modify_parcours',
            // page des onglets bénéficiaire
            'can_view_page_patient_accueil',
            // page d'accueil
            'can_view_colonne_evaluateur',
        ]
    ];

    /**
     * @var array Les id_role_user d'un utilisateur
     *
     * Les id_role_user possibles sont:
     * '1' : super admin
     * '2' : coordinateur
     * '3' : intervenant
     * '4' : référant
     * '5' : évaluateur Peps
     * '6' : responsable_structure
     * '7' : superviseur
     */
    private array $role_user_ids;

    /**
     * @var array Les rôles d'un utilisateur
     */
    private array $roles_user;

    /**
     * @var string L'id statut structure de la structure auquel appartient l'utilisateur
     *
     * Les id_statut_structure sont:
     * '1' : Maison Sport Santé
     * '2' : Centre Evaluateur
     * '3' : Structure Sportive
     * '4' : Centre PEPS
     */
    private $id_statut_structure;

    /**
     * @var bool Si le coordonnateur est un coordonnateur PEPS
     */
    private $est_coordinateur_peps;

    /**
     * Permissions constructor.
     *
     * @param array $session the session array
     */
    public function __construct(array $session)
    {
        $this->id_statut_structure = $session['id_statut_structure'];
        $this->est_coordinateur_peps = $session['est_coordinateur_peps'];
        $this->role_user_ids = $session['role_user_ids'];
        $this->roles_user = $this->convertIdsToRolesUser();
    }

    /**
     * Return si l'utilisateur a la permission demandée.
     *
     * @param string $permission la permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles_user as $role) {
            if (in_array($permission, $this->permissions[$role])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return si l'utilisateur a le rôle demandé.
     *
     * @param string $role le rôle
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles_user);
    }

    /**
     * @return bool return si l'utilisateur est intervenant et a au moins un autre rôle
     */
    public function isIntervenantAndOtherRole(): bool
    {
        return in_array(self::INTERVENANT, $this->roles_user) && count($this->roles_user) > 1;
    }

    /**
     * Return the roles of the user, [] si invalide
     *
     * @return array
     */
    private function convertIdsToRolesUser(): array
    {
        $roles = [];

        foreach ($this->role_user_ids as $id_role_user) {
            switch ($id_role_user) {
                case '1':
                    $roles[] = self::SUPER_ADMIN;
                    break;
                case '3':
                    $roles[] = self::INTERVENANT;
                    break;
                case '4':
                    $roles[] = self::REFERENT;
                    break;
                case '5':
                    $roles[] = self::EVALUATEUR;
                    break;
                case '6':
                    $roles[] = self::RESPONSABLE_STRUCTURE;
                    break;
                case '7':
                    $roles[] = self::SUPERVISEUR_PEPS;
                    break;
                case '8':
                    $roles[] = self::SECRETAIRE;
                    break;
                case '2':
                    if ($this->est_coordinateur_peps) {
                        $roles[] = self::COORDONNATEUR_PEPS;
                        break;
                    }
                    if ($this->id_statut_structure == '1') {
                        $roles[] = self::COORDONNATEUR_MSS;
                        break;
                    }
                    $roles[] = self::COORDONNATEUR_NON_MSS;
                    break;
            }
        }

        return $roles;
    }

    /**
     * Return the roles of the user, [] si invalide
     *
     * @return array
     */
    public function getRolesUser(): array
    {
        return $this->roles_user;
    }
}