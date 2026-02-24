<?php

namespace scc\Controllers;

use scc\Controllers\BaseController;
use scc\Models\AdminModel;

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
        foreach($results as $client){
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
        header('Content-Disposition: attachment; filename="'. urlencode($filename).'"');
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

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('stats', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }
}
