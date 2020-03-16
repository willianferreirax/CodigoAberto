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
    "facebook_page" => "ads",
    "facebook_author" => "dsa",
    "facebook_appId" => "das",
    "twitter_creator" => "d",
    "twitter_site" => "a"
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


define("FACEBOOK_LOGIN",[
     
]);

/**
 * SOCIAL LOGIN: GOOGLE
 */

define("GOOGLE_LOGIN",[
    
]);