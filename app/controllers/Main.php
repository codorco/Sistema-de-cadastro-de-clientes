<?php

namespace scc\Controllers;
use scc\Controllers\BaseController;
use scc\Models\Agents;

class Main extends BaseController
{
    public function index()
    {

        $model = new Agents();
        $results = $model->get_total_agents();
        printData($results);

        $data['nome'] = "Joao";
        $data['apelido'] = "Riveiro";
        $this->view('layouts/html_header');
        $this->view('home', $data);
        $this->view('layouts/html_footer');
    }

}