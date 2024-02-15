<?php

function erreur($err = '')
{
    $mess = ($err != '') ? $err : 'Une erreur inconnue s\'est produite';
    exit(
        '<p>' . $mess . '</p>
   <p>Cliquez <a href="/index.php">ici</a> pour revenir à la page d\'accueil</p>'
    );
}

function erreur_invalid_page()
{
    \Sportsante86\Sapa\Outils\SapaLogger::get()->critical(
        'User ' . $_SESSION['email_connecte'] . ':' . $_SESSION['id_user'] . ' attempted to access a page that does not exist',
    );

    $previous = "javascript:history.go(-1)";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $previous = $_SERVER['HTTP_REFERER'];
    }

    exit(
        '<p>La page demandée n\'existe pas.</p>
         <a href="' . $previous . '">Retour à la page précédente</a>'
    );
}

function erreur_permission()
{
    \Sportsante86\Sapa\Outils\SapaLogger::get()->critical(
        'User ' . $_SESSION['email_connecte'] . ':' . $_SESSION['id_user'] . ' attempted to access a resource without entitlement',
        [
            'event' => 'authz_fail:' . $_SESSION['email_connecte'],
        ]
    );

    $previous = "javascript:history.go(-1)";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $previous = $_SERVER['HTTP_REFERER'];
    }

    exit(
        '<p>Vous n\'avez pas la permission d\'accéder à cette page.</p>
         <a href="' . $previous . '">Retour à la page précédente</a>'
    );
}

function erreur_file_not_found($filename)
{
    \Sportsante86\Sapa\Outils\SapaLogger::get()->critical(
        'User ' . $_SESSION['email_connecte'] . ':' . $_SESSION['id_user'] . ' attempted to access a file that does not exist',
        [
            'data' => $filename,
        ]
    );

    $previous = "javascript:history.go(-1)";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $previous = $_SERVER['HTTP_REFERER'];
    }

    exit(
        '<p>Le fichier demandé n\'a pas été trouvé.</p>
         <a href="' . $previous . '">Retour à la page précédente</a>'
    );
}

function force_connected()
{
    if (!isset($_SESSION)) {
        session_start();
    }
    if (empty($_SESSION['is_connected'])) {
        \Sportsante86\Sapa\Outils\SapaLogger::get()->warning('Not connected user attempted to access page');
        erreur(ERR_IS_CO);
    }
}

function erreur_download_file()
{
    $previous = "javascript:history.go(-1)";
    if (isset($_SERVER['HTTP_REFERER'])) {
        $previous = $_SERVER['HTTP_REFERER'];
    }

    exit(
        '<p>Erreur lors du téléchargement du fichier.</p>
         <a href="' . $previous . '">Retour à la page précédente</a>'
    );
}