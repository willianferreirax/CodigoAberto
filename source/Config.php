<?php

define("SITE",[
    "name" => "Auth em MVC com PHP",
    "desc"  => "Aprendendo oauth2 em aplicação de autenticação em MVC com PHP",
    "domain" => "localauth.com",
    "locale" => "pt-br",
    "root" => "http://localhost/CodigoAberto/"
]);

/**
 * SITE MINIFY
 */

 if($_SERVER['SERVER_NAME'] == "localhost"){
     require __DIR__.'/Minify.php';
 }

define("DATA_LAYER_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => "auth",
    "username" => "root",
    "passwd" => "",
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);
/**
 * Social Config
 */

define("SOCIAL",[
    "facebook_page" => "teste",
    "facebook_author" => "teste",
    "facebook_appId" => "teste",
    "twitter_creator" => "teste",
    "twitter_site" => "teste"
]);

/**
 * MAIL CONNECT
 * 
 * API SendGrid
 */

define("MAIL",[
    "host" => "",
    "port" => "",
    "user" => "",
    "passwd" => "",
    "from_name" => "Willian Ferreira",
    "from_email" => "willian1948@hotmail.com"
]);


/**
 * SOCIAL LOGIN: FACEBOOK
 */
//Criar aplicativo no developers.facebook
//o facebook requer um dominio com https
define("FACEBOOK_LOGIN",[
     "clientId" =>"",//dado pelo aplicativo em developers.facebook
     "clientSecret" =>"",//same
     "redirectUrl" => SITE['root']."/facebook",
     "graphApiVersion" => "v4.0"
]);


/**
 * SOCIAL LOGIN: GOOGLE
 */
//o google requer que a url não tenha "www"
define("GOOGLE_LOGIN",[
    "clientId" =>"",
    "clientSecret" =>"",
    "redirectUrl" =>SITE['root'] . "/google"
]);