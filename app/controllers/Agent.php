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

        if(empty($results['data'])){
            header('Location: index.php');
        }

        // exibe a visualização
        $data['user'] = $_SESSION['user'];
        $data['client'] = $results['data'];

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('delete_client_confirmation', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function delete_client_confirm($id)
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
        }

        // Verifique se o $id é válido.
        $id_client = aes_decrypt($id);
        if(!$id_client){

            // id_client é inválido
            header('Location: index.php');
        }

        // Carrega o modelo para excluir os dados do cliente.
        $model = new Agents();
        $model->delete_client($id_client);

        // logger
        logger(get_current_user() . ' - Eliminado o cliente id: ' . $id_client);

        // Retorna à página principal do agente.
        $this->my_clients();
    }

  // =======================================================
    public function upload_file_frm()
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
        }

        // exibir a visualização
        $data['user'] = $_SESSION['user'];

        // Verifica se há algum erro no servidor.
        if (!empty($_SESSION['server_error'])) {
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('upload_file_with_clients_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

// =======================================================
    public function upload_file_submit()
    {
        if (!check_session() || $_SESSION['user']->profile != 'agent') {
            header('Location: index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
        }

        // Verifica se há algum arquivo carregado.
        if (empty($_FILES) || empty($_FILES['clients_file']['name'])) {
            $_SESSION['server_error'] = "Faça o carregamento de um ficheiro XLSX ou CSV.";
            $this->upload_file_frm();
            return;
        }

        // Verifica se a extensão do arquivo enviado é válida.
        $valid_extensions = ['xlsx', 'csv'];
        $tmp = explode('.', $_FILES['clients_file']['name']);
        $extension = end($tmp);
        if (!in_array($extension, $valid_extensions)) {
            $_SESSION['server_error'] = "O ficheiro deve ser do tipo XLSX ou CSV.";
            $this->upload_file_frm();
            return;
        }

        // Verifica o tamanho do arquivo: máximo = 2 MB
        if ($_FILES['clients_file']['size'] > 2000000) {
            $_SESSION['server_error'] = "O ficheiro deve ter, no máximo, 2 MB.";
            $this->upload_file_frm();
            return;
        }

        // Mover arquivo para o destino final
        $file_path = __DIR__ . '/../../uploads/dados_' . time() . '.' . $extension;
        if (move_uploaded_file($_FILES['clients_file']['tmp_name'], $file_path)) {

           // valida o cabeçalho
            $result = $this->has_valid_header($file_path);
            if ($result) {

                // O cabeçalho está correto. Carregue as informações do arquivo no banco de dados.
                $results = $this->load_file_data_to_database($file_path);
                
            } else {

                // O cabeçalho não está correto..
                $_SESSION['server_error'] = "O ficheiro não tem o header no formato correto.";
                $this->upload_file_frm();
                return;
            }
        } else {
            $_SESSION['server_error'] = "Aconteceu um erro inesperado no carregamento do ficheiro.";
            $this->upload_file_frm();
            return;
        }
    }
// =======================================================
    private function has_valid_header($file_path)
    {
        // valida o arquivo
        $data = [];
        $file_info = pathinfo($file_path);

        if($file_info['extension'] == 'csv'){

            // Abre o arquivo CSV para ler apenas o cabeçalho.
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $reader->setInputEncoding('UTF-8');
            $reader->setDelimiter(';');
            $reader->setEnclosure('');
            $sheet = $reader->load($file_path);
            $data = $sheet->getActiveSheet()->toArray()[0];

        } else if($file_info['extension'] == 'xlsx') {

            // Abre o arquivo XLSX para ler apenas o cabeçalho.
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file_path);
            $data = $spreadsheet->getActiveSheet()->toArray()[0];
        }

        // Verifique se o conteúdo do cabeçalho é válido.
        $valid_header = 'name,gender,birthdate,email,phone,interests';
        return implode(',', $data) == $valid_header ? true : false;
    }

  // =======================================================
    private function load_file_data_to_database($file_path)
    {
        $data = [];
        $file_info = pathinfo($file_path);

        if ($file_info['extension'] == 'csv') {

            // Abre o arquivo CSV
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $reader->setInputEncoding('UTF-8');
            $reader->setDelimiter(';');
            $reader->setEnclosure('');
            $sheet = $reader->load($file_path);
            $data = $sheet->getActiveSheet()->toArray();
        } else if ($file_info['extension'] == 'xlsx') {

            // Abre o arquivo XLSX
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file_path);
            $data = $spreadsheet->getActiveSheet()->toArray();
        }

        // inserir dados no banco de dados
        $model = new Agents();

        // Extraia o cabeçalho de $data
        array_shift($data);

        // cria um círculo para inserir cada registro
        foreach($data as $client){

            // verificar se o cliente já existe no banco de dados
            $exists = $model->check_if_client_exists(['text_name' => $client[0]]);
            if(!$exists['status']){

                // Adicionar cliente ao banco de dados
                $post_data = [
                    'text_name' => $client[0],
                    'radio_gender' => $client[1],
                    'text_birthdate' => $client[2],
                    'text_email' => $client[3],
                    'text_phone' => $client[4],
                    'text_interests' => $client[5],
                ];

                $model->add_new_client_to_database($post_data);
                
            } else {
                
                // O cliente já existe.
            }
        }
    }
}
