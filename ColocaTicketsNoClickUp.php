<?php

require 'vendor/autoload.php';
require 'classes/FreshDesk.php';
require 'classes/ClickUp.php';

//Carrega o .env, adicionando as variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['FRESHDESK_API_KEY', 'FRESHDESK_DOMAIN', 'CLICKUP_API_KEY', 'CLICKUP_LIST_ID']);


$freshDesk = new Freshdesk();
$clickUP = new ClickUp();

$contadorTaskCriada = 0;
$chamadosAbertos = $freshDesk->listarChamados();

//Abrindo arquivo de log
$arquivoLog = fopen(__DIR__ . '/log/logs.txt','a');

if($arquivoLog == false){
    die('Não foi possível abrir o arquivo.');
}

//Setando para usar fuso-horário mais próximo
date_default_timezone_set('America/Fortaleza');

fwrite($arquivoLog, date('Y-m-d H:i:s') . " - Rotina executada\n");

foreach($chamadosAbertos as $chamado){
    if(!$clickUP->existeTask($chamado['id'])){

        $clickUP->criarTask(
            $chamado['id'],
            $chamado['subject'],
            $chamado['description_text'] 
        );
        $contadorTaskCriada++;
        fwrite($arquivoLog, "Task de n° - " . $chamado['id'] . " criada com sucesso!\n");
    }
}

fwrite($arquivoLog, "Tasks criadas: {$contadorTaskCriada}\n\n");
fclose($arquivoLog);