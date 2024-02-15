<?php

/**
 * Ce fichier permet de récupérer les données de session et les paramètres de l'utilisateur
 */

use Sportsante86\Sapa\Outils\Permissions;

require '../../bootstrap/bootstrap.php';

force_connected();

if (!empty($_SESSION['email_connecte']) &&
    !empty($_SESSION['nom_connecte']) &&
    !empty($_SESSION['prenom_connecte']) &&
    !empty($_SESSION['id_user'])) {
    // recupération du setting nombre_elements_tableaux
    $query_settings = "
        SELECT valeur
        FROM users
                 JOIN user_settings us on users.id_user = us.id_user
                 JOIN settings s on us.id_setting = s.id_setting
        WHERE users.id_user = :id_user
          AND nom LIKE 'nombre_elements_tableaux'";
    $stmt_settings = $bdd->prepare($query_settings);
    $stmt_settings->bindValue(':id_user', $_SESSION['id_user']);
    $stmt_settings->execute();

    $data = $stmt_settings->fetch();
    $valeur = $data['valeur'] ?? "10"; // on la valeur par défaut est de 10 éléments dans le tableau

    $permissions = new Permissions($_SESSION);

    $session = [
        "id_structure" => $_SESSION['id_structure'],
        "id_region" => $_SESSION['id_region'],
        "id_type_territoire" => $_SESSION['id_type_territoire'],
        "id_territoire" => $_SESSION['id_territoire'],
        "id_statut_structure" => $_SESSION['id_statut_structure'],
        "nombre_elements_tableaux" => $valeur,
        "email_connecte" => $_SESSION['email_connecte'],
        "nom_connecte" => $_SESSION['nom_connecte'],
        "prenom_connecte" => $_SESSION['prenom_connecte'],
        "role_user_ids" => $_SESSION['role_user_ids'],
        "id_user" => $_SESSION['id_user'],
        "is_connected" => $_SESSION['is_connected'],
        "roles_user" => $permissions->getRolesUser(),
    ];

    // set response code - 200 OK
    http_response_code(200);
    echo json_encode($session);
} else {
    // set response code - 500 Internal Server Error
    http_response_code(500);
    echo json_encode(["message" => "Une erreur inconnue s'est produite."]);
    \Sportsante86\Sapa\Outils\SapaLogger::get()->critical(
        'An unexpected error occurred when user ' . $_SESSION['email_connecte'] . ':' . $_SESSION['id_user'] . ' attempted to access a resource',
        [
            'session' => $_SESSION
        ]
    );
}