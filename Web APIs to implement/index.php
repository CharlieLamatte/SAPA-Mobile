<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Connexion</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/portfolio-item.css" rel="stylesheet">

    <script type="text/javascript" src='/js/jquery.js'></script>
    <script type="text/javascript" src="/js/bootstrap.min.js"></script>

</head>

<body>
<?php

use Sportsante86\Sapa\Outils\Permissions;

require 'bootstrap/bootstrap.php';
require 'PHP/header_index.php';

if (!empty($_POST['identifiant']) && !empty($_POST['pswd'])) {
    try {
        $a = new Sportsante86\Sapa\Outils\Authentification($bdd);

        $a->login($_POST['identifiant'], $_POST['pswd']);

        $permissions = new Permissions($_SESSION);

        if ($permissions->hasRole(Permissions::RESPONSABLE_STRUCTURE)) {
            \Sportsante86\Sapa\Outils\SapaLogger::get()->info(
                'User ' . $_POST['identifiant'] . ' login successfully',
                ['event' => 'authn_login_success:' . $_POST['identifiant']]
            );
            header('Location: ../PHP/ResponsableStructure/Accueil.php');
        } elseif (
            $permissions->hasRole(Permissions::SUPER_ADMIN) ||
            $permissions->hasRole(Permissions::SUPERVISEUR_PEPS)
        ) {
            \Sportsante86\Sapa\Outils\SapaLogger::get()->info(
                'User ' . $_POST['identifiant'] . ' login successfully',
                ['event' => 'authn_login_success:' . $_POST['identifiant']]
            );
            header('Location: ../PHP/Settings/TableauDeBord.php');
        } elseif (
            $permissions->hasRole(Permissions::INTERVENANT) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_NON_MSS) ||
            $permissions->hasRole(Permissions::COORDONNATEUR_PEPS) ||
            $permissions->hasRole(Permissions::REFERENT) ||
            $permissions->hasRole(Permissions::EVALUATEUR) ||
            $permissions->hasRole(Permissions::SECRETAIRE)
        ) {
            \Sportsante86\Sapa\Outils\SapaLogger::get()->info(
                'User ' . $_POST['identifiant'] . ' login successfully',
                ['event' => 'authn_login_success:' . $_POST['identifiant']]
            );
            header('Location: ../PHP/Accueil_liste.php');
        } else {
            throw new \Exception("Erreur: role invalide");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        \Sportsante86\Sapa\Outils\SapaLogger::get()->warning(
            'User ' . $_POST['identifiant'] . ' login failed',
            [
                'event' => 'authn_login_fail:' . $_POST['identifiant'],
                'error_message' => $error_message,
            ]
        );
    }
}
?>

<!-- Page Content -->
<div class="container">
    <fieldset id="connexion">
        <div style="text-align: center">
            <!-- On défini une interface de connexion -->
            <legend id="legendCo">Connexion</legend>
        </div>

        <form class="form-horizontal" method="post" action="">
            <div class="form-group">
                <label for="identifiant" class="col-sm-2 control-label">Adresse mail</label>
                <div class="col-sm-10">
                    <input type="email" name="identifiant" id="identifiant" autofocus placeholder="Adresse mail"
                           class="form-control" size="30" required/>
                </div>
            </div>
            <div class="form-group">
                <label for="pswd" class="col-sm-2 control-label">Mot de passe</label>
                <div class="col-sm-10">
                    <input type="password" name="pswd" id="pswd" size="30" class="form-control"
                           placeholder="Mot de passe" required/>
                </div>
            </div>
            <div class="text-right" style="font-size: 13px">
                <a href="/PHP/account_recovery/step1.php">Vous avez oublié votre mot de passe ?</a>
            </div>
            <div class="form-group">
                <div class="col-sm-12 text-center">
                    <button type="submit" class="btn btn-default">Connexion</button>
                </div>
            </div>
        </form>

        <?php
        if (isset($error_message)): ?>
            <p id="connexion-error-message" style="color: red" class="text-center">
                <?= $error_message; ?>
            </p>
        <?php
        endif; ?>
    </fieldset>

    <?php
    require 'PHP/footer.php'; ?>
</div>
<!-- /content -->

<!-- jQuery -->
<script src="js/jquery.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>
<script src="js/index.js"></script>
<script src="js/fixHeader.js"></script>
</body>

</html>