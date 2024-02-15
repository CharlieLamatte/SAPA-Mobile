/**
  calcul de l'évolution du poids du patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_poids;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_poids (id_patient_in INT)
    RETURNS FLOAT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN poids_max - poids_min
               END as evolution
    FROM (select tp.id_test_physio, poids poids_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, poids poids_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution de la taille du patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_taille;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_taille (id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN taille_max - taille_min
               END as evolution
    FROM (select tp.id_test_physio, taille taille_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, taille taille_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution de l'IMC du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_IMC;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_IMC (id_patient_in INT)
    RETURNS FLOAT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN imc_max - imc_min
               END as evolution
    FROM (select tp.id_test_physio, IMC imc_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, IMC imc_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution du tour de taille du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_tourdetaille;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_tourdetaille (id_patient_in INT)
    RETURNS FLOAT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN tourdetaille_max - tourdetaille_min
               END as evolution
    FROM (select tp.id_test_physio, tour_taille tourdetaille_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, tour_taille tourdetaille_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution de la saturation en O2 du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_sat_o2;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_sat_o2 (id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN sat_max - sat_min
               END as evolution
    FROM (select tp.id_test_physio, saturation_repos sat_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, saturation_repos sat_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution de la fréquence cardiaque au repos du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_fc_repos;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_fc_repos (id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN fc_repos_max - fc_repos_min
               END as evolution
    FROM (select tp.id_test_physio, fc_repos fc_repos_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, fc_repos fc_repos_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution de la fréquence cardiaque max mesurée du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_fc_max_mes;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_fc_max_mes (id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN fc_mes_max - fc_mes_min
               END as evolution
    FROM (select tp.id_test_physio, fc_max_mesuree fc_mes_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, fc_max_mesuree fc_mes_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution de la fréquence cardiaque max théorique du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_fc_max_th;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_fc_max_th (id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN fc_th_max - fc_th_min
               END as evolution
    FROM (select tp.id_test_physio, fc_max_theo fc_th_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, fc_max_theo fc_th_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul de l'évolution du score BORG de repos du patient entre la première et la dernière évaluation
 */
