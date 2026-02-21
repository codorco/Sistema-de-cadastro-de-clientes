<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function check_session()
{
    // Verifique se há uma sessão ativa.
    return isset($_SESSION['user']);
}

function logger($message = '', $level = 'info')
{
    // criar canal de log
    $log = new Logger('app_logs');
    $log->pushHandler(new StreamHandler(LOGS_PATH));

    // adicionar mensagem de registro
    switch ($level) {
        case 'info':
            $log->info($message);
            break;
        case 'notice':
            $log->notice($message);
            break;
        case 'warning':
            $log->warning($message);
            break;
        case 'error':
            $log->error($message);
            break;
        case 'critical':
            $log->critical($message);
            break;
        case 'alert':
            $log->alert($message);
            break;
        case 'emergency':
            $log->emergency($message);
            break;
        
        default:
            $log->info($message);
            break;
    }
}

function printData($data, $die = true)
{
    // debug
    echo '<pre>';
    if(is_object($data) || is_array($data)){
        print_r($data);
    } else {
        echo $data;
    }

    if($die){
        die('<br>FIM</br>');
    }
}