<?php
namespace Source\Controllers;

use Exception;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\Google;
use Source\Models\User;
use Source\Controllers\Controller;
use Source\Support\Email;

class Auth extends Controller{
    
    public function __construct($router)
    {
        parent::__construct($router);
    }

    public function login($data):void{
        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        $passwd = filter_var($data["passwd"], FILTER_DEFAULT);

        if(!$email || !$passwd){
            echo $this->ajaxResponse("message",[
                "type" => "alert",
                "message" => "Informe seu Email e senha para logar"
            ]);
            return;
        }

        $user = (new User())->find("email = :e", "e={$email}")->fetch();

        if(!$user || !password_verify($passwd, $user->passwd)){
            echo $this->ajaxResponse("message",[
                "type" => "error",
                "message" => "Email ou senha incorreto"
            ]);
            return;
        }

        /** SOCIAL VALIDATE */

        $this->socialValidate($user);

        $_SESSION["user"] = $user->id;

        echo $this->ajaxResponse("redirect",[
            "url" => $this->router->route("app.home")
        ]);
    }

    public function register($data):void{
        
        $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);
        
        if(in_array("",$data)){
            echo $this->ajaxResponse("message",[
                'type' => 'error',
                'message' => 'Preencha todos os campos'
            ]);
            return;
        }

        $user = new User();
        $user->first_name = $data["first_name"];
        $user->last_name = $data["last_name"];
        $user->email = $data["email"];
        $user->passwd = password_hash($data["passwd"],PASSWORD_DEFAULT);

        /** SOCIAL VALIDATE */

        $this->socialValidate($user);

        if(!$user->save()){
            echo $this->ajaxResponse("message",[
                'type' => 'error',
                'message' => $user->fail()->getMessage()
            ]);
            return;
        }

        $_SESSION['user'] = $user->id;

