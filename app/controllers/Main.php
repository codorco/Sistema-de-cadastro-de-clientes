<?php

namespace scc\Controllers;

class Main 
{
    public function index($id = null)
    {
        echo "Estou dentro do controlador Main - index<br>";
        echo 'ok';
    }

    public function teste()
    {
        die('aqui no teste!');
    }
}