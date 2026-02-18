<?php

namespace scc\Controllers;

class Main 
{
    public function index($id)
    {
        echo "Estou dentro do controlador Main - index";
        echo '<br>';
        echo "o id indicado foi $id";
    }
}