        echo $this->ajaxResponse('redirect',[
            "url" => $this->router->route('app.home')
        ]);
        
    }

    public function forget($data):void{
        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);

        if(!$email){
            echo $this->ajaxResponse("message",[
                'type' => 'alert',
                'message' => "informe seu email para recuperar a senha"
            ]);
            return;
        }

        $user = (new User())->find("email = :e", "e={$email}")->fetch();

        if(!$user){
            echo $this->ajaxResponse("message",[
                'type' => 'error',
                'message' => "o E-MAIL informado não está cadastrado"
            ]);
            return;
        }

        $user->forget = (md5(uniqid(rand(), true)));

        $user->save();

        $_SESSION["forget"] = $user->id;

        $email = new Email();

        $email->add(
            "Recupere sua senha |".site("name"),
            $this->view->render("emails/recover",[
                "user" =>$user,
                "link" =>$this->router->route("web.reset",[
                    "email" => $user->email,
                    "forget" => $user->forget
                ])
            ]),
            "{$user->first_name} {$user->last_name}",
            $user->email,
        )->send();

        flash("success", "enviamos o link de recuperação para seu email");
        
        echo $this->ajaxResponse("redirect",[
            "url" => $this->router->route("web.forget")
        ]);

    }

    public function reset($data):void{
        if(empty($_SESSION["forget"]) || !$user = (new User())->findById($_SESSION["forget"])){
            flash("error","Não foi possivel recupera, tente novamente");
            echo $this->ajaxResponse("redirect",[
                "url" => $this->router->route("web.forget")
            ]);
            return;
        }

        if(empty($data["password"]) || empty($data["password_re"])){
            echo $this->ajaxResponse("message",[
                'type' => 'alert',
                'message' => "Informe e repita sua nova senha"
            ]);
            return;
        }

        if($data["password"] != $data["password_re"]){
            echo $this->ajaxResponse("message",[
                'type' => 'error',
                'message' => "Você informou duas senhas diferentes"
            ]);
            return;
        }

        $user->passwd = $data["password"];
        $user->forget = null;

        if(!$user->save()){
            echo $this->ajaxResponse("message",[
                'type' => 'error',
                'message' => $user->fail->getMessage()
            ]);
            return;
        }

        unset($_SESSION["forget"]);

        flash("success", "sua senha foi atualizada com sucesso");

        echo $this->ajaxResponse("redirect",[
            "url" => $this->router->route("web.login")
        ]);
    }

    public function facebook() :void{
        $facebook = new Facebook(FACEBOOK_LOGIN);
        $error = filter_input(INPUT_GET,'error',FILTER_SANITIZE_STRIPPED);
        $code = filter_input(INPUT_GET,'code',FILTER_SANITIZE_STRIPPED);
        
        if(!$error && !$code){
            $auth_url = $facebook->getAuthorizationUrl(["scope"=>"email"]);
            header("Location:{$auth_url}");
            return;
        }

        if($error){
            flash("error","Não foi possivel logar com o facebook");
            $this->router->redirect('web.login');
        }

        if($code && empty($_SESSION['facebook_auth'])){
            try{
                $token = $facebook->getAccessToken("authorization_code", ["code"=>$code]);
                $_SESSION['facebook_auth'] = serialize($facebook->getResourceOwner($token));
            }catch(Exception $e){
                flash('error',"Não foi possivel logar com facebook");
                $this->router->redirect('web.login');
            }
        }
        /** @var $facebook_user FacebookUser */

        $facebook_user = unserialize($_SESSION['facebook_auth']);

        $user_by_id = (new User())->find("facebook_id = :id","id={$facebook_user->getId()}")->fetch();

        //LOGIN BY ID
        if($user_by_id){
            unset($_SESSION["facebook_auth"]);

            $_SESSION['user'] = $user_by_id->id;
            $this->router->redirect("app.home");
        }

        //LOGIN BY EMAIL
        $user_by_email = (new User())->find("email = :e","e={$facebook_user->getEmail()}")->fetch();

        if($user_by_email){
            flash('info',"Olá {$facebook_user->getFirstName()}, faça login para conectar seu facebook à sua conta");

            $this->router->redirect("web.login");
        }

        //REGISTER IF NOT
        $link =$this->router->route("web.login");
        flash(
            "info",
            "Olá {$facebook_user->getFirstName()}, <b>se já possui uma conta clique em <a title='fazer login' href='{$link}'>FAZER LOGIN</b>, ou complete seu cadastro"
        );
        $this->router->redirect("web.register");

    }

    //abrimos mão um pouco da metodologia DRY, para melhor integração do software
    //não integramos dinamicamente para impedir de no caso de uma rede mudar a regra, perdemos toda a integração
    
    public function google():void{
        $google = new Google(GOOGLE_LOGIN);
        $error = filter_input(INPUT_GET,'error',FILTER_SANITIZE_STRIPPED);
        $code = filter_input(INPUT_GET,'code',FILTER_SANITIZE_STRIPPED);
        
        if(!$error && !$code){
            $auth_url = $google->getAuthorizationUrl();
            header("Location:{$auth_url}");
            return;
        }

        if($error){
            flash("error","Não foi possivel logar com o google");
            $this->router->redirect('web.login');
        }

        if($code && empty($_SESSION['google_auth'])){
            try{
                $token = $google->getAccessToken("authorization_code", ["code"=>$code]);
                $_SESSION['google_auth'] = serialize($google->getResourceOwner($token));
            }catch(Exception $e){
                flash('error',"Não foi possivel logar com google");
                $this->router->redirect('web.login');
            }
        }
        /** @var $google_user GoogleUser */

        $google_user = unserialize($_SESSION['google_auth']);

        $user_by_id = (new User())->find("google_id = :id","id={$google_user->getId()}")->fetch();

        //LOGIN BY ID
        if($user_by_id){
            unset($_SESSION["google_auth"]);

            $_SESSION['user'] = $user_by_id->id;
            $this->router->redirect("app.home");
        }

        //LOGIN BY EMAIL
        $user_by_email = (new User())->find("email = :e","e={$google_user->getEmail()}")->fetch();

        if($user_by_email){
            flash('info',"Olá {$google_user->getFirstName()}, faça login para conectar seu google à sua conta");

            $this->router->redirect("web.login");
        }

        //REGISTER IF NOT
        $link =$this->router->route("web.login");
        flash(
            "info",
            "Olá {$google_user->getFirstName()}, <b>se já possui uma conta clique em <a title='fazer login' href='{$link}'>FAZER LOGIN</b>, ou complete seu cadastro"
        );
        $this->router->redirect("web.register");

    }

    public function socialValidate(User $user):void{

        /**
         * FACEBOOK
         */

        if(!empty($_SESSION["facebook_auth"])){
            /** @var $facebook_user FacebookUser */
            $facebook_user = unserialize($_SESSION["facebook_auth"]);
            
            $user->facebook_id = $facebook_user->getId();
            $user->photo = $facebook_user->getId();
            $user->save();

            unset($_SESSION["facebook_auth"]);
        }

        /**
         * GOOGLE
         */

        if(!empty($_SESSION["google_auth"])){
            /** @var $google_user GoogleUser */
            $google_user = unserialize($_SESSION["google_auth"]);
            
            $user->google_id = $google_user->getId();
            $user->photo = $google_user->getAvatar();
            $user->save();

            unset($_SESSION["google_auth"]);
        }
    }
}