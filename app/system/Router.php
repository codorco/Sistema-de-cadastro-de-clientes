<?php

// Define o endereço virtual desta classe (Namespace) para o Autoload do Composer encontrá-la
namespace scc\System;

use scc\Controllers\Main;
use Exception;

class Router 
{
    // Método estático: pode ser chamado como Router::dispatch() sem precisar de "new Router"
    public static function dispatch()
    {
        // 1. Identifica se a requisição é GET (leitura) ou POST (envio de formulário/dados)
        $httpverb = $_SERVER['REQUEST_METHOD'];

        // 2. Define valores padrão: se a URL estiver vazia, o sistema vai para o controller 'main'
        $controller = 'main';
        
        // 3. Define a função padrão: se a URL estiver vazia, o sistema executa o método 'index'
        $method = 'index';

        // 4. Verifica se existe o parâmetro 'ct' na URL (ex: ?ct=clientes)
        if(isset($_GET['ct'])){
            // Se existir, atualiza a variável $controller com o valor da URL
            $controller = $_GET['ct'];
        }

        // 5. Verifica se existe o parâmetro 'mt' na URL (ex: ?mt=adicionar)
        if(isset($_GET['mt'])){
            // Se existir, atualiza a variável $method com o valor da URL
            $method = $_GET['mt'];
        }

        // 6. Cria uma cópia de TUDO que veio na URL para tratar como parâmetros extras
        $parameters = $_GET;

        // 7. Limpeza: verifica se o 'ct' está na lista de parâmetros
        if(key_exists("ct", $parameters)){
            // Remove o 'ct' da lista, pois ele já foi usado para definir o Controller
            unset($parameters['ct']);
        }

        // 8. Limpeza: verifica se o 'mt' está na lista de parâmetros
        if(key_exists("mt", $parameters)){
            // Remove o 'mt' da lista, pois ele já foi usado para definir o Método
            unset($parameters['mt']);
        }

        // tries to instanciate the controller and execute the method

        try {
            $class = "scc\Controllers\\$controller";
            $controller = new $class();
            $controller->$method(...$parameters);
        } catch (Exception $err) {
            die($err->getMessage());
        }


    }
}