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

        printData($_POST);

        /* 
        criar a possibilidade dos inputs irem para o formulário novamente? old()
        */
    }

    // =======================================================
    public function edit_client($id)
    {
        echo "editar $id";
    }

    // =======================================================
    public function delete_client($id)
    {
        echo "eliminar $id";
    }
}
