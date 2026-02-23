<?php

namespace scc\Controllers;

use scc\Controllers\BaseController;
use scc\Models\Agents;

class Agent extends BaseController
{
    // =======================================================
    public function my_clients()
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
        }

        // obtem todos os clientes de agentes
        $id_agent = $_SESSION['user']->id;
        $model = new Agents();
        $results = $model->get_agent_clients($id_agent);

        $data['user'] = $_SESSION['user'];
        $data['clients'] = $results['data'];

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('agent_clients', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function new_client_frm()
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
        }

        $data['user'] = $_SESSION['user'];
        $data['flatpickr'] = true;

        // Verifica se há erros de validação.
        if(!empty($_SESSION['validation_errors'])){
            $data['validation_errors'] = $_SESSION['validation_errors'];
            unset($_SESSION['validation_errors']);
        }
        // Verifique se há algum erro no servidor.
        if(!empty($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('insert_client_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function new_client_submit()
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
        }

        // validação de formulário
        $validation_errors = [];

        // text_name
        if (empty($_POST['text_name'])) {
            $validation_errors[] = "Nome é de preenchimento obrigatório.";
        } else {
            if (strlen($_POST['text_name']) < 3 || strlen($_POST['text_name']) > 50) {
                $validation_errors[] = "O nome deve ter entre 3 e 50 caracteres.";
            }
        }

        // gênero
        if(empty($_POST['radio_gender'])){
            $validation_errors[] = "É obrigatório definir o género.";
        }

        // text_birthdate
        if(empty($_POST['text_birthdate'])){
            $validation_errors[] = "Data de nascimento é obrigatória.";
        } else {
            // Verifique se a data de nascimento é válida e anterior a hoje.
            $birthdate = \DateTime::createFromFormat('d-m-Y', $_POST['text_birthdate']);
            if(!$birthdate) {
                $validation_errors[] = "A data de nascimento não está no formato correto.";
            } else {
                $today = new \DateTime();
                if($birthdate >= $today){
                    $validation_errors[] = "A data de nascimento tem que ser anterior ao dia atual.";
                }
            }
        }

        // email
        if(empty($_POST['text_email'])){
            $validation_errors[] = "Email é de preenchimento obrigatório.";
        } else {
            if(!filter_var($_POST['text_email'], FILTER_VALIDATE_EMAIL)){
                $validation_errors[] = "Email não é válido.";
            }
        }

        // telefone
        if(empty($_POST['text_phone'])){
            $validation_errors[] = "Telefone é de preenchimento obrigatório.";
        } else {
            if(!preg_match("/^9{1}\d{8}$/", $_POST['text_phone'])){
                $validation_errors[] = "O telefone deve começar por 9 e ter 9 algarismos no total.";
            }
        }

        // Verifique se há erros de validação para retornar ao formulário.
        if(!empty($validation_errors)){
            $_SESSION['validation_errors'] = $validation_errors;
            $this->new_client_frm();
            return;
        }

         // Verifique se o cliente já existe com o mesmo nome.
        $model = new Agents();
        $results = $model->check_if_client_exists($_POST);

        if($results['status']){

            // Já existe uma pessoa com o mesmo nome para este agente. Retorna um erro do servidor.
            $_SESSION['server_error'] = "Já existe um cliente com esse nome.";
            $this->new_client_frm();
            return;
        }

        // Adicionar novo cliente ao banco de dados
        $model->add_new_client_to_database($_POST);

        // logger - NUNCA COLOCAR DADOS PESSOAIS NOS LOGS! Estou colocando a modo de treino. 
        logger(get_active_user_name() . " - adicionou novo cliente: " . $_POST['text_name'] . ' | ' .$_POST['text_email']);

        // Voltar à página principal de clientes
        $this->my_clients();
    }
 // =======================================================
    public function edit_client($id)
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
        }

        // Verifica se o $id é válido.
        $id_client = aes_decrypt($id);
        if(!$id_client){

            // id_client é inválido
            header('Location: index.php');
        }

        // Carrega o modelo para obter os dados do cliente.
        $model = new Agents();
        $results = $model->get_client_data($id_client);

        // Verifica se os dados do cliente existem.
        if($results['status'] == 'error'){

            // dados de cliente inválidos
            header('Location: index.php');
        }

        $data['client'] = $results['data'];
        $data['client']->birthdate = date('d-m-Y', strtotime($data['client']->birthdate));

        // Exibe o formulário de edição do cliente
        $data['user'] = $_SESSION['user'];
        $data['flatpickr'] = true;

        // Verifica se há erros de validação.
        if(!empty($_SESSION['validation_errors'])){
            $data['validation_errors'] = $_SESSION['validation_errors'];
            unset($_SESSION['validation_errors']);
        }
        // Verifica se há algum erro no servidor.
        if(!empty($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('edit_client_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function edit_client_submit()
    {

        if (!check_session() || $_SESSION['user']->profile != 'agent' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
        }

        // validação de formulário
        $validation_errors = [];

        // text_name
        if (empty($_POST['text_name'])) {
            $validation_errors[] = "Nome é de preenchimento obrigatório.";
        } else {
            if (strlen($_POST['text_name']) < 3 || strlen($_POST['text_name']) > 50) {
                $validation_errors[] = "O nome deve ter entre 3 e 50 caracteres.";
            }
        }

        // gênero
        if(empty($_POST['radio_gender'])){
            $validation_errors[] = "É obrigatório definir o género.";
        }

        // text_birthdate
        if(empty($_POST['text_birthdate'])){
            $validation_errors[] = "Data de nascimento é obrigatória.";
        } else {
            // Verifica se a data de nascimento é válida e anterior a hoje.
            $birthdate = \DateTime::createFromFormat('d-m-Y', $_POST['text_birthdate']);
            if(!$birthdate) {
                $validation_errors[] = "A data de nascimento não está no formato correto.";
            } else {
                $today = new \DateTime();
                if($birthdate >= $today){
                    $validation_errors[] = "A data de nascimento tem que ser anterior ao dia atual.";
                }
            }
        }

        // email
        if(empty($_POST['text_email'])){
            $validation_errors[] = "Email é de preenchimento obrigatório.";
        } else {
            if(!filter_var($_POST['text_email'], FILTER_VALIDATE_EMAIL)){
                $validation_errors[] = "Email não é válido.";
            }
        }

        // telefone
        if(empty($_POST['text_phone'])){
            $validation_errors[] = "Telefone é de preenchimento obrigatório.";
        } else {
            if(!preg_match("/^9{1}\d{8}$/", $_POST['text_phone'])){
                $validation_errors[] = "O telefone deve começar por 9 e ter 9 algarismos no total.";
            }
        }

        // Verifica se o id_client está presente no POST e se é válido.
        if(empty($_POST['id_client'])){
            header('Location: index.php');
        }
        $id_client = aes_decrypt($_POST['id_client']);
        if(!$id_client){
            header('Location: index.php');
        }

        // Verifica se há erros de validação para retornar ao formulário.
        if(!empty($validation_errors)){
            $_SESSION['validation_errors'] = $validation_errors;
            $this->edit_client(aes_encrypt($id_client));
            return;
        }
        // Verifique se existe outro cliente do mesmo agente com o mesmo nome.
        $model = new Agents();
        $results = $model->check_other_client_with_same_name($id_client, $_POST['text_name']);

        // verificar se há...
        if($results['status']){
            $_SESSION['server_error'] = "Já existe outro cliente com o mesmo nome.";
            $this->edit_client(aes_encrypt($id_client));
            return;
        }

        // Atualiza os dados do cliente do agente no banco de dados.
        $model->update_client_data($id_client, $_POST);

        // logger
        logger(get_active_user_name() . " - atualizou dados do cliente ID: " . $id_client);

        // Voltar à página principal de clientes
        $this->my_clients();
    }

    // =======================================================
    public function delete_client($id)
    {
        echo "eliminar" . aes_decrypt($id);
    }
}
