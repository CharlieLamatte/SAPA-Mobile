<?php

use Sportsante86\Sapa\Model\User;
use Sportsante86\Sapa\Outils\Permissions;

require '../../bootstrap/bootstrap.php';
require '../../Outils/JsonFileProtection.php';

// get posted data
$input_data = json_decode(file_get_contents("php://input"), true);

if (!empty($input_data['id_user']) &&
    !empty($input_data['nom_user']) &&
    !empty($input_data['prenom_user']) &&
    !empty($input_data['role_user_ids']) &&
    !empty($input_data['email_user']) &&
    is_bool($input_data['is_mdp_modified'])) {
    $user = new User($bdd);
    $p = new Permissions($_SESSION);

    $data = [
        'id_user' => $input_data['id_user'],
        'nom_user' => $input_data['nom_user'],
        'prenom_user' => $input_data['prenom_user'],
        'email_user' => $input_data['email_user'],
        'id_territoire' => $input_data['id_territoire'],
        'id_structure' => $input_data['structure']['id_structure'],
        'role_user_ids' => $input_data['role_user_ids'],
        'mdp' => $input_data['mdp'],
        'tel_f_user' => $input_data['tel_f_user'],
        'tel_p_user' => $input_data['tel_p_user'],

        'is_mdp_modified' => $input_data['is_mdp_modified'],
        'settings' => $input_data['settings'] ?? [],
        'est_coordinateur_peps' => $input_data['est_coordinateur_peps'] ?? false,

        'id_statut_intervenant' => $input_data['id_statut_intervenant'],
        'diplomes' => $input_data['diplomes'] ?? [],
        'numero_carte' => $input_data['numero_carte'],
        'nom_fonction' => $input_data['nom_fonction'],
    ];
    // seul l'admin peut désactiver un compte
    if ($p->hasRole(Permissions::SUPER_ADMIN)) {
        $data['is_deactivated'] = $input_data['is_deactivated'];
    }

    $update_ok = $user->update($data);

    if ($update_ok) {
        $item = $user->readOne($input_data['id_user']);

        \Sportsante86\Sapa\Outils\SapaLogger::get()->info(
            'User ' . $_SESSION['email_connecte'] . ' updated user ' . $item['email_user'] . ' (role '. $item['role_user']. ') successfully',
            ['event' => 'update:user']
        );

        if ($p->hasRole(Permissions::SUPER_ADMIN) && $input_data['is_deactivated']) {
            \Sportsante86\Sapa\Outils\SapaLogger::get()->info(
                'User ' . $_SESSION['email_connecte'] . ' deactivated user ' . $item['email_user'] . ' (role '. $item['role_user']. ') successfully',
                ['event' => 'update:user']
            );
        }

        // set response code - 200 OK
        http_response_code(200);
        echo json_encode($item);
    } else {
        // set response code - 500 Internal Server Error
        http_response_code(500);
        echo json_encode(["message" => "Une erreur inconnue s'est produite."]);
        \Sportsante86\Sapa\Outils\SapaLogger::get()->error(
            'An unexpected error occurred when user ' . $_SESSION['email_connecte'] . ':' . $_SESSION['id_user'] . ' attempted to access a resource',
            [
                'error_message' => $user->getErrorMessage(),
                'data' => json_encode($input_data),
            ]
        );
    }
} else {
    // set response code - 400 bad request
    http_response_code(400);
    echo json_encode(["message" => "Data is incomplete."]);
    \Sportsante86\Sapa\Outils\SapaLogger::get()->error(
        'User ' . $_SESSION['email_connecte'] . ':' . $_SESSION['id_user'] . ' attempted to access a resource using incomplete data',
        [
            'data' => json_encode($input_data)
        ]
    );
}