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
}