<?php

class ClickUp
{
    private $apiKeyClickUp;
    private $listId;


    public function __construct()
    {
        $this->apiKeyClickUp = $_ENV['CLICKUP_API_KEY'];
        $this->listId = $_ENV['CLICKUP_LIST_ID'];

    }

    public function existeTask($id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clickup.com/api/v2/list/{$this->listId}/task",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: {$this->apiKeyClickUp}"
            ]
        ]);

        $resposta = curl_exec($curl);

        curl_close($curl);


        $tarefas = json_decode($resposta, true);

        //Varendo as tasks para ver se tem aquele id
        foreach ($tarefas['tasks'] as $task) {

            if (str_contains($task['name'], "#{$id} -")) {
                return true;
            }
        }

        return false;
    }
    public function criarTask($id, $descricao)
    {
        $task = ["name" => "#{$id} - $descricao", "description" => "N° Chamado no FreshDesk: $id\n\nDescrição:\n$descricao", "status" => "backlog"];

        //Cria o cliente http
        $curl = curl_init();

        //Configurando o cliente
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clickup.com/api/v2/list/{$this->listId}/task",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($task),
            CURLOPT_HTTPHEADER => [
                "Authorization: {$this->apiKeyClickUp}",
                "Content-Type: application/json"
            ]
        ]);

        $respostaAPIClickUp = curl_exec($curl);

        curl_close($curl);

        return json_decode($respostaAPIClickUp, true);
    }
}