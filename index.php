<?php

ob_start();
session_start();

require __DIR__ . "/vendor/autoload.php";

use CoffeeCode\Router\Router;

$router = new Router(site());


/**
 * WEB
 */
$router->namespace("Source\Controllers");

$router->group(null);
$router->get('/', 'Web:login', 'web.login');
$router->get('/cadastrar', 'Web:register', 'web.register');
$router->get('/recuperar', 'Web:forget', 'web.forget');//não bloqueia vc nao estar logado
$router->get('/senha/{email}/{forget}', 'Web:reset', 'web.reset');

/**
 * AUTH
 */
$router->group(null);
$router->post("/login", "Auth:login", "auth.login");
$router->post("/register", "Auth:register", "auth.register");
$router->post("/forget", "Auth:forget", "auth.forget");
$router->post("/reset", "Auth:reset", "auth.reset");

 /**
 * AUTH SOCIAL
 */
$router->group(null);
$router->get("/facebook","Auth:facebook","auth.facebook");
$router->get("/google","Auth:google","auth.google");


 /**
 * PROFILE
 */
$router->group("/me");
$router->get("/", "App:home", "App.home");
$router->get("/sair", "App:logoff", "App.logoff");


 /**
 * ERRORS
 */
$router->group("ops");
$router->get('/{errcode}', 'Web:error', 'web.error');

 /**
 * ROUTE PROCESS
 */
$router->dispatch();

/**
 * ERRORS PROCESS
 */
if($router->error()){
    $router->redirect("web.error",["errcode" => $router->error()]);
}

ob_end_flush();