<?php

require '../bootstrap/bootstrap.php';

\Sportsante86\Sapa\Outils\SapaLogger::get()->info(
    'User ' . $_SESSION['email_connecte'] . ' logout successfully',
    ['event' => 'authn_logout_success:' . $_SESSION['email_connecte']]
);

session_destroy();

//Redirection vers la page de connection
header('Location: /index.php');