DROP FUNCTION IF EXISTS evolution_eval_phys_borg_repos;
DELIMITER $$
CREATE FUNCTION evolution_eval_phys_borg_repos (id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_test_physio is not null AND tmax.id_test_physio is not null AND
                    (tmin.id_test_physio <> tmax.id_test_physio)
                   THEN borg_repos_max - borg_repos_min
               END as evolution
    FROM (select tp.id_test_physio, borg_repos borg_repos_min, date_eval date_min
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select tp.id_test_physio, borg_repos borg_repos_max, date_eval date_max
          from evaluations
                   join test_physio tp on evaluations.id_evaluation = tp.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul l'évolution de la distance parcourue par un patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_aerobie;
DELIMITER $$
CREATE FUNCTION evolution_eval_aerobie(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_apt_aerobie is not null AND tmax.id_eval_apt_aerobie is not null AND
                    (tmin.id_eval_apt_aerobie <> tmax.id_eval_apt_aerobie)
                   THEN dist_max - dist_min
               END as evolution
    FROM (select id_eval_apt_aerobie, distance_parcourue dist_min, date_eval date_min
          from evaluations
                   join eval_apt_aerobie eaa on evaluations.id_evaluation = eaa.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_apt_aerobie, distance_parcourue dist_max, date_eval date_max
          from evaluations
                   join eval_apt_aerobie eaa on evaluations.id_evaluation = eaa.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul l'évolution de la force de la main gauche par un patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_mb_sup_main_gauche;
DELIMITER $$
CREATE FUNCTION evolution_eval_mb_sup_main_gauche(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_force_musc_mb_sup is not null AND tmax.id_eval_force_musc_mb_sup is not null AND
                    (tmin.id_eval_force_musc_mb_sup <> tmax.id_eval_force_musc_mb_sup)
                   THEN force_max - force_min
               END as evolution
    FROM (select id_eval_force_musc_mb_sup, mg force_min, date_eval date_min
          from evaluations
                   join eval_force_musc_mb_sup efmms on evaluations.id_evaluation = efmms.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_force_musc_mb_sup, mg force_max, date_eval date_max
          from evaluations
                   join eval_force_musc_mb_sup efmms on evaluations.id_evaluation = efmms.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul l'évolution de la force de la main droite par un patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_mb_sup_main_droite;
DELIMITER $$
CREATE FUNCTION evolution_eval_mb_sup_main_droite(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_force_musc_mb_sup is not null AND tmax.id_eval_force_musc_mb_sup is not null AND
                    (tmin.id_eval_force_musc_mb_sup <> tmax.id_eval_force_musc_mb_sup)
                   THEN force_max - force_min
               END as evolution
    FROM (select id_eval_force_musc_mb_sup, md force_min, date_eval date_min
          from evaluations
                   join eval_force_musc_mb_sup efmms on evaluations.id_evaluation = efmms.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_force_musc_mb_sup, md force_max, date_eval date_max
          from evaluations
                   join eval_force_musc_mb_sup efmms on evaluations.id_evaluation = efmms.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul l'évolution de la durée en équilibre pour le pied droit par un patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_equilibre_statique_pied_droit;
DELIMITER $$
CREATE FUNCTION evolution_eval_equilibre_statique_pied_droit(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_eq_stat is not null AND tmax.id_eval_eq_stat is not null AND
                    (tmin.id_eval_eq_stat <> tmax.id_eval_eq_stat)
                   THEN pied_droit_sol_max - pied_droit_sol_min
               END as evolution
    FROM (select id_eval_eq_stat, pied_droit_sol pied_droit_sol_min, date_eval date_min
          from evaluations
                   join eval_eq_stat ees on evaluations.id_evaluation = ees.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_eq_stat, pied_droit_sol pied_droit_sol_max, date_eval date_max
          from evaluations
                   join eval_eq_stat ees on evaluations.id_evaluation = ees.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  calcul l'évolution de la durée en équilibre pour le pied gauche par un patient entre la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_equilibre_statique_pied_gauche;
DELIMITER $$
CREATE FUNCTION evolution_eval_equilibre_statique_pied_gauche(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_eq_stat is not null AND tmax.id_eval_eq_stat is not null AND
                    (tmin.id_eval_eq_stat <> tmax.id_eval_eq_stat)
                   THEN pied_gauche_sol_max - pied_gauche_sol_min
               END as evolution
    FROM (select id_eval_eq_stat, pied_gauche_sol pied_gauche_sol_min, date_eval date_min
          from evaluations
                   join eval_eq_stat ees on evaluations.id_evaluation = ees.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_eq_stat, pied_gauche_sol pied_gauche_sol_max, date_eval date_max
          from evaluations
                   join eval_eq_stat ees on evaluations.id_evaluation = ees.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;


/**
  Calcul de l'évolution de la distance au sol (test souplesse) par un patient entre la première
  et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_souplesse;
DELIMITER $$
CREATE FUNCTION evolution_eval_souplesse(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_soupl is not null AND tmax.id_eval_soupl is not null AND
                    (tmin.id_eval_soupl <> tmax.id_eval_soupl)
                   THEN distance_max - distance_min
               END as evolution
    FROM (select id_eval_soupl, distance distance_min, date_eval date_min
          from evaluations
                   join eval_soupl es on evaluations.id_evaluation = es.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_soupl, distance distance_max, date_eval date_max
          from evaluations
                   join eval_soupl es on evaluations.id_evaluation = es.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  Calcul de l'évolution de la distance en haut pour la main gauche (test mobilité scapulo-humerale) par un patient entre
  la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_mobilite_scapulo_humerale_main_gauche;
DELIMITER $$
CREATE FUNCTION evolution_eval_mobilite_scapulo_humerale_main_gauche(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_mobilite_scapulo_humerale is not null AND
                    tmax.id_eval_mobilite_scapulo_humerale is not null AND
                    (tmin.id_eval_mobilite_scapulo_humerale <> tmax.id_eval_mobilite_scapulo_humerale)
                   THEN main_gauche_haut_max - main_gauche_haut_min
               END as evolution
    FROM (select id_eval_mobilite_scapulo_humerale, main_droite_haut main_gauche_haut_min, date_eval date_min
          from evaluations
                   join eval_mobilite_scapulo_humerale emsh on evaluations.id_evaluation = emsh.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_mobilite_scapulo_humerale, main_gauche_haut main_gauche_haut_max, date_eval date_max
          from evaluations
                   join eval_mobilite_scapulo_humerale emsh on evaluations.id_evaluation = emsh.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  Calcul de l'évolution de la distance en haut pour la main droite (test mobilité scapulo-humerale) par un patient entre
  la première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_mobilite_scapulo_humerale_main_droite;
DELIMITER $$
CREATE FUNCTION evolution_eval_mobilite_scapulo_humerale_main_droite(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_mobilite_scapulo_humerale is not null AND
                    tmax.id_eval_mobilite_scapulo_humerale is not null AND
                    (tmin.id_eval_mobilite_scapulo_humerale <> tmax.id_eval_mobilite_scapulo_humerale)
                   THEN main_droite_haut_max - main_droite_haut_min
               END as evolution
    FROM (select id_eval_mobilite_scapulo_humerale, main_droite_haut main_droite_haut_min, date_eval date_min
          from evaluations
                   join eval_mobilite_scapulo_humerale emsh on evaluations.id_evaluation = emsh.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_mobilite_scapulo_humerale, main_droite_haut main_droite_haut_max, date_eval date_max
          from evaluations
                   join eval_mobilite_scapulo_humerale emsh on evaluations.id_evaluation = emsh.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  Calcul de l'évolution du nombre de levers (test endurance musculaire membres inférieurs) par un patient entre la
  première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_endurance_mb_inf_nombre_levers;
DELIMITER $$
CREATE FUNCTION evolution_eval_endurance_mb_inf_nombre_levers(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_end_musc_mb_inf is not null AND tmax.id_eval_end_musc_mb_inf is not null AND
                    (tmin.id_eval_end_musc_mb_inf <> tmax.id_eval_end_musc_mb_inf)
                   THEN nb_lever_max - nb_lever_min
               END as evolution
    FROM (select id_eval_end_musc_mb_inf, nb_lever nb_lever_min, date_eval date_min
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_end_musc_mb_inf, nb_lever nb_lever_max, date_eval date_max
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  Calcul de l'évolution de la FC à 30 secondes (test endurance musculaire membres inférieurs) par un patient entre la
  première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_endurance_mb_inf_fc30;
DELIMITER $$
CREATE FUNCTION evolution_eval_endurance_mb_inf_fc30(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_end_musc_mb_inf is not null AND tmax.id_eval_end_musc_mb_inf is not null AND
                    (tmin.id_eval_end_musc_mb_inf <> tmax.id_eval_end_musc_mb_inf)
                   THEN fc30_max - fc30_min
               END as evolution
    FROM (select id_eval_end_musc_mb_inf, fc30 fc30_min, date_eval date_min
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_end_musc_mb_inf, fc30 fc30_max, date_eval date_max
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  Calcul de l'évolution de la FC à 30 secondes (test endurance musculaire membres inférieurs) par un patient entre la
  première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_endurance_mb_inf_sat30;
DELIMITER $$
CREATE FUNCTION evolution_eval_endurance_mb_inf_sat30(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_end_musc_mb_inf is not null AND tmax.id_eval_end_musc_mb_inf is not null AND
                    (tmin.id_eval_end_musc_mb_inf <> tmax.id_eval_end_musc_mb_inf)
                   THEN sat30_min - sat30_max
               END as evolution
    FROM (select id_eval_end_musc_mb_inf, sat30 sat30_min, date_eval date_min
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_end_musc_mb_inf, sat30 sat30_max, date_eval date_max
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;

/**
  Calcul de l'évolution de la FC à 30 secondes (test endurance musculaire membres inférieurs) par un patient entre la
  première et la dernière évaluation réalisée
 */
DROP FUNCTION IF EXISTS evolution_eval_endurance_mb_inf_borg30;
DELIMITER $$
CREATE FUNCTION evolution_eval_endurance_mb_inf_borg30(id_patient_in INT)
    RETURNS INT
BEGIN
    DECLARE evolution INT DEFAULT null;

    SELECT CASE
               WHEN tmin.id_eval_end_musc_mb_inf is not null AND tmax.id_eval_end_musc_mb_inf is not null AND
                    (tmin.id_eval_end_musc_mb_inf <> tmax.id_eval_end_musc_mb_inf)
                   THEN borg30_min - borg30_max
               END as evolution
    FROM (select id_eval_end_musc_mb_inf, borg30 borg30_min, date_eval date_min
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval
          LIMIT 1) as tmin
             JOIN
         (select id_eval_end_musc_mb_inf, borg30 borg30_max, date_eval date_max
          from evaluations
                   join eval_endurance_musc_mb_inf eemmi on evaluations.id_evaluation = eemmi.id_evaluation
          where evaluations.id_patient = id_patient_in
          order by id_type_eval DESC
          LIMIT 1) as tmax
    INTO evolution;

    RETURN evolution;
END $$

DELIMITER ;