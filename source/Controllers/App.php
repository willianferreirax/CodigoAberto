<?php

namespace Source\Controllers;

use Source\Models\User;

class App extends Controller{
    /**@var User */
    protected $user;

    public function __construct($router)
    {
        parent::__construct($router);

        //restrição de acesso
        if(empty($_SESSION["user"]) || !$this->user = (new User())->findById($_SESSION["user"])){
            unset($_SESSION["user"]);

            flash("error","Acesso negado. Favor logue-se");
            $this->router->redirect("web.login");
        }
    }

    public function home():void{
        $head = $this->seo->optimize(
            "Bem-vindo(a) {$this->user->firstname}". site('name'),
            site("desc"),
            $this->router->route('app.home'),
            routeImage("Conta de {$this->user->firstname}")
        )->render();

        echo $this->view->render('theme/dashboard', [
            'head' => $head,
            'user' => $this->user
        ]);
    }

    public function logoff():void{
        unset($_SESSION["user"]);

        flash(
            "info",
            "Você saiu com sucesso, {$this->user->firstname}",
        );

        $this->router->redirect("web.login");

    }
}