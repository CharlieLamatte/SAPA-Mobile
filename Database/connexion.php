<?php

try {
    $host = 'localhost';
    $dbname = 'sportsanzbtest2';
    $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8';
    $username = 'root';
    $password = 'root';

    $bdd = new PDO($dsn, $username, $password);
    $bdd->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    \Sportsante86\Sapa\Outils\SapaLogger::get()->alert(
        'Create PDO instance failed',
        ['error_message' => $e->getMessage(),]
    );
    erreur();
}