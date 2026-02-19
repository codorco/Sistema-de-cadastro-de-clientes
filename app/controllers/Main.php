<?php

namespace scc\Controllers;

class Main 
{
    public function index()
    {
        echo "Estou dentro do controlador Main - index<br>";
        echo 'ok';
        teste();
    }

    public function teste()
    {
        die('aqui no teste!');
    }
}