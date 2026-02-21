<?php

namespace scc\Controllers;

use scc\Controllers\BaseController;
use scc\Models\Agents;

class Main extends BaseController
{
    // =======================================================
    public function index()
    {
        // Verifique se não há nenhum usuário ativo na sessão.
        if(!check_session())
        {
            $this->login_frm();
            return;
        }

        $data['user'] = $_SESSION['user'];

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('homepage', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    // LOGIN
    // =======================================================
    public function login_frm()
    {
        // Verificar se já existe um usuário na sessão.
        if(check_session())
        {
            $this->index();
            return;
        }
            
        // Verificar se há erros (após login_submit)
        $data = [];
        if(!empty($_SESSION['validation_errors']))
        {
            $data['validation_errors'] = $_SESSION['validation_errors'];
            unset($_SESSION['validation_errors']);
        }
        //A verificação indica que o login é inválido.
        if(!empty($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        // Exibir formulário de login
        $this->view('layouts/html_header');
        $this->view('login_frm', $data);
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function login_submit()
    {
        // Verifique se já existe uma sessão ativa.
        if(check_session()){
            $this->index();
            return;
        }

        // verificar se houve uma solicitação de postagem
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // validação de formulário
        $validation_errors = [];
        if(empty($_POST['text_username']) || empty($_POST['text_password'])){
            $validation_errors[] = "Username e password são obrigatórios.";
        }

        // check if there are validation errors
        if(!empty($validation_errors)){
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        // get form data
        $username = $_POST['text_username'];
        $password = $_POST['text_password'];

        // Verifique se o nome de usuário é um e-mail válido e se tem entre 5 e 50 caracteres.
        if(!filter_var($username, FILTER_VALIDATE_EMAIL))
        {
            $validation_errors[] = 'O username tem que ser um email válido.';
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        // Verifique se o nome de usuário tem entre 5 e 50 caracteres.
        if(strlen($username) < 5 || strlen($username) > 50){
            $validation_errors[] = 'O username deve ter entre 5 e 50 caracteres.';
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        // Verifique se a senha é válida.
        if(strlen($password) < 6 || strlen($password) > 12){
            $validation_errors[] = 'A password deve ter entre 6 e 12 caracteres.';
            // $_SESSION['validation_errors'] = $validation_errors;
            // $this->login_frm();
            // return;
        }

        $model = new Agents();
        $result = $model->check_login($username, $password);
        if(!$result['status']){

            // logger
            logger("$username - login invalido", 'error');

            // Login Invalido
            $_SESSION['server_error'] = 'Login inválido.';
            $this->login_frm();
            return;

        }

        // logger
        logger("$username - Login com sucesso");

        // Carregar informações do usuário para a sessão
        $results = $model->get_user_data($username);

        // Adiciona o usuario a sessão 
        $_SESSION['user'] = $results['data'];

        // Atualiza a data do último login no bd
        $results = $model->set_user_last_login($_SESSION['user']->id);

        // Ir para a página principal
        $this->index();
    }

    // =======================================================
    public function logout()
    {
        // disable direct access to logout
        if(!check_session()){
            $this->index();
            return;
        }

        // logger
        logger($_SESSION['user']->name . ' - fez logout');

        // clear user from session
        unset($_SESSION['user']);

        // go to index (login form)
        $this->index();
    }
}