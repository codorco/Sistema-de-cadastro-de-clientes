<?php

namespace scc\Controllers;
use scc\Controllers\BaseController;

class Main extends BaseController
{
    public function index()
    {
        $data['nome'] = "Joao";
        $data['apelido'] = "Riveiro";
        $this->view('layouts/html_header');
        $this->view('home', $data);
        $this->view('layouts/html_footer');
    }

}