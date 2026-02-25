<?php

namespace scc\Controllers;

use scc\Controllers\BaseController;
use scc\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use scc\System\SendEmail;

class Admin extends BaseController
{
    // =======================================================
    public function all_clients()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Obtem todos os clientes de todos os agentes.
        $model = new AdminModel();
        $results = $model->get_all_clients();

        $data['user'] = $_SESSION['user'];
        $data['clients'] = $results->results;

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('global_clients', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }
    // =======================================================
    public function export_clients_XLSX()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Obtem todos os clientes de todos os agentes.
        $model = new AdminModel();
        $results = $model->get_all_clients();
        $results = $results->results;

        // Adiciona o cabeçalho à coleção
        $data[] = ['name', 'gender', 'birthdate', 'email', 'phone', 'interests', 'agent', 'created_at'];

        // Coloca todos os clientes na ordem $data
        foreach ($results as $client) {
            $data[] = [
                $client->name,
                $client->gender,
                $client->birthdate,
                $client->email,
                $client->phone,
                $client->interests,
                $client->agent,
                $client->created_at
            ];
        }

        // Armazena os dados no arquivo XLSX.
        $filename = 'output_' . time() . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'dados');
        $spreadsheet->addSheet($worksheet);
        $worksheet->fromArray($data);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        $writer->save('php://output');

        // logger
        logger(get_active_user_name() . " - fez download da lista de clientes para o ficheiro: " . $filename . " | total: " . count($data) - 1 . " registos.");
    }
    // =======================================================
    public function stats()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Obtem os totais dos clientes do agente.
        $model = new AdminModel();
        $data['agents'] = $model->get_agents_clients_stats();

        // Exibe a página de estatísticas
        $data['user'] = $_SESSION['user'];

        // Preparar dados para o Chart.js
        if (count($data['agents']) != 0) {
            $labels_tmp = [];
            $totals_tmp = [];
            foreach ($data['agents'] as $agent) {
                $labels_tmp[] = $agent->agente;
                $totals_tmp[] = $agent->total_clientes;
            }
            $data['chart_labels'] = '["' . implode('","', $labels_tmp) . '"]';
            $data['chart_totals'] = '[' . implode(',', $totals_tmp) . ']';
            $data['chartjs'] = true;
        }

        // Obtem estatísticas globais
        $data['global_stats'] = $model->get_global_stats();

        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('stats', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function create_pdf_report()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // logger
        logger(get_active_user_name() . " - visualizou o PDF com o report estatístico.");

        // Obtem os totais dos clientes do agente e estatísticas globais.
        $model = new AdminModel();
        $agents = $model->get_agents_clients_stats();
        $global_stats = $model->get_global_stats();


        // gera arquivo PDF
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => __DIR__ . '/../../uploads/mpdf' // Aponta para a pasta que criaste
        ]);

        // definir coordenadas iniciais
        $x = 50;    // horizontal
        $y = 50;    // vertical
        $html = "";

        // logotipo e nome do aplicativo
        $html .= '<div style="position: absolute; left: ' . $x . 'px; top: ' . $y . 'px;">';
        $html .= '<img src="assets/images/logo_32.png">';
        $html .= '</div>';
        $html .= '<h2 style="position: absolute; left: ' . ($x + 50) . 'px; top: ' . ($y - 10) . 'px;">' . APP_NAME . '</h2>';

        // separador
        $y += 50;
        $html .= '<div style="position: absolute; left: ' . $x . 'px; top: ' . $y . 'px; width: 700px; height: 1px; background-color: rgb(200,200,200);"></div>';

        // título do relatório
        $y += 20;
        $html .= '<h3 style="position: absolute; left: ' . $x . 'px; top: ' . $y . 'px; width: 700px; text-align: center;">REPORT DE DADOS DE ' . date('d-m-Y') . '</h4>';

        // -----------------------------------------------------------
        // agentes de tabela e totais
        $y += 50;

        $html .= '
            <div style="position: absolute; left: ' . ($x + 90) . 'px; top: ' . $y . 'px; width: 500px;">
                <table style="border: 1px solid black; border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60%; border: 1px solid black; text-align: left;">Agente</th>
                            <th style="width: 40%; border: 1px solid black;">N.º de Clientes</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach ($agents as $agent) {
            $html .=
                '<tr style="border: 1px solid black;">
                    <td style="border: 1px solid black;">' . $agent->agente . '</td>
                    <td style="text-align: center;">' . $agent->total_clientes . '</td>
                </tr>';
            $y += 25;
        }

        $html .= '
            </tbody>
            </table>
            </div>';

        // -----------------------------------------------------------
        // tabela globais
        $y += 50;

        $html .= '
            <div style="position: absolute; left: ' . ($x + 90) . 'px; top: ' . $y . 'px; width: 500px;">
                <table style="border: 1px solid black; border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60%; border: 1px solid black; text-align: left;">Item</th>
                            <th style="width: 40%; border: 1px solid black;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>';

        $html .= '<tr><td>Total agentes:</td><td style="text-align: right;">' . ($global_stats['total_agents']->value ?? 'N/A') . '</td></tr>';
        $html .= '<tr><td>Total clientes:</td><td style="text-align: right;">' . ($global_stats['total_clients']->value ?? 'N/A') . '</td></tr>';
        $html .= '<tr><td>Total clientes removidos:</td><td style="text-align: right;">' . ($global_stats['total_deleted_clients']->value ?? 'N/A') . '</td></tr>';
        $html .= '<tr><td>Média de clientes por agente:</td><td style="text-align: right;">' . sprintf("%.2f", $global_stats['average_clients_per_agent']->value ?? 0) . '</td></tr>';
        $html .= '<tr><td>Idade do cliente mais novo:</td><td style="text-align: right;">' . (($global_stats['younger_client']->value ?? null) ? $global_stats['younger_client']->value . ' anos.' : 'N/A') . '</td></tr>';
        $html .= '<tr><td>Idade do cliente mais velho:</td><td style="text-align: right;">' . (($global_stats['oldest_client']->value ?? null) ? $global_stats['oldest_client']->value . ' anos.' : 'N/A') . '</td></tr>';

        $html .= '<tr><td>Percentagem de homens:</td><td style="text-align: right;">' . (($global_stats['percentage_males']->value ?? null) ? sprintf("%.2f", $global_stats['percentage_males']->value) . ' %' : 'N/A') . '</td></tr>';

        $html .= '<tr><td>Percentagem de mulheres:</td><td style="text-align: right;">' . (($global_stats['percentage_females']->value ?? null) ? sprintf("%.2f", $global_stats['percentage_females']->value) . ' %' : 'N/A') . '</td></tr>';

        $html .= '
                    </tbody>
                </table>
            </div>';

        // -----------------------------------------------------------

        $pdf->WriteHTML($html);

        $pdf->Output();
    }
    // =======================================================
    public function agents_management()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // obtem agentes
        $model = new AdminModel();
        $results = $model->get_agents_for_management();
        $data['agents'] = $results->results;

        $data['user'] = $_SESSION['user'];

        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('agents_management', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function new_agent_frm()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        $data['user'] = $_SESSION['user'];

        // verifica erros de validação
        if (isset($_SESSION['validation_error'])) {
            $data['validation_error'] = $_SESSION['validation_error'];
            unset($_SESSION['validation_error']);
        }

        // verifica erros do servidor
        if (isset($_SESSION['server_error'])) {
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('agents_add_new_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function new_agent_submit()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // verificar se houve uma postagem
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
        }

        // validação de formulário
        $validation_error = null;

        // Verifica se o e-mail do agente é válido.
        if (empty($_POST['text_name']) || !filter_var($_POST['text_name'], FILTER_VALIDATE_EMAIL)) {
            $validation_error = "O nome do agente deve ser um email válido.";
        }

        // Verifica se o perfil é válido.
        $valid_profiles = ['admin', 'agent'];
        if (empty($_POST['select_profile']) || !in_array($_POST['select_profile'], $valid_profiles)) {
            $validation_error = "O perfil selecionado é inválido.";
        }

        if (!empty($validation_error)) {
            $_SESSION['validation_error'] = $validation_error;
            $this->new_agent_frm();
            return;
        }

        // Verifica se já existe um agente com o mesmo nome de usuário.
        $model = new AdminModel();
        $results = $model->check_if_user_exists_with_same_name($_POST['text_name']);

        if ($results) {

            // Existe um agente com esse nome (e-mail).
            $_SESSION['server_error'] = "Já existe um agente com o mesmo nome.";
            $this->new_agent_frm();
            return;
        }

        // Adicionar novo agente ao banco de dados
        $results = $model->add_new_agent($_POST);

        if ($results['status'] == 'error') {

            // logger
            logger(get_active_user_name() . " - aconteceu um erro na criação de novo registo de agente.");
            header('Location: index.php');
        }

        // Enviar e-mail com purl
        $url = explode('?', $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
        $url = $url[0] . '?ct=main&mt=define_password&purl=' . $results['purl'];
        $email = new SendEmail();
        $data = [
            'to' => $_POST['text_name'],
            'link' => $url
        ];

        $results = $email->send_email(APP_NAME . ' Conclusão do registo de agente', 'email_body_new_agent', $data);
        if ($results['status'] == 'error') {

            // logger
            logger(get_active_user_name() . " - não foi possível enviar o email para conclusão do registo: " . $_POST['text_name'] . ' - erro: ' . $results['message'], 'error');
            die($results['message']);
        }

        // logger
        logger(get_active_user_name() . " - enviado com sucesso email para conclusão do registo: " . $_POST['text_name']);

        // Exibe a página de sucesso
        $data['user'] = $_SESSION['user'];
        $data['email'] = $_POST['text_name'];

        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('agents_email_sent', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }
    // =======================================================
    public function edit_agent($id)
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Verifica se o id é válido.
        if (empty($id)) {
            header('Location: index.php');
        }

        $id = aes_decrypt($id);
        if (!$id) {
            header('Location: index.php');
        }

        // Obtém dados do agente.
        $model = new AdminModel();
        $results = $model->get_agent_data($id);

        // Erro de validação
        if (isset($_SESSION['validation_error'])) {
            $data['validation_error'] = $_SESSION['validation_error'];
            unset($_SESSION['validation_error']);
        }

        // Erro do servidor
        if (isset($_SESSION['server_error'])) {
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }


        $data['user'] = $_SESSION['user'];
        $data['agent'] = $results->results[0];

        // Exibe o formulário de edição de agente.
        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('agents_edit_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function edit_agent_submit()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Verifica se houve uma postagem.
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: index.php');
        }

        // Verifica se o id está presente e é válido.
        if (empty($_POST['id'])) {
            header('Location: index.php');
        }

        $id = aes_decrypt($_POST['id']);
        if (!$id) {
            header('Location: index.php');
        }

        // Validação de formulário.
        $validation_error = null;

        // Verifica se o agente é um email válido.
        if (empty($_POST['text_name']) || !filter_var($_POST['text_name'], FILTER_VALIDATE_EMAIL)) {
            $validation_error = "O nome do agente deve ser um email válido.";
        }

        // Verifica se o perfil é válido.
        $valid_profiles = ['admin', 'agent'];
        if (empty($_POST['select_profile']) || !in_array($_POST['select_profile'], $valid_profiles)) {
            $validation_error = "O perfil selecionado é inválido.";
        }

        if (!empty($validation_error)) {
            $_SESSION['validation_error'] = $validation_error;
            $this->edit_agent(aes_encrypt($id));
            return;
        }

        // Verifica se já existe outro agente com o mesmo nome de usuário.
        $model = new AdminModel();
        $results = $model->check_if_another_user_exists_with_same_name($id, $_POST['text_name']);

        if ($results) {

            // Existe outro agente com esse nome (e-mail).
            $_SESSION['server_error'] = "Já existe outro agente com o mesmo nome.";
            $this->edit_agent(aes_encrypt($id));
            return;
        }

        // Edita o agente no banco de dados.
        $results = $model->edit_agent($id, $_POST);

        if ($results->status == 'error') {

            // logger
            logger(get_active_user_name() . " - aconteceu um erro na edição de dados do agente ID: $id", 'error');
            header('Location: index.php');
        } else {

            // logger
            logger(get_active_user_name() . " - editado com sucesso os dados do agente ID: $id - " . $_POST['text_name']);
        }

        // Ir para a página de gerenciamento de agentes.
        $this->agents_management();
    }
// =======================================================
    public function edit_delete($id = '')
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Verifica se o id é válido.
        $id = aes_decrypt($id);
        if(!$id){
            header('Location: index.php');
        }

        // Obtém dados do agente.
        $model = new AdminModel();
        $results = $model->get_agent_data_and_total_clients($id);

        // Exibe a página de confirmação.
        $data['user'] = $_SESSION['user'];
        $data['agent'] = $results->results[0];

        // Exibe o formulário de edição de agente.
        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('agents_delete_confirmation', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function delete_agent_confirm($id = '')
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Verifica se o id é válido.
        $id = aes_decrypt($id);
        if(!$id){
            header('Location: index.php');
        }

        // Deleta o agente do banco de dados (soft delete).
        $model = new AdminModel();
        $results = $model->delete_agent($id);
        
        if($results->status == 'success'){

            // logger
            logger(get_active_user_name() . " - eliminado com sucesso o agente ID: $id");
            
        } else {

            // logger
            logger(get_active_user_name() . " - aconteceu um erro na eliminação do agente ID: $id", 'error');
            
        }

        // Ir para a página de gerenciamento de agentes.
        $this->agents_management();
    }

    // =======================================================
    public function edit_recover($id = '')
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Verifica se o id é válido.
        $id = aes_decrypt($id);
        if(!$id){
            header('Location: index.php');
        }

        // Obtém dados do agente.
        $model = new AdminModel();
        $results = $model->get_agent_data_and_total_clients($id);

        // Exibe a página de confirmação.
        $data['user'] = $_SESSION['user'];
        $data['agent'] = $results->results[0];

        // Exibe o formulário de edição de agente.
        $this->view('layouts/html_header', $data);
        $this->view('navbar', $data);
        $this->view('agents_recover_confirmation', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function recover_agent_confirm($id = '')
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Verifica se o id é válido.
        $id = aes_decrypt($id);
        if(!$id){
            header('Location: index.php');
        }

        // Obtém dados do agente.
        $model = new AdminModel();
        $results = $model->recover_agent($id);
        
        if($results->status == 'success'){

            // logger
            logger(get_active_user_name() . " - recuperado com sucesso o agente ID: $id");
            
        } else {

            // logger
            logger(get_active_user_name() . " - aconteceu um erro na recuperação do agente ID: $id", 'error');
            
        }

        // Ir para a página de gerenciamento de agentes.
        $this->agents_management();
    }
   // =======================================================
    public function export_agents_XLSX()
    {
        // Verifica se a sessão possui um usuário com perfil de administrador.
        if (!check_session() || $_SESSION['user']->profile != 'admin') {
            header('Location: index.php');
        }

        // Obter dados dos agentes.
        $model = new AdminModel();
        $results = $model->get_agents_data_and_total_clients();
        $results = $results->results;

        // Adiciona cabeçalho à coleção.
        $data[] = ['name', 'profile', 'active', 'last login', 'created at', 'updated at', 'deleted at', 'total active clients', 'total deleted clients'];

        // Coloca todos os agentes na coleção de dados $data.
        foreach ($results as $agent) {

            // Remove a primeira propriedade (id).
            unset($agent->id);

            // Adiciona dados como array (o $agent original é um objeto stdClass).
            $data[] = (array)$agent;
        }

        // Armazena os dados no arquivo XLSX.
        $filename = 'output_' . time() . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'dados');
        $spreadsheet->addSheet($worksheet);
        $worksheet->fromArray($data);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        $writer->save('php://output');

        // logger
        logger(get_active_user_name() . " - fez download da lista de agentes para o ficheiro: " . $filename . " | total: " . count($data) - 1 . " registos.");
    }
}
