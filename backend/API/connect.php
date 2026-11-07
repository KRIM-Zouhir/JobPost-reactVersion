<?php
    require_once __DIR__ . '/../../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    try{
        $db = new PDO('mysql:host=localhost;dbname=jobboard;charset=utf8;', 'root', $_ENV['pwdhost']);
        return $db;
    }catch (Exception $e) {
        echo "An error has occured with DB!";
    }

?>